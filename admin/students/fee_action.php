<?php
require_once __DIR__ . '/../includes/auth_helper.php';

if (!is_logged_in()) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
    exit();
}

require_once __DIR__ . '/../../database/db_config.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use Mpdf\Mpdf;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Collect Fees
    if ($action === 'collect_fee') {
        $student_id = $_POST['student_id'] ?? 0;
        $amount_paid = floatval($_POST['amount_paid'] ?? 0);
        $payment_method = $_POST['payment_method'] ?? 'Cash';
        $payment_date = $_POST['payment_date'] ?? date('Y-m-d');
        $remarks = trim($_POST['remarks'] ?? '');

        if ($student_id <= 0 || $amount_paid <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid student or amount.']);
            exit();
        }

        try {
            $pdo->beginTransaction();

            // Generate Transaction No: PRC/FEE/YYYY/SERIAL
            $year = date('Y');
            $stmt = $pdo->prepare("SELECT COUNT(id) FROM student_fees WHERE transaction_no LIKE ?");
            $stmt->execute(["PRC/FEE/$year/%"]);
            $count = $stmt->fetchColumn();
            $transaction_no = "PRC/FEE/$year/" . str_pad($count + 1, 4, '0', STR_PAD_LEFT);

            $sql = "INSERT INTO student_fees (student_id, transaction_no, amount_paid, payment_method, payment_date, remarks) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$student_id, $transaction_no, $amount_paid, $payment_method, $payment_date, $remarks]);

            $pdo->commit();
            echo json_encode(['status' => 'success', 'message' => 'Fee collected successfully!', 'transaction_id' => $pdo->lastInsertId()]);
        } catch (PDOException $e) {
            $pdo->rollBack();
            echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
        }
        exit();
    }
}

// Get Fee Summary & History
if (isset($_GET['action']) && $_GET['action'] === 'get_fee_summary') {
    $student_id = $_GET['student_id'] ?? 0;

    try {
        // Get Total Fees and Summary
        $stmt = $pdo->prepare("SELECT total_fees, student_name FROM students WHERE id = ?");
        $stmt->execute([$student_id]);
        $student = $stmt->fetch();

        if (!$student) {
            echo json_encode(['status' => 'error', 'message' => 'Student not found.']);
            exit();
        }

        // Get Total Paid
        $stmt = $pdo->prepare("SELECT SUM(amount_paid) as total_paid FROM student_fees WHERE student_id = ?");
        $stmt->execute([$student_id]);
        $total_paid = $stmt->fetchColumn() ?: 0;

        $summary = [
            'total_fees' => $student['total_fees'],
            'total_paid' => $total_paid,
            'balance' => $student['total_fees'] - $total_paid
        ];

        // Get Transactions
        $stmt = $pdo->prepare("SELECT id, transaction_no, amount_paid, payment_method, payment_date FROM student_fees WHERE student_id = ? ORDER BY payment_date DESC, id DESC");
        $stmt->execute([$student_id]);
        $transactions = $stmt->fetchAll();

        echo json_encode([
            'status' => 'success',
            'summary' => $summary,
            'transactions' => $transactions
        ]);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit();
}

// Generate Receipt (GET request)
if (isset($_GET['action']) && $_GET['action'] === 'generate_receipt') {
    $transaction_id = $_GET['id'] ?? 0;

    try {
        $stmt = $pdo->prepare("SELECT f.*, s.student_name, s.enrollment_no, s.total_fees, c.title as course_title,
                                (SELECT SUM(amount_paid) FROM student_fees WHERE student_id = s.id AND id <= f.id) as paid_so_far
                               FROM student_fees f 
                               JOIN students s ON f.student_id = s.id 
                               LEFT JOIN courses c ON s.course_id = c.id
                               WHERE f.id = ?");
        $stmt->execute([$transaction_id]);
        $data = $stmt->fetch();

        if (!$data) {
            die("Transaction not found.");
        }

        $balance = $data['total_fees'] - $data['paid_so_far'];

        $mpdf = new Mpdf([
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 10,
            'margin_bottom' => 10,
            'format' => 'A5-L' // Professional A5 Landscape for receipts
        ]);

        $html = '
        <style>
            .receipt-box { border: 2px solid #333; padding: 20px; font-family: sans-serif; }
            .header { text-align: center; border-bottom: 2px solid #eee; padding-bottom: 10px; margin-bottom: 20px; }
            .header h1 { margin: 0; color: #28a745; font-size: 24px; }
            .header p { margin: 5px 0; font-size: 12px; color: #666; }
            .info-row { margin-bottom: 10px; }
            .label { font-weight: bold; width: 120px; display: inline-block; }
            .value { border-bottom: 1px dotted #999; display: inline-block; width: 300px; }
            .details-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
            .details-table th { background: #f8f8f8; text-align: left; padding: 8px; border: 1px solid #ddd; }
            .details-table td { padding: 8px; border: 1px solid #ddd; }
            .footer { margin-top: 30px; text-align: right; }
            .signature { display: inline-block; width: 150px; border-top: 1px solid #333; text-align: center; padding-top: 5px; margin-top: 40px; }
        </style>
        <div class="receipt-box">
            <div class="header">
                <h1>PRAYAG COMPUTER</h1>
                <p>Advanced Computer & Professional Training Center</p>
                <p>ISO Certified Institute | Visit: prayagcomputer.in</p>
            </div>
            
            <table width="100%">
                <tr>
                    <td><span class="label">Receipt No:</span> <span style="font-weight:bold;">' . $data['transaction_no'] . '</span></td>
                    <td align="right"><span class="label">Date:</span> ' . date('d-m-Y', strtotime($data['payment_date'])) . '</td>
                </tr>
            </table>

            <div style="margin-top:20px;">
                <div class="info-row">
                    <span class="label">Student Name:</span> <span class="value">' . strtoupper($data['student_name']) . '</span>
                </div>
                <div class="info-row">
                    <span class="label">Enrollment No:</span> <span class="value">' . $data['enrollment_no'] . '</span>
                </div>
                <div class="info-row">
                    <span class="label">Course:</span> <span class="value">' . $data['course_title'] . '</span>
                </div>
            </div>

            <table class="details-table">
                <thead>
                    <tr>
                        <th width="60%">Description</th>
                        <th width="40%">Amount (INR)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Being Monthly/Full Fee Payment via ' . $data['payment_method'] . ' ' . ($data['remarks'] ? '('.$data['remarks'].')' : '') . '</td>
                        <td align="right"><b>₹' . number_format($data['amount_paid'], 2) . '</b></td>
                    </tr>
                </tbody>
            </table>

            <div style="margin-top:15px; font-size:12px;">
                <table width="100%">
                    <tr>
                        <td width="50%">
                            Total Fees: ₹' . number_format($data['total_fees'], 2) . '<br>
                            Total Paid: ₹' . number_format($data['paid_so_far'], 2) . '<br>
                            <b>Balance Due: ₹' . number_format($balance, 2) . '</b>
                        </td>
                        <td width="50%" align="right">
                            <div class="signature">Authorized Signatory</div>
                        </td>
                    </tr>
                </table>
            </div>
            
            <p style="text-align:center; font-size:10px; color:#999; margin-top:20px;">This is a computer generated receipt. No signature required.</p>
        </div>';

        $mpdf->WriteHTML($html);
        $filename = "Receipt_" . $data['transaction_no'] . ".pdf";
        $mpdf->Output($filename, 'I'); // 'I' for inline display in browser
    } catch (Exception $e) {
        die("Error generating PDF: " . $e->getMessage());
    }
    exit();
}

echo json_encode(['status' => 'error', 'message' => 'Invalid request.']);
exit();
?>

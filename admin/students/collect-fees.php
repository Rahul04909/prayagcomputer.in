<?php include '../header.php'; ?>

<link rel="stylesheet" href="../assets/css/loader.css">

<style>
    .fee-card { border: none; border-radius: 15px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); }
    .summary-card { padding: 20px; border-radius: 12px; border: none; transition: all 0.3s; }
    .summary-card:hover { transform: translateY(-5px); }
    .bg-total { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff; }
    .bg-paid { background: linear-gradient(135deg, #28a745 0%, #218838 100%); color: #fff; }
    .bg-pending { background: linear-gradient(135deg, #ff9a9e 0%, #fecfef 100%); color: #d32f2f; }
    .bg-pending h3 { color: #d32f2f; }
    .transaction-table { border-radius: 10px; overflow: hidden; }
    #loader-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(255,255,255,0.7); display: none; align-items: center; justify-content: center; z-index: 9999; }

    /* Professional Select2 Styling */
    .select2-container--bootstrap4 .select2-selection--single {
        border-radius: 30px !important;
        border: 1px solid #e0e0e0 !important;
        height: 42px !important;
        padding-top: 6px !important;
        padding-left: 15px !important;
        transition: all 0.3s;
        box-shadow: inset 0 1px 2px rgba(0,0,0,0.02);
    }
    .select2-container--bootstrap4.select2-container--focus .select2-selection--single {
        border-color: #28a745 !important;
        box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.1) !important;
    }
    .select2-container--bootstrap4 .select2-selection__placeholder {
        color: #999 !important;
    }
    .select2-container--bootstrap4 .select2-selection--single .select2-selection__arrow {
        top: 8px !important;
        right: 12px !important;
    }
</style>

<div id="loader-overlay">
    <div class="loader"></div>
</div>

<div class="content-header p-0"></div>

<?php 
// Fetch Students for Search Dropdown
try {
    $stu_stmt = $pdo->query("SELECT id, student_name, enrollment_no FROM students WHERE status = 1 ORDER BY student_name ASC");
    $all_students = $stu_stmt->fetchAll();
} catch (PDOException $e) {
    $all_students = [];
}
?>

<section class="content mb-5">
    <div class="container-fluid">
        <!-- Student Selection -->
        <div class="card fee-card mb-4">
            <div class="card-body p-4">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h4 style="font-weight:800; color:#333;"><i class="fas fa-money-bill-wave text-success mr-2"></i> Collect Student Fees</h4>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group mb-0">
                            <?php $get_student_id = $_GET['student_id'] ?? ''; ?>
                            <select id="studentSelect" class="form-control select2" style="width: 100%;">
                                <option value="">Select Student (Name or Enrollment No)</option>
                                <?php foreach ($all_students as $s): ?>
                                    <option value="<?= $s['id'] ?>" <?= ($get_student_id == $s['id']) ? 'selected' : '' ?>><?= htmlspecialchars($s['student_name']) ?> (<?= $s['enrollment_no'] ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dynamic Content (Initially Hidden) -->
        <div id="feeContent" style="display:none;">
            <!-- Summary Cards -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card summary-card bg-total shadow-sm">
                        <small>Total Course Fees</small>
                        <h3 id="totalFees" class="mb-0">₹0.00</h3>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card summary-card bg-paid shadow-sm">
                        <small>Total Amount Paid</small>
                        <h3 id="totalPaid" class="mb-0">₹0.00</h3>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card summary-card bg-pending shadow-sm">
                        <small>Pending Balance</small>
                        <h3 id="pendingBalance" class="mb-0">₹0.00</h3>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Payment Form -->
                <div class="col-lg-5">
                    <div class="card fee-card">
                        <div class="card-header bg-white border-bottom p-3">
                            <h5 class="mb-0 font-weight-bold">Collect Payment</h5>
                        </div>
                        <div class="card-body">
                            <form id="paymentForm">
                                <input type="hidden" name="student_id" id="hiddenStudentId">
                                <input type="hidden" name="action" value="collect_fee">
                                
                                <div class="form-group mb-3">
                                    <label class="font-weight-600">Amount Paid (₹) <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" name="amount_paid" class="form-control form-control-lg" required placeholder="0.00">
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label>Payment Method</label>
                                            <select name="payment_method" class="form-control">
                                                <option value="Cash">Cash</option>
                                                <option value="Online">Online</option>
                                                <option value="Bank Transfer">Bank Transfer</option>
                                                <option value="Other">Other</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label>Payment Date</label>
                                            <input type="date" name="payment_date" class="form-control" value="<?= date('Y-m-d') ?>">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group mb-4">
                                    <label>Remarks</label>
                                    <textarea name="remarks" class="form-control" rows="2" placeholder="Monthly Fee / Installment..."></textarea>
                                </div>
                                <button type="submit" class="btn btn-success btn-block btn-lg shadow-sm">
                                    <i class="fas fa-check-circle mr-2"></i> Submit Payment
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Transaction History -->
                <div class="col-lg-7">
                    <div class="card fee-card">
                        <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 font-weight-bold">Transaction History</h5>
                            <button class="btn btn-sm btn-outline-primary" onclick="loadFeeDetails()"><i class="fas fa-sync-alt"></i></button>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0 transaction-table">
                                    <thead class="bg-light">
                                        <tr class="text-xs text-muted text-uppercase">
                                            <th class="pl-3">Txn No</th>
                                            <th>Date</th>
                                            <th>Amount</th>
                                            <th>Method</th>
                                            <th class="text-right pr-3">Receipt</th>
                                        </tr>
                                    </thead>
                                    <tbody id="txnHistory">
                                        <!-- Transactions will load here via AJAX -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Initial Placeholder -->
        <div id="selectPlaceholder" class="text-center py-5">
            <i class="fas fa-user-circle fa-5x text-light mb-3"></i>
            <h5 class="text-muted">Please select a student to manage fees</h5>
        </div>
    </div>
</section>

<!-- Include Select2 -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    $('.select2').select2({
        theme: 'bootstrap4',
        placeholder: "Search student..."
    });

    $('#studentSelect').on('change', function() {
        loadFeeDetails();
    });

    if ($('#studentSelect').val()) {
        loadFeeDetails();
    }

    $('#paymentForm').on('submit', function(e) {
        e.preventDefault();
        $('#loader-overlay').css('display', 'flex');
        $.ajax({
            url: 'fee_action.php',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                $('#loader-overlay').hide();
                if(response.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Payment Successful',
                        text: response.message,
                        showCancelButton: true,
                        confirmButtonText: 'Print Receipt',
                        cancelButtonText: 'Done'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.open('fee_action.php?action=generate_receipt&id=' + response.transaction_id, '_blank');
                        }
                        $('#paymentForm')[0].reset();
                        loadFeeDetails();
                    });
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            },
            error: function() {
                $('#loader-overlay').hide();
                Swal.fire('Error', 'Connection failed', 'error');
            }
        });
    });
});

function loadFeeDetails() {
    let studentId = $('#studentSelect').val();
    if(!studentId) {
        $('#feeContent').hide();
        $('#selectPlaceholder').show();
        return;
    }

    $('#loader-overlay').css('display', 'flex');
    $('#hiddenStudentId').val(studentId);

    // I'll reuse fee_action.php for fetching details - adding a GET handler there in next step
    $.ajax({
        url: 'fee_action.php',
        type: 'GET',
        data: { action: 'get_fee_summary', student_id: studentId },
        dataType: 'json',
        success: function(response) {
            $('#loader-overlay').hide();
            if(response.status === 'success') {
                $('#selectPlaceholder').hide();
                $('#feeContent').show();
                
                $('#totalFees').text('₹' + parseFloat(response.summary.total_fees).toLocaleString('en-IN', {minimumFractionDigits: 2}));
                $('#totalPaid').text('₹' + parseFloat(response.summary.total_paid).toLocaleString('en-IN', {minimumFractionDigits: 2}));
                $('#pendingBalance').text('₹' + parseFloat(response.summary.balance).toLocaleString('en-IN', {minimumFractionDigits: 2}));

                let html = '';
                if(response.transactions.length === 0) {
                    html = '<tr><td colspan="5" class="text-center py-4 text-muted">No transactions found.</td></tr>';
                } else {
                    response.transactions.forEach(txn => {
                        html += `
                            <tr>
                                <td class="pl-3"><small class="font-weight-bold text-primary">${txn.transaction_no}</small></td>
                                <td><small>${txn.payment_date}</small></td>
                                <td><small class="font-weight-bold">₹${parseFloat(txn.amount_paid).toLocaleString('en-IN', {minimumFractionDigits: 2})}</small></td>
                                <td><span class="badge badge-light border">${txn.payment_method}</span></td>
                                <td class="text-right pr-3">
                                    <a href="fee_action.php?action=generate_receipt&id=${txn.id}" target="_blank" class="btn btn-xs btn-outline-success">
                                        <i class="fas fa-print"></i>
                                    </a>
                                </td>
                            </tr>
                        `;
                    });
                }
                $('#txnHistory').html(html);
            } else {
                Swal.fire('Error', response.message, 'error');
            }
        },
        error: function() {
            $('#loader-overlay').hide();
            Swal.fire('Error', 'Failed to fetch student details', 'error');
        }
    });
}
</script>

<?php include '../footer.php'; ?>

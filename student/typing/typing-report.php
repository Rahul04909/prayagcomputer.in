<?php
require_once __DIR__ . '/../includes/auth_helper.php';

if (!$auth->isLogged()) {
    header('Location: ../login.php');
    exit();
}

$result_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$result_id) {
    die("Invalid Report ID");
}

$student = get_current_student_data();
$student_id = $student['id'];

try {
    $stmt = $pdo->prepare("SELECT r.*, t.title, t.language, t.content 
                           FROM typing_results r 
                           JOIN typing_tests t ON r.test_id = t.id 
                           WHERE r.id = ? AND r.student_id = ?");
    $stmt->execute([$result_id, $student_id]);
    $result = $stmt->fetch();

    if (!$result) {
        die("Report not found or access denied.");
    }
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

$typed = trim($result['typed_text'] ?? '');
$original = trim(str_replace(["\r", "\n", "\t", "&nbsp;", "\xc2\xa0"], ' ', strip_tags(html_entity_decode($result['content']))));

// Splitting words for comparison
$origWords = preg_split('/\s+/', $original, -1, PREG_SPLIT_NO_EMPTY);
$typedWords = preg_split('/\s+/', $typed, -1, PREG_SPLIT_NO_EMPTY);

$evaluatedHtml = '';
$totalTypedCount = count($typedWords);

for ($i = 0; $i < count($origWords); $i++) {
    $ow = $origWords[$i];
    if ($i < $totalTypedCount) {
        $tw = $typedWords[$i];
        if ($tw === $ow) {
            $evaluatedHtml .= '<span class="text-success font-weight-bold">' . htmlspecialchars($tw) . '</span> ';
        } else {
            // Evaluated as wrong
            $evaluatedHtml .= '<span class="text-danger" style="text-decoration:line-through; font-weight:bold;" title="Correct: '.htmlspecialchars($ow).'">' . htmlspecialchars($tw) . '</span> ';
        }
    } else {
        // Not typed yet
        $evaluatedHtml .= '<span class="text-muted" style="opacity:0.6;">' . htmlspecialchars($ow) . '</span> ';
    }
}

// Any extra typed words
if ($totalTypedCount > count($origWords)) {
    for ($i = count($origWords); $i < $totalTypedCount; $i++) {
        $evaluatedHtml .= '<span class="text-danger" style="text-decoration:line-through; font-weight:bold;" title="Extra word">' . htmlspecialchars($typedWords[$i]) . '</span> ';
    }
}

$duration_min = $result['test_time'] / 60;
$time_spent_str = floor($result['test_time'] / 60) . ' min ' . ($result['test_time'] % 60) . ' sec';

// Base Font based on language
$fontFamily = "'Inter', sans-serif";
if ($result['language'] === 'Hindi') {
    $fontFamily = "'Kruti Dev 010', sans-serif";
} else if ($result['language'] === 'Punjabi') {
    $fontFamily = "'Raavi', sans-serif";
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Typing Report - <?= htmlspecialchars($student['name']) ?></title>
    <!-- AdminLTE & Bootstrap -->
    <link rel="stylesheet" href="../../admin/assets/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="../../admin/assets/plugins/fontawesome-free/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background: #eef2f5; color: #333; }
        .report-container { max-width: 900px; margin: 40px auto; background: #fff; padding: 40px; box-shadow: 0 5px 20px rgba(0,0,0,0.05); border-radius: 12px; }
        
        .header-section { display: flex; align-items: center; justify-content: space-between; border-bottom: 2px solid #28a745; padding-bottom: 20px; margin-bottom: 30px; }
        .header-logo { max-height: 80px; }
        .institute-title { font-size: 28px; font-weight: 800; color: #28a745; letter-spacing: -0.5px; margin: 0; }
        .institute-sub { color: #666; font-size: 14px; font-weight: 500; }
        
        .candidate-box { border: 1px solid #e0e0e0; border-radius: 8px; padding: 20px; background: #fcfcfc; margin-bottom: 30px; }
        .candidate-title { font-weight: 700; color: #444; border-bottom: 1px solid #ddd; padding-bottom: 10px; margin-bottom: 15px; font-size: 18px; text-transform: uppercase; letter-spacing: 1px; }
        .info-row { display: flex; margin-bottom: 10px; }
        .info-label { width: 140px; font-weight: 600; color: #555; }
        .info-val { font-weight: 700; color: #111; }
        
        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin-bottom: 30px; }
        .stat-card { border: 1px solid #eaedf4; border-radius: 8px; padding: 20px 10px; text-align: center; background: #fff; box-shadow: 0 2px 8px rgba(0,0,0,0.02); }
        .stat-val { font-size: 32px; font-weight: 800; color: #28a745; line-height: 1; margin-bottom: 5px; }
        .stat-lbl { font-size: 12px; text-transform: uppercase; font-weight: 700; color: #888; letter-spacing: 0.5px; }
        
        .text-evaluation-box { border: 1px solid #ccc; border-radius: 8px; padding: 25px; background: #fafafa; font-size: 16px; line-height: 2; margin-bottom: 30px; word-wrap: break-word; }
        .eval-title { font-weight: 700; color: #333; margin-bottom: 15px; font-size: 18px; display: flex; justify-content: space-between; align-items: center; }
        .lang-text { font-family: <?= $fontFamily ?> !important; font-size: <?= in_array($result['language'], ['Hindi', 'Punjabi']) ? '20' : '16' ?>px; }
        
        .legend-box { display: inline-flex; gap: 15px; font-size: 12px; }
        .legend-item { display: flex; align-items: center; gap: 5px; font-weight: 600; color: #555; }
        .legend-color { width: 12px; height: 12px; border-radius: 2px; }
        
        .print-btn-wrapper { text-align: center; margin-top: 40px; }
        .btn-print { background: #28a745; color: #fff; border: none; padding: 12px 30px; font-size: 16px; font-weight: 700; border-radius: 6px; cursor: pointer; transition: 0.2s; box-shadow: 0 4px 10px rgba(40,167,69,0.3); }
        .btn-print:hover { background: #218838; transform: translateY(-2px); }
        .btn-back { background: #6c757d; color: #fff; border: none; padding: 12px 30px; font-size: 16px; font-weight: 700; border-radius: 6px; cursor: pointer; transition: 0.2s; text-decoration: none; display: inline-block; margin-right: 15px; }
        .btn-back:hover { background: #5a6268; color: #fff; }

        @font-face {
            font-family: 'Kruti Dev 010';
            src: url('../assets/fonts/krutidev-010-hindi-font-download.ttf') format('truetype');
        }
        @font-face {
            font-family: 'Raavi';
            src: url('../assets/fonts/raavi.ttf') format('truetype');
        }

        @media print {
            body { background: #fff; }
            .report-container { box-shadow: none; margin: 0; padding: 20px; max-width: 100%; border: none; }
            .print-btn-wrapper { display: none; }
            .stat-card { border: 1px solid #ccc; }
        }
    </style>
</head>
<body>

<div class="report-container">
    <!-- Header -->
    <div class="header-section">
        <div>
            <h1 class="institute-title">Prayag Computer Institute</h1>
            <div class="institute-sub">Performance Report - Typing Module</div>
        </div>
        <div>
            <!-- You can dynamically inject logo here if needed -->
            <img src="../../assets/img/logo.png" onerror="this.style.display='none';" class="header-logo" alt="Logo">
        </div>
    </div>

    <!-- Candidate Info -->
    <div class="candidate-box">
        <div class="candidate-title"><i class="fas fa-user-graduate mr-2"></i> Candidate Details</div>
        <div class="row">
            <div class="col-md-6">
                <div class="info-row"><div class="info-label">Name:</div> <div class="info-val"><?= htmlspecialchars($student['name']) ?></div></div>
                <div class="info-row"><div class="info-label">Enrollment No:</div> <div class="info-val"><?= htmlspecialchars($student['enrollment_no'] ?? 'N/A') ?></div></div>
                <div class="info-row"><div class="info-label">Exam Center:</div> <div class="info-val">Prayag Computer Institute</div></div>
            </div>
            <div class="col-md-6">
                <div class="info-row"><div class="info-label">Test Title:</div> <div class="info-val"><?= htmlspecialchars($result['title']) ?></div></div>
                <div class="info-row"><div class="info-label">Language:</div> <div class="info-val"><?= htmlspecialchars($result['language']) ?></div></div>
                <div class="info-row"><div class="info-label">Date & Time:</div> <div class="info-val"><?= date('d M Y, h:i A', strtotime($result['test_date'])) ?></div></div>
                <div class="info-row"><div class="info-label">Time Spent:</div> <div class="info-val"><?= $time_spent_str ?></div></div>
            </div>
        </div>
    </div>

    <!-- Scores Grid -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-val"><?= $result['wpm'] ?></div>
            <div class="stat-lbl">Gross Speed (WPM)</div>
        </div>
        <div class="stat-card" style="border-bottom: 3px solid #28a745;">
            <div class="stat-val text-dark"><?= $result['net_wpm'] ?></div>
            <div class="stat-lbl text-dark">Net Speed (WPM)</div>
        </div>
        <div class="stat-card">
            <div class="stat-val text-primary"><?= $result['accuracy'] ?>%</div>
            <div class="stat-lbl">Accuracy</div>
        </div>
        <div class="stat-card">
            <div class="stat-val text-danger"><?= $result['errors'] ?></div>
            <div class="stat-lbl">Mistakes (Errors)</div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-6">
            <div class="info-row"><div class="info-label">Total Words Typed:</div> <div class="info-val"><?= $result['total_words'] ?></div></div>
        </div>
        <div class="col-6">
            <div class="info-row"><div class="info-label">Correct Words:</div> <div class="info-val text-success"><?= $result['correct_words'] ?></div></div>
        </div>
    </div>

    <!-- Text Evaluation -->
    <div class="text-evaluation-box">
        <div class="eval-title">
            <span><i class="fas fa-keyboard mr-2"></i> Evaluated Text</span>
            <div class="legend-box">
                <div class="legend-item"><div class="legend-color bg-success"></div> Correct Word</div>
                <div class="legend-item"><div class="legend-color bg-danger"></div> Incorrect / Extra</div>
                <div class="legend-item"><div class="legend-color" style="background:#ddd;"></div> Missing / Skipped</div>
            </div>
        </div>
        
        <div class="lang-text">
            <?= $evaluatedHtml ?>
        </div>
        
        <?php if(empty($typed)): ?>
            <div class="alert alert-warning mt-3"><i class="fas fa-exclamation-triangle"></i> No typed content found. The candidate may have submitted without typing.</div>
        <?php endif; ?>
    </div>

    <!-- Signatures (For Print) -->
    <div class="row mt-5 pt-4 d-none d-print-flex">
        <div class="col-6 text-center">
            <div style="border-top: 1px solid #000; width: 60%; margin: 0 auto; padding-top: 10px; font-weight: bold;">Candidate Signature</div>
        </div>
        <div class="col-6 text-center">
            <div style="border-top: 1px solid #000; width: 60%; margin: 0 auto; padding-top: 10px; font-weight: bold;">Invigilator / Instructor Signature</div>
        </div>
    </div>

    <!-- Actions -->
    <div class="print-btn-wrapper">
        <a href="english.php" class="btn-back"><i class="fas fa-arrow-left mr-2"></i> Back to Dashboard</a>
        <button onclick="window.print()" class="btn-print"><i class="fas fa-print mr-2"></i> Print Report</button>
    </div>
</div>

</body>
</html>

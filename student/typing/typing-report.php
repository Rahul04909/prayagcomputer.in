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

// Build detailed word-by-word evaluation
$totalTypedCount = count($typedWords);
$totalOrigCount = count($origWords);
$correctCount = 0;
$wrongCount = 0;
$skippedCount = 0;
$extraCount = 0;
$totalCorrectChars = 0;
$totalCharsCompared = 0;

// Build evaluated HTML with character-level coloring
$evaluatedHtml = '';
$mistakesList = []; // Collect mistakes for detailed table

for ($i = 0; $i < $totalOrigCount; $i++) {
    $ow = $origWords[$i];
    if ($i < $totalTypedCount) {
        $tw = $typedWords[$i];
        $totalCharsCompared += max(strlen($ow), strlen($tw));
        if ($tw === $ow) {
            $correctCount++;
            $totalCorrectChars += strlen($ow);
            $evaluatedHtml .= '<span class="eval-word eval-correct">' . htmlspecialchars($tw) . '</span> ';
        } else {
            $wrongCount++;
            // Character-level comparison for correct char count
            $maxLen = max(strlen($ow), strlen($tw));
            for ($c = 0; $c < $maxLen; $c++) {
                if ($c < strlen($tw) && $c < strlen($ow) && $tw[$c] === $ow[$c]) {
                    $totalCorrectChars++;
                }
            }
            // Build char-level colored HTML
            $charHtml = '';
            for ($c = 0; $c < strlen($ow); $c++) {
                if ($c < strlen($tw)) {
                    if ($tw[$c] === $ow[$c]) {
                        $charHtml .= '<span class="char-correct">' . htmlspecialchars($ow[$c]) . '</span>';
                    } else {
                        $charHtml .= '<span class="char-wrong">' . htmlspecialchars($ow[$c]) . '</span>';
                    }
                } else {
                    $charHtml .= '<span class="char-missing">' . htmlspecialchars($ow[$c]) . '</span>';
                }
            }
            if (strlen($tw) > strlen($ow)) {
                $extra = substr($tw, strlen($ow));
                $charHtml .= '<span class="char-extra">' . htmlspecialchars($extra) . '</span>';
            }
            $evaluatedHtml .= '<span class="eval-word eval-wrong">' . $charHtml . '</span> ';
            $mistakesList[] = ['pos' => $i + 1, 'original' => $ow, 'typed' => $tw];
        }
    } else {
        $skippedCount++;
        $evaluatedHtml .= '<span class="eval-word eval-skipped">' . htmlspecialchars($ow) . '</span> ';
    }
}

// Any extra typed words beyond original
if ($totalTypedCount > $totalOrigCount) {
    $extraCount = $totalTypedCount - $totalOrigCount;
    for ($i = $totalOrigCount; $i < $totalTypedCount; $i++) {
        $evaluatedHtml .= '<span class="eval-word eval-extra">' . htmlspecialchars($typedWords[$i]) . '</span> ';
        $mistakesList[] = ['pos' => $i + 1, 'original' => '—', 'typed' => $typedWords[$i]];
    }
}

$duration_min = $result['test_time'] / 60;
$time_spent_str = floor($result['test_time'] / 60) . ' min ' . ($result['test_time'] % 60) . ' sec';

// Performance grade calculation
$accuracy = (float)$result['accuracy'];
$wpm = (float)$result['wpm'];
$netWpm = (float)$result['net_wpm'];

if ($accuracy >= 95 && $netWpm >= 40) $grade = 'A+';
elseif ($accuracy >= 90 && $netWpm >= 35) $grade = 'A';
elseif ($accuracy >= 85 && $netWpm >= 30) $grade = 'B+';
elseif ($accuracy >= 80 && $netWpm >= 25) $grade = 'B';
elseif ($accuracy >= 70 && $netWpm >= 20) $grade = 'C';
else $grade = 'D';

$gradeColors = [
    'A+' => '#00c853', 'A' => '#2e7d32', 'B+' => '#1565c0',
    'B' => '#f57f17', 'C' => '#e65100', 'D' => '#c62828'
];
$gradeColor = $gradeColors[$grade] ?? '#555';

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
    <link rel="stylesheet" href="../../admin/assets/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="../../admin/assets/plugins/fontawesome-free/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Roboto+Mono:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #1a73e8;
            --success: #00c853;
            --danger: #c62828;
            --warning: #f57f17;
            --dark: #1a1a2e;
            --gray-50: #f8fafc;
            --gray-100: #f1f5f9;
            --gray-200: #e2e8f0;
            --gray-300: #cbd5e1;
            --gray-400: #94a3b8;
            --gray-500: #64748b;
            --gray-600: #475569;
            --gray-700: #334155;
            --gray-800: #1e293b;
            --gray-900: #0f172a;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        
        body { 
            font-family: 'Inter', sans-serif; 
            background: var(--gray-100); 
            color: var(--gray-800);
            min-height: 100vh;
        }

        /* ── Hero Header ── */
        .report-hero {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 40%, #0f3460 80%, #1a73e8 100%);
            color: #fff;
            padding: 30px 40px 25px;
            position: relative;
            overflow: hidden;
        }
        .report-hero::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(26,115,232,0.15) 0%, transparent 70%);
            border-radius: 50%;
        }
        .report-hero::after {
            content: '';
            position: absolute;
            bottom: -40%;
            left: 10%;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(0,200,83,0.1) 0%, transparent 70%);
            border-radius: 50%;
        }
        .hero-inner {
            position: relative;
            z-index: 2;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .hero-left { display: flex; align-items: center; gap: 25px; }
        .hero-logo {
            width: 70px;
            height: 70px;
            border-radius: 14px;
            background: rgba(255,255,255,0.12);
            border: 2px solid rgba(255,255,255,0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            overflow: hidden;
        }
        .hero-logo img { max-width: 100%; max-height: 100%; object-fit: contain; }
        .hero-title { font-size: 26px; font-weight: 800; letter-spacing: -0.5px; margin-bottom: 4px; }
        .hero-subtitle { font-size: 14px; font-weight: 500; color: rgba(255,255,255,0.7); letter-spacing: 0.5px; }
        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(255,255,255,0.12);
            border: 1px solid rgba(255,255,255,0.2);
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            margin-top: 8px;
            backdrop-filter: blur(10px);
        }
        .hero-right { text-align: right; }
        .hero-report-id { font-size: 12px; color: rgba(255,255,255,0.5); font-weight: 600; letter-spacing: 1px; text-transform: uppercase; }
        .hero-date { font-size: 14px; font-weight: 600; color: rgba(255,255,255,0.85); margin-top: 4px; }

        /* ── Content Body ── */
        .report-body {
            padding: 0 40px 40px;
            max-width: 100%;
        }

        /* ── Grade Banner ── */
        .grade-strip {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #fff;
            border-radius: 0 0 16px 16px;
            padding: 18px 30px;
            margin-bottom: 25px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.06);
            border-top: 3px solid var(--primary);
        }
        .grade-box {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .grade-circle {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            font-weight: 900;
            color: #fff;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        .grade-label { font-size: 14px; font-weight: 700; color: var(--gray-700); }
        .grade-desc { font-size: 12px; color: var(--gray-400); font-weight: 500; margin-top: 2px; }
        .candidate-strip {
            display: flex;
            gap: 30px;
            font-size: 13px;
        }
        .cand-item {
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .cand-label { color: var(--gray-400); font-weight: 600; }
        .cand-val { color: var(--gray-800); font-weight: 700; }

        /* ── Stats Cards Row ── */
        .stats-row {
            display: grid;
            grid-template-columns: repeat(6, 1fr);
            gap: 16px;
            margin-bottom: 25px;
        }
        .stat-card {
            background: #fff;
            border-radius: 12px;
            padding: 22px 18px;
            text-align: center;
            box-shadow: 0 2px 12px rgba(0,0,0,0.04);
            border: 1px solid var(--gray-200);
            position: relative;
            overflow: hidden;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.08);
        }
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
        }
        .stat-card.card-wpm::before { background: linear-gradient(90deg, #1a73e8, #4fc3f7); }
        .stat-card.card-net::before { background: linear-gradient(90deg, #00c853, #69f0ae); }
        .stat-card.card-acc::before { background: linear-gradient(90deg, #7c4dff, #b388ff); }
        .stat-card.card-err::before { background: linear-gradient(90deg, #c62828, #ef5350); }
        .stat-card.card-words::before { background: linear-gradient(90deg, #f57f17, #ffd54f); }
        .stat-card.card-time::before { background: linear-gradient(90deg, #00838f, #4dd0e1); }

        .stat-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 12px;
            font-size: 16px;
        }
        .stat-card.card-wpm .stat-icon { background: #e3f2fd; color: #1a73e8; }
        .stat-card.card-net .stat-icon { background: #e8f5e9; color: #2e7d32; }
        .stat-card.card-acc .stat-icon { background: #ede7f6; color: #7c4dff; }
        .stat-card.card-err .stat-icon { background: #ffebee; color: #c62828; }
        .stat-card.card-words .stat-icon { background: #fff8e1; color: #f57f17; }
        .stat-card.card-time .stat-icon { background: #e0f7fa; color: #00838f; }

        .stat-value {
            font-size: 32px;
            font-weight: 900;
            line-height: 1;
            margin-bottom: 6px;
            font-family: 'Roboto Mono', 'Inter', monospace;
        }
        .stat-card.card-wpm .stat-value { color: #1a73e8; }
        .stat-card.card-net .stat-value { color: #2e7d32; }
        .stat-card.card-acc .stat-value { color: #7c4dff; }
        .stat-card.card-err .stat-value { color: #c62828; }
        .stat-card.card-words .stat-value { color: #f57f17; }
        .stat-card.card-time .stat-value { color: #00838f; }
        
        .stat-label {
            font-size: 11px;
            text-transform: uppercase;
            font-weight: 700;
            color: var(--gray-400);
            letter-spacing: 0.8px;
        }

        /* ── Dual Panel Layout ── */
        .dual-panel {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 25px;
        }

        .panel {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.04);
            border: 1px solid var(--gray-200);
            overflow: hidden;
        }
        .panel-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px 22px;
            border-bottom: 1px solid var(--gray-200);
            background: var(--gray-50);
        }
        .panel-title {
            font-size: 14px;
            font-weight: 700;
            color: var(--gray-700);
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .panel-title i { color: var(--primary); }
        .panel-body { padding: 22px; }

        /* ── Candidate Details Panel ── */
        .detail-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
        }
        .detail-item {
            display: flex;
            flex-direction: column;
        }
        .detail-label {
            font-size: 11px;
            font-weight: 600;
            color: var(--gray-400);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }
        .detail-value {
            font-size: 14px;
            font-weight: 700;
            color: var(--gray-800);
        }

        /* ── Performance Breakdown ── */
        .perf-bars { display: flex; flex-direction: column; gap: 16px; }
        .perf-bar-item { }
        .perf-bar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 6px;
        }
        .perf-bar-label { font-size: 12px; font-weight: 600; color: var(--gray-600); }
        .perf-bar-val { font-size: 13px; font-weight: 800; font-family: 'Roboto Mono', monospace; }
        .perf-bar-track {
            width: 100%;
            height: 10px;
            background: var(--gray-200);
            border-radius: 5px;
            overflow: hidden;
        }
        .perf-bar-fill {
            height: 100%;
            border-radius: 5px;
            transition: width 1s ease;
        }

        /* ── Evaluated Text Section ── */
        .eval-section {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.04);
            border: 1px solid var(--gray-200);
            margin-bottom: 25px;
            overflow: hidden;
        }
        .eval-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px 22px;
            border-bottom: 1px solid var(--gray-200);
            background: var(--gray-50);
        }
        .eval-title {
            font-size: 14px;
            font-weight: 700;
            color: var(--gray-700);
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .eval-title i { color: var(--primary); }

        .eval-legend {
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
        }
        .legend-item {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 11px;
            font-weight: 600;
            color: var(--gray-500);
        }
        .legend-dot {
            width: 10px;
            height: 10px;
            border-radius: 3px;
        }
        .legend-dot.dot-correct { background: var(--success); }
        .legend-dot.dot-wrong { background: var(--danger); }
        .legend-dot.dot-skipped { background: var(--gray-300); }
        .legend-dot.dot-extra { background: var(--warning); }

        .eval-text-body {
            padding: 25px 30px;
            font-size: 16px;
            line-height: 2.2;
            word-wrap: break-word;
        }
        .eval-text-body.lang-text {
            font-family: <?= $fontFamily ?> !important;
            font-size: <?= in_array($result['language'], ['Hindi', 'Punjabi']) ? '20' : '16' ?>px;
        }

        /* Word evaluation styles */
        .eval-word {
            display: inline;
            padding: 2px 1px;
            border-radius: 3px;
            transition: background 0.2s;
        }
        .eval-correct { color: #2e7d32; font-weight: 600; }
        .eval-wrong { 
            background: #fff0f0;
            border-bottom: 2px solid var(--danger);
            font-weight: 700;
        }
        .eval-skipped { color: var(--gray-300); font-style: italic; }
        .eval-extra {
            background: #fff8e1;
            border-bottom: 2px solid var(--warning);
            text-decoration: line-through;
            font-weight: 700;
            color: var(--warning);
        }
        .char-correct { color: #2e7d32; }
        .char-wrong { color: #c62828; font-weight: 800; }
        .char-missing { color: #c62828; opacity: 0.5; text-decoration: underline; }
        .char-extra { color: var(--warning); text-decoration: line-through; font-weight: 700; }

        /* ── Mistakes Table ── */
        .mistakes-section {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.04);
            border: 1px solid var(--gray-200);
            margin-bottom: 25px;
            overflow: hidden;
        }
        .mistakes-table {
            width: 100%;
            border-collapse: collapse;
        }
        .mistakes-table thead th {
            padding: 12px 18px;
            text-align: left;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: var(--gray-400);
            border-bottom: 1px solid var(--gray-200);
            background: var(--gray-50);
        }
        .mistakes-table tbody td {
            padding: 12px 18px;
            font-size: 14px;
            border-bottom: 1px solid var(--gray-100);
        }
        .mistakes-table tbody tr:hover {
            background: var(--gray-50);
        }
        .mistake-num {
            display: inline-flex;
            width: 28px;
            height: 28px;
            border-radius: 6px;
            align-items: center;
            justify-content: center;
            background: #ffebee;
            color: var(--danger);
            font-weight: 700;
            font-size: 12px;
        }
        .word-correct-cell {
            color: #2e7d32;
            font-weight: 600;
            font-family: 'Roboto Mono', monospace;
        }
        .word-wrong-cell {
            color: #c62828;
            font-weight: 700;
            font-family: 'Roboto Mono', monospace;
            text-decoration: line-through;
        }

        /* ── Footer Actions ── */
        .report-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 20px 0;
            border-top: 1px solid var(--gray-200);
            margin-top: 10px;
        }
        .footer-left { display: flex; gap: 12px; }
        .btn-action {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 28px;
            font-size: 14px;
            font-weight: 700;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
        }
        .btn-action:hover { transform: translateY(-1px); }
        .btn-back {
            background: var(--gray-100);
            color: var(--gray-600);
            border: 1px solid var(--gray-300);
        }
        .btn-back:hover { background: var(--gray-200); color: var(--gray-800); }
        .btn-print-report {
            background: linear-gradient(135deg, #1a73e8, #4fc3f7);
            color: #fff;
            box-shadow: 0 4px 14px rgba(26,115,232,0.3);
        }
        .btn-print-report:hover { box-shadow: 0 6px 20px rgba(26,115,232,0.4); color: #fff; }
        .btn-retake {
            background: linear-gradient(135deg, #00c853, #69f0ae);
            color: #fff;
            box-shadow: 0 4px 14px rgba(0,200,83,0.3);
        }
        .btn-retake:hover { box-shadow: 0 6px 20px rgba(0,200,83,0.4); color: #fff; }

        .footer-branding {
            font-size: 11px;
            color: var(--gray-400);
            font-weight: 500;
        }

        /* ── Signature Row (Print) ── */
        .signature-row {
            display: none;
            justify-content: space-between;
            padding-top: 60px;
            margin-top: 40px;
        }
        .sig-box {
            width: 200px;
            text-align: center;
        }
        .sig-line {
            border-top: 1px solid #333;
            padding-top: 8px;
            font-size: 12px;
            font-weight: 700;
            color: var(--gray-600);
        }

        /* ── Custom Fonts ── */
        @font-face {
            font-family: 'Kruti Dev 010';
            src: url('../assets/fonts/krutidev-010-hindi-font-download.ttf') format('truetype');
        }
        @font-face {
            font-family: 'Raavi';
            src: url('../assets/fonts/raavi.ttf') format('truetype');
        }

        /* ── Scrollbar ── */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: var(--gray-100); }
        ::-webkit-scrollbar-thumb { background: var(--gray-300); border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: var(--gray-400); }

        /* ── Print Styles ── */
        @media print {
            body { background: #fff !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .report-hero { padding: 20px 30px; }
            .report-body { padding: 0 30px 20px; }
            .stat-card { break-inside: avoid; }
            .stat-card:hover { transform: none; box-shadow: none; }
            .report-footer { display: none !important; }
            .signature-row { display: flex !important; }
            .eval-section, .mistakes-section, .panel { break-inside: avoid; }
            .no-print { display: none !important; }
        }

        /* ── Responsive ── */
        @media (max-width: 992px) {
            .stats-row { grid-template-columns: repeat(3, 1fr); }
            .dual-panel { grid-template-columns: 1fr; }
            .report-body { padding: 0 20px 20px; }
            .report-hero { padding: 20px; }
            .hero-inner { flex-direction: column; text-align: center; gap: 15px; }
            .hero-right { text-align: center; }
        }
        @media (max-width: 576px) {
            .stats-row { grid-template-columns: repeat(2, 1fr); }
            .candidate-strip { flex-wrap: wrap; gap: 10px; }
            .eval-legend { gap: 8px; }
        }
    </style>
</head>
<body>

<!-- ═══════════════════════════════════════════ HERO HEADER ═══════════════════════════════════════ -->
<div class="report-hero">
    <div class="hero-inner">
        <div class="hero-left">
            <div class="hero-logo">
                <img src="../../assets/img/logo.png" onerror="this.parentElement.innerHTML='<i class=\'fas fa-keyboard\' style=\'font-size:28px; color:rgba(255,255,255,0.5)\'></i>';" alt="Logo">
            </div>
            <div>
                <div class="hero-title">Prayag Computer Institute</div>
                <div class="hero-subtitle">Typing Performance Report — <?= htmlspecialchars($result['language']) ?> Module</div>
                <div class="hero-badge">
                    <i class="fas fa-file-alt"></i>
                    <?= htmlspecialchars($result['title']) ?>
                </div>
            </div>
        </div>
        <div class="hero-right">
            <div class="hero-report-id">Report #<?= $result_id ?></div>
            <div class="hero-date"><i class="far fa-calendar-alt mr-1"></i> <?= date('d M Y, h:i A', strtotime($result['test_date'])) ?></div>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════ BODY ═══════════════════════════════════════════ -->
<div class="report-body">

    <!-- Grade Strip -->
    <div class="grade-strip">
        <div class="grade-box">
            <div class="grade-circle" style="background: <?= $gradeColor ?>;"><?= $grade ?></div>
            <div>
                <div class="grade-label">Performance Grade</div>
                <div class="grade-desc">Based on Net WPM & Accuracy</div>
            </div>
        </div>
        <div class="candidate-strip">
            <div class="cand-item">
                <span class="cand-label"><i class="fas fa-user mr-1"></i>Candidate:</span>
                <span class="cand-val"><?= htmlspecialchars($student['name']) ?></span>
            </div>
            <div class="cand-item">
                <span class="cand-label"><i class="fas fa-id-card mr-1"></i>Enrollment:</span>
                <span class="cand-val"><?= htmlspecialchars($student['enrollment_no'] ?? 'N/A') ?></span>
            </div>
            <div class="cand-item">
                <span class="cand-label"><i class="fas fa-clock mr-1"></i>Duration:</span>
                <span class="cand-val"><?= $time_spent_str ?></span>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="stats-row">
        <div class="stat-card card-wpm">
            <div class="stat-icon"><i class="fas fa-tachometer-alt"></i></div>
            <div class="stat-value"><?= $result['wpm'] ?></div>
            <div class="stat-label">Gross WPM</div>
        </div>
        <div class="stat-card card-net">
            <div class="stat-icon"><i class="fas fa-bolt"></i></div>
            <div class="stat-value"><?= $result['net_wpm'] ?></div>
            <div class="stat-label">Net WPM</div>
        </div>
        <div class="stat-card card-acc">
            <div class="stat-icon"><i class="fas fa-bullseye"></i></div>
            <div class="stat-value"><?= $result['accuracy'] ?>%</div>
            <div class="stat-label">Accuracy</div>
        </div>
        <div class="stat-card card-err">
            <div class="stat-icon"><i class="fas fa-times-circle"></i></div>
            <div class="stat-value"><?= $result['errors'] ?></div>
            <div class="stat-label">Mistakes</div>
        </div>
        <div class="stat-card card-words">
            <div class="stat-icon"><i class="fas fa-font"></i></div>
            <div class="stat-value"><?= $result['total_words'] ?></div>
            <div class="stat-label">Words Typed</div>
        </div>
        <div class="stat-card card-time">
            <div class="stat-icon"><i class="fas fa-stopwatch"></i></div>
            <div class="stat-value"><?= $result['correct_words'] ?></div>
            <div class="stat-label">Correct Words</div>
        </div>
    </div>

    <!-- Dual Panel: Details & Performance -->
    <div class="dual-panel">
        <!-- Candidate Details -->
        <div class="panel">
            <div class="panel-header">
                <div class="panel-title"><i class="fas fa-user-graduate"></i> Candidate Details</div>
            </div>
            <div class="panel-body">
                <div class="detail-grid">
                    <div class="detail-item">
                        <div class="detail-label">Full Name</div>
                        <div class="detail-value"><?= htmlspecialchars($student['name']) ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Enrollment No</div>
                        <div class="detail-value"><?= htmlspecialchars($student['enrollment_no'] ?? 'N/A') ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Test Title</div>
                        <div class="detail-value"><?= htmlspecialchars($result['title']) ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Language</div>
                        <div class="detail-value"><?= htmlspecialchars($result['language']) ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Exam Center</div>
                        <div class="detail-value">Prayag Computer Institute</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Date & Time</div>
                        <div class="detail-value"><?= date('d M Y, h:i A', strtotime($result['test_date'])) ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Performance Breakdown -->
        <div class="panel">
            <div class="panel-header">
                <div class="panel-title"><i class="fas fa-chart-bar"></i> Performance Breakdown</div>
            </div>
            <div class="panel-body">
                <div class="perf-bars">
                    <?php 
                    $accPct = min(100, max(0, $accuracy));
                    $wpmPct = min(100, ($wpm / 80) * 100); // 80 WPM as max scale
                    $netPct = min(100, ($netWpm / 80) * 100);
                    $correctPct = $result['total_words'] > 0 ? round(($correctCount / $result['total_words']) * 100) : 0;
                    ?>
                    <div class="perf-bar-item">
                        <div class="perf-bar-header">
                            <span class="perf-bar-label">Accuracy</span>
                            <span class="perf-bar-val" style="color: #7c4dff;"><?= $accuracy ?>%</span>
                        </div>
                        <div class="perf-bar-track">
                            <div class="perf-bar-fill" style="width: <?= $accPct ?>%; background: linear-gradient(90deg, #7c4dff, #b388ff);"></div>
                        </div>
                    </div>
                    <div class="perf-bar-item">
                        <div class="perf-bar-header">
                            <span class="perf-bar-label">Gross WPM</span>
                            <span class="perf-bar-val" style="color: #1a73e8;"><?= $wpm ?></span>
                        </div>
                        <div class="perf-bar-track">
                            <div class="perf-bar-fill" style="width: <?= $wpmPct ?>%; background: linear-gradient(90deg, #1a73e8, #4fc3f7);"></div>
                        </div>
                    </div>
                    <div class="perf-bar-item">
                        <div class="perf-bar-header">
                            <span class="perf-bar-label">Net WPM</span>
                            <span class="perf-bar-val" style="color: #2e7d32;"><?= $netWpm ?></span>
                        </div>
                        <div class="perf-bar-track">
                            <div class="perf-bar-fill" style="width: <?= $netPct ?>%; background: linear-gradient(90deg, #00c853, #69f0ae);"></div>
                        </div>
                    </div>
                    <div class="perf-bar-item">
                        <div class="perf-bar-header">
                            <span class="perf-bar-label">Correct Words</span>
                            <span class="perf-bar-val" style="color: #00838f;"><?= $correctCount ?> / <?= $result['total_words'] ?></span>
                        </div>
                        <div class="perf-bar-track">
                            <div class="perf-bar-fill" style="width: <?= $correctPct ?>%; background: linear-gradient(90deg, #00838f, #4dd0e1);"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Evaluated Text -->
    <div class="eval-section">
        <div class="eval-header">
            <div class="eval-title"><i class="fas fa-keyboard"></i> Evaluated Text — Word-by-Word Analysis</div>
            <div class="eval-legend">
                <div class="legend-item"><div class="legend-dot dot-correct"></div> Correct</div>
                <div class="legend-item"><div class="legend-dot dot-wrong"></div> Incorrect</div>
                <div class="legend-item"><div class="legend-dot dot-skipped"></div> Not Typed</div>
                <div class="legend-item"><div class="legend-dot dot-extra"></div> Extra Word</div>
            </div>
        </div>
        <div class="eval-text-body lang-text">
            <?php if(empty($typed)): ?>
                <div class="text-center py-4" style="color: var(--gray-400);">
                    <i class="fas fa-exclamation-triangle fa-2x mb-3" style="color: var(--warning);"></i>
                    <p style="font-family: 'Inter', sans-serif; font-size: 14px;">No typed content found. The candidate may have submitted without typing.</p>
                </div>
            <?php else: ?>
                <?= $evaluatedHtml ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Mistakes Detail Table -->
    <?php if (!empty($mistakesList)): ?>
    <div class="mistakes-section">
        <div class="panel-header">
            <div class="panel-title"><i class="fas fa-exclamation-triangle" style="color: var(--danger);"></i> Detailed Mistake Analysis (<?= count($mistakesList) ?> Errors)</div>
        </div>
        <div style="overflow-x: auto;">
            <table class="mistakes-table">
                <thead>
                    <tr>
                        <th style="width: 60px;">#</th>
                        <th style="width: 80px;">Word #</th>
                        <th>Expected Word</th>
                        <th>Typed Word</th>
                        <th style="width: 100px;">Type</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($mistakesList as $idx => $m): ?>
                    <tr>
                        <td><span class="mistake-num"><?= $idx + 1 ?></span></td>
                        <td style="font-weight: 600; color: var(--gray-500);"><?= $m['pos'] ?></td>
                        <td class="word-correct-cell"><?= htmlspecialchars($m['original']) ?></td>
                        <td class="word-wrong-cell"><?= htmlspecialchars($m['typed']) ?></td>
                        <td>
                            <?php if ($m['original'] === '—'): ?>
                                <span style="background: #fff8e1; color: var(--warning); padding: 3px 10px; border-radius: 4px; font-size: 11px; font-weight: 700;">EXTRA</span>
                            <?php else: ?>
                                <span style="background: #ffebee; color: var(--danger); padding: 3px 10px; border-radius: 4px; font-size: 11px; font-weight: 700;">WRONG</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <!-- Signatures (Print Only) -->
    <div class="signature-row">
        <div class="sig-box">
            <div class="sig-line">Candidate Signature</div>
        </div>
        <div class="sig-box">
            <div class="sig-line">Invigilator / Instructor</div>
        </div>
    </div>

    <!-- Footer Actions -->
    <div class="report-footer no-print">
        <div class="footer-left">
            <a href="javascript:history.back()" class="btn-action btn-back">
                <i class="fas fa-arrow-left"></i> Go Back
            </a>
            <button onclick="window.print()" class="btn-action btn-print-report">
                <i class="fas fa-print"></i> Print Report
            </button>
            <a href="<?= $result['language'] === 'Hindi' ? 'hindi.php' : ($result['language'] === 'Punjabi' ? 'punjabi.php' : 'english.php') ?>" class="btn-action btn-retake">
                <i class="fas fa-redo"></i> Take Another Test
            </a>
        </div>
        <div class="footer-branding">
            Powered by <strong>Prayag Computer Institute</strong> — Typing Module
        </div>
    </div>
</div>

</body>
</html>

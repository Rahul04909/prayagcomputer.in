<?php
require_once __DIR__ . '/../includes/auth_helper.php';

// ── Access Check ─────────────────────────────────────────────────────────────
if (!$auth->isLogged()) {
    header('Location: ../login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("<div style='text-align:center; padding:50px;'><h2 style='color:#c62828;'>Invalid Request</h2><p>Results cannot be accessed directly.</p></div>");
}

$id = isset($_POST['test_id']) ? (int)$_POST['test_id'] : 0;
$time_spent = isset($_POST['time_spent']) ? (int)$_POST['time_spent'] : 0;
$raw_transcription = $_POST['translation_text'] ?? '';

if (!$id) {
    die("Invalid Test ID");
}

try {
    $stmt = $pdo->prepare("SELECT t.*, c.name as category_name 
                           FROM steno_tests t 
                           LEFT JOIN steno_exam_categories c ON t.category_id = c.id 
                           WHERE t.id = ?");
    $stmt->execute([$id]);
    $test = $stmt->fetch();

    if (!$test) {
        die("Test not found.");
    }
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

$student_data = get_current_student_data();
$student_name = $student_data['student_name'] ?? 'Student';

// ── Evaluation Engine (Greedy Diff Algorithm) ────────────────────────────────
function cleanStenoText($text) {
    $text = strip_tags(html_entity_decode($text));
    $text = str_replace(["\r", "\n", "\t", "&nbsp;", "\xc2\xa0"], ' ', $text);
    return preg_replace('/\s+/', ' ', trim($text));
}

$originalTxt = cleanStenoText($test['content']);
$transcribedTxt = cleanStenoText($raw_transcription);

$origArr = explode(' ', $originalTxt);
$tranArr = explode(' ', $transcribedTxt);

// If content was totally empty in DB, avoid division by zero
if(empty($origArr[0]) && count($origArr) === 1) $origArr = [];
if(empty($tranArr[0]) && count($tranArr) === 1) $tranArr = [];

$htmlDiff = '';
$errors = 0;
$correct = 0;

$i = 0; // original pointer
$j = 0; // transcribed pointer

while ($i < count($origArr) || $j < count($tranArr)) {
    if ($i >= count($origArr)) {
        // User added extra words at the end
        $htmlDiff .= "<del class='diff-extra bg-danger text-white rounded px-1 text-decoration-line-through mx-1 mb-1 d-inline-block' title='Extra Word'>{$tranArr[$j]}</del>";
        $errors++;
        $j++;
        continue;
    }
    if ($j >= count($tranArr)) {
        // User missed words at the end
        $htmlDiff .= "<ins class='diff-miss bg-warning text-dark border-bottom border-warning rounded px-1 text-decoration-none mx-1 mb-1 d-inline-block' title='Missed Word'>{$origArr[$i]}</ins>";
        $errors++;
        $i++;
        continue;
    }
    
    // Strict match or case-insensitive match (steno evaluation is usually strict, but we'll trim)
    if (trim($origArr[$i]) === trim($tranArr[$j])) {
        $htmlDiff .= "<span class='text-success mx-1 mb-1 d-inline-block'>{$origArr[$i]}</span>";
        $correct++;
        $i++;
        $j++;
    } else {
        $foundMatch = false;
        // Lookahead Resync Window (Size = 6 words)
        for ($lookahead = 1; $lookahead <= 6; $lookahead++) {
            // Did user completely skip 1-6 words?
            if (($i + $lookahead) < count($origArr) && trim($origArr[$i + $lookahead]) === trim($tranArr[$j])) {
                for ($k = 0; $k < $lookahead; $k++) {
                    $htmlDiff .= "<ins class='diff-miss bg-warning text-dark border-bottom border-warning rounded px-1 text-decoration-none mx-1 mb-1 d-inline-block' title='Missed Word'>{$origArr[$i]}</ins>";
                    $errors++;
                    $i++;
                }
                $foundMatch = true;
                break;
            }
            // Did user insert 1-6 extra hallucinated words?
            if (($j + $lookahead) < count($tranArr) && trim($origArr[$i]) === trim($tranArr[$j + $lookahead])) {
                for ($k = 0; $k < $lookahead; $k++) {
                    $htmlDiff .= "<del class='diff-extra bg-danger text-white rounded px-1 text-decoration-line-through mx-1 mb-1 d-inline-block' title='Extra Word'>{$tranArr[$j]}</del>";
                    $errors++;
                    $j++;
                }
                $foundMatch = true;
                break;
            }
        }
        
        if (!$foundMatch) {
            // Misspelled/Incorrectly transcribed word (Replaced)
            $htmlDiff .= "<span class='diff-wrong bg-danger text-white rounded px-1 mx-1 mb-1 d-inline-block' title='Expected: {$origArr[$i]}'>{$tranArr[$j]}</span>";
            $errors++;
            $i++;
            $j++;
        }
    }
}

$total_words = count($origArr);
$accuracy = $total_words > 0 ? max(0, Math::fromFloat(($correct / $total_words) * 100)) : 0; // We use simple math, but let's do native PHP rounding
$accuracy = $total_words > 0 ? round(($correct / $total_words) * 100, 2) : 100;
$time_mins = $time_spent > 0 ? ($time_spent / 60) : ($test['test_duration'] ?? 1); 
$wpm = $time_mins > 0 ? round($correct / $time_mins) : 0;

$performanceColor = $accuracy >= 90 ? 'success' : ($accuracy >= 75 ? 'warning' : 'danger');
$performanceBg = $accuracy >= 90 ? 'bg-success' : ($accuracy >= 75 ? 'bg-warning' : 'bg-danger');

// Note: Usually we would INSERT INTO steno_results right here tracking their ID, score, etc.
// Since the schema doesn't describe 'steno_results', we just show the report dynamically.
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Steno Result: <?= htmlspecialchars($test['title']) ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto+Mono:wght@400;500;700&family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background: #f4f6f9; color:#343a40; }
        .result-card { border: none; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.06); overflow: hidden; }
        .score-circle { width: 120px; height: 120px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 32px; font-weight: 800; color: white; margin: 0 auto 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.2); border: 4px solid rgba(255,255,255,0.7); }
        .score-box { background: white; border-radius: 10px; padding: 20px; text-align: center; border: 1px solid #eee; box-shadow: 0 2px 5px rgba(0,0,0,0.02); }
        .score-val { font-size: 34px; font-weight: 800; font-family: 'Roboto Mono', monospace; line-height: 1; margin-bottom: 5px; color: #2c3e50; }
        .score-lbl { font-size: 11px; text-transform: uppercase; color: #7f8c8d; font-weight: 700; letter-spacing: 1px; }
        .legend-box { background: #fff; border-radius: 6px; padding: 10px 15px; border: 1px solid #eee; font-size: 12px; display: inline-flex; align-items: center; gap: 15px; font-weight: 600; flex-wrap: wrap;}
        
        .diff-board {
            background: #fff; padding: 30px; border-radius: 10px; border: 1px solid #ced4da;
            font-size: <?= $test['language'] === 'Hindi' ? '18' : '16' ?>px; line-height: 2.2; max-height: 500px; overflow-y: auto; text-align: justify;
        }
        <?php if ($test['language'] === 'Hindi'): ?>
        @font-face { font-family: 'Kruti Dev 010'; src: url('../assets/fonts/krutidev-010-hindi-font-download.ttf') format('truetype'); }
        .diff-board { font-family: 'Kruti Dev 010', sans-serif !important; }
        <?php else: ?>
        .diff-board { font-family: 'Inter', sans-serif !important; }
        <?php endif; ?>
    </style>
</head>
<body class="py-5">

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="font-weight-bold mb-1"><i class="fas fa-clipboard-check text-<?= $performanceColor ?> mr-2"></i> Steno Transcription Report</h2>
            <p class="text-muted">Test: <strong><?= htmlspecialchars($test['title']) ?></strong> | Participant: <strong><?= htmlspecialchars($student_name) ?></strong></p>
        </div>
        <a href="<?= strtolower($test['language']) ?>.php" class="btn btn-outline-secondary font-weight-bold"><i class="fas fa-home mr-1"></i> Dashboard</a>
    </div>

    <!-- Stats Row -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="result-card <?= $performanceBg ?> h-100 p-4 text-center text-white">
                <div class="score-circle mt-2"><?= $accuracy ?>%</div>
                <h5 class="font-weight-bold mb-0">Total Accuracy</h5>
                <p class="small opacity-75">Transcription Match</p>
            </div>
        </div>
        <div class="col-md-9">
            <div class="result-card bg-white p-4 h-100">
                <h5 class="border-bottom pb-2 mb-4 font-weight-bold text-secondary">Performance Metrics</h5>
                <div class="row g-3">
                    <div class="col-sm-3">
                        <div class="score-box">
                            <div class="score-val text-primary"><?= $wpm ?></div>
                            <div class="score-lbl">Gross WPM</div>
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <div class="score-box">
                            <div class="score-val text-<?= $performanceColor ?>"><?= $correct ?></div>
                            <div class="score-lbl">Correct Words</div>
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <div class="score-box">
                            <div class="score-val text-danger"><?= $errors ?></div>
                            <div class="score-lbl">Total Mistakes</div>
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <div class="score-box">
                            <div class="score-val text-info"><?= floor($time_spent / 60) ?>m <?= $time_spent % 60 ?>s</div>
                            <div class="score-lbl">Time Elapsed</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Differential Analysis Report -->
    <div class="card result-card">
        <div class="card-header bg-white d-flex justify-content-between align-items-center p-3 border-bottom-0">
            <h5 class="mb-0 font-weight-bold text-dark"><i class="fas fa-glasses mr-2 text-primary"></i> Examiner Transcription Analysis</h5>
            <div class="legend-box">
                <span class="text-success"><i class="fas fa-check-circle mr-1"></i> Correct</span>
                <span class="text-white bg-danger px-2 py-1 rounded"><i class="fas fa-times-circle mr-1"></i> Incorrect Spelling</span>
                <span class="text-dark bg-warning px-2 py-1 rounded border-bottom border-warning"><i class="fas fa-minus-circle mr-1"></i> Missing / Skipped</span>
                <span class="text-white bg-danger px-2 py-1 rounded text-decoration-line-through"><i class="fas fa-plus-circle mr-1"></i> Extra Words</span>
            </div>
        </div>
        <div class="card-body bg-light p-4">
            <div class="diff-board shadow-sm">
                <?= $htmlDiff ?>
            </div>
        </div>
        <div class="card-footer bg-white text-muted small text-center py-3 border-top-0">
            Hover over incorrect words to see the originally expected dictionary word. High volumes of missing words typically indicate transcription latency during dictation playback.
        </div>
    </div>

</div>

</body>
</html>

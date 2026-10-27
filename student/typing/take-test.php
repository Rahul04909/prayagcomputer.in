<?php
require_once __DIR__ . '/../includes/auth_helper.php';

// ── Access & Auth Check ──────────────────────────────────────────────────────
if (!$auth->isLogged()) {
    header('Location: ../login.php');
    exit();
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) {
    die("Invalid Test ID");
}

try {
    $stmt = $pdo->prepare("SELECT t.*, c.name as category_name 
                           FROM typing_tests t 
                           LEFT JOIN typing_exam_categories c ON t.category_id = c.id 
                           WHERE t.id = ? AND t.status = 1");
    $stmt->execute([$id]);
    $test = $stmt->fetch();

    if (!$test) {
        die("Test not found or inactive.");
    }
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// Student name for session/results
$student_data = get_current_student_data();
$student_name = $student_data['name'] ?? 'Student';
$typing_access = $student_data['typing_access'] ?? 'None';

$access_granted = false;
if ($typing_access === 'All') {
    $access_granted = true;
} else if ($test['language'] === 'English' && in_array($typing_access, ['English', 'Both', 'All'])) {
    $access_granted = true;
} else if ($test['language'] === 'Hindi' && in_array($typing_access, ['Hindi', 'Both', 'All'])) {
    $access_granted = true;
} else if ($test['language'] === 'Punjabi' && in_array($typing_access, ['Punjabi', 'All'])) {
    $access_granted = true;
}

if (!$access_granted) {
    die("<div style='text-align:center; padding:50px; font-family:sans-serif;'>
            <h2 style='color:#c62828;'>Access Denied</h2>
            <p>You are not a registered candidate for this module. Please contact your administrator.</p>
            <a href='../dashboard.php' style='display:inline-block; margin-top:20px; padding:10px 20px; background:#28a745; color:#fff; text-decoration:none; border-radius:5px;'>Return to Dashboard</a>
         </div>");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Typing Test: <?= htmlspecialchars($test['title']) ?></title>
    <!-- AdminLTE & Bootstrap -->
    <link rel="stylesheet" href="../../admin/assets/plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="../../admin/assets/dist/css/adminlte.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto+Mono:wght@400;500;700&family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --bg-main: #f4f6f9;
            --primary-green: #28a745;
            --accent-blue: #007bff;
            --header-bg: #8e9dbf;
        }
        * { box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #e0e4ef; height: 100vh; margin: 0; overflow: hidden; }
        
        /* Layout Structure */
        .test-wrapper { display: flex; height: 100vh; width: 100vw; }
        .sidebar-left { width: 70px; background: #fff; border-right: 1px solid #d1d5db; display: flex; flex-direction: column; align-items: center; padding: 15px 5px; flex-shrink: 0; }
        .sidebar-right { width: 300px; background: #eaedf4; border-left: 1px solid #c8ced9; padding: 10px; overflow-y: auto; flex-shrink: 0; }
        .main-container { flex: 1; display: flex; flex-direction: column; background: #cbd5e1; height: 100vh; min-width: 0; }

        /* Top Nav */
        .top-nav { height: 45px; background: #fff; display: flex; align-items: center; justify-content: center; padding: 0 15px; border-bottom: 2px solid #28a745; flex-shrink: 0; }
        .top-nav .mode-btn { border: 1px solid #c62828; color: #c62828; font-size: 11px; padding: 3px 10px; border-radius: 4px; font-weight: bold; cursor: pointer; text-transform: uppercase; }

        /* Content Areas */
        .text-display-box { 
            flex: 1.2; background: #fff; margin: 10px 15px; border-radius: 6px; border: 1px solid #ced4da;
            padding: 20px 25px; font-family: 'Consolas', 'Roboto Mono', monospace; font-size: <?= in_array($test['language'], ['Hindi', 'Punjabi']) ? '16' : '14' ?>px; line-height: 1.8;
            overflow-y: auto; color: #212529; position: relative; box-shadow: 0 2px 5px rgba(0,0,0,0.02);
            min-height: 150px; letter-spacing: 0.5px; scroll-behavior: smooth;
        }
        .text-display-box.no-scrollbar::-webkit-scrollbar { display: none; }
        .text-display-box.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        .info-bar { 
            height: 46px; 
            background: #5c6b87; 
            display: flex; 
            align-items: center; 
            justify-content: space-between; 
            padding: 0 15px; 
            color: #fff; 
            flex-shrink: 0; 
            border-bottom: 2px solid #4a566d; 
            border-top: 1px solid #7a89a8;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .tool-btn {
            background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.3);
            color: #fff; width: 24px; height: 24px; border-radius: 4px; display: flex; align-items: center; justify-content: center;
            cursor: pointer; transition: all 0.2s; font-size: 11px; outline: none; padding: 0;
        }
        .tool-btn:hover { background: rgba(255,255,255,0.3); }
        .tool-btn:active { background: rgba(255,255,255,0.4); transform: scale(0.95); }
        .font-size-val { font-weight: 700; width: 26px; text-align: center; display: inline-block; font-size: 14px; }
        .opacity-75 { opacity: 0.85; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px; font-weight: 700; }

        .typing-input-box {
            flex: 1; min-height: 120px; background: #fff; margin: 10px 15px; border-radius: 6px; border: 1px solid #ced4da;
            padding: 20px 25px; font-family: 'Consolas', 'Roboto Mono', monospace; font-size: <?= in_array($test['language'], ['Hindi', 'Punjabi']) ? '16' : '14' ?>px; width: calc(100% - 30px);
            resize: none; outline: none; border-top: 4px solid var(--header-bg); flex-shrink: 0; box-shadow: 0 2px 5px rgba(0,0,0,0.02);
            letter-spacing: 0.5px; line-height: 1.8; color: #0d6efd;
        }

        /* Sidebar Styling */
        .settings-section { background: #fff; border: 1px solid #d1d5db; border-radius: 6px; padding: 12px; margin-bottom: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .settings-title { font-size: 11px; font-weight: 800; color: #555; margin-bottom: 8px; border-bottom: 1px solid #eee; padding-bottom: 4px; text-transform: uppercase; letter-spacing: 0.5px; }
        .form-check-label { font-size: 13px; color: #444; }
        .stat-val { font-size: 32px; font-weight: 800; line-height: 1; margin-bottom: 6px; font-family: 'Inter', sans-serif; }
        .stat-lbl { font-size: 11px; text-transform: uppercase; color: #777; font-weight: 700; letter-spacing: 0.5px; margin-top: 0; }
        .timer-val { font-size: 36px; font-weight: 800; color: #c62828; font-family: 'Roboto Mono', monospace; }

        /* Words & Highlighting */
        .word { display: inline-block; padding: 0 2px; border-radius: 2px; margin-bottom: 4px; }
        .word.current { background: #fff59d; }
        .word.error-input { background: #ffcdd2 !important; color: #c62828 !important; }
        .word.correct { color: #2e7d32; }
        .word.incorrect { color: #c62828; text-decoration: underline; }

        /* Fonts Integration */
        @font-face {
            font-family: 'Kruti Dev 010';
            src: url('../assets/fonts/krutidev-010-hindi-font-download.ttf') format('truetype');
        }
        @font-face {
            font-family: 'Raavi';
            src: url('../assets/fonts/raavi.ttf') format('truetype');
        }
        
        <?php if ($test['language'] === 'Hindi'): ?>
        .text-display-box, .typing-input-box {
            font-family: 'Kruti Dev 010', sans-serif !important;
        }
        <?php elseif ($test['language'] === 'Punjabi'): ?>
        .text-display-box, .typing-input-box {
            font-family: 'Raavi', sans-serif !important;
        }
        <?php endif; ?>
    </style>
</head>
<body>

<div class="test-wrapper">
    <!-- Left Sidebar: Eliminated in favor of a full-width experience -->
    <!-- Main Section -->
    <div class="main-container">
        <div class="top-nav">
            <div style="font-size:14px; color:#28a745; font-weight:800; letter-spacing:1px;"><?= htmlspecialchars($test['title']) ?></div>
        </div>

        <!-- Reference Text Box -->
        <div id="textDisplay" class="text-display-box">
             <div class="text-center py-5 text-muted">
                <i class="fas fa-spinner fa-spin fa-2x mb-3"></i>
                <p>Initializing typing engine...</p>
             </div>
        </div>

        <!-- Info Bar -->
        <div class="info-bar" style="display: flex; flex-direction: row; align-items: center; justify-content: space-between; padding: 0 15px; height: 52px; background: #5c6b87; color: #fff; overflow-x: auto; white-space: nowrap; border-top: 1px solid #7a89a8; border-bottom: 2px solid #4a566d; flex-shrink: 0; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
            <!-- Left Side: Duration -->
            <div style="display: flex; flex-direction: row; align-items: center; margin-right: 20px;">
                <span style="opacity: 0.85; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px; font-weight: 700; margin-right: 10px;">Duration:</span>
                <?php $defaultDuration = !empty($test['test_time']) ? (int)$test['test_time'] : 10; ?>
                <select id="durationSelect" class="form-control form-control-sm" style="background: rgba(255,255,255,0.1); color: #fff; border: 1px solid rgba(255,255,255,0.2); width: auto; font-size: 13px; font-weight: 600; cursor: pointer; height: 32px; padding: 0 10px; border-radius: 4px; box-shadow: none;">
                    <option value="1" style="color:#000;" <?= $defaultDuration === 1 ? 'selected' : '' ?>>1 Min</option>
                    <option value="2" style="color:#000;" <?= $defaultDuration === 2 ? 'selected' : '' ?>>2 Min</option>
                    <option value="5" style="color:#000;" <?= $defaultDuration === 5 ? 'selected' : '' ?>>5 Min</option>
                    <option value="10" style="color:#000;" <?= $defaultDuration === 10 ? 'selected' : '' ?>>10 Min</option>
                    <option value="15" style="color:#000;" <?= $defaultDuration === 15 ? 'selected' : '' ?>>15 Min</option>
                </select>
            </div>
            
            <!-- Right Side: Tools -->
            <div style="display: flex; flex-direction: row; align-items: center;">
                <!-- Font Size Controls -->
                <div style="display: flex; flex-direction: row; align-items: center; background: rgba(0,0,0,0.15); border: 1px solid rgba(255,255,255,0.1); border-radius: 6px; padding: 4px 8px; margin-right: 20px; height: 34px;">
                    <span style="opacity: 0.85; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px; font-weight: 700; margin-right: 12px;">Size:</span>
                    <button id="fontDec" title="Decrease Font Size" style="background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.2); color: #fff; width: 24px; height: 24px; border-radius: 4px; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 16px; font-weight: bold; padding: 0; line-height: 1; outline: none;">&minus;</button>
                    <span id="fontSizeDisplay" style="font-weight: 700; width: 28px; text-align: center; display: inline-block; font-size: 14px; margin: 0 4px;"><?= in_array($test['language'], ['Hindi', 'Punjabi']) ? '16' : '14' ?></span>
                    <button id="fontInc" title="Increase Font Size" style="background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.2); color: #fff; width: 24px; height: 24px; border-radius: 4px; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 16px; font-weight: bold; padding: 0; line-height: 1; outline: none;">+</button>
                </div>

                <!-- Bold Toggle -->
                <div style="display: flex; flex-direction: row; align-items: center; background: rgba(0,0,0,0.15); border: 1px solid rgba(255,255,255,0.1); border-radius: 6px; padding: 4px 12px; height: 34px;">
                    <span style="opacity: 0.85; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px; font-weight: 700; margin-right: 10px;">Bold:</span>
                    <div class="custom-control custom-switch" style="margin: 0; padding-left: 2.25rem;">
                        <input type="checkbox" class="custom-control-input" id="boldToggle" style="cursor: pointer;">
                        <label class="custom-control-label" for="boldToggle" style="margin: 0; padding: 0; cursor: pointer; height: 16px;"></label>
                    </div>
                </div>
            </div>
        </div>


        <!-- Typing Input Area -->
        <textarea id="typingInput" class="typing-input-box" placeholder="Loading typing engine..." disabled></textarea>
        
        <div style="display: flex; align-items: center; justify-content: space-between; padding: 6px 15px; background: #e0e4ef; flex-shrink: 0;">
            <div style="font-size:12px; color:#555; font-weight: 500;">
                <i class="fas fa-info-circle text-primary mr-1"></i> Timer will start automatically when you begin typing.
            </div>
            <button id="submitTestBtn" class="btn btn-danger btn-sm font-weight-bold" style="padding: 5px 20px; font-size: 13px; letter-spacing: 0.5px; border-radius: 4px; box-shadow: 0 2px 6px rgba(198,40,40,0.3);" disabled>
                <i class="fas fa-paper-plane mr-1"></i> SUBMIT TEST
            </button>
        </div>
    </div>

    <!-- Right Sidebar: Settings & Live Stats -->
    <div class="sidebar-right">
        <!-- Timer Section -->
        <div class="settings-section text-center py-3" style="border: 2px solid #c62828;">
            <div class="stat-lbl mb-1">Time Remaining</div>
            <div id="timerDisplay" class="timer-val">00:00</div>
        </div>

        <!-- Stats Panel -->
        <div class="settings-section p-0 overflow-hidden" id="statPanel">
            <div style="display: grid; grid-template-columns: 1fr 1fr; background: #fff;">
                <div class="text-center py-3" style="border-right: 1px solid #eee; border-bottom: 1px solid #eee;">
                    <div id="liveWpm" class="stat-val text-success">0</div>
                    <div id="wpmLbl" class="stat-lbl">WPM</div>
                </div>
                <div class="text-center py-3" style="border-bottom: 1px solid #eee;">
                    <div id="liveAccuracy" class="stat-val text-primary">0%</div>
                    <div id="accLbl" class="stat-lbl">Accuracy</div>
                </div>
                <div class="text-center py-3" style="border-right: 1px solid #eee;">
                    <div id="liveErrors" class="stat-val text-danger">0</div>
                    <div id="errLbl" class="stat-lbl">Mistakes</div>
                </div>
                <div class="text-center py-3">
                    <div id="liveWords" class="stat-val text-info">0</div>
                    <div id="wordsLbl" class="stat-lbl">Words Typed</div>
                </div>
            </div>
        </div>

        <div class="settings-section">
            <div class="settings-title">Typing Mode</div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="examMode">
                <label class="form-check-label" for="examMode"><b>Strict Exam Mode</b><br><small class="text-muted">Hides all live performance stats</small></label>
            </div>
        </div>

        <div class="settings-section">
            <div class="settings-title">Backspace Rules</div>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="backspaceOpt" id="bsFull" value="full" checked>
                <label class="form-check-label" for="bsFull">Allow Full Backspace</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="backspaceOpt" id="bsOne" value="one">
                <label class="form-check-label" for="bsOne">One Word Backspace</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="backspaceOpt" id="bsNone" value="none">
                <label class="form-check-label" for="bsNone">Disable Backspace</label>
            </div>
        </div>

        <div class="settings-section">
            <div class="settings-title">Visual Aids</div>
            <div class="form-check mb-1">
                <input class="form-check-input highlight-opt" type="radio" name="highlightOpt" id="hlWord" value="word" checked>
                <label class="form-check-label" for="hlWord">Word Highlight</label>
            </div>
            <div class="form-check mb-1">
                <input class="form-check-input highlight-opt" type="radio" name="highlightOpt" id="hlWordErr" value="wordErr">
                <label class="form-check-label" for="hlWordErr">Word + Error (CPCT)</label>
            </div>
            <div class="form-check">
                <input class="form-check-input highlight-opt" type="radio" name="highlightOpt" id="hlNone" value="none">
                <label class="form-check-label" for="hlNone">No Highlighting</label>
            </div>
        </div>

        <div class="settings-section">
            <div class="settings-title">Scroll & View</div>
            <div class="d-flex justify-content-between">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="showScroll" checked>
                    <label class="form-check-label" for="showScroll">Scrollbar</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="autoScroll" checked>
                    <label class="form-check-label" for="autoScroll">Auto Scroll</label>
                </div>
            </div>
        </div>

        <div style="display: flex; gap: 8px;">
            <button class="btn btn-outline-danger btn-sm font-weight-bold" style="flex:1;" onclick="window.location.reload();">
                <i class="fas fa-sync-alt mr-1"></i> RESTART
            </button>
            <button id="sidebarSubmitBtn" class="btn btn-danger btn-sm font-weight-bold" style="flex:1;" disabled>
                <i class="fas fa-paper-plane mr-1"></i> SUBMIT
            </button>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<?php if ($test['language'] === 'Punjabi'): ?>
<script src="../assets/js/punjabi_inscript.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        enablePunjabiInscript('typingInput');
    });
</script>
<?php endif; ?>

<script>
    // =========================================================================
    // PRAYAG COMPUTER TYPING ENGINE (Complete Clean Rewrite)
    // Formulas:
    //   - WPM = Correctly typed words / elapsed minutes
    //   - Gross WPM = Total typed words / elapsed minutes
    //   - Accuracy = (Correctly typed words / Total typed words) * 100
    //   - Live Speed Refresh: Every 5 seconds (Typing Master / Sony Typing Tutor style)
    // =========================================================================

    const testContent = <?= json_encode(str_replace(["\r", "\n", "\t", "&nbsp;", "\xc2\xa0"], ' ', html_entity_decode(strip_tags($test['content'])))) ?>;
    const testWords = (testContent || '').trim().split(/\s+/).filter(w => w.length > 0);
    const words = testWords;
    const defaultTime = <?= (int)$test['test_time'] ?>;

    // State Variables
    let currentWordIdx = 0;
    let typedWords = [];              // Space-confirmed typed words array
    let currentTypingWord = '';       // Word currently being typed (before space)
    let totalDurationSec = 600;       // Total seconds for current test
    let timeLeft = 600;               // Remaining seconds countdown
    let timerInterval = null;
    let hasStartedTyping = false;
    let isFinished = false;
    let displayWpm = 0;               // Live displayed WPM (refreshed every 5s)

    // UI Elements
    const textDisplay = $('#textDisplay');
    const typingInput = $('#typingInput');
    const timerDisplay = $('#timerDisplay');

    $(document).ready(function() {
        initTest();
    });

    // ── 1. Initialize / Reset Test ──
    function initTest() {
        try {
            if (!testWords || testWords.length === 0) {
                textDisplay.html('<div class="alert alert-warning">No content found for this test.</div>');
                return;
            }

            // Reset state
            currentWordIdx = 0;
            typedWords = [];
            currentTypingWord = '';
            hasStartedTyping = false;
            isFinished = false;
            displayWpm = 0;
            if (timerInterval) clearInterval(timerInterval);
            timerInterval = null;

            // Render reference words
            textDisplay.empty();
            testWords.forEach((word, idx) => {
                textDisplay.append(`<span class="word" id="word-${idx}">${word}</span> `);
            });

            // Set duration
            const selectedDuration = parseInt($('#durationSelect').val(), 10) || 10;
            totalDurationSec = selectedDuration * 60;
            timeLeft = totalDurationSec;

            updateTimerDisplay();
            updateHighlight();

            // Reset stats panel
            $('#liveWpm').text('0');
            $('#liveAccuracy').text('100%');
            $('#liveErrors').text('0');
            $('#liveWords').text('0');

            // Reset buttons & textarea
            $('#submitTestBtn, #sidebarSubmitBtn').prop('disabled', true);
            const placeholderText = <?= $test['language'] === 'Hindi' ? "'ऊपर दिखाए गए पाठ को टाइप करना शुरू करें...'" : ($test['language'] === 'Punjabi' ? "'ਉੱਪਰ ਦਿਖਾਏ ਗਏ ਟੈਕਸਟ ਨੂੰ ਟਾਈਪ ਕਰਨਾ ਸ਼ੁਰੂ ਕਰੋ...'" : "'Start typing the text shown above...'") ?>;
            typingInput.prop('disabled', false).val('').attr('placeholder', placeholderText);

        } catch (e) {
            console.error("Init Error:", e);
            textDisplay.html('<div class="alert alert-danger">Error loading test engine.</div>');
        }
    }

    // ── 2. Timer Controls ──
    function updateTimerDisplay() {
        const m = Math.floor(timeLeft / 60);
        const s = timeLeft % 60;
        timerDisplay.text(`${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}`);
    }

    function startTimer() {
        if (hasStartedTyping || isFinished) return;
        hasStartedTyping = true;

        // Enable manual submit buttons as soon as typing begins
        $('#submitTestBtn, #sidebarSubmitBtn').prop('disabled', false);

        timerInterval = setInterval(() => {
            timeLeft--;
            updateTimerDisplay();
            updateLiveStats(false);

            if (timeLeft <= 0) {
                finishTest();
            }
        }, 1000);
    }

    // ── 3. Word Evaluation Function ──
    function evaluateCurrentWords() {
        let correctCount = 0;
        let wrongCount = 0;
        const totalEvaluated = typedWords.length;

        for (let i = 0; i < totalEvaluated; i++) {
            if (i < testWords.length && typedWords[i] === testWords[i]) {
                correctCount++;
            } else {
                wrongCount++;
            }
        }

        return {
            total: totalEvaluated,
            correct: correctCount,
            mistakes: wrongCount,
            accuracy: totalEvaluated > 0 ? Math.round((correctCount / totalEvaluated) * 100) : 100
        };
    }

    // ── 4. Live Stats Update (Typing Master / Sony 5-Sec Interval) ──
    function updateLiveStats(forceSpeedUpdate = false) {
        if (!hasStartedTyping || isFinished) return;

        const elapsedSec = Math.max(0, totalDurationSec - timeLeft);
        const elapsedMin = elapsedSec / 60;
        const evalResult = evaluateCurrentWords();

        // Speed update: Every 5 seconds (5s, 10s, 15s, 20s...) or forced
        // First 5 seconds displays 0 to prevent erratic initial spikes
        if (elapsedSec >= 5 && (elapsedSec % 5 === 0 || forceSpeedUpdate || displayWpm === 0)) {
            if (elapsedMin > 0) {
                // Exact formula: Correct words / Elapsed minutes
                displayWpm = Math.round(evalResult.correct / elapsedMin);
            }
        } else if (elapsedSec < 5) {
            displayWpm = 0;
        }

        const hlMode = $('input[name="highlightOpt"]:checked').val();
        if (hlMode === 'none') {
            $('#liveWpm').text('---');
            $('#liveAccuracy').text('---');
            $('#liveErrors').text('---');
            $('#liveWords').text('---');
        } else {
            $('#liveWpm').text(displayWpm);
            $('#liveAccuracy').text(evalResult.accuracy + '%');
            $('#liveErrors').text(evalResult.mistakes);
            $('#liveWords').text(evalResult.total);
    }
    const calculateStats = updateLiveStats;

    // ── 5. Helper: Current Word Being Typed ──
    function getCurrentTypedWord() {
        const val = typingInput.val();
        if (!val) return '';
        const lastSpace = val.lastIndexOf(' ');
        if (lastSpace === -1) return val;
        return val.substring(lastSpace + 1);
    }

    // ── 6. Finish & Save Test ──
    function finishTest() {
        if (isFinished) return;
        isFinished = true;

        if (timerInterval) clearInterval(timerInterval);
        typingInput.prop('disabled', true);
        $('#submitTestBtn, #sidebarSubmitBtn').prop('disabled', true);

        // Include last word if user didn't press space at the very end
        const lastWord = getCurrentTypedWord().trim();
        if (lastWord.length > 0 && currentWordIdx < testWords.length) {
            typedWords.push(lastWord);
        }

        // Exact elapsed time in seconds and minutes
        const finalElapsedSec = Math.max(1, totalDurationSec - timeLeft);
        const finalElapsedMin = finalElapsedSec / 60;

        // Final evaluation
        const evalResult = evaluateCurrentWords();
        const totalWords = evalResult.total;
        const correctWords = evalResult.correct;
        const errors = evalResult.mistakes;
        const accuracy = evalResult.accuracy;

        // Final exact speeds
        // WPM = Correct Words / Minutes
        const finalWpm = finalElapsedMin > 0 ? Math.round(correctWords / finalElapsedMin) : 0;
        // Gross WPM = Total Words / Minutes
        const finalGrossWpm = finalElapsedMin > 0 ? Math.round(totalWords / finalElapsedMin) : 0;

        Swal.fire({
            title: 'Processing Results...',
            didOpen: () => { Swal.showLoading(); },
            allowOutsideClick: false
        });

        fetch('ajax_save_result.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                test_id: <?= $id ?>,
                wpm: finalGrossWpm,
                net_wpm: finalWpm,
                accuracy: accuracy,
                errors: errors,
                total_words: totalWords,
                correct_words: correctWords,
                test_time: finalElapsedSec,
                typed_text: typingInput.val()
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const statsHtml = `
                    <div class="row pt-3">
                        <div class="col-4"><h5>${finalWpm}</h5><p class="small text-muted">WPM SPEED</p></div>
                        <div class="col-4"><h5>${correctWords}/${totalWords}</h5><p class="small text-muted">CORRECT/TOTAL</p></div>
                        <div class="col-4"><h5>${accuracy}%</h5><p class="small text-muted">ACCURACY</p></div>
                    </div>
                `;

                Swal.fire({
                    title: '<span style="color:#28a745">Test Completed!</span>',
                    html: statsHtml + `
                        <div class="alert alert-light border mt-2">
                            Errors: <b>${errors}</b> | Words: <b>${totalWords}</b> | Time: <b>${Math.floor(finalElapsedSec/60)}m ${finalElapsedSec%60}s</b>
                        </div>
                    `,
                    icon: 'success',
                    confirmButtonText: 'View Detailed Report',
                    allowOutsideClick: false
                }).then(() => {
                    if (data.result_id) {
                        window.location.href = 'typing-report.php?id=' + data.result_id;
                    } else {
                        window.location.href = 'english.php';
                    }
                });
            } else {
                Swal.fire('Error', data.message || 'Failed to save result', 'error');
            }
        })
        .catch(err => Swal.fire('Error', 'Connection error: ' + err, 'error'));
    }

    // ── 7. Update Active Word Highlighting ──
    function updateHighlight() {
        $('.word').removeClass('current error-input');
        if (!isFinished) {
            const hlMode = $('input[name="highlightOpt"]:checked').val();
            if (hlMode !== 'none') {
                const currentWordEl = $(`#word-${currentWordIdx}`);
                currentWordEl.addClass('current');
                
                if ($('#autoScroll').is(':checked')) {
                    const wordEl = currentWordEl[0];
                    if (wordEl) {
                        const box = textDisplay[0];
                        if (wordEl.offsetTop + wordEl.clientHeight > box.scrollTop + box.clientHeight - 30) {
                            box.scrollTop = wordEl.offsetTop - (box.clientHeight / 2);
                        }
                    }
                }
            }
        }
    }
            } else {
                Swal.fire('Error', data.message || 'Failed to save result', 'error');
            }
        })
        .catch(err => Swal.fire('Error', 'Connection error: ' + err, 'error'));
    }

    // Submit Test Button Handler (both bottom bar and sidebar)
    $('#submitTestBtn, #sidebarSubmitBtn').on('click', function() {
        if (!hasStartedTyping || isFinished) return;
        Swal.fire({
            title: 'Submit Test?',
            html: '<p>Are you sure you want to submit your test?</p><p class="text-muted small">Your current progress will be saved and the test will end.</p>',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#c62828',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-paper-plane mr-1"></i> Yes, Submit',
            cancelButtonText: 'Continue Typing'
        }).then((result) => {
            if (result.isConfirmed) {
                finishTest();
            } else {
                typingInput.focus();
            }
        });
    });

    typingInput.on('input', function() {
        if (isFinished) {
            return;
        }
        startTimer();

        let rawVal = $(this).val();
        
        // === CORE FIX: Robust word splitting ===
        // We track words by splitting on spaces, but we must handle:
        // 1. Multiple consecutive spaces (collapse them)
        // 2. Trailing space = word is finalized
        // 3. No trailing space = last segment is still being typed
        
        // Normalize: collapse multiple spaces into single space
        // But preserve trailing space (it means the user pressed space to move to next word)
        const hasTrailingSpace = rawVal.endsWith(' ');
        const normalizedVal = rawVal.replace(/\s+/g, ' ');
        
        // Update the textarea if normalization changed the value (prevent double-spaces)
        if (normalizedVal !== rawVal) {
            const cursorPos = this.selectionStart - (rawVal.length - normalizedVal.length);
            $(this).val(normalizedVal);
            this.selectionStart = this.selectionEnd = Math.max(0, cursorPos);
            rawVal = normalizedVal;
        }
        
        // Split into segments
        let segments = rawVal.split(' ');
        
        // If trailing space exists, the last segment is empty string — means previous word is done
        // Finalized words = all segments except the last one (which is being typed or empty)
        let finalizedSegments = segments.slice(0, -1); // All space-confirmed words
        let currentSegment = segments[segments.length - 1]; // Currently being typed
        
        // Filter out any remaining empty entries from finalized words
        finalizedSegments = finalizedSegments.filter(w => w.length > 0);
        
        // Update global tracking
        typedWords = finalizedSegments;
        currentWordIdx = typedWords.length; // The word user is currently typing

        const hlMode = $('input[name="highlightOpt"]:checked').val();
        
        // Update display for all words that need visual update
        const updateUpTo = Math.min(currentWordIdx + 2, words.length);
        for (let idx = 0; idx < updateUpTo; idx++) {
            const targetWord = words[idx];
            if (!targetWord) continue;
            
            const wordEl = $(`#word-${idx}`);
            if (!wordEl.length) continue;
            
            if (idx < currentWordIdx) {
                 // === Finalized word: compare typed vs target ===
                 const typed = typedWords[idx];
                 if (!typed) continue;
                 
                 wordEl.removeClass('current error-input');
                 if (hlMode !== 'none') {
                     if (typed === targetWord) {
                         wordEl.addClass('correct').removeClass('incorrect');
                         wordEl.text(targetWord);
                     } else {
                         wordEl.addClass('incorrect').removeClass('correct');
                         if (hlMode === 'wordErr') {
                              let html = '';
                              for (let i = 0; i < targetWord.length; i++) {
                                  if (i < typed.length) {
                                      if (typed[i] === targetWord[i]) {
                                          html += `<span style="color: #2e7d32;">${targetWord[i]}</span>`;
                                      } else {
                                          html += `<span style="color: #c62828;">${targetWord[i]}</span>`;
                                      }
                                  } else {
                                      html += `<span style="color: #c62828;">${targetWord[i]}</span>`;
                                  }
                              }
                              if (typed.length > targetWord.length) {
                                  let extra = typed.substring(targetWord.length);
                                  html += `<span style="color: #c62828; text-decoration: underline;">${extra}</span>`;
                              }
                              wordEl.html(html);
                         } else {
                              wordEl.text(targetWord);
                         }
                     }
                 } else {
                     wordEl.removeClass('correct incorrect');
                     wordEl.text(targetWord);
                 }
            } 
            else if (idx === currentWordIdx) {
                 // === Currently being typed ===
                 const typedVal = currentSegment;
                 wordEl.removeClass('correct incorrect');
                 
                 if (hlMode === 'none') {
                     wordEl.text(targetWord);
                 } else if (hlMode === 'wordErr') {
                     let html = '';
                     for (let i = 0; i < targetWord.length; i++) {
                         if (i < typedVal.length) {
                             if (typedVal[i] === targetWord[i]) {
                                 html += `<span style="color: #2e7d32;">${targetWord[i]}</span>`;
                             } else {
                                 html += `<span style="color: #c62828;">${targetWord[i]}</span>`;
                             }
                         } else {
                             html += targetWord[i];
                         }
                     }
                     if (typedVal.length > targetWord.length) {
                         let extra = typedVal.substring(targetWord.length);
                         html += `<span style="color: #c62828; text-decoration: underline;">${extra}</span>`;
                     }
                     wordEl.html(html);
                 } else if (hlMode === 'word') {
                     wordEl.text(targetWord);
                     if (typedVal.length > 0 && !targetWord.startsWith(typedVal)) {
                         wordEl.addClass('error-input');
                     } else {
                         wordEl.removeClass('error-input');
                     }
                 }
            }
            else {
                 // === Future words: reset to clean state ===
                 wordEl.removeClass('correct incorrect current error-input');
                 wordEl.text(targetWord);
            }
        }
        
        updateHighlight();
        calculateStats();

        // Auto-finish if all words are typed
        if (currentWordIdx >= words.length) finishTest();
    });

    typingInput.on('keydown', function(e) {
        if (isFinished) return;
        startTimer();

        const bsMode = $('input[name="backspaceOpt"]:checked').val();
        
        if (e.key === 'Backspace') {
            if (bsMode === 'none') { e.preventDefault(); return; }
            if (bsMode === 'one') {
                const val = typingInput.val();
                const selStart = this.selectionStart;
                const selEnd = this.selectionEnd;
                
                if (selStart !== selEnd) {
                    if (val.substring(selStart, selEnd).includes(' ')) {
                        e.preventDefault(); return;
                    }
                } else if (selStart > 0 && val[selStart - 1] === ' ') {
                    e.preventDefault(); return;
                }
            }
        }

        if (bsMode === 'one' && e.key.length === 1 && this.selectionStart !== this.selectionEnd) {
            const val = typingInput.val();
            if (val.substring(this.selectionStart, this.selectionEnd).includes(' ')) {
                e.preventDefault(); return;
            }
        }

        if (e.key === ' ') {
            const val = typingInput.val();
            if (val === '' || val.endsWith(' ') || (this.selectionStart > 0 && val[this.selectionStart-1] === ' ' && val[this.selectionStart] === ' ')) {
                e.preventDefault(); return;
            }
        }

        if (e.key === 'Enter') {
            e.preventDefault(); 
        }
    });

    typingInput.on('mousedown', function(e) {
        const bsMode = $('input[name="backspaceOpt"]:checked').val();
        if (bsMode !== 'full') {
            e.preventDefault();
            this.focus(); 
        }
    });
    
    // Add logic to reset current word display if highlight mode is changed midway
    $('.highlight-opt').change(function() {
        const wordEl = $(`#word-${currentWordIdx}`);
        if(wordEl.length){
            wordEl.text(words[currentWordIdx]);
        }
        updateHighlight();
        calculateStats();
    });

    // Settings listeners
    $('#durationSelect').change(function() {
        if (hasStartedTyping) {
            Swal.fire({
                title: 'Change Duration?',
                text: 'This will restart the test. All current progress will be lost.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#c62828',
                confirmButtonText: 'Yes, Restart'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.reload();
                }
            });
        } else {
            initTest();
        }
    });
    $('#examMode').change(function() {
        if (this.checked) $('#statPanel').css('visibility', 'hidden');
        else $('#statPanel').css('visibility', 'visible');
    });

    $('.mode-btn:contains("Go Exam Mode")').click(() => {
        $('#examMode').prop('checked', true).trigger('change');
    });

    $('div:contains("Add New Exercise")').css('cursor', 'pointer').click(() => {
        Swal.fire({
            title: 'Restart Test?',
            text: "All current progress will be lost.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            confirmButtonText: 'Yes, Restart'
        }).then((result) => {
            if (result.isConfirmed) window.location.reload();
        });
    });

    $('#fontInc').click(() => {
        let size = parseInt($('#fontSizeDisplay').text());
        size = Math.min(32, size + 2);
        $('#fontSizeDisplay').text(size);
        textDisplay.css('font-size', size + 'px');
        typingInput.css('font-size', size + 'px');
    });
    $('#fontDec').click(() => {
        let size = parseInt($('#fontSizeDisplay').text());
        size = Math.max(14, size - 2);
        $('#fontSizeDisplay').text(size);
        textDisplay.css('font-size', size + 'px');
        typingInput.css('font-size', size + 'px');
    });
    $('#boldToggle').change(function() {
        const weight = this.checked ? '700' : '400';
        textDisplay.css('font-weight', weight);
        typingInput.css('font-weight', weight);
    });

    $('#showScroll').change(function() {
        if (this.checked) {
            textDisplay.removeClass('no-scrollbar');
        } else {
            textDisplay.addClass('no-scrollbar');
        }
    });

    // Init state for scrollbar
    if (!$('#showScroll').is(':checked')) {
        textDisplay.addClass('no-scrollbar');
    }

</script>

</body>
</html>

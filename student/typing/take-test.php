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
            padding: 20px 25px; font-family: 'Consolas', 'Roboto Mono', monospace; font-size: <?= $test['language'] === 'Hindi' ? '' : '14' ?>px; line-height: 1.8;
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
            padding: 20px 25px; font-family: 'Consolas', 'Roboto Mono', monospace; font-size: <?= $test['language'] === 'Hindi' ? '24' : '14' ?>px; width: calc(100% - 30px);
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
        <?php if ($test['language'] === 'Hindi'): ?>
        .text-display-box, .typing-input-box {
            font-family: 'Kruti Dev 010', sans-serif !important;
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
                <select id="durationSelect" class="form-control form-control-sm" style="background: rgba(255,255,255,0.1); color: #fff; border: 1px solid rgba(255,255,255,0.2); width: auto; font-size: 13px; font-weight: 600; cursor: pointer; height: 32px; padding: 0 10px; border-radius: 4px; box-shadow: none;">
                    <option value="1" style="color:#000;">1 Min</option>
                    <option value="2" style="color:#000;">2 Min</option>
                    <option value="5" style="color:#000;">5 Min</option>
                    <option value="10" selected style="color:#000;">10 Min</option>
                    <option value="15" style="color:#000;">15 Min</option>
                </select>
            </div>
            
            <!-- Right Side: Tools -->
            <div style="display: flex; flex-direction: row; align-items: center;">
                <!-- Font Size Controls -->
                <div style="display: flex; flex-direction: row; align-items: center; background: rgba(0,0,0,0.15); border: 1px solid rgba(255,255,255,0.1); border-radius: 6px; padding: 4px 8px; margin-right: 20px; height: 34px;">
                    <span style="opacity: 0.85; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px; font-weight: 700; margin-right: 12px;">Size:</span>
                    <button id="fontDec" title="Decrease Font Size" style="background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.2); color: #fff; width: 24px; height: 24px; border-radius: 4px; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 16px; font-weight: bold; padding: 0; line-height: 1; outline: none;">&minus;</button>
                    <span id="fontSizeDisplay" style="font-weight: 700; width: 28px; text-align: center; display: inline-block; font-size: 14px; margin: 0 4px;"><?= $test['language'] === 'Hindi' ? '24' : '14' ?></span>
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
        
        <div class="text-center py-2" style="font-size:12px; color:#555; background: #e0e4ef; font-weight: 500; flex-shrink: 0;">
            <i class="fas fa-info-circle text-primary mr-1"></i> Timer will start automatically when you begin typing.
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
                    <div id="wpmLbl" class="stat-lbl">Gross WPM</div>
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

        <button class="btn btn-block btn-outline-danger btn-sm font-weight-bold" onclick="window.location.reload();">
            <i class="fas fa-sync-alt mr-1"></i> RESTART EXERCISE
        </button>
    </div>
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    const testContent = <?= json_encode(str_replace(["\r", "\n", "\t"], ' ', strip_tags($test['content']))) ?>;
    const defaultTime = <?= (int)$test['test_time'] ?>;
    
    let words = (testContent || '').split(/\s+/).filter(w => w.length > 0);
    let currentWordIdx = 0;
    let typedWords = [];
    let startTime = null;
    let timerInterval = null;
    let timeLeft = defaultTime * 60;
    let isFinished = false;

    // UI Elements
    const textDisplay = $('#textDisplay');
    const typingInput = $('#typingInput');
    const timerDisplay = $('#timerDisplay');
    
    $(document).ready(function() {
        initTest();
    });
    
    function initTest() {
        try {
            if (!words || words.length === 0) {
                textDisplay.html('<div class="alert alert-warning">No content found for this test.</div>');
                return;
            }
            textDisplay.empty();
            words.forEach((word, idx) => {
                textDisplay.append(`<span class="word" id="word-${idx}">${word}</span> `);
            });
            updateHighlight();
            var placeholderText = <?= $test['language'] === 'Hindi' ? "'ऊपर दिखाए गए पाठ को टाइप करना शुरू करें...'" : "'Start typing the text shown above...'" ?>;
            typingInput.prop('disabled', false).val('').attr('placeholder', placeholderText);
            timeLeft = $('#durationSelect').val() * 60;
            updateTimerDisplay();
        } catch (e) {
            console.error("Init Error:", e);
            textDisplay.html('<div class="alert alert-danger">Error loading test engine.</div>');
        }
    }

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

    function updateTimerDisplay() {
        const m = Math.floor(timeLeft / 60);
        const s = timeLeft % 60;
        timerDisplay.text(`${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}`);
    }

    function startTimer() {
        if (startTime) return;
        startTime = new Date();
        timerInterval = setInterval(() => {
            timeLeft--;
            updateTimerDisplay();
            calculateStats();
            if (timeLeft <= 0) finishTest();
        }, 1000);
    }

    function calculateStats() {
        const timeSpentMin = (new Date() - startTime) / 60000;
        if (timeSpentMin <= 0) return;

        let totalChars = 0;
        let totalCorrectChars = 0;
        let errors = 0;
        let correctWords = 0;

        typedWords.forEach((typed, idx) => {
            const target = words[idx];
            if (typed === target) {
                correctWords++;
                totalChars += target.length + 1;
                totalCorrectChars += target.length + 1;
            } else {
                errors++;
                totalChars += Math.max(typed.length, target.length) + 1;
                for (let i = 0; i < Math.max(typed.length, target.length); i++) {
                    if (i < typed.length && i < target.length && typed[i] === target[i]) {
                        totalCorrectChars++;
                    }
                }
            }
        });

        const currentTyped = typingInput.val().trim();
        const currentTarget = words[currentWordIdx];
        totalChars += currentTyped.length;
        if (currentTarget) {
            for (let i = 0; i < Math.min(currentTyped.length, currentTarget.length); i++) {
                if (currentTyped[i] === currentTarget[i]) totalCorrectChars++;
            }
        }

        const hlMode = $('input[name="highlightOpt"]:checked').val();
        const grossWpm = Math.round((totalChars / 5) / timeSpentMin);
        const cpctSpeed = Math.round(totalCorrectChars / timeSpentMin); 
        const accuracy = Math.round((totalCorrectChars / Math.max(1, totalChars)) * 100);

        if (hlMode === 'none') {
            $('#liveWpm').text('---');
            $('#liveAccuracy').text('---');
            $('#liveErrors').text('---');
            $('#liveWords').text('---');
        } else if (hlMode === 'wordErr') {
            $('#liveWpm').text(cpctSpeed);
            $('#wpmLbl').text("CPCT Speed");
            $('#liveAccuracy').text(accuracy + '%');
            $('#liveErrors').text(errors);
            $('#liveWords').text(totalCorrectChars);
            $('#wordsLbl').text("Correct Chars");
        } else {
            $('#liveWpm').text(grossWpm);
            $('#wpmLbl').text("Gross WPM");
            $('#liveAccuracy').text((typedWords.length > 0 ? Math.round((correctWords / typedWords.length) * 100) : 100) + '%');
            $('#liveErrors').text(errors);
            $('#liveWords').text(typedWords.length);
            $('#wordsLbl').text("Words Typed");
        }
    }

    function finishTest() {
        if (isFinished) return;
        isFinished = true;
        clearInterval(timerInterval);
        typingInput.prop('disabled', true);

        const durSec = ($('#durationSelect').val() * 60) - timeLeft;
        const timeSpentMin = durSec / 60;
        
        let totalChars = 0;
        let totalCorrectChars = 0;
        let errorsCount = 0;
        let correctWordsCount = 0;

        typedWords.forEach((typed, idx) => {
            const target = words[idx];
            if (typed === target) {
                correctWordsCount++;
                totalChars += target.length + 1;
                totalCorrectChars += target.length + 1;
            } else {
                errorsCount++;
                totalChars += Math.max(typed.length, target.length) + 1;
                for (let i = 0; i < Math.max(typed.length, target.length); i++) {
                    if (i < typed.length && i < target.length && typed[i] === target[i]) {
                        totalCorrectChars++;
                    }
                }
            }
        });

        const currentTyped = typingInput.val().trim();
        const currentTarget = words[currentWordIdx];
        totalChars += currentTyped.length;
        if (currentTarget) {
            for (let i = 0; i < Math.min(currentTyped.length, currentTarget.length); i++) {
                if (currentTyped[i] === currentTarget[i]) totalCorrectChars++;
            }
        }

        const hlMode = $('input[name="highlightOpt"]:checked').val();
        let finalWpm = 0, finalAccuracy = 100, finalErrors = errorsCount;
        
        if (hlMode === 'wordErr') {
            finalWpm = timeSpentMin > 0 ? Math.round(totalCorrectChars / timeSpentMin) : 0;
            finalAccuracy = totalChars > 0 ? Math.round((totalCorrectChars / totalChars) * 100) : 100;
        } else {
            finalWpm = timeSpentMin > 0 ? Math.round((totalChars / 5) / timeSpentMin) : 0;
            finalAccuracy = typedWords.length > 0 ? Math.round((correctWordsCount / typedWords.length) * 100) : 100;
        }

        const wpm = finalWpm;
        const accuracy = finalAccuracy;
        const errors = finalErrors;
        const totalWords = typedWords.length;
        const correctWords = correctWordsCount;

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
                wpm: wpm,
                accuracy: accuracy,
                errors: errors,
                total_words: totalWords,
                correct_words: correctWords,
                test_time: durSec
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                let statsHtml = '';
                if (hlMode === 'wordErr') {
                    statsHtml = `
                        <div class="row pt-3">
                            <div class="col-4"><h5>${wpm}</h5><p class="small text-muted">CPCT SPEED<br><small>(CPM)</small></p></div>
                            <div class="col-4"><h5>${totalCorrectChars}</h5><p class="small text-muted">CORRECT<br>CHARS</p></div>
                            <div class="col-4"><h5>${accuracy}%</h5><p class="small text-muted">ACCURACY</p></div>
                        </div>
                    `;
                } else {
                    statsHtml = `
                        <div class="row pt-3">
                            <div class="col-4"><h5>${wpm}</h5><p class="small text-muted">GROSS WPM</p></div>
                            <div class="col-4"><h5>${data.net_wpm}</h5><p class="small text-muted">NET WPM</p></div>
                            <div class="col-4"><h5>${accuracy}%</h5><p class="small text-muted">ACCURACY</p></div>
                        </div>
                    `;
                }

                Swal.fire({
                    title: '<span style="color:#28a745">Test Completed!</span>',
                    html: statsHtml + `
                        <div class="alert alert-light border mt-2">
                            Errors: <b>${errors}</b> | Time Spent: <b>${Math.floor(durSec/60)}m ${durSec%60}s</b>
                        </div>
                    `,
                    icon: 'success',
                    confirmButtonText: 'Back to Dashboard',
                    allowOutsideClick: false
                }).then(() => {
                    window.location.href = 'english.php';
                });
            } else {
                Swal.fire('Error', data.message || 'Failed to save result', 'error');
            }
        })
        .catch(err => Swal.fire('Error', 'Connection error' + err, 'error'));
    }

    typingInput.on('input', function() {
        if (isFinished) {
            $(this).val( $(this).val() ); 
            return;
        }
        startTimer();

        let rawVal = $(this).val();
        
        // Split by standard spaces
        let typedArr = rawVal.split(' ');
        currentWordIdx = typedArr.length - 1;
        
        // Update typedWords array for finished words (everything except the last incomplete word)
        typedWords = typedArr.slice(0, currentWordIdx);

        const hlMode = $('input[name="highlightOpt"]:checked').val();
        
        // Evaluate words up to the current one + ahead to clean up
        for (let idx = 0; idx <= currentWordIdx + 1; idx++) {
            const targetWord = words[idx];
            if (!targetWord) continue;
            
            const wordEl = $(`#word-${idx}`);
            
            if (idx < currentWordIdx) {
                 const typed = typedArr[idx];
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
                                      html += `<span style="color: #c62828;">${targetWord[i]}</span>`; // Untyped chars in missed word are errors
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
                 const typedVal = typedArr[idx];
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
                     if (!targetWord.startsWith(typedVal)) {
                         wordEl.addClass('error-input');
                     } else {
                         wordEl.removeClass('error-input');
                     }
                 }
            }
            else {
                 wordEl.removeClass('correct incorrect current error-input');
                 wordEl.text(targetWord);
            }
        }
        
        updateHighlight();
        calculateStats();

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
    $('#durationSelect').change(() => initTest());
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

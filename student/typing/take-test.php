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
        }
        body { font-family: 'Inter', sans-serif; background: #e0e4ef; height: 100vh; overflow: hidden; }
        
        /* Layout */
        .test-wrapper { display: flex; height: 100vh; width: 100vw; }
        .sidebar-left { width: 80px; background: #fff; border-right: 1px solid #d1d5db; display: flex; flex-direction: column; align-items: center; padding: 20px 0; }
        .sidebar-right { width: 300px; background: #eaedf4; border-left: 1px solid #c8ced9; padding: 15px; overflow-y: auto; }
        .main-container { flex: 1; display: flex; flex-direction: column; background: #cbd5e1; padding: 0; }

        /* Top Bar */
        .top-nav { height: 40px; background: #fff; display: flex; align-items: center; justify-content: space-between; padding: 0 15px; border-bottom: 2px solid #28a745; }
        .top-nav .mode-btn { border: 1px solid #c62828; color: #c62828; font-size: 11px; padding: 2px 8px; border-radius: 3px; font-weight: bold; cursor: pointer; }

        /* Content Areas */
        .text-display-box { 
            flex: 1; background: #fff; margin: 15px; border-radius: 4px; border: 1px solid #999;
            padding: 20px; font-family: 'Roboto Mono', monospace; font-size: 20px; line-height: 1.6;
            overflow-y: auto; color: #333; position: relative;
        }
        .typing-input-box {
            height: 350px; background: #fff; margin: 0 15px 15px; border-radius: 4px; border: 1px solid #999;
            padding: 15px; font-family: 'Roboto Mono', monospace; font-size: 20px; width: calc(100% - 30px);
            resize: none; outline: none; border-top: 5px solid #8e9dbf;
        }

        /* Stats Bar */
        .info-bar { height: 45px; background: #8e9dbf; display: flex; align-items: center; padding: 0 15px; color: #fff; font-size: 14px; font-weight: 600; gap: 20px; }
        .info-bar select { border: none; border-radius: 3px; font-size: 12px; padding: 2px 5px; }

        /* Words & Highlighting */
        .word { display: inline-block; margin-right: 8px; border-bottom: 2px solid transparent; }
        .word.current { background: #fff59d; border-radius: 3px; }
        .word.error-input { background: #ffcdd2 !important; color: #c62828 !important; }
        .word.correct { color: #2e7d32; }
        .word.incorrect { color: #c62828; border-bottom: 2px solid #c62828; }

        /* Sidebar Settings */
        .settings-section { background: #ffffff55; border: 1px solid #ffffffaa; border-radius: 5px; padding: 10px; margin-bottom: 15px; }
        .settings-title { font-size: 12px; font-weight: 700; color: #444; margin-bottom: 8px; border-bottom: 1px solid #ddd; padding-bottom: 5px; }
        .form-check-label { font-size: 13px; color: #555; }
        .form-check { margin-bottom: 5px; }

        /* Stats Display */
        .stat-item { margin-bottom: 10px; }
        .stat-val { font-size: 24px; font-weight: bold; color: #28a745; }
        .stat-lbl { font-size: 11px; text-transform: uppercase; color: #666; }

        /* Overlay */
        #resultOverlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.85); display: none; align-items: center; justify-content: center; z-index: 9999;
        }
    </style>
</head>
<body>

<div class="test-wrapper">
    <!-- Left Sidebar: Font Options -->
    <div class="sidebar-left">
        <label style="font-size:10px; font-weight:bold;">Select Font</label>
        <div class="mt-2">
            <input type="checkbox" id="boldToggle"> <span style="font-size:11px;">Bold</span>
        </div>
    </div>

    <!-- Main Section -->
    <div class="main-container">
        <div class="top-nav">
            <div class="mode-btn">Go Printout Mode</div>
            <div style="font-size:12px; color:#28a745; font-weight:bold;">Add New Exercise</div>
            <div class="mode-btn">Go Exam Mode</div>
        </div>

        <!-- Reference Text Box -->
        <div id="textDisplay" class="text-display-box">
            <!-- Content will be injected by JS -->
            Loading test content...
        </div>

        <!-- Info Bar -->
        <div class="info-bar">
            <div>Duration: 
                <select id="durationSelect">
                    <option value="1">1 Minute</option>
                    <option value="2">2 Minutes</option>
                    <option value="5">5 Minutes</option>
                    <option value="10" selected>10 Minutes</option>
                    <option value="15">15 Minutes</option>
                </select>
            </div>
            <div class="flex-grow-1 text-center">Exercise : <span id="exerciseIdx">1/1</span></div>
            <div class="d-flex align-items-center gap-2">
                <button class="btn btn-xs btn-light" id="fontDec"><i class="fas fa-minus"></i></button>
                <span id="fontSizeDisplay">20</span>
                <button class="btn btn-xs btn-light" id="fontInc"><i class="fas fa-plus"></i></button>
            </div>
        </div>

        <!-- Typing Input Area -->
        <textarea id="typingInput" class="typing-input-box" placeholder="Wait for the timer to load, then start typing here..." disabled></textarea>
        
        <div class="text-center pb-2" style="font-size:10px; color:#555;">
            Select test duration and start typing. Timer will start automatically.
        </div>
    </div>

    <!-- Right Sidebar: Settings & Live Stats -->
    <div class="sidebar-right">
        <div class="settings-section" style="background: #fff; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
            <div class="row text-center">
                <div class="col-6 mb-3">
                    <div id="liveWpm" class="stat-val">0</div>
                    <div class="stat-lbl">WPM</div>
                </div>
                <div class="col-6 mb-3">
                    <div id="liveAccuracy" class="stat-val">0%</div>
                    <div class="stat-lbl">Accuracy</div>
                </div>
                <div class="col-6">
                    <div id="liveErrors" class="stat-val" style="color:#c62828">0</div>
                    <div class="stat-lbl">Errors</div>
                </div>
                <div class="col-6">
                    <div id="timerDisplay" class="stat-val" style="color:#accent-blue">00:00</div>
                    <div class="stat-lbl">Time Left</div>
                </div>
            </div>
        </div>

        <div class="settings-section">
            <div class="settings-title">Backspace Options</div>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="backspaceOpt" id="bsFull" value="full" checked>
                <label class="form-check-label" for="bsFull">Full Backspace</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="backspaceOpt" id="bsOne" value="one">
                <label class="form-check-label" for="bsOne">One Word Backspace</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="backspaceOpt" id="bsNone" value="none">
                <label class="form-check-label" for="bsNone">Deactivate Backspace</label>
            </div>
        </div>

        <div class="settings-section">
            <div class="settings-title">Highlight Options</div>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="highlightOpt" id="hlWord" value="word" checked>
                <label class="form-check-label" for="hlWord">Word Highlight</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="highlightOpt" id="hlWordErr" value="wordErr">
                <label class="form-check-label" for="hlWordErr">Word + Error Highlight</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="highlightOpt" id="hlNone" value="none">
                <label class="form-check-label" for="hlNone">No Highlight</label>
            </div>
        </div>

        <div class="settings-section">
            <div class="settings-title">Scrollbar Options</div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="showScroll" checked>
                <label class="form-check-label" for="showScroll">Show Scrollbar</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="autoScroll" checked>
                <label class="form-check-label" for="autoScroll">Auto Scroll</label>
            </div>
        </div>

        <div class="settings-section">
            <div class="settings-title">Paragraph Settings</div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="applyWordLimit">
                <label class="form-check-label" for="applyWordLimit">Apply Word Limit</label>
            </div>
            <input type="number" id="wordLimitVal" class="form-control form-control-sm mt-2" value="500" disabled>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="../../admin/assets/plugins/jquery/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    const testContent = `<?= addslashes(str_replace(["\r", "\n"], ' ', strip_tags($test['content']))) ?>`;
    const defaultTime = <?= (int)$test['test_time'] ?>;
    
    let words = testContent.split(/\s+/).filter(w => w.length > 0);
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
    
    function initTest() {
        textDisplay.empty();
        words.forEach((word, idx) => {
            textDisplay.append(`<span class="word" id="word-${idx}">${word}</span> `);
        });
        updateHighlight();
        typingInput.prop('disabled', false).val('');
        timeLeft = $('#durationSelect').val() * 60;
        updateTimerDisplay();
    }

    function updateHighlight() {
        $('.word').removeClass('current error-input');
        if (!isFinished) {
            const currentWordEl = $(`#word-${currentWordIdx}`);
            currentWordEl.addClass('current');
            
            if ($('#autoScroll').is(':checked')) {
                const wordEl = currentWordEl[0];
                if (wordEl) {
                    const box = textDisplay[0];
                    const offset = wordEl.offsetTop - box.offsetTop;
                    if (offset > box.clientHeight / 2) {
                        box.scrollTop = offset - box.clientHeight / 2;
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
        let errors = 0;
        let correctWords = 0;

        typedWords.forEach((typed, idx) => {
            if (typed === words[idx]) {
                correctWords++;
                totalChars += words[idx].length + 1;
            } else {
                errors++;
            }
        });

        const currentTyped = typingInput.val().trim();
        totalChars += currentTyped.length;

        const grossWpm = Math.round((totalChars / 5) / timeSpentMin);
        const accuracy = typedWords.length > 0 ? Math.round((correctWords / typedWords.length) * 100) : 100;

        $('#liveWpm').text(grossWpm);
        $('#liveAccuracy').text(accuracy + '%');
        $('#liveErrors').text(errors);
    }

    function finishTest() {
        if (isFinished) return;
        isFinished = true;
        clearInterval(timerInterval);
        typingInput.prop('disabled', true);
        Swal.fire({
            title: 'Test Completed!',
            html: `Results: <b>${$('#liveWpm').text()} WPM</b> | Accuracy: <b>${$('#liveAccuracy').text()}</b>`,
            icon: 'success',
            confirmButtonText: 'Back to Dashboard'
        }).then(() => {
            window.location.href = 'english.php';
        });
    }

    typingInput.on('input', function() {
        if (isFinished) return;
        const hlMode = $('input[name="highlightOpt"]:checked').val();
        if (hlMode === 'wordErr') {
            const typedVal = $(this).val();
            const targetWord = words[currentWordIdx];
            const currentWordEl = $(`#word-${currentWordIdx}`);
            if (!targetWord.startsWith(typedVal)) {
                currentWordEl.addClass('error-input');
            } else {
                currentWordEl.removeClass('error-input');
            }
        }
        calculateStats();
    });

    typingInput.on('keydown', function(e) {
        if (isFinished) return;
        startTimer();

        const bsMode = $('input[name="backspaceOpt"]:checked').val();
        if (e.key === 'Backspace') {
            if (bsMode === 'none') { e.preventDefault(); return; }
            if (bsMode === 'one' && typingInput.val() === '') { e.preventDefault(); return; }
        }

        if (e.key === ' ' || e.key === 'Enter') {
            e.preventDefault();
            const typed = typingInput.val().trim();
            if (typed.length > 0 || e.key === ' ') {
                typedWords[currentWordIdx] = typed;
                const wordEl = $(`#word-${currentWordIdx}`);
                wordEl.removeClass('error-input');
                if (typed === words[currentWordIdx]) {
                    wordEl.addClass('correct').removeClass('incorrect');
                } else {
                    wordEl.addClass('incorrect').removeClass('correct');
                }
                currentWordIdx++;
                typingInput.val('');
                updateHighlight();
                if (currentWordIdx >= words.length) finishTest();
            }
        }
    });

    // Settings listeners
    $('#durationSelect').change(() => initTest());
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

    // Initialize
    initTest();

</script>

</body>
</html>

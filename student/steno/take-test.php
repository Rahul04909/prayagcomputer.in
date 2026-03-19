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
                           FROM steno_tests t 
                           LEFT JOIN steno_exam_categories c ON t.category_id = c.id 
                           WHERE t.id = ? AND t.status = 1");
    $stmt->execute([$id]);
    $test = $stmt->fetch();

    if (!$test) {
        die("Test not found or inactive.");
    }
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// Student context
$student_data = get_current_student_data();
$student_name = $student_data['student_name'] ?? 'Student';
$student_image = !empty($student_data['image']) ? '../../admin/' . $student_data['image'] : '../src/images/user-avtar.png';
$steno_access  = $student_data['steno_access'] ?? 'None';

$access_granted = false;
if ($steno_access === 'Both') {
    $access_granted = true;
} else if ($test['language'] === 'English' && in_array($steno_access, ['English', 'Both', 'All'])) {
    $access_granted = true;
} else if ($test['language'] === 'Hindi' && in_array($steno_access, ['Hindi', 'Both', 'All'])) {
    $access_granted = true;
}

if (!$access_granted) {
    die("<div style='text-align:center; padding:50px; font-family:sans-serif;'>
            <h2 style='color:#c62828;'>Access Denied</h2>
            <p>You are not a registered candidate for this module. Please contact your administrator.</p>
            <a href='../index.php' style='display:inline-block; margin-top:20px; padding:10px 20px; background:#28a745; color:#fff; text-decoration:none; border-radius:5px;'>Return to Dashboard</a>
         </div>");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Steno Test: <?= htmlspecialchars($test['title']) ?></title>
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
        .sidebar-right { width: 300px; background: #eaedf4; border-left: 1px solid #c8ced9; padding: 10px; overflow-y: auto; flex-shrink: 0; display: flex; flex-direction: column;}
        .main-container { flex: 1; display: flex; flex-direction: column; background: #cbd5e1; height: 100vh; min-width: 0; }

        /* Top Nav */
        .top-nav { height: 45px; background: #fff; display: flex; align-items: center; justify-content: center; padding: 0 15px; border-bottom: 2px solid #28a745; flex-shrink: 0; }

        /* Content Areas */
        .top-status-box { 
            background: #fff; margin: 10px 15px; border-radius: 6px; border: 1px solid #ced4da;
            padding: 20px 25px; display: flex; flex-direction: column; align-items: center; justify-content: center;
            color: #212529; position: relative; box-shadow: 0 2px 5px rgba(0,0,0,0.02); height: 120px; flex-shrink: 0;
        }
        
        .info-bar { 
            height: 46px; background: #2c3e50; display: flex; align-items: center; justify-content: space-between; 
            padding: 0 15px; color: #fff; flex-shrink: 0; border-bottom: 1px solid #1a252f; border-top: 1px solid #34495e; box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .typing-input-box {
            flex: 1; min-height: 200px; background: #fff; margin: 10px 15px; border-radius: 6px; border: 1px solid #ced4da;
            padding: 20px 25px; font-family: 'Consolas', 'Roboto Mono', monospace; font-size: <?= $test['language'] === 'Hindi' ? '18' : '16' ?>px; width: calc(100% - 30px);
            resize: none; outline: none; border-top: 4px solid var(--header-bg); flex-shrink: 0; box-shadow: 0 2px 5px rgba(0,0,0,0.02);
            letter-spacing: 0.5px; line-height: 1.8; color: #0d6efd;
        }
        .typing-input-box:focus { border-top: 4px solid #28a745; }
        .typing-input-box:disabled { background: #f8f9fa; cursor: not-allowed; }

        /* Sidebar Styling */
        .settings-section { background: #fff; border: 1px solid #d1d5db; border-radius: 6px; padding: 12px; margin-bottom: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .settings-title { font-size: 11px; font-weight: 800; color: #555; margin-bottom: 8px; border-bottom: 1px solid #eee; padding-bottom: 4px; text-transform: uppercase; letter-spacing: 0.5px; }
        .stat-val { font-size: 38px; font-weight: 800; line-height: 1; margin-bottom: 6px; font-family: 'Inter', sans-serif; color: #333; }
        .stat-lbl { font-size: 11px; text-transform: uppercase; color: #777; font-weight: 700; letter-spacing: 0.5px; margin-top: 0; }
        .timer-val { font-size: 38px; font-weight: 800; color: #c62828; font-family: 'Roboto Mono', monospace; }

        /* Fonts Integration */
        @font-face { font-family: 'Kruti Dev 010'; src: url('../assets/fonts/krutidev-010-hindi-font-download.ttf') format('truetype'); }
        
        <?php if ($test['language'] === 'Hindi'): ?>
        .typing-input-box { font-family: 'Kruti Dev 010', sans-serif !important; }
        <?php endif; ?>

        /* Audio Element Skinning */
        audio { outline: none; border-radius: 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); width: 80%; max-width: 600px; }
        audio::-webkit-media-controls-panel { background-color: #f1f3f5; }
        audio::-webkit-media-controls-play-button { background-color: #28a745; border-radius: 50%; }

        .phase-indicator { background: #e3f2fd; color: #0d6efd; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; text-transform: uppercase; margin-bottom: 5px;}
        .phase-indicator.active-dictation { background: #fff3cd; color: #856404; }
        .phase-indicator.active-buffer { background: #e3f2fd; color: #0d6efd; }
        .phase-indicator.active-transcribing { background: #d4edda; color: #155724; }
    </style>
</head>
<body>

<div class="test-wrapper">
    <!-- Main Section -->
    <div class="main-container">
        <div class="top-nav">
            <div style="font-size:14px; color:#28a745; font-weight:800; letter-spacing:1px;"><?= htmlspecialchars($test['title']) ?></div>
        </div>

        <!-- Top Status Area (Phase dependent display) -->
        <div class="top-status-box" id="statusBox">
            <span class="phase-indicator active-dictation" id="phaseTag"><i class="fas fa-headphones mr-1"></i> Phase 1: Dictation</span>
            <h4 class="mb-3" id="statusMessage" style="font-weight: 700; color: #343a40;">Please listen to the dictation and take your shorthand notes.</h4>
            <?php if (!empty($test['audio_file'])): ?>
                <audio id="dictationAudio" controls controlsList="nodownload">
                    <source src="../../admin/steno/uploads/<?= htmlspecialchars($test['audio_file']) ?>" type="audio/mpeg">
                    Your browser does not support the audio element.
                </audio>
            <?php else: ?>
                <div class="alert alert-danger w-100 text-center">Audio file missing.</div>
            <?php endif; ?>
            <div id="bufferUi" style="display:none; text-align:center;">
                <p class="text-muted" style="font-size:15px; margin:0;">Your transcription test will start automatically when the timer expires.</p>
                <button id="skipBufferBtn" class="btn btn-outline-primary btn-sm mt-3"><i class="fas fa-forward mr-1"></i> Skip Delay & Start Typing</button>
            </div>
            <div id="typingUi" style="display:none; text-align:center;">
                <p class="text-success" style="font-size:15px; margin:0; font-weight:600;"><i class="fas fa-keyboard mr-1"></i> Transcription phase has started. Please translate your shorthand notes down below.</p>
            </div>
        </div>

        <!-- Info Bar (Tools) -->
        <div class="info-bar">
            <!-- Left Side: Config Info -->
            <div style="display: flex; flex-direction: row; align-items: center; margin-right: 20px;">
                <span style="opacity: 0.85; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px; font-weight: 700; margin-right: 6px;"><i class="fas fa-cog mr-1"></i> Exam Config:</span>
                <span class="badge badge-light" style="color:#000; font-weight:600; font-size:11px; margin-right:10px;"><?= $test['language'] ?> Steno</span>
                <span class="badge badge-warning text-dark" style="font-weight:600; font-size:11px; margin-right:10px;">Audio Base</span>
            </div>
            
            <!-- Right Side: Tools -->
            <div style="display: flex; flex-direction: row; align-items: center;">
                <div style="display: flex; flex-direction: row; align-items: center; background: rgba(0,0,0,0.25); border: 1px solid rgba(255,255,255,0.1); border-radius: 6px; padding: 4px 8px; margin-right: 20px; height: 34px;">
                    <span style="opacity: 0.85; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px; font-weight: 700; margin-right: 12px;">Text Size:</span>
                    <button id="fontDec" style="background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.2); color: #fff; width: 24px; height: 24px; border-radius: 4px; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 16px; font-weight: bold; padding: 0; line-height: 1; outline: none;">&minus;</button>
                    <span id="fontSizeDisplay" style="font-weight: 700; width: 28px; text-align: center; display: inline-block; font-size: 14px; margin: 0 4px;"><?= $test['language'] === 'Hindi' ? '18' : '16' ?></span>
                    <button id="fontInc" style="background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.2); color: #fff; width: 24px; height: 24px; border-radius: 4px; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 16px; font-weight: bold; padding: 0; line-height: 1; outline: none;">+</button>
                </div>
            </div>
        </div>

        <!-- Typing Input Area -->
        <form id="submissionForm" action="steno-result.php" method="POST" style="flex:1; display:flex; flex-direction:column; margin:0; padding:0;">
            <input type="hidden" name="test_id" value="<?= $id ?>">
            <input type="hidden" name="time_spent" id="timeSpentInput" value="0">
            <textarea id="typingInput" name="translation_text" class="typing-input-box" placeholder="Audio dictation must finish before transcription unlocks..." disabled spellcheck="false"></textarea>
        </form>
    </div>

    <!-- Right Sidebar: Settings & Live Stats -->
    <div class="sidebar-right">
        
        <!-- User Profile Card -->
        <div class="settings-section text-center p-3" style="background: #fff; border-top: 4px solid var(--primary-green);">
            <img src="<?= htmlspecialchars($student_image) ?>" alt="Student" style="width:60px; height:60px; border-radius:50%; object-fit:cover; margin-bottom:10px; border:2px solid #eaedf4;">
            <div style="font-weight: 800; color: #343a40; font-size:15px; line-height:1.2;"><?= htmlspecialchars($student_name) ?></div>
            <div style="font-size:11px; color:#777; font-weight:600; text-transform:uppercase; margin-top:3px;"><i class="fas fa-check-circle text-success mr-1"></i> Registered Candidate</div>
        </div>

        <!-- Timer Section -->
        <div class="settings-section text-center py-4" style="border: 2px solid #ccc;" id="timerContainer">
            <div class="stat-lbl mb-2" id="timerLabel">Audio Playing</div>
            <div id="timerDisplay" class="timer-val" style="color: #6c757d;">--:--</div>
        </div>

        <div style="flex-grow:1;"></div> <!-- Spacer -->

        <!-- Submission Actions -->
        <div class="settings-section" style="background:transparent; border:none; box-shadow:none; padding:0;">
            <button id="finalSubmitBtn" class="btn btn-success btn-lg btn-block shadow-sm" style="font-weight:700; border-radius:8px; display:none;">
                <i class="fas fa-paper-plane mr-2"></i> Submit Exam
            </button>
            <button class="btn btn-block btn-outline-secondary btn-sm mt-3 font-weight-bold" onclick="if(confirm('Are you sure you want to abandon this test?')) window.location.href='<?= strtolower($test['language']) ?>.php';">
                <i class="fas fa-times mr-1"></i> ABANDON TEST
            </button>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    const bufferMinutes   = <?= (int)($test['buffer_time'] ?? 0) ?>;
    const durationMinutes = <?= (int)($test['test_duration'] ?? 10) ?>;
    
    let currentPhase = 'DICTATION'; // DICTATION -> BUFFER -> TYPING -> SUBMITTED
    let phaseTimer = null;
    let secondsLeft = 0;
    let examStartTime = 0;

    const audioEl = document.getElementById('dictationAudio');
    const timerDisplay = $('#timerDisplay');
    const timerLabel = $('#timerLabel');
    const timerContainer = $('#timerContainer');
    const typingInput = $('#typingInput');
    const phaseTag = $('#phaseTag');
    const statusMessage = $('#statusMessage');
    const skipBufferBtn = $('#skipBufferBtn');

    // Prevent copy paste
    typingInput.bind('cut copy paste', function (e) {
        e.preventDefault();
    });

    // 1. Audio Phase Logic ──────────────────────────────────────────
    if (audioEl) {
        audioEl.addEventListener('ended', function() {
            if (currentPhase === 'DICTATION') {
                startBufferPhase();
            }
        });
        audioEl.addEventListener('play', function() {
            timerDisplay.text('LIVE');
            timerLabel.text('Audio Playing');
            timerContainer.css('border-color', '#0d6efd');
            timerDisplay.css('color', '#0d6efd');
        });
        audioEl.addEventListener('pause', function() {
            if(currentPhase === 'DICTATION') {
                timerDisplay.text('PAUSED');
                timerContainer.css('border-color', '#ccc');
                timerDisplay.css('color', '#6c757d');
            }
        });
    } else {
        // If no audio attached, jump to buffer immediately
        startBufferPhase();
    }

    // 2. Buffer Phase Logic ──────────────────────────────────────────
    function startBufferPhase() {
        currentPhase = 'BUFFER';
        if (audioEl) audioEl.style.display = 'none';
        
        phaseTag.removeClass('active-dictation').addClass('active-buffer').html('<i class="fas fa-hourglass-half mr-1"></i> Phase 2: Reading Delay');
        statusMessage.text('Reading Delay: Please review your shorthand notes.');
        $('#bufferUi').show();

        if (bufferMinutes > 0) {
            secondsLeft = bufferMinutes * 60;
            timerLabel.text('Starts In');
            timerContainer.css('border-color', '#fd7e14');
            timerDisplay.css('color', '#fd7e14');
            
            updateTimerUi();
            phaseTimer = setInterval(function() {
                secondsLeft--;
                updateTimerUi();
                if (secondsLeft <= 0) {
                    clearInterval(phaseTimer);
                    startTypingPhase();
                }
            }, 1000);
        } else {
            startTypingPhase(); // Instantly jump
        }
    }

    skipBufferBtn.click(function() {
        if(currentPhase === 'BUFFER') {
            clearInterval(phaseTimer);
            startTypingPhase();
        }
    });

    // 3. Typing Phase Logic ──────────────────────────────────────────
    function startTypingPhase() {
        currentPhase = 'TYPING';
        $('#bufferUi').hide();
        $('#typingUi').show();
        
        phaseTag.removeClass('active-buffer').addClass('active-transcribing').html('<i class="fas fa-keyboard mr-1"></i> Phase 3: Transcription');
        statusMessage.text('Transcription Started');
        
        typingInput.prop('disabled', false).val('').attr('placeholder', 'Start typing your transcription here...');
        typingInput.focus();
        
        $('#finalSubmitBtn').show();

        secondsLeft = durationMinutes * 60;
        examStartTime = Date.now();
        
        timerLabel.text('Time Remaining');
        timerContainer.css('border-color', '#c62828');
        timerDisplay.css('color', '#c62828');

        updateTimerUi();
        phaseTimer = setInterval(function() {
            secondsLeft--;
            updateTimerUi();
            if (secondsLeft <= 0) {
                clearInterval(phaseTimer);
                autoSubmitExam();
            }
        }, 1000);
    }

    // 4. Submission Logic ────────────────────────────────────────────
    function updateTimerUi() {
        const m = Math.floor(secondsLeft / 60);
        const s = Math.floor(secondsLeft % 60);
        timerDisplay.text(`${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}`);
    }

    $('#finalSubmitBtn').click(function(e) {
        e.preventDefault();
        Swal.fire({
            title: 'Submit Exam?',
            text: "Are you sure you want to finish and submit your transcription?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            confirmButtonText: 'Yes, Submit!'
        }).then((result) => {
            if (result.isConfirmed) {
                autoSubmitExam();
            }
        });
    });

    function autoSubmitExam() {
        if (currentPhase === 'SUBMITTED') return;
        currentPhase = 'SUBMITTED';
        clearInterval(phaseTimer);
        
        typingInput.prop('readonly', true);
        const timeSpentSecs = Math.floor((Date.now() - examStartTime) / 1000);
        $('#timeSpentInput').val(timeSpentSecs);
        
        Swal.fire({
            title: 'Submitting Exam...',
            didOpen: () => { Swal.showLoading(); },
            allowOutsideClick: false,
            allowEscapeKey: false
        });

        // Add 1 second simulated delay for better UX feels
        setTimeout(() => {
            document.getElementById('submissionForm').submit();
        }, 800);
    }

    // Font Toolkit
    $('#fontInc').click(() => {
        let size = parseInt($('#fontSizeDisplay').text());
        size = Math.min(32, size + 2);
        $('#fontSizeDisplay').text(size);
        typingInput.css('font-size', size + 'px');
    });
    $('#fontDec').click(() => {
        let size = parseInt($('#fontSizeDisplay').text());
        size = Math.max(14, size - 2);
        $('#fontSizeDisplay').text(size);
        typingInput.css('font-size', size + 'px');
    });

</script>

</body>
</html>

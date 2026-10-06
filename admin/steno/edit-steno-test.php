<?php include '../header.php'; ?>

<!-- Summernote CSS -->
<link rel="stylesheet" href="../../vendor/summernote/summernote/dist/summernote-bs4.css">
<link rel="stylesheet" href="../assets/css/loader.css">

<style>
    .test-card { border: none; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
    .test-card .card-header { background: #17a2b8; color: white; border-radius: 12px 12px 0 0; padding: 15px 20px; font-weight: 600; }
    .form-group label { font-weight: 600; color: #495057; }
    .note-editor { border-radius: 8px; overflow: hidden; }
    #loader-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(255,255,255,0.7); display: none; align-items: center; justify-content: center; z-index: 9999; }
    .audio-preview { background: #f8f9fa; border-radius: 8px; padding: 15px; border: 1px dashed #dee2e6; margin-top: 10px; }
</style>

<div id="loader-overlay">
    <div class="loader"></div>
</div>

<div class="content-header p-0"></div>

<?php
$id = $_GET['id'] ?? 0;

if (empty($id)) {
    echo "<script>window.location.href = 'manage-steno-tests.php';</script>";
    exit();
}

// Fetch categories for the dropdown
try {
    $stmt = $pdo->query("SELECT id, name FROM steno_exam_categories WHERE status = 1 ORDER BY name ASC");
    $categories = $stmt->fetchAll();

    // Fetch test details
    $stmt = $pdo->prepare("SELECT * FROM steno_tests WHERE id = ?");
    $stmt->execute([$id]);
    $test = $stmt->fetch();

    if (!$test) {
        echo "<script>window.location.href = 'manage-steno-tests.php';</script>";
        exit();
    }
} catch (PDOException $e) {
    $categories = [];
    $test = null;
}
?>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-11 mx-auto">
                <div class="card test-card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-edit mr-2"></i> Edit Steno Test: <?= htmlspecialchars($test['title']) ?></span>
                        <a href="manage-steno-tests.php" class="btn btn-sm btn-light border text-info">
                            <i class="fas fa-list mr-1"></i> All Tests
                        </a>
                    </div>
                    <form id="stenoTestForm" enctype="multipart/form-data">
                        <input type="hidden" name="id" value="<?= $id ?>">
                        <div class="card-body">
                            <div class="row">
                                <!-- Main Content Area -->
                                <div class="col-md-8">
                                    <div class="form-group mb-3">
                                        <label for="title">Test Title</label>
                                        <input type="text" id="title" name="title" class="form-control" placeholder="e.g. SSC CGL Steno Grade D - Set 1" value="<?= htmlspecialchars($test['title']) ?>" required>
                                    </div>
                                    
                                    <div class="form-group mb-3">
                                        <label for="short_description">Short Description</label>
                                        <textarea name="short_description" class="form-control" rows="2" placeholder="Briefly describe the test focus..."><?= htmlspecialchars($test['short_description']) ?></textarea>
                                    </div>

                                    <div class="form-group mb-3">
                                        <label for="content">Original Content (Transcription Text)</label>
                                        <textarea id="content" name="content"><?= $test['content'] ?></textarea>
                                    </div>
                                </div>

                                <!-- Sidebar Content Area -->
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label for="category_id">Select Category</label>
                                        <select name="category_id" id="category_id" class="form-control" required>
                                            <option value="">-- Select Category --</option>
                                            <?php foreach ($categories as $cat): ?>
                                                <option value="<?= $cat['id'] ?>" <?= $cat['id'] == $test['category_id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="row">
                                        <div class="col-6">
                                            <div class="form-group mb-3">
                                                <label for="language">Language</label>
                                                <select name="language" id="language" class="form-control" required>
                                                    <option value="English" <?= $test['language'] == 'English' ? 'selected' : '' ?>>English</option>
                                                    <option value="Hindi" <?= $test['language'] == 'Hindi' ? 'selected' : '' ?>>Hindi</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="form-group mb-3">
                                                <label for="level">Test Level</label>
                                                <select name="level" id="level" class="form-control" required>
                                                    <option value="Easy" <?= $test['level'] == 'Easy' ? 'selected' : '' ?>>Easy</option>
                                                    <option value="Medium" <?= $test['level'] == 'Medium' ? 'selected' : '' ?>>Medium</option>
                                                    <option value="Hard" <?= $test['level'] == 'Hard' ? 'selected' : '' ?>>Hard</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group mb-3">
                                        <label>Audio File (Optional)</label>
                                        <div class="custom-file mb-2">
                                            <input type="file" class="custom-file-input" id="audio_file" name="audio_file" accept=".mp3,.wav,.ogg,.m4a,.aac,.mp4">
                                            <label class="custom-file-label" for="audio_file">Change audio/video file...</label>
                                        </div>
                                        <div id="audioPreviewContainer" class="audio-preview">
                                            <p class="small text-muted mb-2"><i class="fas fa-play-circle mr-2"></i> Current Media: <span id="fileName"><?= basename($test['audio_file']) ?></span></p>
                                            <div id="mediaPreview">
                                                <?php 
                                                    $ext = strtolower(pathinfo($test['audio_file'], PATHINFO_EXTENSION));
                                                    if ($ext === 'mp4'):
                                                ?>
                                                    <video id="mediaPlayer" controls style="width:100%; max-height:200px; background:#000;">
                                                        <source src="../../<?= $test['audio_file'] ?>" type="video/mp4">
                                                    </video>
                                                <?php else: ?>
                                                    <audio id="mediaPlayer" controls style="width:100%; height:30px;" src="../../<?= $test['audio_file'] ?>"></audio>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-6">
                                            <div class="form-group mb-3">
                                                <label for="test_duration">Test Timing (Min)</label>
                                                <input type="number" name="test_duration" id="test_duration" class="form-control" value="<?= $test['test_duration'] ?>" min="1" required>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="form-group mb-3" title="Wait time before transcription starts after audio finishes">
                                                <label for="buffer_time">Start Delay (Min)</label>
                                                <select name="buffer_time" id="buffer_time" class="form-control">
                                                    <?php for($i=1; $i<=15; $i++): ?>
                                                        <option value="<?= $i ?>" <?= $i == $test['buffer_time'] ? 'selected' : '' ?>><?= $i ?> Minutes</option>
                                                    <?php endfor; ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group mb-3">
                                        <label>Status</label>
                                        <select name="status" class="form-control">
                                            <option value="1" <?= $test['status'] == 1 ? 'selected' : '' ?>>Active / Published</option>
                                            <option value="0" <?= $test['status'] == 0 ? 'selected' : '' ?>>Inactive / Draft</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-white text-right">
                            <button type="submit" class="btn btn-info btn-lg px-5 shadow-sm">
                                <i class="fas fa-save mr-2"></i> Update Steno Test
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Summernote JS -->
<script src="../../vendor/summernote/summernote/dist/summernote-bs4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    // Initialize Summernote
    $('#content').summernote({
        placeholder: 'Paste the original transcription text here...',
        height: 400,
        toolbar: [
            ['style', ['style']],
            ['font', ['bold', 'underline', 'clear']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['table', ['table']],
            ['view', ['fullscreen', 'codeview']]
        ]
    });

    // Media preview
    $('#audio_file').change(function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const isVideo = file.type.startsWith('video/');
                let playerHtml = '';
                if (isVideo) {
                    playerHtml = `<video id="mediaPlayer" controls style="width:100%; max-height:200px; background:#000;"><source src="${e.target.result}" type="${file.type}"></video>`;
                } else {
                    playerHtml = `<audio id="mediaPlayer" controls style="width:100%; height:30px;"><source src="${e.target.result}" type="${file.type}"></audio>`;
                }
                $('#mediaPreview').html(playerHtml);
                $('#fileName').text(file.name);
                $('#audioPreviewContainer').fadeIn();
            }
            reader.readAsDataURL(file);
            $(this).next('.custom-file-label').html(file.name);
        }
    });

    // Form Submission
    $('#stenoTestForm').on('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        formData.append('action', 'update_steno_test');
        
        $('#loader-overlay').css('display', 'flex');

        $.ajax({
            url: 'steno_action.php',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            dataType: 'json',
            success: function(response) {
                $('#loader-overlay').hide();
                if (response.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Test Updated!',
                        text: response.message
                    }).then(() => {
                        window.location.href = 'manage-steno-tests.php';
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message
                    });
                }
            },
            error: function() {
                $('#loader-overlay').hide();
                Swal.fire({
                    icon: 'error',
                    title: 'System Error',
                    text: 'An unexpected error occurred. Please check file size limits.'
                });
            }
        });
    });
});
</script>

<?php include '../footer.php'; ?>

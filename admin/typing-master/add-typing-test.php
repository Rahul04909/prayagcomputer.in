<?php include '../header.php'; ?>

<!-- Summernote CSS -->
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.css" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/loader.css">

<style>
    .form-card { border: none; border-radius: 15px; box-shadow: 0 4px 25px rgba(0,0,0,0.08); overflow: hidden; }
    .form-header { background: #fff; border-bottom: 1px solid #f1f1f1; padding: 25px; }
    .form-body { padding: 30px; background: #fff; }
    .form-label { font-weight: 600; color: #495057; margin-bottom: 8px; font-size: 14px; }
    .form-control, .form-select { border-radius: 10px; border: 1px solid #e0e0e0; padding: 10px 15px; background: #fcfcfc; transition: all 0.3s; }
    .form-control:focus, .form-select:focus { border-color: #28a745; background: #fff; box-shadow: 0 0 0 3px rgba(40,167,69,0.1); }
    .btn-save { border-radius: 10px; padding: 12px 30px; font-weight: 600; }
    .note-editor { border-radius: 10px !important; border: 1px solid #e0e0e0 !important; overflow: hidden; }
</style>

<div id="loader-overlay">
    <div class="loader"></div>
</div>

<div class="content-header p-0"></div>

<?php
// Fetch Typing Categories
try {
    $cat_stmt = $pdo->query("SELECT id, name FROM typing_exam_categories WHERE status = 1 ORDER BY name ASC");
    $categories = $cat_stmt->fetchAll();
} catch (PDOException $e) {
    $categories = [];
}
?>

<section class="content">
    <div class="container-fluid">
        <form id="addTypingTestForm">
            <div class="row">
                <div class="col-lg-8">
                    <div class="card form-card">
                        <div class="form-header">
                            <h4 class="mb-0" style="font-weight:700; color:#333;">Test Content & Details</h4>
                        </div>
                        <div class="form-body">
                            <div class="form-group mb-4">
                                <label class="form-label">Test Title <span class="text-danger">*</span></label>
                                <input type="text" name="title" class="form-control" placeholder="Enter test title" required>
                            </div>

                            <div class="form-group mb-4">
                                <label class="form-label">Short Description</label>
                                <textarea name="short_description" class="form-control" rows="3" placeholder="Briefly describe the test content..."></textarea>
                            </div>

                            <div class="form-group mb-0">
                                <label class="form-label">Test Content <span class="text-danger">*</span></label>
                                <textarea id="content" name="content"></textarea>
                                <small class="text-muted mt-2 d-block">This is the actual text the user will type.</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card form-card mb-4">
                        <div class="form-header">
                            <h4 class="mb-0" style="font-weight:700; color:#333;">Configuration</h4>
                        </div>
                        <div class="form-body">
                            <div class="form-group mb-4">
                                <label class="form-label">Exam Category <span class="text-danger">*</span></label>
                                <select name="category_id" class="form-select" required>
                                    <option value="">Select Category</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="row mb-4">
                                <div class="col-6">
                                    <label class="form-label">Language <span class="text-danger">*</span></label>
                                    <select name="language" class="form-select">
                                        <option value="English">English</option>
                                        <option value="Hindi">Hindi</option>
                                        <option value="Punjabi">Punjabi</option>
                                    </select>
                                </div>
                                <div class="col-6">
                                    <label class="form-label">Level <span class="text-danger">*</span></label>
                                    <select name="level" class="form-select">
                                        <option value="Easy">Easy</option>
                                        <option value="Medium" selected>Medium</option>
                                        <option value="Hard">Hard</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group mb-4">
                                <label class="form-label">Test Type <span class="text-danger">*</span></label>
                                <select name="test_type" class="form-select">
                                    <option value="Typing Test">Typing Test</option>
                                    <option value="Practice Test">Practice Test</option>
                                    <option value="Lesson">Lesson</option>
                                </select>
                            </div>

                            <div class="row mb-4">
                                <div class="col-6">
                                    <label class="form-label">Test Time (Min) <span class="text-danger">*</span></label>
                                    <input type="number" name="test_time" class="form-control" value="5" min="1" required>
                                </div>
                                <div class="col-6">
                                    <label class="form-label">Status</label>
                                    <select name="status" class="form-select">
                                        <option value="1">Published</option>
                                        <option value="0">Draft</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mt-4">
                                <button type="submit" class="btn btn-success w-100 btn-save shadow-sm">
                                    <i class="fas fa-check mr-2"></i> Create Typing Test
                                </button>
                                <a href="manage-typing-tests.php" class="btn btn-light w-100 mt-2" style="border-radius:10px;">Cancel</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</section>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    // Initialize Summernote
    $('#content').summernote({
        placeholder: 'Paste or type the test content here...',
        tabsize: 2,
        height: 400,
        toolbar: [
            ['style', ['style']],
            ['font', ['bold', 'underline', 'clear']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['table', ['table']],
            ['insert', ['link']],
            ['view', ['fullscreen', 'codeview', 'help']]
        ]
    });

    // Form Submission
    $('#addTypingTestForm').on('submit', function(e) {
        e.preventDefault();
        
        // Validation check for Summernote
        if ($('#content').summernote('isEmpty')) {
            Swal.fire('Error!', 'Please provide test content.', 'error');
            return;
        }

        const formData = new FormData(this);
        formData.append('action', 'add_typing_test');

        $('#loader-overlay').css('display', 'flex');

        $.ajax({
            url: 'typing_action.php',
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
                        title: 'Test Created!',
                        text: response.message
                    }).then(() => {
                        window.location.href = 'manage-typing-tests.php';
                    });
                } else {
                    Swal.fire('Error!', response.message, 'error');
                }
            },
            error: function() {
                $('#loader-overlay').hide();
                Swal.fire('Error!', 'Something went wrong during submission.', 'error');
            }
        });
    });
});
</script>

<?php include '../footer.php'; ?>

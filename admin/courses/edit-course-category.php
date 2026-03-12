<?php include '../header.php'; ?>

<!-- Summernote CSS -->
<link rel="stylesheet" href="../../vendor/summernote/summernote/dist/summernote-bs4.css">
<link rel="stylesheet" href="../assets/css/loader.css">

<?php
$id = $_GET['id'] ?? 0;
try {
    $stmt = $pdo->prepare("SELECT * FROM course_categories WHERE id = ?");
    $stmt->execute([$id]);
    $cat = $stmt->fetch();

    if (!$cat) {
        echo "<script>alert('Category not found!'); window.location.href='manage-categories.php';</script>";
        exit;
    }
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<style>
    .category-card { border: none; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
    .category-card .card-header { background: #28a745; color: white; border-radius: 12px 12px 0 0; padding: 15px 20px; font-weight: 600; }
    .seo-section { background: #f8f9fa; border-radius: 8px; padding: 20px; margin-top: 30px; border: 1px dashed #dee2e6; }
    .form-group label { font-weight: 600; color: #495057; }
    .note-editor { border-radius: 8px; overflow: hidden; }
    #loader-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(255,255,255,0.7); display: none; align-items: center; justify-content: center; z-index: 9999; }
</style>

<div id="loader-overlay">
    <div class="loader"></div>
</div>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6"><h1 class="m-0 text-dark">Edit Category</h1></div>
            <div class="col-sm-6 text-right">
                <a href="manage-categories.php" class="btn btn-outline-success"><i class="fas fa-arrow-left mr-1"></i> Back to List</a>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-10 mx-auto">
                <div class="card category-card">
                    <div class="card-header">Modify Category: <?= htmlspecialchars($cat['name']) ?></div>
                    <form id="editCategoryForm" enctype="multipart/form-data">
                        <input type="hidden" name="id" value="<?= $cat['id'] ?>">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="form-group mb-3">
                                        <label for="name">Category Name</label>
                                        <input type="text" id="name" name="name" class="form-control" value="<?= htmlspecialchars($cat['name']) ?>" required>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label for="description">Category Description</label>
                                        <textarea id="description" name="description"><?= htmlspecialchars($cat['description']) ?></textarea>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label>Featured Image</label>
                                        <div class="custom-file mb-2">
                                            <input type="file" class="custom-file-input" id="featured_image" name="featured_image" accept="image/*">
                                            <label class="custom-file-label" for="featured_image">Change image</label>
                                        </div>
                                        <div id="imgPreview" class="text-center mt-3 p-2 border rounded" style="background:#f4f4f4;">
                                            <img src="<?= !empty($cat['featured_image']) ? '../src/images/' . $cat['featured_image'] : '' ?>" alt="Preview" style="max-width:100%; height:auto; border-radius:4px; <?= empty($cat['featured_image']) ? 'display:none;' : '' ?>">
                                        </div>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label for="status">Status</label>
                                        <select name="status" id="status" class="form-control">
                                            <option value="1" <?= $cat['status'] == 1 ? 'selected' : '' ?>>Active</option>
                                            <option value="0" <?= $cat['status'] == 0 ? 'selected' : '' ?>>Inactive</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="seo-section">
                                <h5 class="mb-4 text-success" style="font-weight:700;"><i class="fas fa-search mr-2"></i> SEO Information</h5>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label>SEO Meta Title</label>
                                            <input type="text" name="seo_title" class="form-control" value="<?= htmlspecialchars($cat['seo_title']) ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label>SEO Meta Keywords</label>
                                            <input type="text" name="seo_keywords" class="form-control" value="<?= htmlspecialchars($cat['seo_keywords']) ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group mb-3">
                                            <label>SEO Meta Description</label>
                                            <textarea name="seo_description" class="form-control" rows="3"><?= htmlspecialchars($cat['seo_description']) ?></textarea>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label>SEO Schema (JSON-LD)</label>
                                            <textarea name="seo_schema" class="form-control" rows="4"><?= htmlspecialchars($cat['seo_schema']) ?></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-white text-right">
                            <button type="submit" class="btn btn-success btn-lg px-5 shadow-sm"><i class="fas fa-save mr-2"></i> Update Category</button>
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
    $('#description').summernote({ height: 300 });

    $('#featured_image').change(function() {
        const file = this.files[0];
        if (file) {
            let reader = new FileReader();
            reader.onload = function(event) {
                $('#imgPreview img').show().attr('src', event.target.result);
            }
            reader.readAsDataURL(file);
            $(this).next('.custom-file-label').html(file.name);
        }
    });

    $('#editCategoryForm').on('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        formData.append('action', 'update_category');
        $('#loader-overlay').css('display', 'flex');

        $.ajax({
            url: 'course_action.php',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            dataType: 'json',
            success: function(response) {
                $('#loader-overlay').hide();
                if (response.status === 'success') {
                    Swal.fire({ icon: 'success', title: 'Updated!', text: response.message }).then(() => {
                        window.location.href = 'manage-categories.php';
                    });
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: response.message });
                }
            },
            error: function() {
                $('#loader-overlay').hide();
                Swal.fire({ icon: 'error', title: 'Error', text: 'Connection failed.' });
            }
        });
    });
});
</script>

<?php include '../footer.php'; ?>

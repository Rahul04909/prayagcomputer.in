<?php include '../header.php'; ?>

<!-- Summernote CSS -->
<link rel="stylesheet" href="../../vendor/summernote/summernote/dist/summernote-bs4.css">
<link rel="stylesheet" href="../assets/css/loader.css">

<?php
$id = $_GET['id'] ?? 0;
try {
    // Fetch course data
    $stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ?");
    $stmt->execute([$id]);
    $course = $stmt->fetch();

    if (!$course) {
        echo "<script>alert('Course not found!'); window.location.href='manage-courses.php';</script>";
        exit;
    }

    // Fetch categories
    $cat_stmt = $pdo->query("SELECT id, name FROM course_categories WHERE status = 1 ORDER BY name ASC");
    $categories = $cat_stmt->fetchAll();
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<style>
    .course-card { border: none; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
    .course-card .card-header { background: #28a745; color: white; border-radius: 12px 12px 0 0; padding: 15px 20px; font-weight: 600; }
    .seo-section { background: #f8f9fa; border-radius: 8px; padding: 20px; margin-top: 30px; border: 1px dashed #dee2e6; }
    .form-group label { font-weight: 600; color: #495057; }
    .price-summary { background: #e9ecef; border-radius: 8px; padding: 15px; margin-top: 10px; }
    #loader-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(255,255,255,0.7); display: none; align-items: center; justify-content: center; z-index: 9999; }
</style>

<div id="loader-overlay">
    <div class="loader"></div>
</div>

<div class="content-header p-0"></div>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-11 mx-auto">
                <div class="card course-card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span>Edit Course: <?= htmlspecialchars($course['title']) ?></span>
                        <a href="manage-courses.php" class="btn btn-sm btn-light border text-success">
                            <i class="fas fa-arrow-left mr-1"></i> Back to List
                        </a>
                    </div>
                    <form id="editCourseForm" enctype="multipart/form-data">
                        <input type="hidden" name="id" value="<?= $course['id'] ?>">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="form-group mb-3">
                                        <label for="title">Course Title</label>
                                        <input type="text" id="title" name="title" class="form-control" value="<?= htmlspecialchars($course['title']) ?>" required>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label for="duration">Course Duration</label>
                                        <input type="text" id="duration" name="duration" class="form-control" value="<?= htmlspecialchars($course['duration'] ?? '') ?>" placeholder="e.g. 3 Months / 6 Months">
                                    </div>
                                    <div class="form-group mb-3">
                                        <label>Course Slug</label>
                                        <input type="text" class="form-control" value="<?= htmlspecialchars($course['slug']) ?>" readonly style="background:#f8f9fa;">
                                    </div>
                                    <div class="form-group mb-3">
                                        <label for="description">Course Description</label>
                                        <textarea id="description" name="description"><?= htmlspecialchars($course['description']) ?></textarea>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label for="category_id">Course Category</label>
                                        <select name="category_id" id="category_id" class="form-control" required>
                                            <option value="">-- Select Category --</option>
                                            <?php foreach ($categories as $cat): ?>
                                                <option value="<?= $cat['id'] ?>" <?= $cat['id'] == $course['category_id'] ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($cat['name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="form-group mb-3">
                                        <label>Featured Image</label>
                                        <div class="custom-file mb-2">
                                            <input type="file" class="custom-file-input" id="featured_image" name="featured_image" accept="image/*">
                                            <label class="custom-file-label" for="featured_image">Change image</label>
                                        </div>
                                        <div id="imgPreview" class="text-center mt-3 p-2 border rounded" style="background:#f4f4f4;">
                                            <img src="<?= !empty($course['featured_image']) ? '../src/images/' . $course['featured_image'] : '' ?>" alt="Preview" style="max-width:100%; height:auto; border-radius:4px; <?= empty($course['featured_image']) ? 'display:none;' : '' ?>">
                                        </div>
                                    </div>

                                    <div class="card bg-light border-0 mb-3">
                                        <div class="card-body">
                                            <h6 class="font-weight-bold mb-3">Pricing Details</h6>
                                            <div class="form-group mb-3">
                                                <label for="mrp">MRP (₹)</label>
                                                <input type="number" id="mrp" name="mrp" class="form-control price-input" value="<?= $course['mrp'] ?>" step="0.1">
                                            </div>
                                            <div class="form-group mb-3">
                                                <label for="sale_price">Sales Price (₹)</label>
                                                <input type="number" id="sale_price" name="sale_price" class="form-control price-input" value="<?= $course['sale_price'] ?>" step="0.1">
                                            </div>
                                            <div id="discountSummary" class="price-summary">
                                                <div class="d-flex justify-content-between">
                                                    <span>Savings:</span>
                                                    <span id="savingsVal" class="text-success font-weight-bold">₹0</span>
                                                </div>
                                                <div class="d-flex justify-content-between mt-1">
                                                    <span>Discount:</span>
                                                    <span id="discountVal" class="text-danger font-weight-bold">0% OFF</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="status">Display Status</label>
                                        <select name="status" id="status" class="form-control">
                                            <option value="1" <?= $course['status'] == 1 ? 'selected' : '' ?>>Active</option>
                                            <option value="0" <?= $course['status'] == 0 ? 'selected' : '' ?>>Inactive</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="seo-section shadow-sm">
                                <h5 class="mb-4 text-success" style="font-weight:700;"><i class="fas fa-search mr-2"></i> SEO Metadata</h5>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label>SEO Meta Title</label>
                                            <input type="text" name="seo_title" class="form-control" value="<?= htmlspecialchars($course['seo_title']) ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label>SEO Meta Keywords</label>
                                            <input type="text" name="seo_keywords" class="form-control" value="<?= htmlspecialchars($course['seo_keywords']) ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group mb-3">
                                            <label>SEO Meta Description</label>
                                            <textarea name="seo_description" class="form-control" rows="3"><?= htmlspecialchars($course['seo_description']) ?></textarea>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label>SEO Schema (JSON-LD)</label>
                                            <textarea name="seo_schema" class="form-control" rows="4"><?= htmlspecialchars($course['seo_schema']) ?></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-white text-right">
                            <button type="submit" class="btn btn-success btn-lg px-5 shadow-sm"><i class="fas fa-save mr-2"></i> Update Course</button>
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

    $('.price-input').on('input', calculateDiscount);
    calculateDiscount();

    function calculateDiscount() {
        const mrp = parseFloat($('#mrp').val()) || 0;
        const sale = parseFloat($('#sale_price').val()) || 0;
        if (mrp > 0 && sale > 0 && mrp > sale) {
            const savings = mrp - sale;
            const discount = Math.round((savings / mrp) * 100);
            $('#savingsVal').text('₹' + savings.toLocaleString());
            $('#discountVal').text(discount + '% OFF');
            $('#discountSummary').show();
        } else {
            $('#discountSummary').hide();
        }
    }

    $('#editCourseForm').on('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        formData.append('action', 'update_course');
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
                        window.location.href = 'manage-courses.php';
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

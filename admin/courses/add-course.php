<?php include '../header.php'; ?>

<!-- Summernote CSS -->
<link rel="stylesheet" href="../../vendor/summernote/summernote/dist/summernote-bs4.css">
<link rel="stylesheet" href="../assets/css/loader.css">

<style>
    .course-card { border: none; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
    .course-card .card-header { background: #28a745; color: white; border-radius: 12px 12px 0 0; padding: 15px 20px; font-weight: 600; }
    .seo-section { background: #f8f9fa; border-radius: 8px; padding: 20px; margin-top: 30px; border: 1px dashed #dee2e6; }
    .form-group label { font-weight: 600; color: #495057; }
    .note-editor { border-radius: 8px; overflow: hidden; }
    #loader-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(255,255,255,0.7); display: none; align-items: center; justify-content: center; z-index: 9999; }
    .price-summary { background: #e9ecef; border-radius: 8px; padding: 15px; margin-top: 10px; }
    .discount-badge { font-size: 14px; font-weight: 700; color: #d32f2f; }
</style>

<div id="loader-overlay">
    <div class="loader"></div>
</div>

<div class="content-header p-0"></div> <!-- Placeholder to maintain spacing if needed, but header.php handles title -->

<?php
// Fetch categories for the dropdown
try {
    $stmt = $pdo->query("SELECT id, name FROM course_categories WHERE status = 1 ORDER BY name ASC");
    $categories = $stmt->fetchAll();
} catch (PDOException $e) {
    $categories = [];
}
?>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-11 mx-auto">
                <div class="card course-card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span>Course Information & Pricing</span>
                        <a href="manage-courses.php" class="btn btn-sm btn-light border text-success">
                            <i class="fas fa-list mr-1"></i> View All Courses
                        </a>
                    </div>
                    <form id="courseForm" enctype="multipart/form-data">
                        <div class="card-body">
                            <div class="row">
                                <!-- Main Content Area -->
                                <div class="col-md-8">
                                    <div class="form-group mb-3">
                                        <label for="title">Course Title</label>
                                        <input type="text" id="title" name="title" class="form-control" placeholder="e.g. Advanced Web Development Masterclass" required>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label>Course Duration</label>
                                        <div class="row">
                                            <div class="col-6">
                                                <input type="number" id="duration" name="duration" class="form-control" placeholder="Value (e.g. 3)" required>
                                            </div>
                                            <div class="col-6">
                                                <select name="duration_type" id="duration_type" class="form-control" required>
                                                    <option value="Days">Days</option>
                                                    <option value="Months" selected>Months</option>
                                                    <option value="Years">Years</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label for="slug">Course Slug (Automatic)</label>
                                        <input type="text" id="slug" name="slug" class="form-control" placeholder="course-slug" readonly required>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label for="description">Course Description</label>
                                        <textarea id="description" name="description"></textarea>
                                    </div>
                                </div>

                                <!-- Sidebar Content Area (Category, Price, Image) -->
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label for="category_id">Select Category</label>
                                        <select name="category_id" id="category_id" class="form-control" required>
                                            <option value="">-- Select Category --</option>
                                            <?php foreach ($categories as $cat): ?>
                                                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="form-group mb-3">
                                        <label>Featured Image</label>
                                        <div class="custom-file mb-2">
                                            <input type="file" class="custom-file-input" id="featured_image" name="featured_image" accept="image/*">
                                            <label class="custom-file-label" for="featured_image">Choose image</label>
                                        </div>
                                        <div id="imgPreview" class="text-center mt-3 p-2 border rounded" style="background:#f4f4f4; min-height:100px;">
                                            <img src="" alt="Preview" style="max-width:100%; height:auto; border-radius:4px; display:none;">
                                            <div id="no-img-text" class="text-muted"><i class="fas fa-image fa-2x"></i><br>Image preview</div>
                                        </div>
                                    </div>

                                    <div class="card bg-light border-0">
                                        <div class="card-body">
                                            <h6 class="font-weight-bold mb-3">Pricing Details</h6>
                                            <div class="form-group mb-3">
                                                <label for="mrp">MRP (₹)</label>
                                                <input type="number" id="mrp" name="mrp" class="form-control price-input" step="0.1" placeholder="0.00">
                                            </div>
                                            <div class="form-group mb-3">
                                                <label for="sale_price">Sales Price (₹)</label>
                                                <input type="number" id="sale_price" name="sale_price" class="form-control price-input" step="0.1" placeholder="0.00">
                                            </div>
                                            <div id="discountSummary" class="price-summary" style="display:none;">
                                                <div class="d-flex justify-content-between">
                                                    <span>You Save:</span>
                                                    <span id="savingsVal" class="text-success font-weight-bold">₹0</span>
                                                </div>
                                                <div class="d-flex justify-content-between mt-1">
                                                    <span>Discount:</span>
                                                    <span id="discountVal" class="discount-badge text-danger">0% OFF</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- SEO Section -->
                            <div class="seo-section shadow-sm border-success">
                                <h5 class="mb-4 text-success" style="font-weight:700;"><i class="fas fa-search mr-2"></i> SEO Optimization</h5>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label>SEO Meta Title</label>
                                            <input type="text" id="seo_title" name="seo_title" class="form-control" placeholder="SEO Title for Google">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label>SEO Meta Keywords</label>
                                            <input type="text" name="seo_keywords" class="form-control" placeholder="keyword1, keyword2, ...">
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group mb-3">
                                            <label>SEO Meta Description</label>
                                            <textarea id="seo_description" name="seo_description" class="form-control" rows="3" placeholder="Compelling description for search results"></textarea>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label>SEO Schema (JSON) - <span class="text-success">Generated Automatically</span></label>
                                            <textarea id="seo_schema" name="seo_schema" class="form-control" rows="4"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-white text-right">
                            <button type="submit" class="btn btn-success btn-lg px-5 shadow-sm">
                                <i class="fas fa-check-circle mr-2"></i> Publish Course
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
    $('#description').summernote({
        placeholder: 'Enter course syllabus and details...',
        height: 300,
        toolbar: [
            ['style', ['style']],
            ['font', ['bold', 'underline', 'clear']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['table', ['table']],
            ['insert', ['link', 'picture', 'video']],
            ['view', ['fullscreen', 'codeview', 'help']]
        ]
    });

    // Slug generation
    $('#title').on('keyup touchstart', function() {
        let title = $(this).val();
        let slug = title.toLowerCase().replace(/[^a-z0-9]/g, '-').replace(/-+/g, '-').replace(/^-|-$/g, '');
        $('#slug').val(slug);
        $('#seo_title').val(title); // Set SEO title as well
        updateSchema();
    });

    // Image preview
    $('#featured_image').change(function() {
        const file = this.files[0];
        if (file) {
            let reader = new FileReader();
            reader.onload = function(event) {
                $('#imgPreview img').show().attr('src', event.target.result);
                $('#no-img-text').hide();
            }
            reader.readAsDataURL(file);
            $(this).next('.custom-file-label').html(file.name);
        }
    });

    // Pricing Calculation
    $('.price-input').on('input', function() {
        calculateDiscount();
    });

    function calculateDiscount() {
        const mrp = parseFloat($('#mrp').val()) || 0;
        const sale = parseFloat($('#sale_price').val()) || 0;

        if (mrp > 0 && sale > 0 && mrp > sale) {
            const savings = mrp - sale;
            const discount = Math.round((savings / mrp) * 100);
            
            $('#savingsVal').text('₹' + savings.toLocaleString());
            $('#discountVal').text(discount + '% OFF');
            $('#discountSummary').fadeIn();
        } else {
            $('#discountSummary').fadeOut();
        }
    }

    // JSON-LD Schema Auto-generation
    function updateSchema() {
        const title = $('#title').val();
        const category = $("#category_id option:selected").text() || "Courses";
        
        const schema = {
            "@context": "https://schema.org/",
            "@type": "Course",
            "name": title,
            "description": $('#seo_description').val() || "Learn " + title + " at Prayag Computer Centre.",
            "provider": {
                "@type": "Organization",
                "name": "Prayag Computer Centre",
                "sameAs": "https://prayagcomputer.in"
            }
        };
        $('#seo_schema').val(JSON.stringify(schema, null, 2));
    }

    $('#seo_description').on('keyup', updateSchema);
    $('#category_id').on('change', updateSchema);

    // Form Submission
    $('#courseForm').on('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        formData.append('action', 'add_course');
        
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
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message
                    }).then(() => {
                        window.location.href = 'manage-courses.php';
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: response.message
                    });
                }
            },
            error: function() {
                $('#loader-overlay').hide();
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Connection failed. Please check your network.'
                });
            }
        });
    });
});
</script>

<?php include '../footer.php'; ?>

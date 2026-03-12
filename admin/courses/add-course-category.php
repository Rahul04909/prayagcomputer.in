<?php include '../header.php'; ?>

<!-- Summernote CSS -->
<link rel="stylesheet" href="../../vendor/summernote/summernote/dist/summernote-bs4.css">
<link rel="stylesheet" href="../assets/css/loader.css">

<style>
    .category-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    }
    .category-card .card-header {
        background: #28a745;
        color: white;
        border-radius: 12px 12px 0 0;
        padding: 15px 20px;
        font-weight: 600;
    }
    .seo-section {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 20px;
        margin-top: 30px;
        border: 1px dashed #dee2e6;
    }
    .form-group label {
        font-weight: 600;
        color: #495057;
    }
    .note-editor {
        border-radius: 8px;
        overflow: hidden;
    }
    #loader-overlay {
        position: fixed;
        top: 0; left: 0;
        width: 100%; height: 100%;
        background: rgba(255,255,255,0.7);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 9999;
    }
</style>

<div id="loader-overlay">
    <div class="loader"></div>
</div>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">Add New Category</h1>
            </div>
            <div class="col-sm-6 text-right">
                <a href="manage-categories.php" class="btn btn-outline-success">
                    <i class="fas fa-list mr-1"></i> View All Categories
                </a>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-10 mx-auto">
                <div class="card category-card">
                    <div class="card-header">
                        Course Category Details
                    </div>
                    <form id="categoryForm" enctype="multipart/form-data">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="form-group mb-3">
                                        <label for="name">Category Name</label>
                                        <input type="text" id="name" name="name" class="form-control" placeholder="e.g. Diploma in Computer Application" required>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label for="description">Category Description</label>
                                        <textarea id="description" name="description"></textarea>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label>Featured Image</label>
                                        <div class="custom-file mb-2">
                                            <input type="file" class="custom-file-input" id="featured_image" name="featured_image" accept="image/*">
                                            <label class="custom-file-label" for="featured_image">Choose image</label>
                                        </div>
                                        <div id="imgPreview" class="text-center mt-3 p-2 border rounded" style="display:none; background:#f4f4f4;">
                                            <img src="" alt="Preview" style="max-width:100%; height:auto; border-radius:4px;">
                                        </div>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label for="status">Status</label>
                                        <select name="status" id="status" class="form-control">
                                            <option value="1">Active</option>
                                            <option value="0">Inactive</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- SEO Section -->
                            <div class="seo-section">
                                <h5 class="mb-4 text-success" style="font-weight:700;">
                                    <i class="fas fa-search mr-2"></i> SEO Information
                                </h5>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label>SEO Meta Title</label>
                                            <input type="text" name="seo_title" id="seo_title" class="form-control" placeholder="Best Computer course in city...">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label>SEO Meta Keywords</label>
                                            <input type="text" name="seo_keywords" class="form-control" placeholder="DCA, computer courses, web design...">
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group mb-3">
                                            <label>SEO Meta Description</label>
                                            <textarea name="seo_description" id="seo_description" class="form-control" rows="3" placeholder="A brief description of the course category for search engines..."></textarea>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label>SEO Schema (JSON-LD)</label>
                                            <textarea name="seo_schema" id="seo_schema" class="form-control" rows="4" placeholder='{"@context": "https://schema.org", ...}'></textarea>
                                            <small class="text-muted">You can leave this blank for automatic generation based on content.</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-white text-right">
                            <button type="submit" class="btn btn-success btn-lg px-5 shadow-sm">
                                <i class="fas fa-check-circle mr-2"></i> Save Category
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
        placeholder: 'Write category description here...',
        height: 300,
        toolbar: [
            ['style', ['style']],
            ['font', ['bold', 'underline', 'clear']],
            ['fontname', ['fontname']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['table', ['table']],
            ['insert', ['link', 'picture', 'video']],
            ['view', ['fullscreen', 'codeview', 'help']]
        ]
    });

    // Image Preview
    $('#featured_image').change(function() {
        const file = this.files[0];
        if (file) {
            let reader = new FileReader();
            reader.onload = function(event) {
                $('#imgPreview').show().find('img').attr('src', event.target.result);
            }
            reader.readAsDataURL(file);
            $(this).next('.custom-file-label').html(file.name);
        }
    });

    // Auto SEO & Schema Generation Logic
    $('#name').on('input', function() {
        const name = $(this).val();
        if($('#seo_title').val() === '') $('#seo_title').val(name + ' | Prayag Computer');
        
        // Auto-generate basic Schema
        const schema = {
            "@context": "https://schema.org",
            "@type": "Course",
            "name": name,
            "description": $('#seo_description').val() || name,
            "provider": {
                "@type": "Organization",
                "name": "Prayag Computer Center",
                "sameAs": "https://prayagcomputer.in"
            }
        };
        $('#seo_schema').val(JSON.stringify(schema, null, 2));
    });

    // Form Submission
    $('#categoryForm').on('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        formData.append('action', 'add_category');
        
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
                        text: response.message,
                        confirmButtonColor: '#28a745'
                    }).then(() => {
                        window.location.href = 'manage-categories.php';
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
                    text: 'Connection failed. Please try again.'
                });
            }
        });
    });
});
</script>

<?php include '../footer.php'; ?>

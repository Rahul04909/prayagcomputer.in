<?php include '../header.php'; ?>

<link rel="stylesheet" href="../assets/css/loader.css">

<style>
    .cat-table-card { border: none; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); }
    .cat-logo { width: 50px; height: 50px; object-fit: contain; border-radius: 8px; border: 1px solid #eee; background: #fff; padding: 5px; }
    .status-badge { padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; }
    .pagination .page-link { color: #28a745; border-radius: 5px; margin: 0 2px; }
    .pagination .page-item.active .page-link { background-color: #28a745; border-color: #28a745; }
    #loader-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(255,255,255,0.7); display: none; align-items: center; justify-content: center; z-index: 9999; }
    
    .logo-preview-box {
        width: 100px;
        height: 100px;
        border: 2px dashed #ddd;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 15px;
        position: relative;
        overflow: hidden;
    }
    .logo-preview-box img {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
    }
    .logo-preview-box .upload-placeholder {
        text-align: center;
        color: #999;
        font-size: 12px;
    }
</style>

<div id="loader-overlay">
    <div class="loader"></div>
</div>

<div class="content-header p-0"></div>

<?php
// Pagination logic
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;

try {
    // Total count
    $total_stmt = $pdo->query("SELECT COUNT(id) FROM steno_exam_categories");
    $total_results = $total_stmt->fetchColumn();
    $total_pages = ceil($total_results / $limit);

    // Fetch categories
    $stmt = $pdo->prepare("SELECT * FROM steno_exam_categories ORDER BY created_at DESC LIMIT $start, $limit");
    $stmt->execute();
    $categories = $stmt->fetchAll();
} catch (PDOException $e) {
    $categories = [];
    $total_results = 0;
}
?>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card cat-table-card">
                    <div class="card-header d-flex justify-content-between align-items-center bg-white p-3">
                        <h3 class="card-title" style="font-weight:700; color:#343a40; margin:0;">Steno Exam Categories (<?= $total_results ?>)</h3>
                        <button type="button" class="btn btn-sm btn-success shadow-sm" onclick="openAddModal()">
                            <i class="fas fa-plus mr-1"></i> Add New Category
                        </button>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light text-muted small">
                                    <tr>
                                        <th class="pl-4">Logo</th>
                                        <th>Category Name</th>
                                        <th>Date Created</th>
                                        <th>Status</th>
                                        <th class="text-right pr-4">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($categories)): ?>
                                        <tr>
                                            <td colspan="5" class="text-center py-5 text-muted">No categories found.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($categories as $cat): ?>
                                            <tr>
                                                <td class="pl-4">
                                                    <?php if (!empty($cat['logo'])): ?>
                                                        <img src="../src/images/<?= htmlspecialchars($cat['logo']) ?>" alt="" class="cat-logo">
                                                    <?php else: ?>
                                                        <div class="cat-logo d-flex align-items-center justify-content-center">
                                                            <i class="fas fa-image text-muted"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                </td>
                                                <td><span style="font-weight:600; color:#2c3e50;"><?= htmlspecialchars($cat['name']) ?></span></td>
                                                <td><span class="text-sm"><?= date('d M Y', strtotime($cat['created_at'])) ?></span></td>
                                                <td>
                                                    <?php if ($cat['status']): ?>
                                                        <span class="status-badge" style="background:#e8f5e9; color:#2e7d32;">Active</span>
                                                    <?php else: ?>
                                                        <span class="status-badge" style="background:#ffebee; color:#c62828;">Inactive</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-right pr-4">
                                                    <button onclick='openEditModal(<?= json_encode($cat) ?>)' class="btn btn-sm btn-outline-primary mr-1" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button onclick="deleteCategory(<?= $cat['id'] ?>, '<?= htmlspecialchars(addslashes($cat['name'])) ?>')" class="btn btn-sm btn-outline-danger" title="Delete">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <?php if ($total_pages > 1): ?>
                    <div class="card-footer bg-white border-top-0">
                        <nav aria-label="Page navigation">
                            <ul class="pagination pagination-sm justify-content-center mb-0">
                                <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                                    <a class="page-link" href="?page=<?= $page - 1 ?>">Previous</a>
                                </li>
                                <?php for($i = 1; $i <= $total_pages; $i++): ?>
                                    <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                                        <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                                    </li>
                                <?php endfor; ?>
                                <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
                                    <a class="page-link" href="?page=<?= $page + 1 ?>">Next</a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Manage Modal -->
<div class="modal fade" id="manageModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius: 15px; border: none;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title" id="modalTitle" style="font-weight: 700;">Add New Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="catForm" enctype="multipart/form-data">
                <div class="modal-body p-4">
                    <input type="hidden" name="action" id="formAction" value="add_steno_category">
                    <input type="hidden" name="id" id="catId" value="">
                    
                    <div class="form-group mb-4">
                        <label class="font-weight-600 mb-2">Category Name</label>
                        <input type="text" name="name" id="catName" class="form-control" required placeholder="e.g. SSC Steno, RRB Steno">
                    </div>

                    <div class="form-group mb-4">
                        <label class="font-weight-600 mb-2">Category Logo</label>
                        <div class="logo-preview-box" onclick="$('#logoInput').click()">
                            <img id="logoPreview" src="" style="display: none;">
                            <div class="upload-placeholder" id="logoPlaceholder">
                                <i class="fas fa-cloud-upload-alt fa-2x mb-2"></i><br>
                                Click to Upload Logo
                            </div>
                        </div>
                        <input type="file" name="logo" id="logoInput" style="display: none;" accept="image/*">
                        <small class="text-muted">Recommended size: 200x200px (PNG/JPG/SVG)</small>
                    </div>

                    <div class="form-group mb-0">
                        <label class="font-weight-600 mb-2">Status</label>
                        <select name="status" id="catStatus" class="form-control">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success px-4" id="submitBtn">Save Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        // Logo Preview
        $('#logoInput').change(function() {
            const file = this.files[0];
            if (file) {
                let reader = new FileReader();
                reader.onload = function(event) {
                    $('#logoPreview').attr('src', event.target.result).show();
                    $('#logoPlaceholder').hide();
                }
                reader.readAsDataURL(file);
            }
        });

        // Form Submission
        $('#catForm').on('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
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
                        Swal.fire({ icon: 'success', title: 'Success', text: response.message }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({ icon: 'error', title: 'Oops...', text: response.message });
                    }
                },
                error: function() {
                    $('#loader-overlay').hide();
                    Swal.fire({ icon: 'error', title: 'Error', text: 'Connection failed.' });
                }
            });
        });
    });

    function openAddModal() {
        $('#catForm')[0].reset();
        $('#catId').val('');
        $('#formAction').val('add_steno_category');
        $('#modalTitle').text('Add New Category');
        $('#logoPreview').hide();
        $('#logoPlaceholder').show();
        $('#manageModal').modal('show');
    }

    function openEditModal(cat) {
        $('#formAction').val('update_steno_category');
        $('#catId').val(cat.id);
        $('#catName').val(cat.name);
        $('#catStatus').val(cat.status);
        $('#modalTitle').text('Edit Category');
        
        if (cat.logo) {
            $('#logoPreview').attr('src', '../src/images/' + cat.logo).show();
            $('#logoPlaceholder').hide();
        } else {
            $('#logoPreview').hide();
            $('#logoPlaceholder').show();
        }
        
        $('#manageModal').modal('show');
    }

    function deleteCategory(id, name) {
        Swal.fire({
            title: 'Are you sure?',
            text: "You are about to delete: " + name,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $('#loader-overlay').css('display', 'flex');
                $.ajax({
                    url: 'steno_action.php',
                    type: 'POST',
                    data: { action: 'delete_steno_category', id: id },
                    dataType: 'json',
                    success: function(response) {
                        $('#loader-overlay').hide();
                        if (response.status === 'success') {
                            Swal.fire('Deleted!', response.message, 'success').then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire('Error!', response.message, 'error');
                        }
                    },
                    error: function() {
                        $('#loader-overlay').hide();
                        Swal.fire('Error!', 'Connection failed.', 'error');
                    }
                });
            }
        });
    }
</script>

<?php include '../footer.php'; ?>

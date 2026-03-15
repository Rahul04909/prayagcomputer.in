<?php include '../header.php'; ?>

<link rel="stylesheet" href="../assets/css/loader.css">

<style>
    .cat-card { border: none; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); transition: transform 0.2s; }
    .cat-card:hover { transform: translateY(-3px); }
    .status-badge { padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; cursor: pointer; }
    .cat-logo { width: 45px; height: 45px; object-fit: contain; border-radius: 8px; background: #f8f9fa; padding: 5px; border: 1px solid #eee; }
    .pagination .page-link { color: #28a745; border-radius: 5px; margin: 0 2px; }
    .pagination .page-item.active .page-link { background-color: #28a745; border-color: #28a745; }
    #loader-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(255,255,255,0.7); display: none; align-items: center; justify-content: center; z-index: 9999; }
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
    $total_stmt = $pdo->query("SELECT COUNT(id) FROM typing_exam_categories");
    $total_results = $total_stmt->fetchColumn();
    $total_pages = ceil($total_results / $limit);

    $stmt = $pdo->query("SELECT * FROM typing_exam_categories ORDER BY created_at DESC LIMIT $start, $limit");
    $categories = $stmt->fetchAll();
} catch (PDOException $e) {
    $categories = [];
    $total_results = 0;
    $total_pages = 0;
}
?>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card cat-card">
                    <div class="card-header bg-white p-3">
                        <div class="row align-items-center">
                            <div class="col-6">
                                <h3 class="card-title" style="font-weight:700; color:#343a40; margin:0;">Typing Exam Categories (<?= $total_results ?>)</h3>
                            </div>
                            <div class="col-6 text-end">
                                <button class="btn btn-sm btn-success shadow-sm" onclick="openModal()">
                                    <i class="fas fa-plus mr-1"></i> Add Category
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light text-muted small text-uppercase">
                                    <tr>
                                        <th class="pl-4">Category Name</th>
                                        <th>Logo</th>
                                        <th>Status</th>
                                        <th>Created Date</th>
                                        <th class="text-right pr-4">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($categories)): ?>
                                        <tr>
                                            <td colspan="5" class="text-center py-5 text-muted">No categories found. Click "Add Category" to get started.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($categories as $cat): ?>
                                            <tr>
                                                <td class="pl-4">
                                                    <span style="font-weight:600; color:#2c3e50;"><?= htmlspecialchars($cat['name']) ?></span>
                                                </td>
                                                <td>
                                                    <?php if ($cat['logo']): ?>
                                                        <img src="../<?= $cat['logo'] ?>" class="cat-logo" alt="Logo">
                                                    <?php else: ?>
                                                        <div class="cat-logo d-flex align-items-center justify-content-center text-muted"><i class="fas fa-image"></i></div>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <span onclick="toggleStatus(<?= $cat['id'] ?>, <?= $cat['status'] ? 1 : 0 ?>)" 
                                                          class="status-badge" 
                                                          style="background: <?= $cat['status'] ? '#e8f5e9' : '#ffebee' ?>; color: <?= $cat['status'] ? '#2e7d32' : '#c62828' ?>;">
                                                        <?= $cat['status'] ? 'Active' : 'Inactive' ?>
                                                    </span>
                                                </td>
                                                <td><small class="text-muted"><?= date('d M Y', strtotime($cat['created_at'])) ?></small></td>
                                                <td class="text-right pr-4">
                                                    <button onclick="openModal(<?= htmlspecialchars(json_encode($cat)) ?>)" class="btn btn-sm btn-outline-primary mr-2 shadow-sm">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button onclick="deleteCategory(<?= $cat['id'] ?>, '<?= htmlspecialchars(addslashes($cat['name'])) ?>')" class="btn btn-sm btn-outline-danger shadow-sm">
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
                                        <a class="page-link" href="?page=<?= $page - 1 ?>"><i class="fas fa-chevron-left"></i></a>
                                    </li>
                                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                        <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                                            <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                                        </li>
                                    <?php endfor; ?>
                                    <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
                                        <a class="page-link" href="?page=<?= $page + 1 ?>"><i class="fas fa-chevron-right"></i></a>
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

<!-- Category Modal -->
<div class="modal fade" id="categoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius:15px;">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title" id="modalTitle" style="font-weight:700; color:#343a40;">Add New Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="categoryForm" enctype="multipart/form-data">
                <input type="hidden" name="id" id="cat_id">
                <div class="modal-body py-4">
                    <div class="form-group mb-4">
                        <label class="form-label" style="font-weight:600; color:#495057;">Category Name</label>
                        <input type="text" name="name" id="cat_name" class="form-control form-control-lg border-0 bg-light" placeholder="e.g. SSC Typing, Railway" required style="border-radius:10px;">
                    </div>
                    <div class="form-group mb-4">
                        <label class="form-label" style="font-weight:600; color:#495057;">Category Logo</label>
                        <div class="custom-file">
                            <input type="file" name="logo" class="form-control border-0 bg-light" id="cat_logo" accept="image/*" style="border-radius:10px;">
                        </div>
                        <div id="logoPreview" class="mt-3 text-center" style="display:none;">
                            <img src="" id="previewImg" class="rounded shadow-sm" style="max-width: 100px; height: auto; border: 2px solid #fff;">
                        </div>
                    </div>
                    <div class="form-group mb-0">
                        <label class="form-label" style="font-weight:600; color:#495057;">Status</label>
                        <select name="status" id="cat_status" class="form-select border-0 bg-light" style="border-radius:10px;">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0 pb-4 justify-content-center">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal" style="border-radius:10px;">Cancel</button>
                    <button type="submit" class="btn btn-success px-5 shadow-sm" style="border-radius:10px; font-weight:600;">Save Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function openModal(cat = null) {
        $('#categoryForm')[0].reset();
        $('#logoPreview').hide();
        if (cat) {
            $('#cat_id').val(cat.id);
            $('#cat_name').val(cat.name);
            $('#cat_status').val(cat.status);
            $('#modalTitle').text('Edit Category');
            if (cat.logo) {
                $('#previewImg').attr('src', '../' + cat.logo);
                $('#logoPreview').show();
            }
        } else {
            $('#cat_id').val('');
            $('#modalTitle').text('Add New Category');
        }
        $('#categoryModal').modal('show');
    }

    $('#cat_logo').change(function() {
        const file = this.files[0];
        if (file) {
            let reader = new FileReader();
            reader.onload = function(event) {
                $('#previewImg').attr('src', event.target.result);
                $('#logoPreview').fadeIn();
            }
            reader.readAsDataURL(file);
        }
    });

    $('#categoryForm').submit(function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        formData.append('action', 'save_typing_category');
        
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
                    Swal.fire({ icon: 'success', title: 'Success!', text: response.message }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: response.message });
                }
            },
            error: function() {
                $('#loader-overlay').hide();
                Swal.fire({ icon: 'error', title: 'Oops...', text: 'Something went wrong!' });
            }
        });
    });

    function deleteCategory(id, name) {
        Swal.fire({
            title: 'Are you sure?',
            text: `You want to delete category: ${name}`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $('#loader-overlay').css('display', 'flex');
                $.ajax({
                    url: 'typing_action.php',
                    type: 'POST',
                    data: { action: 'delete_typing_category', id: id },
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
                    }
                });
            }
        });
    }

    function toggleStatus(id, currentStatus) {
        const newStatus = currentStatus === 1 ? 0 : 1;
        $.ajax({
            url: 'typing_action.php',
            type: 'POST',
            data: { action: 'toggle_typing_status', id: id, status: newStatus },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    location.reload();
                }
            }
        });
    }
</script>

<?php include '../footer.php'; ?>

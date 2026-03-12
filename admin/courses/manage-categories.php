<?php include '../header.php'; ?>

<link rel="stylesheet" href="../assets/css/loader.css">

<style>
    .cat-table-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.05);
    }
    .cat-table-card .card-header {
        background: #fff;
        border-bottom: 2px solid #f4f6f9;
        padding: 20px;
    }
    .cat-img {
        width: 50px;
        height: 50px;
        object-fit: cover;
        border-radius: 8px;
        border: 1px solid #eee;
    }
    .status-badge {
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }
    .pagination .page-link {
        color: #28a745;
        border-radius: 5px;
        margin: 0 2px;
    }
    .pagination .page-item.active .page-link {
        background-color: #28a745;
        border-color: #28a745;
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
                <h1 class="m-0 text-dark">Manage Course Categories</h1>
            </div>
            <div class="col-sm-6 text-right">
                <a href="add-course-category.php" class="btn btn-success shadow-sm">
                    <i class="fas fa-plus mr-1"></i> Add New Category
                </a>
            </div>
        </div>
    </div>
</div>

<?php
// Pagination logic
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;

try {
    // Total count
    $total_stmt = $pdo->query("SELECT COUNT(id) FROM course_categories");
    $total_results = $total_stmt->fetchColumn();
    $total_pages = ceil($total_results / $limit);

    // Fetch data
    $stmt = $pdo->prepare("SELECT * FROM course_categories ORDER BY created_at DESC LIMIT $start, $limit");
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
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="card-title" style="font-weight:700; color:#343a40;">Registered Categories (<?= $total_results ?>)</h3>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="pl-4" style="width: 80px;">Image</th>
                                        <th>Category Name</th>
                                        <th>Slug</th>
                                        <th>SEO Title</th>
                                        <th>Status</th>
                                        <th class="text-right pr-4">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($categories)): ?>
                                        <tr>
                                            <td colspan="6" class="text-center py-5 text-muted">No categories found.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($categories as $cat): ?>
                                            <tr>
                                                <td class="pl-4">
                                                    <?php if (!empty($cat['featured_image'])): ?>
                                                        <img src="../src/images/<?= htmlspecialchars($cat['featured_image']) ?>" alt="" class="cat-img">
                                                    <?php else: ?>
                                                        <div class="cat-img bg-light d-flex align-items-center justify-content-center">
                                                            <i class="fas fa-image text-muted"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                </td>
                                                <td style="font-weight:600;"><?= htmlspecialchars($cat['name']) ?></td>
                                                <td><code class="text-xs"><?= htmlspecialchars($cat['slug']) ?></code></td>
                                                <td class="text-muted small"><?= htmlspecialchars($cat['seo_title'] ?: '-') ?></td>
                                                <td>
                                                    <?php if ($cat['status']): ?>
                                                        <span class="status-badge bg-success-light text-success" style="background:#e8f5e9;">Active</span>
                                                    <?php else: ?>
                                                        <span class="status-badge bg-danger-light text-danger" style="background:#ffebee;">Inactive</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-right pr-4">
                                                    <a href="edit-course-category.php?id=<?= $cat['id'] ?>" class="btn btn-sm btn-outline-primary mr-1">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button onclick="deleteCategory(<?= $cat['id'] ?>, '<?= htmlspecialchars(addslashes($cat['name'])) ?>')" class="btn btn-sm btn-outline-danger">
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
                    <div class="card-footer bg-white">
                        <nav aria-label="Page navigation">
                            <ul class="pagination justify-content-center mb-0">
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

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function deleteCategory(id, name) {
    Swal.fire({
        title: 'Are you sure?',
        text: "You are about to delete the category: " + name,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $('#loader-overlay').css('display', 'flex');
            $.ajax({
                url: 'course_action.php',
                type: 'POST',
                data: { action: 'delete_category', id: id },
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

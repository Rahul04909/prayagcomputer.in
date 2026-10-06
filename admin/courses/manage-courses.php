<?php include '../header.php'; ?>

<link rel="stylesheet" href="../assets/css/loader.css">

<style>
    .course-table-card { border: none; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); }
    .course-img { width: 60px; height: 40px; object-fit: cover; border-radius: 4px; border: 1px solid #eee; }
    .status-badge { padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; }
    .price-text { font-weight: 700; color: #333; }
    .mrp-text { text-decoration: line-through; color: #999; font-size: 0.85em; }
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
    // Total count
    $total_stmt = $pdo->query("SELECT COUNT(id) FROM courses");
    $total_results = $total_stmt->fetchColumn();
    $total_pages = ceil($total_results / $limit);

    // Fetch data with category name
    $stmt = $pdo->prepare("SELECT c.*, cat.name as category_name 
                          FROM courses c 
                          LEFT JOIN course_categories cat ON c.category_id = cat.id 
                          ORDER BY c.created_at DESC 
                          LIMIT $start, $limit");
    $stmt->execute();
    $courses = $stmt->fetchAll();
} catch (PDOException $e) {
    $courses = [];
    $total_results = 0;
}
?>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card course-table-card">
                    <div class="card-header d-flex justify-content-between align-items-center bg-white p-3">
                        <h3 class="card-title" style="font-weight:700; color:#343a40; margin:0;">Courses List (<?= $total_results ?>)</h3>
                        <a href="add-course.php" class="btn btn-sm btn-success shadow-sm">
                            <i class="fas fa-plus mr-1"></i> Add New Course
                        </a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light text-muted small">
                                    <tr>
                                        <th class="pl-4">Image</th>
                                        <th>Course Details</th>
                                        <th>Category</th>
                                        <th>Duration</th>
                                        <th>Pricing</th>
                                        <th>Status</th>
                                        <th class="text-right pr-4">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($courses)): ?>
                                        <tr>
                                            <td colspan="6" class="text-center py-5 text-muted">No courses found.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($courses as $course): ?>
                                            <tr>
                                                <td class="pl-4">
                                                    <?php if (!empty($course['featured_image'])): ?>
                                                        <img src="../src/images/<?= htmlspecialchars($course['featured_image']) ?>" alt="" class="course-img">
                                                    <?php else: ?>
                                                        <div class="course-img bg-light d-flex align-items-center justify-content-center">
                                                            <i class="fas fa-image text-muted"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div style="font-weight:600; color:#2c3e50;"><?= htmlspecialchars($course['title']) ?></div>
                                                    <code class="text-xs text-muted"><?= htmlspecialchars($course['slug']) ?></code>
                                                </td>
                                                <td><span class="badge badge-light border"><?= htmlspecialchars($course['category_name'] ?: 'Uncategorized') ?></span></td>
                                                <td><span class="text-sm"><?= htmlspecialchars($course['duration'] ?: 'N/A') . ' ' . htmlspecialchars($course['duration_type'] ?: '') ?></span></td>
                                                <td>
                                                    <span class="price-text">₹<?= number_format($course['sale_price'], 2) ?></span>
                                                    <?php if ($course['mrp'] > $course['sale_price']): ?>
                                                        <br><span class="mrp-text">₹<?= number_format($course['mrp'], 2) ?></span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($course['status']): ?>
                                                        <span class="status-badge" style="background:#e8f5e9; color:#2e7d32;">Active</span>
                                                    <?php else: ?>
                                                        <span class="status-badge" style="background:#ffebee; color:#c62828;">Inactive</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-right pr-4">
                                                    <a href="edit-course.php?id=<?= $course['id'] ?>" class="btn btn-sm btn-outline-primary mr-1" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button onclick="deleteCourse(<?= $course['id'] ?>, '<?= htmlspecialchars(addslashes($course['title'])) ?>')" class="btn btn-sm btn-outline-danger" title="Delete">
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

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function deleteCourse(id, title) {
    Swal.fire({
        title: 'Are you sure?',
        text: "You are about to delete: " + title,
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
                data: { action: 'delete_course', id: id },
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

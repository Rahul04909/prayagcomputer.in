<?php include '../header.php'; ?>

<link rel="stylesheet" href="../assets/css/loader.css">

<style>
    .test-table-card { border: none; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); }
    .status-badge { padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; }
    .level-badge { font-size: 10px; text-transform: uppercase; font-weight: 700; }
    .pagination .page-link { color: #28a745; border-radius: 5px; margin: 0 2px; }
    .pagination .page-item.active .page-link { background-color: #28a745; border-color: #28a745; }
    #loader-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(255,255,255,0.7); display: none; align-items: center; justify-content: center; z-index: 9999; }
</style>

<div id="loader-overlay">
    <div class="loader"></div>
</div>

<div class="content-header p-0"></div>

<?php
// Pagination and Filter logic
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;

// Filter parameters
$category_filter = isset($_GET['category']) ? (int)$_GET['category'] : '';
$language_filter = isset($_GET['language']) ? $_GET['language'] : '';
$level_filter = isset($_GET['level']) ? $_GET['level'] : '';
$search_filter = isset($_GET['search']) ? trim($_GET['search']) : '';

try {
    // Fetch categories for filter dropdown
    $cat_stmt = $pdo->query("SELECT id, name FROM typing_exam_categories WHERE status = 1 ORDER BY name ASC");
    $all_categories = $cat_stmt->fetchAll();

    // Build WHERE clause
    $where = ["1=1"];
    $params = [];

    if ($category_filter) {
        $where[] = "t.category_id = ?";
        $params[] = $category_filter;
    }
    if ($language_filter) {
        $where[] = "t.language = ?";
        $params[] = $language_filter;
    }
    if ($level_filter) {
        $where[] = "t.level = ?";
        $params[] = $level_filter;
    }
    if ($search_filter) {
        $where[] = "(t.title LIKE ? OR t.short_description LIKE ?)";
        $params[] = "%$search_filter%";
        $params[] = "%$search_filter%";
    }

    $where_sql = implode(" AND ", $where);

    // Total count with filters
    $total_stmt = $pdo->prepare("SELECT COUNT(t.id) FROM typing_tests t WHERE $where_sql");
    $total_stmt->execute($params);
    $total_results = $total_stmt->fetchColumn();
    $total_pages = ceil($total_results / $limit);

    // Fetch tests
    $sql = "SELECT t.*, c.name as category_name 
            FROM typing_tests t 
            LEFT JOIN typing_exam_categories c ON t.category_id = c.id 
            WHERE $where_sql 
            ORDER BY t.created_at DESC LIMIT $start, $limit";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $tests = $stmt->fetchAll();
} catch (PDOException $e) {
    $tests = [];
    $total_results = 0;
    $total_pages = 0;
    $all_categories = [];
}
?>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card test-table-card">
                    <div class="card-header bg-white p-3">
                        <div class="row align-items-center">
                            <div class="col-md-2">
                                <h3 class="card-title" style="font-weight:700; color:#343a40; margin:0;">Typing Tests (<?= $total_results ?>)</h3>
                            </div>
                            <div class="col-md-8">
                                <form method="GET" class="row g-2 justify-content-center">
                                    <div class="col-md-3">
                                        <select name="category" class="form-select form-select-sm" onchange="this.form.submit()">
                                            <option value="">All Categories</option>
                                            <?php foreach ($all_categories as $cat): ?>
                                                <option value="<?= $cat['id'] ?>" <?= ($category_filter == $cat['id']) ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <select name="language" class="form-select form-select-sm" onchange="this.form.submit()">
                                            <option value="">Language</option>
                                            <option value="English" <?= ($language_filter == 'English') ? 'selected' : '' ?>>English</option>
                                            <option value="Hindi" <?= ($language_filter == 'Hindi') ? 'selected' : '' ?>>Hindi</option>
                                            <option value="Punjabi" <?= ($language_filter == 'Punjabi') ? 'selected' : '' ?>>Punjabi</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <select name="level" class="form-select form-select-sm" onchange="this.form.submit()">
                                            <option value="">Level</option>
                                            <option value="Easy" <?= ($level_filter == 'Easy') ? 'selected' : '' ?>>Easy</option>
                                            <option value="Medium" <?= ($level_filter == 'Medium') ? 'selected' : '' ?>>Medium</option>
                                            <option value="Hard" <?= ($level_filter == 'Hard') ? 'selected' : '' ?>>Hard</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="input-group input-group-sm">
                                            <input type="text" name="search" class="form-control" placeholder="Search title..." value="<?= htmlspecialchars($search_filter) ?>">
                                            <button class="btn btn-success" type="submit"><i class="fas fa-search"></i></button>
                                            <?php if ($category_filter || $language_filter || $level_filter || $search_filter): ?>
                                                <a href="manage-typing-tests.php" class="btn btn-outline-secondary" title="Clear Filters"><i class="fas fa-times"></i></a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <div class="col-md-2 text-end">
                                <a href="add-typing-test.php" class="btn btn-sm btn-success shadow-sm w-100">
                                    <i class="fas fa-plus mr-1"></i> Add New Test
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light text-muted small">
                                    <tr>
                                        <th class="pl-4">Test Title</th>
                                        <th>Category</th>
                                        <th>Type</th>
                                        <th>Lang/Level</th>
                                        <th>Time</th>
                                        <th>Status</th>
                                        <th class="text-right pr-4">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($tests)): ?>
                                        <tr>
                                            <td colspan="7" class="text-center py-5 text-muted">No tests found. Add your first test!</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($tests as $test): ?>
                                            <tr>
                                                <td class="pl-4">
                                                    <span style="font-weight:600; color:#2c3e50;"><?= htmlspecialchars($test['title']) ?></span><br>
                                                    <small class="text-muted"><?= date('d M Y', strtotime($test['created_at'])) ?></small>
                                                </td>
                                                <td><span class="badge badge-light border"><?= htmlspecialchars($test['category_name'] ?: 'Uncategorized') ?></span></td>
                                                <td><small class="badge badge-info shadow-none" style="font-weight:500;"><?= $test['test_type'] ?></small></td>
                                                <td>
                                                    <span class="text-sm font-weight-bold"><?= $test['language'] ?></span><br>
                                                    <?php 
                                                        $lvl_color = ['Easy'=>'#28a745', 'Medium'=>'#ffc107', 'Hard'=>'#dc3545'][$test['level']];
                                                    ?>
                                                    <span class="level-badge" style="color: <?= $lvl_color ?>;"><?= $test['level'] ?></span>
                                                </td>
                                                <td>
                                                    <i class="far fa-clock mr-1 text-muted"></i> <?= $test['test_time'] ?> min
                                                </td>
                                                <td>
                                                    <?php if ($test['status']): ?>
                                                        <span class="status-badge" style="background:#e8f5e9; color:#2e7d32;">Published</span>
                                                    <?php else: ?>
                                                        <span class="status-badge" style="background:#f4f4f4; color:#666;">Draft</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-right pr-4">
                                                    <button onclick="Swal.fire('Info', 'Edit functionality coming soon!', 'info')" class="btn btn-sm btn-outline-primary mr-1">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button onclick="deleteTest(<?= $test['id'] ?>, '<?= htmlspecialchars(addslashes($test['title'])) ?>')" class="btn btn-sm btn-outline-danger">
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
                </div>
                
                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <div class="card-footer bg-white border-top-0">
                        <nav aria-label="Page navigation">
                            <ul class="pagination pagination-sm justify-content-center mb-0">
                                <?php 
                                    $query_params = $_GET;
                                    function get_page_url($p, $params) {
                                        $params['page'] = $p;
                                        return "?" . http_build_query($params);
                                    }
                                ?>
                                <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                                    <a class="page-link" href="<?= get_page_url($page - 1, $query_params) ?>"><i class="fas fa-chevron-left"></i></a>
                                </li>
                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                    <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                                        <a class="page-link" href="<?= get_page_url($i, $query_params) ?>"><?= $i ?></a>
                                    </li>
                                <?php endfor; ?>
                                <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
                                    <a class="page-link" href="<?= get_page_url($page + 1, $query_params) ?>"><i class="fas fa-chevron-right"></i></a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function deleteTest(id, title) {
        Swal.fire({
            title: 'Delete Test?',
            text: "Are you sure you want to remove: " + title,
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
                    data: { action: 'delete_typing_test', id: id },
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
                        Swal.fire('Error!', 'Operation failed.', 'error');
                    }
                });
            }
        });
    }
</script>

<?php include '../footer.php'; ?>

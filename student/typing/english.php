<?php include '../header.php'; ?>

<?php
// ── Access Guard ─────────────────────────────────────────────────────────────
if ($typing_access === 'None') {
    echo '<div class="container-fluid py-5 text-center">
        <i class="fas fa-lock fa-4x text-warning mb-3"></i>
        <h4 class="text-muted">You do not have access to English Typing.</h4>
        <p class="text-muted">Please contact the admin to enable this module.</p>
    </div>';
    include '../footer.php';
    exit();
}

// ── Filters & Pagination ─────────────────────────────────────────────────────
$limit  = 12;
$page   = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$start  = ($page - 1) * $limit;

$cat_filter    = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$level_filter  = isset($_GET['level'])    ? trim($_GET['level'])    : '';
$type_filter   = isset($_GET['type'])     ? trim($_GET['type'])     : '';
$search_filter = isset($_GET['search'])   ? trim($_GET['search'])   : '';

try {
    // Exam categories with English tests
    $cats = $pdo->query(
        "SELECT c.*, COUNT(t.id) as test_count
         FROM typing_exam_categories c
         LEFT JOIN typing_tests t ON t.category_id = c.id AND t.language = 'English' AND t.status = 1
         WHERE c.status = 1
         GROUP BY c.id
         ORDER BY c.name ASC"
    )->fetchAll();

    // Build WHERE for tests
    $where  = ["t.language = 'English'", "t.status = 1"];
    $params = [];

    if ($cat_filter) {
        $where[]  = "t.category_id = ?";
        $params[] = $cat_filter;
    }
    if ($level_filter) {
        $where[]  = "t.level = ?";
        $params[] = $level_filter;
    }
    if ($type_filter) {
        $where[]  = "t.test_type = ?";
        $params[] = $type_filter;
    }
    if ($search_filter) {
        $where[]  = "(t.title LIKE ? OR t.short_description LIKE ?)";
        $params[] = "%$search_filter%";
        $params[] = "%$search_filter%";
    }

    $where_sql = implode(' AND ', $where);

    // Total count
    $total_stmt = $pdo->prepare("SELECT COUNT(t.id) FROM typing_tests t WHERE $where_sql");
    $total_stmt->execute($params);
    $total_results = $total_stmt->fetchColumn();
    $total_pages   = ceil($total_results / $limit);

    // Fetch tests
    $sql = "SELECT t.*, c.name AS category_name, c.logo AS category_logo
            FROM typing_tests t
            LEFT JOIN typing_exam_categories c ON t.category_id = c.id
            WHERE $where_sql
            ORDER BY t.created_at DESC
            LIMIT $start, $limit";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $tests = $stmt->fetchAll();

} catch (PDOException $e) {
    $cats = $tests = [];
    $total_results = $total_pages = 0;
}

// Helper: level color
function levelColor($level) {
    return match($level) {
        'Easy'   => ['bg' => '#e8f5e9', 'c' => '#2e7d32'],
        'Medium' => ['bg' => '#fff3e0', 'c' => '#e65100'],
        'Hard'   => ['bg' => '#ffebee', 'c' => '#c62828'],
        default  => ['bg' => '#f5f5f5', 'c' => '#616161'],
    };
}

// Helper: type icon
function typeIcon($type) {
    return match($type) {
        'Typing Test'   => 'fa-stopwatch',
        'Practice Test' => 'fa-pencil-alt',
        'Lesson'        => 'fa-book-reader',
        default         => 'fa-file-alt',
    };
}

// Page URL builder
function pageUrl($p, $extra = []) {
    $params = array_merge($_GET, $extra, ['page' => $p]);
    return '?' . http_build_query($params);
}
?>

<style>
    .cat-card { border: none; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); }
    .status-badge { padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; }
    .level-badge { font-size: 10px; text-transform: uppercase; font-weight: 700; padding: 2px 8px; border-radius: 4px; }
    .cat-logo { width: 30px; height: 30px; object-fit: contain; border-radius: 4px; background: #f8f9fa; padding: 2px; border: 1px solid #eee; }
    .pagination .page-link { color: #28a745; border-radius: 5px; margin: 0 2px; }
    .pagination .page-item.active .page-link { background-color: #28a745; border-color: #28a745; }
    #loader-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(255,255,255,0.7); display: none; align-items: center; justify-content: center; z-index: 9999; }
    .btn-start { border-radius: 8px; font-weight: 600; font-size: 12px; padding: 6px 15px; transition: all 0.3s; }
    .btn-start:hover { transform: translateY(-1px); box-shadow: 0 4px 8px rgba(40, 167, 69, 0.2); }
    .category-strip { display: flex; overflow-x: auto; gap: 10px; padding-bottom: 15px; margin-bottom: 20px; scrollbar-width: thin; }
    .category-strip::-webkit-scrollbar { height: 4px; }
    .category-strip::-webkit-scrollbar-thumb { background: #ddd; border-radius: 10px; }
    .cat-chip { 
        white-space: nowrap; padding: 8px 16px; border-radius: 20px; background: #fff; border: 1px solid #e0e0e0;
        color: #666; font-size: 13px; font-weight: 600; transition: all 0.2s; cursor: pointer; text-decoration: none !important;
    }
    .cat-chip:hover, .cat-chip.active { background: #28a745; color: #fff; border-color: #28a745; }
    .cat-chip i { margin-right: 6px; }
</style>

<div id="loader-overlay">
    <div class="loader"></div>
</div>

<section class="content">
    <div class="container-fluid">
        
        <!-- Category Strip -->
        <?php if (!empty($cats)): ?>
        <div class="category-strip">
            <a href="english.php" class="cat-chip <?= !$cat_filter ? 'active' : '' ?>">
                <i class="fas fa-th-large"></i> All Categories
            </a>
            <?php foreach ($cats as $cat): ?>
                <a href="?category=<?= $cat['id'] ?>" class="cat-chip <?= $cat_filter == $cat['id'] ? 'active' : '' ?>">
                    <?php if ($cat['logo']): ?>
                        <img src="../../admin/<?= htmlspecialchars($cat['logo']) ?>" style="width:16px; height:16px; margin-right:6px; object-fit:contain;">
                    <?php else: ?>
                        <i class="fas fa-folder"></i>
                    <?php endif; ?>
                    <?= htmlspecialchars($cat['name']) ?>
                </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-12">
                <div class="card cat-card">
                    <div class="card-header bg-white p-3">
                        <form method="GET" class="row g-2 align-items-center">
                            <div class="col-md-3">
                                <h3 class="card-title" style="font-weight:700; color:#343a40; margin:0;">Available Tests (<?= $total_results ?>)</h3>
                            </div>
                            <div class="col-md-3">
                                <div class="input-group input-group-sm">
                                    <input type="text" name="search" class="form-control" placeholder="Search test name..." value="<?= htmlspecialchars($search_filter) ?>">
                                    <button class="btn btn-success" type="submit"><i class="fas fa-search"></i></button>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <select name="level" class="form-select form-select-sm" onchange="this.form.submit()">
                                    <option value="">All Levels</option>
                                    <option value="Easy" <?= ($level_filter == 'Easy') ? 'selected' : '' ?>>Easy</option>
                                    <option value="Medium" <?= ($level_filter == 'Medium') ? 'selected' : '' ?>>Medium</option>
                                    <option value="Hard" <?= ($level_filter == 'Hard') ? 'selected' : '' ?>>Hard</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select name="type" class="form-select form-select-sm" onchange="this.form.submit()">
                                    <option value="">All Types</option>
                                    <option value="Typing Test" <?= ($type_filter == 'Typing Test') ? 'selected' : '' ?>>Typing Test</option>
                                    <option value="Practice Test" <?= ($type_filter == 'Practice Test') ? 'selected' : '' ?>>Practice Test</option>
                                    <option value="Lesson" <?= ($type_filter == 'Lesson') ? 'selected' : '' ?>>Lesson</option>
                                </select>
                            </div>
                            <div class="col-md-2 text-end">
                                <?php if ($cat_filter || $level_filter || $type_filter || $search_filter): ?>
                                    <a href="english.php" class="btn btn-sm btn-outline-secondary w-100" title="Clear Filters">
                                        <i class="fas fa-times mr-1"></i> Clear
                                    </a>
                                <?php endif; ?>
                            </div>
                            <?php if ($cat_filter): ?>
                                <input type="hidden" name="category" value="<?= $cat_filter ?>">
                            <?php endif; ?>
                        </form>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light text-muted small text-uppercase">
                                    <tr>
                                        <th class="pl-4">Test Information</th>
                                        <th>Category</th>
                                        <th>Config</th>
                                        <th>Time</th>
                                        <th class="text-right pr-4">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($tests)): ?>
                                        <tr>
                                            <td colspan="5" class="text-center py-5 text-muted">
                                                <i class="fas fa-keyboard fa-3x mb-3 d-block opacity-2"></i>
                                                No typing tests found matching your criteria.
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($tests as $t): 
                                            $lvl = levelColor($t['level']);
                                        ?>
                                            <tr>
                                                <td class="pl-4">
                                                    <div class="d-flex align-items-center">
                                                        <div class="mr-3 text-center" style="width: 40px; height: 40px; border-radius: 10px; background: #f0fff4; display: flex; align-items: center; justify-content: center; color: #28a745;">
                                                            <i class="fas fa-file-alt"></i>
                                                        </div>
                                                        <div>
                                                            <span style="font-weight:700; color:#2c3e50; font-size: 15px;"><?= htmlspecialchars($t['title']) ?></span><br>
                                                            <small class="text-muted d-block text-truncate" style="max-width: 250px;"><?= htmlspecialchars(strip_tags($t['short_description'] ?? '')) ?></small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <?php if ($t['category_logo']): ?>
                                                            <img src="../../admin/<?= htmlspecialchars($t['category_logo']) ?>" class="cat-logo mr-2">
                                                        <?php else: ?>
                                                            <div class="cat-logo mr-2 d-flex align-items-center justify-content-center text-muted small"><i class="fas fa-folder"></i></div>
                                                        <?php endif; ?>
                                                        <span class="badge badge-light border" style="font-weight:600;"><?= htmlspecialchars($t['category_name'] ?: 'N/A') ?></span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge" style="background: <?= $lvl['bg'] ?>; color: <?= $lvl['c'] ?>; border-radius: 4px; font-size: 10px; text-transform: uppercase; font-weight: 700;"><?= $t['level'] ?></span><br>
                                                    <small class="text-muted" style="font-weight: 600;"><i class="fas fa-tag mr-1" style="font-size: 9px;"></i><?= $t['test_type'] ?></small>
                                                </td>
                                                <td>
                                                    <div style="font-weight: 700; color: #495057;"><i class="far fa-clock mr-1 text-success"></i> <?= $t['test_time'] ?> Min</div>
                                                    <small class="text-muted" style="font-size: 10px;">Created: <?= date('d M Y', strtotime($t['created_at'])) ?></small>
                                                </td>
                                                <td class="text-right pr-4">
                                                    <a href="take-test.php?id=<?= $t['id'] ?>" class="btn btn-success btn-start shadow-sm">
                                                        <i class="fas fa-play mr-1"></i> Start Test
                                                    </a>
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
                                        <a class="page-link" href="<?= pageUrl($page - 1) ?>"><i class="fas fa-chevron-left"></i></a>
                                    </li>
                                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                        <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                                            <a class="page-link" href="<?= pageUrl($i) ?>"><?= $i ?></a>
                                        </li>
                                    <?php endfor; ?>
                                    <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
                                        <a class="page-link" href="<?= pageUrl($page + 1) ?>"><i class="fas fa-chevron-right"></i></a>
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

<?php include '../footer.php'; ?>

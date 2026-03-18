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
.cat-strip { display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 24px; }
.cat-chip {
    display: flex; align-items: center; gap: 8px;
    padding: 8px 16px; border-radius: 30px;
    border: 1.5px solid #e2e8f0; background: #fff;
    cursor: pointer; font-size: 0.82rem; font-weight: 600;
    color: #4a5568; transition: all 0.2s ease;
    text-decoration: none;
}
.cat-chip:hover { border-color: #28a745; color: #28a745; text-decoration: none; background: #f0fff4; }
.cat-chip.active { background: #28a745; color: #fff; border-color: #28a745; }
.cat-chip img { width: 22px; height: 22px; object-fit: contain; border-radius: 4px; }
.cat-chip .cnt { font-size: 10px; background: rgba(255,255,255,0.3); padding: 1px 6px; border-radius: 10px; }
.cat-chip.active .cnt { background: rgba(255,255,255,0.3); }
.cat-chip:not(.active) .cnt { background: #f1f5f9; color: #64748b; }

.test-card {
    border: none; border-radius: 14px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.06);
    transition: all 0.25s ease; height: 100%;
    overflow: hidden; background: #fff;
}
.test-card:hover { transform: translateY(-4px); box-shadow: 0 8px 25px rgba(0,0,0,0.1); }
.test-card .card-top { background: linear-gradient(135deg,#f0fff4,#e6ffed); padding: 20px; }
.test-card .type-badge { font-size: 10px; text-transform: uppercase; font-weight: 700; letter-spacing: .5px; }
.test-card .title { font-size: 0.95rem; font-weight: 700; color: #1a202c; margin: 10px 0 6px; line-height: 1.4; }
.test-card .desc { font-size: 0.8rem; color: #64748b; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
.test-card .meta { font-size: 0.78rem; color: #64748b; padding: 14px 18px; border-top: 1px solid #f1f5f9; display: flex; gap: 14px; align-items: center; }
.test-card .meta i { color: #28a745; }
.start-btn {
    display: block; text-align: center; padding: 10px;
    background: #28a745; color: #fff; font-weight: 600; font-size: 0.85rem;
    border-radius: 0 0 14px 14px; transition: background .2s;
    text-decoration: none;
}
.start-btn:hover { background: #218838; color: #fff; text-decoration: none; }

.filter-bar { background: #fff; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); padding: 16px 20px; margin-bottom: 24px; }
.filter-bar .form-select, .filter-bar .form-control { border-radius: 8px; font-size: 0.85rem; border: 1.5px solid #e2e8f0; }
.filter-bar .form-select:focus, .filter-bar .form-control:focus { border-color: #28a745; box-shadow: none; }

.pagination .page-link { color: #28a745; border-radius: 6px; margin: 0 2px; }
.pagination .page-item.active .page-link { background: #28a745; border-color: #28a745; }
.empty-state { text-align: center; padding: 60px 20px; color: #94a3b8; }
</style>

<section class="content">
<div class="container-fluid py-3">

    <!-- Header -->
    <div class="d-flex align-items-center mb-4">
        <div style="width:44px;height:44px;border-radius:12px;background:linear-gradient(135deg,#28a745,#218838);display:flex;align-items:center;justify-content:center;margin-right:14px;">
            <i class="fas fa-keyboard text-white" style="font-size:1.1rem;"></i>
        </div>
        <div>
            <h4 class="mb-0" style="font-weight:800;color:#1a202c;">English Typing Tests</h4>
            <p class="mb-0 text-muted" style="font-size:0.83rem;"><?= $total_results ?> test<?= $total_results != 1 ? 's' : '' ?> available</p>
        </div>
    </div>

    <!-- Exam Category Chips -->
    <?php if (!empty($cats)): ?>
    <div class="cat-strip">
        <a href="<?= pageUrl(1, ['category' => '', 'page' => 1]) ?>" class="cat-chip <?= !$cat_filter ? 'active' : '' ?>">
            <i class="fas fa-th-large" style="font-size:14px;"></i> All Categories
            <span class="cnt"><?= array_sum(array_column($cats, 'test_count')) ?></span>
        </a>
        <?php foreach ($cats as $cat): ?>
        <a href="<?= pageUrl(1, ['category' => $cat['id'], 'page' => 1]) ?>" class="cat-chip <?= $cat_filter == $cat['id'] ? 'active' : '' ?>">
            <?php if ($cat['logo']): ?>
                <img src="../../admin/<?= htmlspecialchars($cat['logo']) ?>" alt="">
            <?php else: ?>
                <i class="fas fa-layer-group" style="font-size:13px;"></i>
            <?php endif; ?>
            <?= htmlspecialchars($cat['name']) ?>
            <span class="cnt"><?= $cat['test_count'] ?></span>
        </a>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Filter Bar -->
    <form method="GET" class="filter-bar">
        <div class="row g-2 align-items-center">
            <div class="col-md-4">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0" style="border-radius:8px 0 0 8px; border:1.5px solid #e2e8f0;">
                        <i class="fas fa-search text-muted" style="font-size:.8rem;"></i>
                    </span>
                    <input type="text" name="search" class="form-control border-start-0" placeholder="Search tests..." value="<?= htmlspecialchars($search_filter) ?>" style="border-radius:0 8px 8px 0; border:1.5px solid #e2e8f0; border-left:none;">
                </div>
            </div>
            <div class="col-md-2">
                <select name="level" class="form-select">
                    <option value="">All Levels</option>
                    <?php foreach (['Easy','Medium','Hard'] as $l): ?>
                    <option value="<?= $l ?>" <?= $level_filter === $l ? 'selected' : '' ?>><?= $l ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <select name="type" class="form-select">
                    <option value="">All Types</option>
                    <?php foreach (['Typing Test','Practice Test','Lesson'] as $t): ?>
                    <option value="<?= $t ?>" <?= $type_filter === $t ? 'selected' : '' ?>><?= $t ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php if ($cat_filter): ?>
            <input type="hidden" name="category" value="<?= $cat_filter ?>">
            <?php endif; ?>
            <div class="col-md-2">
                <button type="submit" class="btn btn-success w-100" style="border-radius:8px; font-weight:600; font-size:.85rem;">
                    <i class="fas fa-filter mr-1"></i> Apply
                </button>
            </div>
            <div class="col-md-2">
                <a href="english.php" class="btn btn-light w-100" style="border-radius:8px; font-size:.85rem;">
                    <i class="fas fa-times mr-1"></i> Clear
                </a>
            </div>
        </div>
    </form>

    <!-- Test Cards Grid -->
    <?php if (empty($tests)): ?>
    <div class="empty-state">
        <i class="fas fa-inbox fa-3x mb-3" style="color:#cbd5e1;"></i>
        <h5 style="color:#94a3b8;">No English typing tests found</h5>
        <p style="font-size:.85rem;">Try adjusting your filters or check back later.</p>
    </div>
    <?php else: ?>
    <div class="row" id="testsGrid">
        <?php foreach ($tests as $t):
            $lvl = levelColor($t['level']);
            $ico = typeIcon($t['test_type']);
            $catLogo = $t['category_logo'] ? '../../admin/' . $t['category_logo'] : null;
        ?>
        <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
            <div class="test-card card">
                <div class="card-top">
                    <div class="d-flex justify-content-between align-items-start">
                        <span class="type-badge" style="background:<?= $lvl['bg'] ?>;color:<?= $lvl['c'] ?>;padding:4px 10px;border-radius:20px;">
                            <?= htmlspecialchars($t['level']) ?>
                        </span>
                        <span class="type-badge text-muted" style="background:#f8fafc;padding:4px 10px;border-radius:20px;">
                            <i class="fas <?= $ico ?> mr-1"></i><?= htmlspecialchars($t['test_type']) ?>
                        </span>
                    </div>
                    <p class="title"><?= htmlspecialchars($t['title']) ?></p>
                    <p class="desc mb-0"><?= htmlspecialchars(strip_tags($t['short_description'] ?? '')) ?></p>
                </div>
                <div class="meta">
                    <?php if ($catLogo): ?>
                    <img src="<?= htmlspecialchars($catLogo) ?>" style="width:18px;height:18px;object-fit:contain;border-radius:3px;" alt="">
                    <?php else: ?>
                    <i class="fas fa-layer-group"></i>
                    <?php endif; ?>
                    <span title="Category"><?= htmlspecialchars($t['category_name'] ?? '—') ?></span>
                    <span class="ml-auto"><i class="fas fa-clock mr-1"></i><?= $t['test_time'] ?> min</span>
                </div>
                <a href="take-test.php?id=<?= $t['id'] ?>" class="start-btn">
                    <i class="fas fa-play-circle mr-1"></i> Start Test
                </a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
    <nav class="mt-2 mb-4">
        <ul class="pagination pagination-sm justify-content-center">
            <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                <a class="page-link" href="<?= pageUrl($page - 1) ?>"><i class="fas fa-chevron-left"></i></a>
            </li>
            <?php
            $start_p = max(1, $page - 2);
            $end_p   = min($total_pages, $page + 2);
            if ($start_p > 1) echo '<li class="page-item disabled"><span class="page-link">…</span></li>';
            for ($i = $start_p; $i <= $end_p; $i++):
            ?>
                <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                    <a class="page-link" href="<?= pageUrl($i) ?>"><?= $i ?></a>
                </li>
            <?php endfor;
            if ($end_p < $total_pages) echo '<li class="page-item disabled"><span class="page-link">…</span></li>'; ?>
            <li class="page-item <?= $page >= $total_pages ? 'disabled' : '' ?>">
                <a class="page-link" href="<?= pageUrl($page + 1) ?>"><i class="fas fa-chevron-right"></i></a>
            </li>
        </ul>
        <p class="text-center text-muted mb-0" style="font-size:.8rem;">
            Showing <?= ($start + 1) ?>–<?= min($start + $limit, $total_results) ?> of <?= $total_results ?> tests
        </p>
    </nav>
    <?php endif; ?>
    <?php endif; ?>

</div>
</section>

<?php include '../footer.php'; ?>

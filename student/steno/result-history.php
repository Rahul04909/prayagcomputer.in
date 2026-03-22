<?php include '../header.php'; ?>

<?php
// ── Filters & Pagination ─────────────────────────────────────────────────────
$limit  = 10;
$page   = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$start  = ($page - 1) * $limit;

$student_id = $student['id'];

try {
    // Total count for pagination
    $total_stmt = $pdo->prepare("SELECT COUNT(*) FROM steno_results WHERE student_id = ?");
    $total_stmt->execute([$student_id]);
    $total_results = $total_stmt->fetchColumn();
    $total_pages   = ceil($total_results / $limit);

    // Fetch results with test details
    $sql = "SELECT r.*, t.title as test_title, t.language, t.level, c.name as category_name, c.logo as category_logo
            FROM steno_results r
            JOIN steno_tests t ON r.test_id = t.id
            LEFT JOIN steno_exam_categories c ON t.category_id = c.id
            WHERE r.student_id = ?
            ORDER BY r.test_date DESC
            LIMIT $start, $limit";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$student_id]);
    $results = $stmt->fetchAll();

} catch (PDOException $e) {
    $results = [];
    $total_results = $total_pages = 0;
}

// Helper: performance color
function getAccuracyColor($accuracy) {
    if ($accuracy >= 95) return '#28a745';
    if ($accuracy >= 90) return '#17a2b8';
    if ($accuracy >= 80) return '#ffc107';
    return '#dc3545';
}

function levelColor($level) {
    return match($level) {
        'Easy'   => ['bg' => '#e8f5e9', 'c' => '#2e7d32'],
        'Medium' => ['bg' => '#fff3e0', 'c' => '#e65100'],
        'Hard'   => ['bg' => '#ffebee', 'c' => '#c62828'],
        default  => ['bg' => '#f5f5f5', 'c' => '#616161'],
    };
}
?>

<style>
    .history-card { border: none; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); }
    .accuracy-badge { padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 700; color: #fff; }
    .cat-logo { width: 30px; height: 30px; object-fit: contain; border-radius: 4px; background: #f8f9fa; padding: 2px; border: 1px solid #eee; }
    .pagination .page-link { color: #28a745; border-radius: 5px; margin: 0 2px; }
    .pagination .page-item.active .page-link { background-color: #28a745; border-color: #28a745; }
    .btn-action { border-radius: 8px; font-weight: 600; font-size: 12px; padding: 6px 12px; transition: all 0.3s; }
    .btn-download { background-color: #f8f9fa; color: #333; border: 1px solid #ddd; }
    .btn-download:hover { background-color: #e9ecef; color: #000; }
</style>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card history-card">
                    <div class="card-header bg-white p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h3 class="card-title" style="font-weight:700; color:#343a40; margin:0;">
                                <i class="fas fa-history mr-2 text-success"></i> Steno Test History (<?= $total_results ?>)
                            </h3>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0 text-center">
                                <thead class="bg-light text-muted small text-uppercase">
                                    <tr>
                                        <th class="text-left pl-4">Test Details</th>
                                        <th>Language</th>
                                        <th>Speed (WPM)</th>
                                        <th>Accuracy</th>
                                        <th>Mistakes</th>
                                        <th>Date & Time</th>
                                        <th class="text-right pr-4">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($results)): ?>
                                        <tr>
                                            <td colspan="7" class="text-center py-5 text-muted">
                                                <i class="fas fa-clipboard-list fa-3x mb-3 d-block" style="opacity: 0.2;"></i>
                                                <p>No steno tests recorded yet. Start practicing now!</p>
                                                <a href="english.php" class="btn btn-success btn-sm mt-2">Take English Test</a>
                                                <a href="hindi.php" class="btn btn-success btn-sm mt-2">Take Hindi Test</a>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($results as $r): 
                                            $lvl = levelColor($r['level']);
                                            $accColor = getAccuracyColor($r['accuracy']);
                                        ?>
                                            <tr>
                                                <td class="text-left pl-4">
                                                    <div class="d-flex align-items-center">
                                                        <div class="mr-3 text-center" style="width: 35px; height: 35px; border-radius: 8px; background: #f0fff4; display: flex; align-items: center; justify-content: center; color: #28a745;">
                                                            <i class="fas fa-file-alt"></i>
                                                        </div>
                                                        <div>
                                                            <span style="font-weight:700; color:#2c3e50;"><?= htmlspecialchars($r['test_title']) ?></span><br>
                                                            <small class="text-muted"><?= htmlspecialchars($r['category_name'] ?: 'General') ?></small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge border <?= $r['language'] == 'Hindi' ? 'text-danger border-danger' : 'text-primary border-primary' ?>" style="font-size: 10px;">
                                                        <?= $r['language'] ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div style="font-weight: 800; color: #2c3e50; font-family: 'Roboto Mono', monospace;"><?= $r['wpm'] ?></div>
                                                    <small class="text-muted" style="font-size: 9px; font-weight: 600;">WORDS/MIN</small>
                                                </td>
                                                <td>
                                                    <span class="accuracy-badge" style="background: <?= $accColor ?>;">
                                                        <?= number_format($r['accuracy'], 1) ?>%
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="text-danger font-weight-bold"><?= $r['errors'] ?></span>
                                                    <small class="text-muted d-block" style="font-size: 9px;">MISTAKES</small>
                                                </td>
                                                <td>
                                                    <div style="font-size: 13px; font-weight: 600;"><?= date('d M Y', strtotime($r['test_date'])) ?></div>
                                                    <small class="text-muted"><?= date('h:i A', strtotime($r['test_date'])) ?></small>
                                                </td>
                                                <td class="text-right pr-4">
                                                    <div class="d-flex justify-content-end gap-2">
                                                        <a href="download-result.php?id=<?= $r['id'] ?>" target="_blank" class="btn btn-action btn-download" title="Download PDF">
                                                            <i class="fas fa-download"></i>
                                                        </a>
                                                        <!-- Note: In a real scenario, we might have a view-report.php as well -->
                                                        <button onclick="Swal.fire('Coming Soon', 'Detailed view functionality is being integrated.', 'info')" class="btn btn-action btn-outline-success">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                    </div>
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

<?php include '../footer.php'; ?>

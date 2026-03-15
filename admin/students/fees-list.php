<?php include '../header.php'; ?>

<link rel="stylesheet" href="../assets/css/loader.css">

<style>
    .fees-table-card { border: none; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); margin-bottom: 60px; }
    .student-img { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 2px solid #eee; }
    .enroll-badge { background: #e8f5e9; color: #2e7d32; padding: 2px 8px; border-radius: 4px; font-weight: 700; font-size: 11px; }
    .fees-badge { padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; }
    .bg-pending-soft { background: #fff3f3; color: #d32f2f; border: 1px solid #ffcdd2; }
    .bg-paid-soft { background: #f1f8e9; color: #2e7d32; border: 1px solid #c8e6c9; }
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
$course_filter = isset($_GET['course']) ? (int)$_GET['course'] : '';
$search_filter = isset($_GET['search']) ? trim($_GET['search']) : '';

try {
    // Fetch courses for filter dropdown
    $course_stmt = $pdo->query("SELECT id, title FROM courses WHERE status = 1 ORDER BY title ASC");
    $all_courses = $course_stmt->fetchAll();

    // Build WHERE clause
    $where = ["s.status = 1"]; // Only active students
    $params = [];

    if ($course_filter) {
        $where[] = "s.course_id = ?";
        $params[] = $course_filter;
    }
    if ($search_filter) {
        $where[] = "(s.student_name LIKE ? OR s.enrollment_no LIKE ? OR s.mobile LIKE ? OR s.email LIKE ?)";
        $params[] = "%$search_filter%";
        $params[] = "%$search_filter%";
        $params[] = "%$search_filter%";
        $params[] = "%$search_filter%";
    }

    $where_sql = implode(" AND ", $where);

    // Total count with filters
    $total_stmt = $pdo->prepare("SELECT COUNT(s.id) FROM students s WHERE $where_sql");
    $total_stmt->execute($params);
    $total_results = $total_stmt->fetchColumn();
    $total_pages = ceil($total_results / $limit);

    // Fetch students with fees summary
    $sql = "SELECT s.id, s.student_name, s.enrollment_no, s.total_fees, s.image, s.created_at,
                   c.title as course_title,
                   COALESCE((SELECT SUM(amount_paid) FROM student_fees WHERE student_id = s.id), 0) as paid_amount,
                   (SELECT id FROM student_fees WHERE student_id = s.id ORDER BY id DESC LIMIT 1) as last_txn_id
            FROM students s 
            LEFT JOIN courses c ON s.course_id = c.id 
            WHERE $where_sql 
            ORDER BY s.created_at DESC LIMIT $start, $limit";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $students = $stmt->fetchAll();
} catch (PDOException $e) {
    $students = [];
    $total_results = 0;
    $total_pages = 0;
    $all_courses = [];
}
?>

<section class="content mb-5">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card fees-table-card">
                    <div class="card-header bg-white p-3">
                        <div class="row align-items-center">
                            <div class="col-md-3">
                                <h3 class="card-title" style="font-weight:700; color:#343a40; margin:0;">Fees Monitoring (<?= $total_results ?>)</h3>
                            </div>
                            <div class="col-md-7">
                                <form method="GET" class="row g-2 justify-content-center">
                                    <div class="col-md-5">
                                        <select name="course" class="form-select form-select-sm" onchange="this.form.submit()">
                                            <option value="">All Courses</option>
                                            <?php foreach ($all_courses as $c): ?>
                                                <option value="<?= $c['id'] ?>" <?= ($course_filter == $c['id']) ? 'selected' : '' ?>><?= htmlspecialchars($c['title']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-7">
                                        <div class="input-group input-group-sm">
                                            <input type="text" name="search" class="form-control" placeholder="Search name, enrollment..." value="<?= htmlspecialchars($search_filter) ?>">
                                            <button class="btn btn-success" type="submit"><i class="fas fa-search"></i></button>
                                            <?php if ($course_filter || $search_filter): ?>
                                                <a href="fees-list.php" class="btn btn-outline-secondary" title="Clear Filters"><i class="fas fa-times"></i></a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <div class="col-md-2 text-end">
                                <a href="collect-fees.php" class="btn btn-sm btn-success shadow-sm w-100">
                                    <i class="fas fa-money-bill-wave mr-1"></i> Collect Fees
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light text-muted small">
                                    <tr>
                                        <th class="pl-4">Student Details</th>
                                        <th>Enrollment No</th>
                                        <th>Course</th>
                                        <th class="text-center">Total Fee</th>
                                        <th class="text-center">Paid Amount</th>
                                        <th class="text-center">Pending Amount</th>
                                        <th class="text-right pr-4">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($students)): ?>
                                        <tr>
                                            <td colspan="7" class="text-center py-5 text-muted">No records found.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($students as $student): 
                                            $pending = $student['total_fees'] - $student['paid_amount'];
                                            $status_class = ($pending <= 0) ? 'bg-paid-soft' : 'bg-pending-soft';
                                        ?>
                                            <tr>
                                                <td class="pl-4">
                                                    <div class="d-flex align-items-center">
                                                        <img src="<?= $student['image'] ? '../'.$student['image'] : '../src/images/placeholder-user.png' ?>" class="student-img mr-3" alt="Image">
                                                        <div>
                                                            <span style="font-weight:600; color:#2c3e50;"><?= htmlspecialchars($student['student_name']) ?></span><br>
                                                            <small class="text-muted">Joined: <?= date('d M Y', strtotime($student['created_at'])) ?></small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td><span class="enroll-badge"><?= $student['enrollment_no'] ?></span></td>
                                                <td><small class="badge badge-light border shadow-none" style="font-weight:600;"><?= htmlspecialchars($student['course_title'] ?: 'N/A') ?></small></td>
                                                <td class="text-center"><span class="font-weight-bold">₹<?= number_format($student['total_fees'], 2) ?></span></td>
                                                <td class="text-center"><span class="text-success font-weight-bold">₹<?= number_format($student['paid_amount'], 2) ?></span></td>
                                                <td class="text-center">
                                                    <span class="fees-badge <?= $status_class ?>">
                                                        ₹<?= number_format($pending, 2) ?>
                                                    </span>
                                                </td>
                                                <td class="pr-4">
                                                    <div class="d-flex justify-content-end align-items-center">
                                                        <a href="collect-fees.php?student_id=<?= $student['id'] ?>" class="btn btn-sm btn-outline-success mr-2" title="Collect Fees" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                                                            <i class="fas fa-plus"></i>
                                                        </a>
                                                        <?php if ($student['last_txn_id']): ?>
                                                            <a href="fee_action.php?action=generate_receipt&id=<?= $student['last_txn_id'] ?>" target="_blank" class="btn btn-sm btn-outline-primary" title="Print Last Receipt" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                                                                <i class="fas fa-print"></i>
                                                            </a>
                                                        <?php else: ?>
                                                            <button class="btn btn-sm btn-outline-secondary disabled" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;" title="No transactions yet">
                                                                <i class="fas fa-print"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                    </div>
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
                    <div class="card-footer bg-white border-top-0 mb-4">
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
                <div class="pb-5"></div>
            </div>
        </div>
    </div>
</section>

<?php include '../footer.php'; ?>

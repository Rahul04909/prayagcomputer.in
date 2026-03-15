<?php include '../header.php'; ?>

<link rel="stylesheet" href="../assets/css/loader.css">

<style>
    .student-table-card { border: none; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); }
    .status-badge { padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; cursor: pointer; }
    .student-img { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 2px solid #eee; }
    .enroll-badge { background: #e8f5e9; color: #2e7d32; padding: 2px 8px; border-radius: 4px; font-weight: 700; font-size: 11px; }
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
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$search_filter = isset($_GET['search']) ? trim($_GET['search']) : '';

try {
    // Fetch courses for filter dropdown
    $course_stmt = $pdo->query("SELECT id, title FROM courses WHERE status = 1 ORDER BY title ASC");
    $all_courses = $course_stmt->fetchAll();

    // Build WHERE clause
    $where = ["1=1"];
    $params = [];

    if ($course_filter) {
        $where[] = "s.course_id = ?";
        $params[] = $course_filter;
    }
    if ($status_filter !== '') {
        $where[] = "s.status = ?";
        $params[] = (int)$status_filter;
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

    // Fetch students
    $sql = "SELECT s.*, c.title as course_title 
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

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card student-table-card">
                    <div class="card-header bg-white p-3">
                        <div class="row align-items-center">
                            <div class="col-md-3">
                                <h3 class="card-title" style="font-weight:700; color:#343a40; margin:0;">Students Master (<?= $total_results ?>)</h3>
                            </div>
                            <div class="col-md-7">
                                <form method="GET" class="row g-2 justify-content-center">
                                    <div class="col-md-4">
                                        <select name="course" class="form-select form-select-sm" onchange="this.form.submit()">
                                            <option value="">All Courses</option>
                                            <?php foreach ($all_courses as $c): ?>
                                                <option value="<?= $c['id'] ?>" <?= ($course_filter == $c['id']) ? 'selected' : '' ?>><?= htmlspecialchars($c['title']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                                            <option value="">All Status</option>
                                            <option value="1" <?= ($status_filter === '1') ? 'selected' : '' ?>>Active</option>
                                            <option value="0" <?= ($status_filter === '0') ? 'selected' : '' ?>>Inactive</option>
                                        </select>
                                    </div>
                                    <div class="col-md-5">
                                        <div class="input-group input-group-sm">
                                            <input type="text" name="search" class="form-control" placeholder="Search name, enrollment, mobile..." value="<?= htmlspecialchars($search_filter) ?>">
                                            <button class="btn btn-success" type="submit"><i class="fas fa-search"></i></button>
                                            <?php if ($course_filter || $status_filter !== '' || $search_filter): ?>
                                                <a href="manage-students.php" class="btn btn-outline-secondary" title="Clear Filters"><i class="fas fa-times"></i></a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <div class="col-md-2 text-end">
                                <a href="add-student.php" class="btn btn-sm btn-success shadow-sm w-100">
                                    <i class="fas fa-user-plus mr-1"></i> New Admission
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
                                        <th>Contact</th>
                                        <th>Software Access</th>
                                        <th>Status</th>
                                        <th class="text-right pr-4">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($students)): ?>
                                        <tr>
                                            <td colspan="7" class="text-center py-5 text-muted">No students found. Add your first admission!</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($students as $student): ?>
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
                                                <td>
                                                    <small><i class="fas fa-phone-alt text-success mr-1"></i> <?= $student['mobile'] ?></small><br>
                                                    <small><i class="fas fa-envelope text-primary mr-1"></i> <?= htmlspecialchars($student['email'] ?: 'N/A') ?></small>
                                                </td>
                                                <td>
                                                    <div class="text-xs">
                                                        <span class="badge <?= $student['typing_access'] !== 'None' ? 'badge-success' : 'badge-light' ?> p-1">Typing: <?= $student['typing_access'] ?></span><br>
                                                        <span class="badge <?= $student['steno_access'] !== 'None' ? 'badge-info' : 'badge-light' ?> p-1">Steno: <?= $student['steno_access'] ?></span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div onclick="toggleStatus(<?= $student['id'] ?>, <?= $student['status'] ?>)">
                                                        <?php if ($student['status']): ?>
                                                            <span class="status-badge shadow-sm" style="background:#e8f5e9; color:#2e7d32;"><i class="fas fa-check-circle mr-1"></i> Active</span>
                                                        <?php else: ?>
                                                            <span class="status-badge shadow-sm" style="background:#ffebee; color:#c62828;"><i class="fas fa-times-circle mr-1"></i> Inactive</span>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                                <td class="text-right pr-4">
                                                    <button onclick="Swal.fire('Info', 'Edit functionality coming soon!', 'info')" class="btn btn-sm btn-outline-primary mr-1" title="Edit Profile">
                                                        <i class="fas fa-user-edit"></i>
                                                    </button>
                                                    <button onclick="deleteStudent(<?= $student['id'] ?>, '<?= htmlspecialchars(addslashes($student['student_name'])) ?>')" class="btn btn-sm btn-outline-danger" title="Delete Admission">
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

            </div>
        </div>
    </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function toggleStatus(id, currentStatus) {
        let newStatus = currentStatus === 1 ? 0 : 1;
        $('#loader-overlay').css('display', 'flex');
        $.ajax({
            url: 'student_action.php',
            type: 'POST',
            data: { action: 'toggle_student_status', id: id, status: newStatus },
            dataType: 'json',
            success: function(response) {
                $('#loader-overlay').hide();
                if (response.status === 'success') {
                    location.reload();
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

    function deleteStudent(id, name) {
        Swal.fire({
            title: 'Delete Admission?',
            text: "This will permanently remove: " + name,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $('#loader-overlay').css('display', 'flex');
                $.ajax({
                    url: 'student_action.php',
                    type: 'POST',
                    data: { action: 'delete_student', id: id },
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

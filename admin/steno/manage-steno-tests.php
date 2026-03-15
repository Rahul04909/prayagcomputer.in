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
// Pagination logic
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;

try {
    // Total count
    $total_stmt = $pdo->query("SELECT COUNT(id) FROM steno_tests");
    $total_results = $total_stmt->fetchColumn();
    $total_pages = ceil($total_results / $limit);

    // Fetch tests with category name
    $stmt = $pdo->prepare("SELECT t.*, c.name as category_name 
                           FROM steno_tests t 
                           LEFT JOIN steno_exam_categories c ON t.category_id = c.id 
                           ORDER BY t.created_at DESC LIMIT $start, $limit");
    $stmt->execute();
    $tests = $stmt->fetchAll();
} catch (PDOException $e) {
    $tests = [];
    $total_results = 0;
    $total_pages = 0;
}
?>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card test-table-card">
                    <div class="card-header d-flex justify-content-between align-items-center bg-white p-3">
                        <h3 class="card-title" style="font-weight:700; color:#343a40; margin:0;">Manage Steno Tests (<?= $total_results ?>)</h3>
                        <a href="add-steno-test.php" class="btn btn-sm btn-success shadow-sm">
                            <i class="fas fa-plus mr-1"></i> Add New Test
                        </a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light text-muted small">
                                    <tr>
                                        <th class="pl-4">Test Title</th>
                                        <th>Category</th>
                                        <th>Lang/Level</th>
                                        <th>Duration</th>
                                        <th>Status</th>
                                        <th class="text-right pr-4">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($tests)): ?>
                                        <tr>
                                            <td colspan="6" class="text-center py-5 text-muted">No tests found. Add your first test!</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($tests as $test): ?>
                                            <tr>
                                                <td class="pl-4">
                                                    <span style="font-weight:600; color:#2c3e50;"><?= htmlspecialchars($test['title']) ?></span><br>
                                                    <small class="text-muted"><?= date('d M Y', strtotime($test['created_at'])) ?></small>
                                                </td>
                                                <td><span class="badge badge-light border"><?= htmlspecialchars($test['category_name'] ?: 'Uncategorized') ?></span></td>
                                                <td>
                                                    <span class="text-sm font-weight-bold"><?= $test['language'] ?></span><br>
                                                    <?php 
                                                        $lvl_color = ['Easy'=>'#28a745', 'Medium'=>'#ffc107', 'Hard'=>'#dc3545'][$test['level']];
                                                    ?>
                                                    <span class="level-badge" style="color: <?= $lvl_color ?>;"><?= $test['level'] ?></span>
                                                </td>
                                                <td>
                                                    <i class="far fa-clock mr-1 text-muted"></i> <?= $test['test_duration'] ?> min<br>
                                                    <small class="text-muted">Delay: <?= $test['buffer_time'] ?>m</small>
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
                    url: 'steno_action.php',
                    type: 'POST',
                    data: { action: 'delete_steno_test', id: id },
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

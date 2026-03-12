<?php include '../header.php'; ?>

<link rel="stylesheet" href="../assets/css/loader.css">

<style>
    .enquiry-card { border: none; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); }
    .status-select { 
        padding: 4px 8px; 
        border-radius: 6px; 
        font-size: 11px; 
        font-weight: 600; 
        border: 1px solid #ddd;
        cursor: pointer;
    }
    .status-pending { background: #fff3cd; color: #856404; border-color: #ffeeba; }
    .status-contacted { background: #d1ecf1; color: #0c5460; border-color: #bee5eb; }
    .status-enrolled { background: #d4edda; color: #155724; border-color: #c3e6cb; }
    .status-closed { background: #f8d7da; color: #721c24; border-color: #f5c6cb; }
    
    .pagination .page-link { color: #2563eb; border-radius: 5px; margin: 0 2px; }
    .pagination .page-item.active .page-link { background-color: #2563eb; border-color: #2563eb; }
    #loader-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(255,255,255,0.7); display: none; align-items: center; justify-content: center; z-index: 9999; }
    
    .enquiry-msg { max-width: 250px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; cursor: pointer; color: #64748b; font-size: 0.9em; }
    .enquiry-msg:hover { color: #2563eb; text-decoration: underline; }
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
    $total_stmt = $pdo->query("SELECT COUNT(id) FROM enquiries");
    $total_results = $total_stmt->fetchColumn();
    $total_pages = ceil($total_results / $limit);

    // Fetch enquiries
    $stmt = $pdo->prepare("SELECT * FROM enquiries ORDER BY created_at DESC LIMIT $start, $limit");
    $stmt->execute();
    $enquiries = $stmt->fetchAll();
} catch (PDOException $e) {
    $enquiries = [];
    $total_results = 0;
}
?>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card enquiry-card">
                    <div class="card-header bg-white p-3">
                        <h3 class="card-title" style="font-weight:700; color:#343a40; margin:0;">Course Enquiries (<?= $total_results ?>)</h3>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light text-muted small">
                                    <tr>
                                        <th class="pl-4">Date</th>
                                        <th>Student Details</th>
                                        <th>Course</th>
                                        <th>Message</th>
                                        <th>Status</th>
                                        <th class="text-right pr-4">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($enquiries)): ?>
                                        <tr>
                                            <td colspan="6" class="text-center py-5 text-muted">No enquiries found.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($enquiries as $enquiry): 
                                            $status_class = 'status-' . strtolower($enquiry['status']);
                                        ?>
                                            <tr>
                                                <td class="pl-4">
                                                    <span class="text-sm text-muted"><?= date('d M Y', strtotime($enquiry['created_at'])) ?></span><br>
                                                    <small class="text-xs text-muted"><?= date('h:i A', strtotime($enquiry['created_at'])) ?></small>
                                                </td>
                                                <td>
                                                    <div style="font-weight:600; color:#2c3e50;"><?= htmlspecialchars($enquiry['name']) ?></div>
                                                    <div class="text-xs">
                                                        <a href="tel:<?= $enquiry['phone'] ?>"><i class="fas fa-phone-alt mr-1"></i> <?= htmlspecialchars($enquiry['phone']) ?></a><br>
                                                        <a href="mailto:<?= $enquiry['email'] ?>"><i class="far fa-envelope mr-1"></i> <?= htmlspecialchars($enquiry['email']) ?></a>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge badge-light border"><?= htmlspecialchars($enquiry['course_name'] ?: 'N/A') ?></span>
                                                </td>
                                                <td>
                                                    <div class="enquiry-msg" onclick="viewMessage('<?= htmlspecialchars(addslashes($enquiry['message'])) ?>')">
                                                        <?= htmlspecialchars($enquiry['message']) ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <select onchange="updateStatus(<?= $enquiry['id'] ?>, this.value)" class="status-select <?= $status_class ?>">
                                                        <option value="Pending" <?= $enquiry['status'] == 'Pending' ? 'selected' : '' ?>>Pending</option>
                                                        <option value="Contacted" <?= $enquiry['status'] == 'Contacted' ? 'selected' : '' ?>>Contacted</option>
                                                        <option value="Enrolled" <?= $enquiry['status'] == 'Enrolled' ? 'selected' : '' ?>>Enrolled</option>
                                                        <option value="Closed" <?= $enquiry['status'] == 'Closed' ? 'selected' : '' ?>>Closed</option>
                                                    </select>
                                                </td>
                                                <td class="text-right pr-4">
                                                    <button onclick="deleteEnquiry(<?= $enquiry['id'] ?>)" class="btn btn-sm btn-outline-danger" title="Delete">
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
function viewMessage(msg) {
    Swal.fire({
        title: 'Enquiry Message',
        text: msg,
        icon: 'info',
        confirmButtonColor: '#2563eb'
    });
}

function updateStatus(id, status) {
    $('#loader-overlay').css('display', 'flex');
    $.ajax({
        url: 'course_action.php',
        type: 'POST',
        data: { action: 'update_enquiry_status', id: id, status: status },
        dataType: 'json',
        success: function(response) {
            $('#loader-overlay').hide();
            if (response.status === 'success') {
                Swal.fire({
                    icon: 'success',
                    title: 'Updated',
                    text: response.message,
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
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

function deleteEnquiry(id) {
    Swal.fire({
        title: 'Are you sure?',
        text: "This enquiry will be permanently removed.",
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
                data: { action: 'delete_enquiry', id: id },
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

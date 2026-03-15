<?php include './header.php'; ?>

<?php
// Fetch additional student info
$stmt = $pdo->prepare("SELECT c.title as course_title, s.* FROM students s LEFT JOIN courses c ON s.course_id = c.id WHERE s.id = ?");
$stmt->execute([$student['id']]);
$student_full = $stmt->fetch();

// Calculate Fees
$stmt = $pdo->prepare("SELECT SUM(amount_paid) FROM student_fees WHERE student_id = ?");
$stmt->execute([$student['id']]);
$paid_fees = $stmt->fetchColumn() ?: 0;
$pending_fees = ($student_full['total_fees'] ?? 0) - $paid_fees;
?>

<div class="row">
    <div class="col-12 mb-4">
        <div class="card bg-gradient-success shadow-sm" style="border-radius: 15px; border: none;">
            <div class="card-body p-4">
                <div class="d-flex align-items-center">
                    <img src="<?= $student_image ?>" class="rounded-circle mr-3 border border-white" style="width: 80px; height: 80px; object-fit: cover;">
                    <div class="text-white">
                        <h2 class="mb-1" style="font-weight: 700;">Welcome, <?= htmlspecialchars($student_name) ?>!</h2>
                        <p class="mb-0 opacity-8">Enrollment No: <strong><?= htmlspecialchars($student_full['enrollment_no']) ?></strong> | <?= htmlspecialchars($student_full['course_title'] ?? 'No Course Assigned') ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info shadow-sm" style="border-radius: 12px;">
            <div class="inner">
                <h3><?= htmlspecialchars($student_full['enrollment_no']) ?></h3>
                <p>My Enrollment No</p>
            </div>
            <div class="icon">
                <i class="fas fa-id-card"></i>
            </div>
            <a href="profile.php" class="small-box-footer">View Profile <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="small-box bg-success shadow-sm" style="border-radius: 12px;">
            <div class="inner">
                <h3>₹<?= number_format($student_full['total_fees'] ?? 0, 0) ?></h3>
                <p>Total Course Fees</p>
            </div>
            <div class="icon">
                <i class="fas fa-graduation-cap"></i>
            </div>
            <span class="small-box-footer">Assigned Course</span>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning shadow-sm" style="border-radius: 12px;">
            <div class="inner">
                <h3>₹<?= number_format($paid_fees, 0) ?></h3>
                <p>Total Paid Fees</p>
            </div>
            <div class="icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <span class="small-box-footer">Transaction Verified</span>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="small-box bg-danger shadow-sm" style="border-radius: 12px;">
            <div class="inner">
                <h3>₹<?= number_format($pending_fees, 0) ?></h3>
                <p>Pending Balance</p>
            </div>
            <div class="icon">
                <i class="fas fa-clock"></i>
            </div>
            <span class="small-box-footer">Next Installment</span>
        </div>
    </div>
</div>

<?php include './footer.php'; ?>
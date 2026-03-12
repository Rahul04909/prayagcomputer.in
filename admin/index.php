<?php
// Session check (Simplified for now)
session_start();
// if (!isset($_SESSION['admin_logged_in'])) { header('Location: login.php'); exit(); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Prayag Computer Center</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@11/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts - Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/admin-style.css">
</head>
<body>

    <!-- Sidebar -->
    <?php include 'sidebar.php'; ?>

    <!-- Wrapper -->
    <div id="wrapper">
        <!-- Header -->
        <?php include 'header.php'; ?>

        <!-- Main Content -->
        <main class="p-4">
            <div class="row g-4 mb-4">
                <!-- Stat Cards -->
                <div class="col-xl-3 col-md-6">
                    <div class="stats-card">
                        <div class="stats-icon bg-primary bg-opacity-10 text-primary">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                        <div class="stats-info">
                            <h3>1,245</h3>
                            <p>Total Students</p>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="stats-card">
                        <div class="stats-icon bg-success bg-opacity-10 text-success">
                            <i class="fas fa-book"></i>
                        </div>
                        <div class="stats-info">
                            <h3>15</h3>
                            <p>Active Courses</p>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="stats-card">
                        <div class="stats-icon bg-info bg-opacity-10 text-info">
                            <i class="fas fa-wallet"></i>
                        </div>
                        <div class="stats-info">
                            <h3>₹4.2L</h3>
                            <p>Total Revenue</p>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="stats-card">
                        <div class="stats-icon bg-danger bg-opacity-10 text-danger">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="stats-info">
                            <h3>₹12,400</h3>
                            <p>Pending Dues</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Row -->
            <div class="row g-4 mb-4">
                <div class="col-lg-8">
                    <div class="content-card">
                        <h5 class="fw-bold mb-4">Revenue Trend</h5>
                        <div style="height: 300px;">
                            <canvas id="revenueChart"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="content-card">
                        <h5 class="fw-bold mb-4">Enrollment Distribution</h5>
                        <div style="height: 300px;">
                            <canvas id="enrollmentChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity Table -->
            <div class="content-card">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="fw-bold m-0">Recent Enrollments</h5>
                    <button class="btn btn-primary btn-sm px-3 rounded-pill">View All</button>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>Student Name</th>
                                <th>Course</th>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <img src="https://ui-avatars.com/api/?name=Rahul+Kumar" class="rounded-circle" width="30" height="30">
                                        <span>Rahul Kumar</span>
                                    </div>
                                </td>
                                <td>Stenography (Hindi)</td>
                                <td>Mar 12, 2024</td>
                                <td>₹5,000</td>
                                <td><span class="badge bg-success-subtle text-success border-success-subtle px-2">Paid</span></td>
                                <td><button class="btn btn-link btn-sm text-decoration-none">Edit</button></td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <img src="https://ui-avatars.com/api/?name=Priya+Singh" class="rounded-circle" width="30" height="30">
                                        <span>Priya Singh</span>
                                    </div>
                                </td>
                                <td>ADCA Specialist</td>
                                <td>Mar 11, 2024</td>
                                <td>₹12,000</td>
                                <td><span class="badge bg-warning-subtle text-warning border-warning-subtle px-2">Partially</span></td>
                                <td><button class="btn btn-link btn-sm text-decoration-none">Edit</button></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <?php include 'footer.php'; ?>
    </div>

    <!-- Scripts -->
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Custom JS -->
    <script src="assets/js/admin-script.js"></script>
</body>
</html>

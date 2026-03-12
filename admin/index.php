<?php include 'header.php'; ?>

<div class="dashboard-grid">
    <div class="stat-card">
        <div class="stat-info">
            <div class="stat-label">Total Students</div>
            <div class="stat-value">1,250</div>
        </div>
        <div class="stat-icon">
            <i class="fas fa-users"></i>
        </div>
    </div>
    
    <div class="stat-card yellow">
        <div class="stat-info">
            <div class="stat-label">Active Courses</div>
            <div class="stat-value">24</div>
        </div>
        <div class="stat-icon">
            <i class="fas fa-book-open"></i>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-info">
            <div class="stat-label">Total Earnings</div>
            <div class="stat-value">₹ 1,45,000</div>
        </div>
        <div class="stat-icon">
            <i class="fas fa-wallet"></i>
        </div>
    </div>
    
    <div class="stat-card yellow">
        <div class="stat-info">
            <div class="stat-label">Pending Exams</div>
            <div class="stat-value">08</div>
        </div>
        <div class="stat-icon">
            <i class="fas fa-calendar-alt"></i>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3>Recent Registrations</h3>
        <a href="manage-students.php" style="color: var(--primary-green); text-decoration: none; font-size: 0.85rem; font-weight: 600;">View All</a>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Student Name</th>
                    <th>Course</th>
                    <th>Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Rahul Kumar</td>
                    <td>ADCA</td>
                    <td>12 Mar 2026</td>
                    <td><span class="status-badge status-active">Active</span></td>
                </tr>
                <tr>
                    <td>Priya Singh</td>
                    <td>DCA</td>
                    <td>11 Mar 2026</td>
                    <td><span class="status-badge status-active">Active</span></td>
                </tr>
                <tr>
                    <td>Amit Verma</td>
                    <td>Python</td>
                    <td>10 Mar 2026</td>
                    <td><span class="status-badge status-pending">Pending</span></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<?php include 'footer.php'; ?>

<?php include 'header.php'; ?>

<div class="dashboard-grid">
    <div class="stat-card">
        <div class="stat-info">
            <h3>Total Students</h3>
            <div class="value">1,250</div>
        </div>
        <div class="stat-icon">
            <i class="fas fa-users"></i>
        </div>
    </div>
    
    <div class="stat-card yellow">
        <div class="stat-info">
            <h3>Active Courses</h3>
            <div class="value">24</div>
        </div>
        <div class="stat-icon" style="color: var(--secondary-yellow);">
            <i class="fas fa-book-open"></i>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-info">
            <h3>Pending Fees</h3>
            <div class="value">₹ 45,000</div>
        </div>
        <div class="stat-icon">
            <i class="fas fa-wallet"></i>
        </div>
    </div>
    
    <div class="stat-card yellow">
        <div class="stat-info">
            <h3>Upcoming Exams</h3>
            <div class="value">08</div>
        </div>
        <div class="stat-icon" style="color: var(--secondary-yellow);">
            <i class="fas fa-calendar-alt"></i>
        </div>
    </div>
</div>

<div style="padding: 0 30px 30px;">
    <div style="background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
        <h3 style="margin-bottom: 20px; color: var(--primary-green);">Recent Registrations</h3>
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="text-align: left; border-bottom: 2px solid #eee;">
                    <th style="padding: 12px 0;">Student Name</th>
                    <th style="padding: 12px 0;">Course</th>
                    <th style="padding: 12px 0;">Date</th>
                    <th style="padding: 12px 0;">Status</th>
                </tr>
            </thead>
            <tbody>
                <tr style="border-bottom: 1px solid #f9f9f9;">
                    <td style="padding: 12px 0;">Rahul Kumar</td>
                    <td style="padding: 12px 0;">ADCA</td>
                    <td style="padding: 12px 0;">12 Mar 2026</td>
                    <td style="padding: 12px 0;"><span style="background: #e8f5e9; color: #2e7d32; padding: 4px 8px; border-radius: 4px; font-size: 0.8rem;">Active</span></td>
                </tr>
                <tr style="border-bottom: 1px solid #f9f9f9;">
                    <td style="padding: 12px 0;">Priya Singh</td>
                    <td style="padding: 12px 0;">DCA</td>
                    <td style="padding: 12px 0;">11 Mar 2026</td>
                    <td style="padding: 12px 0;"><span style="background: #e8f5e9; color: #2e7d32; padding: 4px 8px; border-radius: 4px; font-size: 0.8rem;">Active</span></td>
                </tr>
                <tr style="border-bottom: 1px solid #f9f9f9;">
                    <td style="padding: 12px 0;">Amit Verma</td>
                    <td style="padding: 12px 0;">Python</td>
                    <td style="padding: 12px 0;">10 Mar 2026</td>
                    <td style="padding: 12px 0;"><span style="background: #fff8e1; color: #f57f17; padding: 4px 8px; border-radius: 4px; font-size: 0.8rem;">Pending</span></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<?php include 'footer.php'; ?>

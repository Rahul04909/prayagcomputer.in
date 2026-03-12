<?php
// Dynamic Sidebar Menu
$currentPage = basename($_SERVER['PHP_SELF']);

$menuItems = [
    'Dashboard' => ['link' => 'index.php', 'icon' => 'fas fa-th-large'],
    'Students' => ['link' => 'students.php', 'icon' => 'fas fa-user-graduate'],
    'Courses' => ['link' => 'courses.php', 'icon' => 'fas fa-book'],
    'Payments' => ['link' => 'payments.php', 'icon' => 'fas fa-wallet'],
    'Employees' => ['link' => 'employees.php', 'icon' => 'fas fa-users-cog'],
    'Reports' => [
        'icon' => 'fas fa-chart-line',
        'sub' => [
            'Daily' => 'reports_daily.php',
            'Monthly' => 'reports_monthly.php',
            'Yearly' => 'reports_yearly.php'
        ]
    ],
    'Settings' => ['link' => 'settings.php', 'icon' => 'fas fa-cog'],
];
?>
<aside id="sidebar">
    <div class="sidebar-header">
        <h4>PRAYAG CTRL</h4>
    </div>
    <ul class="sidebar-menu">
        <?php foreach ($menuItems as $label => $data): ?>
            <?php if (isset($data['sub'])): ?>
                <li class="menu-item">
                    <a href="javascript:void(0);" class="menu-link" data-bs-toggle="collapse" data-bs-target="#sub-<?php echo strtolower($label); ?>">
                        <i class="<?php echo $data['icon']; ?>"></i>
                        <span><?php echo $label; ?></span>
                        <i class="fas fa-chevron-down ms-auto small"></i>
                    </a>
                    <ul class="collapse list-unstyled ps-4 py-2" id="sub-<?php echo strtolower($label); ?>">
                        <?php foreach ($data['sub'] as $subLabel => $subLink): ?>
                            <li>
                                <a href="<?php echo $subLink; ?>" class="menu-link py-1 <?php echo ($currentPage == $subLink) ? 'active' : ''; ?>">
                                    <span style="font-size: 0.9rem;"><?php echo $subLabel; ?></span>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </li>
            <?php else: ?>
                <li class="menu-item">
                    <a href="<?php echo $data['link']; ?>" class="menu-link <?php echo ($currentPage == $data['link']) ? 'active' : ''; ?>">
                        <i class="<?php echo $data['icon']; ?>"></i>
                        <span><?php echo $label; ?></span>
                    </a>
                </li>
            <?php endif; ?>
        <?php endforeach; ?>
    </ul>
</aside>

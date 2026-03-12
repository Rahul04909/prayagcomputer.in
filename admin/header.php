<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Prayag Computer Centre</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/admin-style.css?v=<?php echo time(); ?>">
</head>
<body>
    <div class="admin-container">
        <?php include 'sidebar.php'; ?>
        
        <main class="main-content">
            <header class="admin-header">
                <div class="header-left">
                    <button id="sidebarToggle" class="sidebar-toggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h2 class="page-title">Dashboard</h2>
                </div>
                
                <div class="header-right">
                    <div class="header-search">
                        <!-- Potential search box -->
                    </div>
                    <div class="notifications">
                        <i class="far fa-bell"></i>
                    </div>
                    <div class="user-profile">
                        <div class="user-avatar">A</div>
                        <span class="user-name">Admin</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                </div>
            </header>

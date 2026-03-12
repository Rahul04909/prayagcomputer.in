<?php
// Top Header Bar
?>
<header class="top-header">
    <div class="header-left">
        <button id="toggle-btn" class="toggle-btn">
            <i class="fas fa-bars"></i>
        </button>
    </div>
    <div class="header-right d-flex align-items-center gap-3">
        <div class="dropdown">
            <a href="#" class="text-decoration-none text-dark d-flex align-items-center gap-2" id="profileDropdown" data-bs-toggle="dropdown">
                <img src="https://ui-avatars.com/api/?name=Admin&background=1E90FF&color=fff" class="rounded-circle" width="35" height="35" alt="Admin">
                <div class="d-none d-sm-block">
                    <span class="d-block fw-bold small">Administrator</span>
                    <span class="text-muted extra-small" style="font-size: 0.7rem;">Super Admin</span>
                </div>
            </a>
            <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-3">
                <li><a class="dropdown-item" href="#"><i class="fas fa-user-cog me-2"></i> Profile Settings</a></li>
                <li><a class="dropdown-item" href="#"><i class="fas fa-lock me-2"></i> Change Password</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-danger" href="#"><i class="fas fa-sign-out-alt me-2"></i> Logout</a></li>
            </ul>
        </div>
    </div>
</header>

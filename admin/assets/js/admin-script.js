document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const menuLinks = document.querySelectorAll('.has-submenu > .menu-link');

    // Sidebar Toggle
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            if (window.innerWidth > 768) {
                sidebar.classList.toggle('collapsed');
            } else {
                sidebar.classList.toggle('active');
            }
        });
    }

    // Submenu Toggle
    menuLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const parent = this.parentElement;
            
            // Close other open submenus
            document.querySelectorAll('.has-submenu.open').forEach(openItem => {
                if (openItem !== parent) {
                    openItem.classList.remove('open');
                }
            });

            parent.classList.toggle('open');
        });
    });

    // Close sidebar on mobile when clicking outside
    document.addEventListener('click', function(e) {
        if (window.innerWidth <= 768) {
            if (!sidebar.contains(e.target) && !sidebarToggle.contains(e.target) && sidebar.classList.contains('active')) {
                sidebar.classList.remove('active');
            }
        }
    });

    // Mark active menu item based on current URL
    const currentPath = window.location.pathname.split('/').pop();
    const allLinks = document.querySelectorAll('.sidebar-menu a');
    
    allLinks.forEach(link => {
        const href = link.getAttribute('href');
        if (href === currentPath) {
            link.closest('.menu-item').classList.add('active');
            // If it's a submenu link, open the parent
            const submenuParent = link.closest('.has-submenu');
            if (submenuParent) {
                submenuParent.classList.add('open');
            }
        }
    });
});

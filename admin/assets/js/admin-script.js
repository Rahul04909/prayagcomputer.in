document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    const wrapper = document.getElementById('wrapper');
    const toggleBtn = document.getElementById('toggle-btn');
    const body = document.body;

    // Sidebar Toggle Logic
    if (toggleBtn) {
        toggleBtn.addEventListener('click', function() {
            if (window.innerWidth > 768) {
                sidebar.classList.toggle('collapsed');
                wrapper.classList.toggle('expanded');
            } else {
                sidebar.classList.toggle('show');
            }
        });
    }

    // Auto-close sidebar on mobile when window is resized
    window.addEventListener('resize', function() {
        if (window.innerWidth > 768) {
            sidebar.classList.remove('show');
        } else {
            sidebar.classList.remove('collapsed');
            wrapper.classList.remove('expanded');
        }
    });

    // Initialize Dashboard Charts (if elements exist)
    const ctxRevenue = document.getElementById('revenueChart');
    if (ctxRevenue) {
        new Chart(ctxRevenue, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    label: 'Monthly Revenue (₹)',
                    data: [45000, 52000, 48000, 61000, 55000, 72000],
                    borderColor: '#1E90FF',
                    backgroundColor: 'rgba(30, 144, 255, 0.1)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    }

    const ctxEnrollments = document.getElementById('enrollmentChart');
    if (ctxEnrollments) {
        new Chart(ctxEnrollments, {
            type: 'bar',
            data: {
                labels: ['Steno', 'DCA', 'ADCA', 'Tally', 'Typing'],
                datasets: [{
                    label: 'Enrollments',
                    data: [150, 120, 80, 200, 180],
                    backgroundColor: [
                        '#1E90FF',
                        '#17a2b8',
                        '#6c757d',
                        '#28a745',
                        '#ffc107'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                }
            }
        });
    }
});

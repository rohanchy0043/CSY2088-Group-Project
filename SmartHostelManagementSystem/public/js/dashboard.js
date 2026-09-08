/**
 * Dashboard specific JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {
    // ===== Refresh stats (for dashboard) =====
    const refreshBtn = document.querySelector('.refresh-stats');
    if (refreshBtn) {
        refreshBtn.addEventListener('click', function(e) {
            e.preventDefault();
            location.reload();
        });
    }

    // ===== Charts placeholder =====
    // You can integrate Chart.js or similar library here

    // ===== Activity log hover =====
    document.querySelectorAll('.activity-item').forEach(item => {
        item.addEventListener('click', function() {
            this.classList.toggle('expanded');
        });
    });

    // ===== Quick actions =====
    document.querySelectorAll('.quick-action').forEach(btn => {
        btn.addEventListener('click', function(e) {
            const action = this.dataset.action;
            if (action) {
                // Handle quick actions
                console.log('Quick action:', action);
            }
        });
    });
});
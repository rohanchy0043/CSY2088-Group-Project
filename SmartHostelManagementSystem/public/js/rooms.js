/**
 * Rooms module JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {
    // ===== Room form validation =====
    const roomForm = document.getElementById('roomForm');
    if (roomForm) {
        roomForm.addEventListener('submit', function(e) {
            const rules = {
                room_number: 'required',
                block: 'required',
                floor: 'required|numeric',
                capacity: 'required|numeric|min:1'
            };
            const result = validateForm(this, rules);
            if (!result.isValid) {
                e.preventDefault();
                showToast('Please fix the errors in the form', 'error');
            }
        });
    }

    // ===== Room status toggle =====
    document.querySelectorAll('.room-status-toggle').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const currentStatus = this.dataset.status;
            const newStatus = currentStatus === 'available' ? 'maintenance' :
                             currentStatus === 'maintenance' ? 'available' :
                             currentStatus === 'occupied' ? 'maintenance' : 'available';

            if (!confirm(`Change room status to "${newStatus}"?`)) return;

            const formData = new FormData();
            formData.append('status', newStatus);
            formData.append('_method', 'PUT');

            fetch(`/api/rooms/${id}`, {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Room status updated!', 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showToast(data.message || 'Error', 'error');
                }
            })
            .catch(() => showToast('Network error', 'error'));
        });
    });

    // ===== Room occupancy indicator =====
    document.querySelectorAll('.room-occupancy').forEach(el => {
        const occupied = parseInt(el.dataset.occupied);
        const capacity = parseInt(el.dataset.capacity);
        const percent = (occupied / capacity) * 100;

        const bar = el.querySelector('.occupancy-bar');
        if (bar) {
            bar.style.width = Math.min(percent, 100) + '%';
            bar.style.background = percent > 80 ? '#ef4444' :
                                  percent > 50 ? '#f59e0b' : '#22c55e';
        }
    });
});
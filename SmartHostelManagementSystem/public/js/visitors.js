/**
 * Visitors module JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {
    // ===== Visitor form validation =====
    const visitorForm = document.getElementById('visitorForm');
    if (visitorForm) {
        visitorForm.addEventListener('submit', function(e) {
            const rules = {
                visitor_name: 'required|min:2',
                contact: 'required|min:7',
                purpose: 'required'
            };
            const result = validateForm(this, rules);
            if (!result.isValid) {
                e.preventDefault();
                showToast('Please fix the errors in the form', 'error');
            }
        });
    }

    // ===== Approve/Reject visitor =====
    document.querySelectorAll('.visitor-action').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const action = this.dataset.action;

            if (!confirm(`Are you sure you want to ${action} this visitor?`)) return;

            const formData = new FormData();
            formData.append('action', action);

            fetch(`/warden/visitors/${id}/approve`, {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast(`Visitor ${action}d!`, 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showToast(data.message || 'Error', 'error');
                }
            })
            .catch(() => showToast('Network error', 'error'));
        });
    });

    // ===== Check-in/Check-out visitor =====
    document.querySelectorAll('.visitor-checkin, .visitor-checkout').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const action = this.dataset.action;
            const label = action === 'checkin' ? 'check in' : 'check out';

            if (!confirm(`Are you sure you want to ${label} this visitor?`)) return;

            fetch(`/warden/visitors/${id}/${action}`, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast(`Visitor ${label}ed!`, 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showToast(data.message || 'Error', 'error');
                }
            })
            .catch(() => showToast('Network error', 'error'));
        });
    });
});
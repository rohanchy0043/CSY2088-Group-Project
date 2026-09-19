/**
 * Complaints module JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {
    // ===== Complaint form validation =====
    const complaintForm = document.getElementById('complaintForm');
    if (complaintForm) {
        complaintForm.addEventListener('submit', function(e) {
            const rules = {
                category: 'required',
                subject: 'required|min:5',
                description: 'required|min:10'
            };
            const result = validateForm(this, rules);
            if (!result.isValid) {
                e.preventDefault();
                showToast('Please fix the errors in the form', 'error');
            }
        });
    }

    // ===== Complaint status update (AJAX) =====
    document.querySelectorAll('.update-complaint-status').forEach(btn => {
        btn.addEventListener('click', function(e) {
            const id = this.dataset.id;
            const status = document.querySelector(`.status-select[data-id="${id}"]`)?.value;

            if (!status || !id) return;

            if (!confirm('Update complaint status to "' + status + '"?')) return;

            const formData = new FormData();
            formData.append('status', status);
            formData.append('_method', 'PUT');

            fetch(`/warden/complaints/${id}/status`, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Complaint status updated!', 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showToast(data.message || 'Error updating status', 'error');
                }
            })
            .catch(() => showToast('Network error', 'error'));
        });
    });

    // ===== Complaint priority change =====
    document.querySelectorAll('.priority-select').forEach(select => {
        select.addEventListener('change', function() {
            const id = this.dataset.id;
            const priority = this.value;
            const formData = new FormData();
            formData.append('priority', priority);
            formData.append('_method', 'PUT');

            fetch(`/api/complaints/${id}`, {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Priority updated!', 'success');
                }
            })
            .catch(() => {});
        });
    });
});
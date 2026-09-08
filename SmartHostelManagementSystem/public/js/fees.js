/**
 * Fees module JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {
    // ===== Fee form validation =====
    const feeForm = document.getElementById('feeForm');
    if (feeForm) {
        feeForm.addEventListener('submit', function(e) {
            const rules = {
                student_id: 'required',
                amount: 'required|numeric',
                due_date: 'required'
            };
            const result = validateForm(this, rules);
            if (!result.isValid) {
                e.preventDefault();
                showToast('Please fix the errors in the form', 'error');
            }
        });
    }

    // ===== Mark fee as paid =====
    document.querySelectorAll('.mark-paid').forEach(btn => {
        btn.addEventListener('click', function(e) {
            const id = this.dataset.id;
            if (!confirm('Mark this fee as paid?')) return;

            const formData = new FormData();
            formData.append('_method', 'PUT');
            formData.append('status', 'paid');

            fetch(`/api/fees/${id}`, {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Fee marked as paid!', 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showToast(data.message || 'Error', 'error');
                }
            })
            .catch(() => showToast('Network error', 'error'));
        });
    });

    // ===== Fee amount formatting =====
    document.querySelectorAll('.fee-amount').forEach(el => {
        const amount = parseFloat(el.textContent);
        if (!isNaN(amount)) {
            el.textContent = 'Rs. ' + amount.toLocaleString('en-IN', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }
    });
});
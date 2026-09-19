document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('loginForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            const email = document.getElementById('email');
            const password = document.getElementById('password');

            let errors = [];

            if (!email.value.trim()) {
                errors.push('Email is required');
                email.classList.add('has-error');
            } else if (!email.value.includes('@') || !email.value.includes('.')) {
                errors.push('Please enter a valid email address');
                email.classList.add('has-error');
            } else {
                email.classList.remove('has-error');
            }

            if (!password.value) {
                errors.push('Password is required');
                password.classList.add('has-error');
            } else {
                password.classList.remove('has-error');
            }

            if (errors.length > 0) {
                e.preventDefault();
                // Show errors
                const errorDiv = document.getElementById('loginErrors') || (() => {
                    const div = document.createElement('div');
                    div.id = 'loginErrors';
                    div.className = 'alert alert-error';
                    form.insertBefore(div, form.firstChild);
                    return div;
                })();
                errorDiv.innerHTML = errors.join('<br>');
                errorDiv.style.display = 'block';
            }
        });

        // Clear errors on input
        document.querySelectorAll('#loginForm input').forEach(input => {
            input.addEventListener('input', function() {
                this.classList.remove('has-error');
                const errorDiv = document.getElementById('loginErrors');
                if (errorDiv) errorDiv.style.display = 'none';
            });
        });
    }
});
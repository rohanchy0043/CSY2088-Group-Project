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

    /* ===== Boxy characters: eyes track cursor + parallax drift ===== */
    const art = document.getElementById('art');
    if (art) {
        const pupils = art.querySelectorAll('.pupil');
        const boxes  = art.querySelectorAll('.boxy');
        let mx = 0, my = 0;

        document.addEventListener('mousemove', function(e) {
            mx = (e.clientX / window.innerWidth)  * 2 - 1;
            my = (e.clientY / window.innerHeight) * 2 - 1;

            // pupils look at the cursor
            pupils.forEach(function(p) {
                const r  = p.parentElement.getBoundingClientRect();
                const cx = r.left + r.width / 2;
                const cy = r.top + r.height / 2;
                const ang  = Math.atan2(e.clientY - cy, e.clientX - cx);
                const dist = Math.min(3.5, Math.hypot(e.clientX - cx, e.clientY - cy) / 120);
                p.style.transform = 'translate(' + (Math.cos(ang) * dist) + 'px, ' + (Math.sin(ang) * dist) + 'px)';
            });
        });

        // whole boxes drift with the cursor, each at its own depth
        (function drift() {
            boxes.forEach(function(b) {
                const depth = parseFloat(b.dataset.depth || 10);
                b.style.transform = 'translate(' + (mx * depth) + 'px, ' + (my * depth * 0.6) + 'px)';
            });
            requestAnimationFrame(drift);
        })();
    }
});
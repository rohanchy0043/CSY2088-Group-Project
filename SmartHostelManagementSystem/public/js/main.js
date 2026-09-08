/**
 * Main JavaScript - Smart Hostel Management System
 */

document.addEventListener('DOMContentLoaded', function() {
    // ===== Mobile Menu Toggle =====
    const menuToggle = document.getElementById('menuToggle');
    const sidebar = document.querySelector('.sidebar');

    if (menuToggle && sidebar) {
        menuToggle.addEventListener('click', function() {
            sidebar.classList.toggle('open');
        });
    }

    // Close sidebar on outside click (mobile)
    document.addEventListener('click', function(e) {
        if (window.innerWidth <= 768) {
            const sidebar = document.querySelector('.sidebar');
            const toggle = document.querySelector('.menu-toggle');
            if (sidebar && !sidebar.contains(e.target) && !toggle?.contains(e.target)) {
                sidebar.classList.remove('open');
            }
        }
    });

    // ===== Auto-dismiss alerts =====
    document.querySelectorAll('.alert').forEach(el => {
        setTimeout(() => {
            el.style.transition = 'opacity 0.5s';
            el.style.opacity = '0';
            setTimeout(() => el.remove(), 500);
        }, 5000);
    });

    // ===== Confirm delete actions =====
    document.querySelectorAll('[data-confirm]').forEach(el => {
        el.addEventListener('click', function(e) {
            if (!confirm(this.dataset.confirm || 'Are you sure you want to delete this?')) {
                e.preventDefault();
            }
        });
    });

    // ===== Tooltip =====
    document.querySelectorAll('[data-tooltip]').forEach(el => {
        el.addEventListener('mouseenter', function() {
            const tooltip = document.createElement('div');
            tooltip.className = 'tooltip';
            tooltip.textContent = this.dataset.tooltip;
            tooltip.style.cssText = `
                position: absolute;
                background: #1e293b;
                color: #fff;
                padding: 4px 10px;
                border-radius: 4px;
                font-size: 12px;
                z-index: 1000;
                pointer-events: none;
                white-space: nowrap;
            `;
            const rect = this.getBoundingClientRect();
            tooltip.style.top = (rect.top - 30) + 'px';
            tooltip.style.left = (rect.left + rect.width / 2 - 30) + 'px';
            document.body.appendChild(tooltip);
            this._tooltip = tooltip;
        });

        el.addEventListener('mouseleave', function() {
            if (this._tooltip) {
                this._tooltip.remove();
                delete this._tooltip;
            }
        });
    });

    const testimonialRoot = document.querySelector('[data-testimonials]');
    if (testimonialRoot) {
        const testimonials = [
            { name: 'Anisha Rai', role: 'Student', image: '/assets/review1.jpg', quote: '“DormSync makes it much easier to keep track of my room, fees and complaints.”' },
            { name: 'Rohan Gurung', role: 'Warden', image: '/assets/review2.jpg', quote: '“Managing students and complaints is much more organised with DormSync.”' },
            { name: 'Sita Thapa', role: 'Student', image: '/assets/review3.jpg', quote: '“I always know what is happening with my room and visitor requests.”' },
            { name: 'Hari Karki', role: 'Warden', image: '/assets/review4.jpg', quote: '“The hostel overview helps me act on issues before they become bigger problems.”' },
            { name: 'Priya Sharma', role: 'Administrator', image: '/assets/review5.jpg', quote: '“The dashboard gives us a clear overview of hostel operations.”' }
        ];
        const card = testimonialRoot.querySelector('.testimonial-card');
        const quote = testimonialRoot.querySelector('#testimonialQuote');
        const avatar = testimonialRoot.querySelector('#testimonialAvatar');
        const name = testimonialRoot.querySelector('#testimonialName');
        const role = testimonialRoot.querySelector('#testimonialRole');
        const dots = [...testimonialRoot.querySelectorAll('.testimonial-dots button')];

        const showTestimonial = index => {
            card.classList.add('is-changing');
            window.setTimeout(() => {
                const item = testimonials[index];
                quote.textContent = item.quote;
                avatar.src = item.image;
                avatar.alt = `Portrait of ${item.name}`;
                name.textContent = item.name;
                role.textContent = item.role;
                dots.forEach((dot, dotIndex) => dot.setAttribute('aria-current', String(dotIndex === index)));
                card.classList.remove('is-changing');
            }, 180);
        };

        dots.forEach((dot, index) => dot.addEventListener('click', () => showTestimonial(index)));
    }

    const journey = document.querySelector('[data-journey]');
    const journeyPath = journey?.querySelector('.journey-line-track');
    if (journey && journeyPath) {
        const updateJourney = () => {
            const scrollable = document.documentElement.scrollHeight - window.innerHeight;
            const progress = scrollable > 0 ? window.scrollY / scrollable : 0;
            journeyPath.style.strokeDashoffset = String(1 - Math.max(0, Math.min(1, progress)));
        };
        updateJourney();
        window.addEventListener('scroll', updateJourney, { passive: true });
        window.addEventListener('resize', updateJourney);
    }
});

/**
 * Toast notification helper
 */
function showToast(message, type = 'success') {
    const colors = {
        success: '#16a34a',
        error: '#dc2626',
        warning: '#f59e0b',
        info: '#2563eb'
    };

    const toast = document.createElement('div');
    toast.className = 'toast';
    toast.style.cssText = `
        position: fixed;
        bottom: 20px;
        right: 20px;
        background: ${colors[type] || '#333'};
        color: #fff;
        padding: 12px 24px;
        border-radius: 8px;
        font-weight: 500;
        z-index: 9999;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        animation: slideIn 0.3s ease;
        max-width: 400px;
    `;
    toast.textContent = message;
    document.body.appendChild(toast);

    // Add animation
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        @keyframes slideOut {
            from { transform: translateX(0); opacity: 1; }
            to { transform: translateX(100%); opacity: 0; }
        }
    `;
    document.head.appendChild(style);

    setTimeout(() => {
        toast.style.animation = 'slideOut 0.3s ease forwards';
        setTimeout(() => {
            toast.remove();
            style.remove();
        }, 300);
    }, 3000);
}

/**
 * AJAX helper function
 */
function ajax(url, options = {}) {
    return fetch(url, {
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        ...options
    }).then(response => response.json());
}
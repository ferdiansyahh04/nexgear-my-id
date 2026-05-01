/**
 * Hypernex Store - Elite Interaction Engine
 */

document.addEventListener('DOMContentLoaded', () => {
    // 1. Smooth Scrolling for Navigation
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            const href = this.getAttribute('href');
            if (href === '#') return;
            
            const target = document.querySelector(href);
            if (target) {
                e.preventDefault();
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // 2. AJAX Add to Cart
    const cartForms = document.querySelectorAll('form[action*="/cart/add"]');
    cartForms.forEach(form => {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const btn = this.querySelector('button');
            const originalContent = btn.innerHTML;
            const url = this.getAttribute('action');
            
            // Loading State
            btn.innerHTML = '<div class="spinner-border spinner-border-sm" role="status"></div>';
            btn.classList.add('disabled');
            
            try {
                const formData = new FormData(this);
                const csrfMeta = document.getElementById('csrf-token');
                const csrfToken = csrfMeta ? csrfMeta.content : '';
                
                const response = await fetch(url, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken
                    }
                });
                
                const data = await response.json();
                
                if (data.status === 'success') {
                    showNotification(data.message, 'success');
                    // Update Cart Pill
                    const pill = document.querySelector('.cart-pill');
                    if (pill) {
                        pill.textContent = data.cartCount;
                        pill.classList.add('pulse');
                        setTimeout(() => pill.classList.remove('pulse'), 500);
                    } else {
                        // If no pill exists, reload to show it or create it
                        window.location.reload(); 
                    }
                } else {
                    showNotification(data.message || 'Error adding to cart', 'error');
                }
            } catch (err) {
                console.error(err);
                showNotification('Network error occurred', 'error');
            } finally {
                btn.innerHTML = originalContent;
                btn.classList.remove('disabled');
            }
        });
    });

    // 3. Navbar scroll effect
    const mainNav = document.getElementById('mainNavbar');
    if (mainNav) {
        window.addEventListener('scroll', () => {
            if (window.scrollY > 50) {
                mainNav.classList.add('scrolled');
            } else {
                mainNav.classList.remove('scrolled');
            }
        });
    }

    // 4. Notification System
    function showNotification(message, type = 'success') {
        let container = document.querySelector('.notification-container');
        if (!container) {
            container = document.createElement('div');
            container.className = 'notification-container';
            document.body.appendChild(container);
        }
        
        const toast = document.createElement('div');
        toast.className = `vp-toast ${type}`;
        toast.innerHTML = `
            <i class="bi ${type === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-circle-fill'}"></i>
            <span>${message}</span>
        `;
        
        container.appendChild(toast);
        
        setTimeout(() => {
            toast.classList.add('show');
        }, 10);
        
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }

    // 5. Dead Link Fallback
    document.querySelectorAll('a').forEach(el => {
        el.addEventListener('click', (e) => {
            const href = el.getAttribute('href');
            if (href === '#' || href === '') {
                e.preventDefault();
                showNotification('Feature coming soon', 'info');
            }
        });
    });
});

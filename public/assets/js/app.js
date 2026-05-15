/**
 * NexGear Store - Elite Interaction Engine
 */

document.addEventListener('DOMContentLoaded', () => {
    // 1. AJAX Add to Bag
    const cartForms = document.querySelectorAll('.ajax-add-to-cart');
    cartForms.forEach(form => {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const btn = this.querySelector('button');
            const btnText = btn.querySelector('.btn-text');
            const originalText = btnText.innerHTML;
            const url = this.getAttribute('action');
            
            // Loading State
            btnText.innerHTML = 'ADDING...';
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
                    // Start Flying Animation
                    const card = this.closest('.product-card');
                    const productImg = card ? card.querySelector('.img-primary') : null;
                    console.log('Flying animation trigger:', { card, productImg });
                    if (productImg) {
                        flyToCart(productImg);
                    } else {
                        console.warn('Could not find .img-primary in .product-card');
                    }

                    // Update Bag Count and UI with a slight delay for the animation
                    setTimeout(() => {
                        const bagCounts = document.querySelectorAll('[data-bs-target="#offcanvasCart"] .ms-1');
                        bagCounts.forEach(el => el.textContent = `(${data.cartCount})`);
                        
                        // Pulse the bag
                        const bagLink = document.querySelector('[data-bs-target="#offcanvasCart"]');
                        if (bagLink) {
                            bagLink.classList.add('bag-pulse');
                            setTimeout(() => bagLink.classList.remove('bag-pulse'), 600);
                        }

                        // Update Offcanvas Content
                        const offcanvasEl = document.getElementById('offcanvasCart');
                        if (offcanvasEl && data.html) {
                            offcanvasEl.innerHTML = new DOMParser().parseFromString(data.html, 'text/html').getElementById('offcanvasCart').innerHTML;
                            
                            // Show the offcanvas after the flight
                            const bsOffcanvas = bootstrap.Offcanvas.getOrCreateInstance(offcanvasEl);
                            bsOffcanvas.show();
                        }
                    }, 800);
                } else {
                    showNotification(data.message || 'Error adding to bag', 'error');
                }
            } catch (err) {
                console.error(err);
                showNotification('Network error occurred', 'error');
            } finally {
                btnText.innerHTML = originalText;
                btn.classList.remove('disabled');
            }
        });
    });

    // 2. AJAX Remove from Bag
    document.addEventListener('click', async function(e) {
        if (e.target.closest('.remove-item')) {
            const btn = e.target.closest('.remove-item');
            const productId = btn.getAttribute('data-id');
            
            // Show loading state
            const originalHTML = btn.innerHTML;
            btn.innerHTML = '<i class="bi bi-hourglass-split"></i>';
            btn.classList.add('disabled');
            
            try {
                const csrfMeta = document.getElementById('csrf-token');
                const csrfToken = csrfMeta ? csrfMeta.content : '';

                const response = await fetch(`${window.location.origin}/cart/remove/${productId}`, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken
                    }
                });
                
                const data = await response.json();
                
                if (data.status === 'success') {
                    // Update Bag Count in Nav
                    const bagCounts = document.querySelectorAll('[data-bs-target="#offcanvasCart"] .ms-1');
                    bagCounts.forEach(el => el.textContent = `(${data.cartCount})`);

                    // Update Offcanvas Content
                    const offcanvasEl = document.getElementById('offcanvasCart');
                    if (offcanvasEl && data.html) {
                        offcanvasEl.innerHTML = new DOMParser().parseFromString(data.html, 'text/html').getElementById('offcanvasCart').innerHTML;
                        showNotification('Item removed from selection');
                    }
                }
            } catch (err) {
                console.error(err);
                btn.innerHTML = originalHTML;
                btn.classList.remove('disabled');
                showNotification('Could not remove item', 'error');
            }
        }
    });

    // 3. Notification System
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

    // 4. Custom Cursor for Product Cards
    const cursor = document.getElementById('customCursor');
    const cards = document.querySelectorAll('.product-card');

    if (cursor) {
        document.addEventListener('mousemove', (e) => {
            cursor.style.left = e.clientX + 'px';
            cursor.style.top = e.clientY + 'px';
            cursor.style.marginLeft = '-30px'; // Half width
            cursor.style.marginTop = '-30px';  // Half height
        });

        cards.forEach(card => {
            card.addEventListener('mouseenter', () => {
                cursor.classList.add('active');
                card.style.cursor = 'none';
            });
            card.addEventListener('mouseleave', () => {
                cursor.classList.remove('active');
                card.style.cursor = 'default';
            });
        });
    }

    /**
     * Flying Image Animation Logic
     */
    function flyToCart(imgElement) {
        if (!imgElement) return;

        const bag = document.querySelector('[data-bs-target="#offcanvasCart"]');
        if (!bag) {
            console.error('FlyToCart: Could not find bag target [data-bs-target="#offcanvasCart"]');
            return;
        }

        const imgRect = imgElement.getBoundingClientRect();
        const bagRect = bag.getBoundingClientRect();

        const clone = imgElement.cloneNode();
        clone.className = 'flying-img';
        
        // Initial position
        clone.style.width = imgRect.width + 'px';
        clone.style.height = imgRect.height + 'px';
        clone.style.top = imgRect.top + 'px';
        clone.style.left = imgRect.left + 'px';
        clone.style.opacity = '1';
        clone.style.transform = 'scale(1) rotate(0deg)';

        document.body.appendChild(clone);

        // Target coordinates (Center of bag)
        const targetX = bagRect.left + (bagRect.width / 2) - 20; // 20 is half of target width (40)
        const targetY = bagRect.top + (bagRect.height / 2) - 20; // 20 is half of target height (40)

        // Ensure the browser has painted the initial state before starting the transition
        requestAnimationFrame(() => {
            requestAnimationFrame(() => {
                // Target position (Fly to bag)
                clone.style.top = targetY + 'px';
                clone.style.left = targetX + 'px';
                clone.style.width = '40px';
                clone.style.height = '40px';
                clone.style.opacity = '0';
                clone.style.transform = 'scale(0.1) rotate(720deg)';
            });
        });

        setTimeout(() => {
            clone.remove();
        }, 1000); // Slightly longer to ensure transition completes
    }
});


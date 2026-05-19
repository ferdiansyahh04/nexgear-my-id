/**
 * NexGear Store - Elite Interaction Engine
 *
 * Modules:
 *   - CSRF helpers
 *   - AJAX add-to-cart with flying-image animation
 *   - AJAX remove-from-cart
 *   - Toast notifications
 *   - Custom cursor on product cards
 *   - A1 Quick View modal
 *   - A2 Live Search overlay (debounced)
 *   - A4 Cart quantity stepper (+/-)
 */

(function () {
    'use strict';

    // ─────────────────────────────────────────────────────────────
    // CSRF helpers — exposed on window.NexGear for reuse across modules
    // ─────────────────────────────────────────────────────────────
    function getCsrf() {
        const meta = document.getElementById('csrf-token');
        return {
            name: meta ? meta.getAttribute('name') : '',
            hash: meta ? meta.content : ''
        };
    }

    function refreshCsrf(data) {
        if (!data) return;
        const meta = document.getElementById('csrf-token');
        if (meta && data.csrfToken) {
            meta.content = data.csrfToken;
        }
        if (data.csrfName && data.csrfToken) {
            document.querySelectorAll(`input[name="${data.csrfName}"]`).forEach((input) => {
                input.value = data.csrfToken;
            });
        }
    }

    /**
     * Wrapper around fetch that always sends CSRF + AJAX headers and refreshes
     * the token from the JSON response when present.
     */
    async function ajaxPost(url, body) {
        const csrf = getCsrf();
        const opts = {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrf.hash
            }
        };
        if (body instanceof FormData) {
            opts.body = body;
        } else if (body && typeof body === 'object') {
            const fd = new FormData();
            Object.entries(body).forEach(([k, v]) => fd.append(k, v));
            opts.body = fd;
        }

        const response = await fetch(url, opts);
        const data = await response.json().catch(() => ({}));
        refreshCsrf(data);
        return { response, data };
    }

    async function ajaxGet(url) {
        const response = await fetch(url, {
            method: 'GET',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data = await response.json().catch(() => ({}));
        return { response, data };
    }

    function debounce(fn, wait) {
        let timer;
        return function debounced(...args) {
            clearTimeout(timer);
            timer = setTimeout(() => fn.apply(this, args), wait);
        };
    }

    // ─────────────────────────────────────────────────────────────
    // Toast notifications (A6 — supports optional action button)
    // ─────────────────────────────────────────────────────────────
    function showNotification(message, type = 'success', options = {}) {
        let container = document.querySelector('.notification-container');
        if (!container) {
            container = document.createElement('div');
            container.className = 'notification-container';
            document.body.appendChild(container);
        }

        const toast = document.createElement('div');
        toast.className = `vp-toast ${type}`;

        const icon = document.createElement('i');
        icon.className = `bi ${type === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-circle-fill'}`;

        const text = document.createElement('span');
        text.className = 'vp-toast-text';
        text.textContent = message;

        toast.appendChild(icon);
        toast.appendChild(text);

        const duration = options.duration ?? 3000;
        let actionBtn = null;
        let dismissed = false;

        const dismiss = () => {
            if (dismissed) return;
            dismissed = true;
            toast.classList.add('is-leaving');
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        };

        if (options.action && typeof options.action.onClick === 'function') {
            actionBtn = document.createElement('button');
            actionBtn.type = 'button';
            actionBtn.className = 'vp-toast-action';
            actionBtn.textContent = options.action.label || 'Undo';
            actionBtn.addEventListener('click', () => {
                try {
                    options.action.onClick();
                } finally {
                    dismiss();
                }
            });
            toast.appendChild(actionBtn);

            // Countdown progress bar
            const progress = document.createElement('div');
            progress.className = 'vp-toast-progress';
            toast.appendChild(progress);
            requestAnimationFrame(() => {
                progress.style.transition = `transform ${duration}ms linear`;
                progress.style.transform = 'scaleX(1)';
            });
        }

        container.appendChild(toast);

        setTimeout(() => toast.classList.add('show'), 10);
        setTimeout(dismiss, duration);

        return { dismiss, element: toast };
    }

    // ─────────────────────────────────────────────────────────────
    // Flying image animation (used by add-to-cart)
    // ─────────────────────────────────────────────────────────────
    function flyToCart(imgElement) {
        if (!imgElement) return;

        const bag = document.querySelector('[data-bs-target="#offcanvasCart"]');
        if (!bag) return;

        const imgRect = imgElement.getBoundingClientRect();
        const bagRect = bag.getBoundingClientRect();

        const clone = imgElement.cloneNode();
        clone.className = 'flying-img';

        clone.style.width = imgRect.width + 'px';
        clone.style.height = imgRect.height + 'px';
        clone.style.top = imgRect.top + 'px';
        clone.style.left = imgRect.left + 'px';
        clone.style.opacity = '1';
        clone.style.transform = 'scale(1) rotate(0deg)';

        document.body.appendChild(clone);

        const targetX = bagRect.left + bagRect.width / 2 - 20;
        const targetY = bagRect.top + bagRect.height / 2 - 20;

        requestAnimationFrame(() => {
            requestAnimationFrame(() => {
                clone.style.top = targetY + 'px';
                clone.style.left = targetX + 'px';
                clone.style.width = '40px';
                clone.style.height = '40px';
                clone.style.opacity = '0';
                clone.style.transform = 'scale(0.1) rotate(720deg)';
            });
        });

        setTimeout(() => clone.remove(), 1000);
    }

    function updateBagCounts(count) {
        document
            .querySelectorAll('[data-bs-target="#offcanvasCart"] .ms-1')
            .forEach((el) => (el.textContent = `(${count})`));
    }

    function pulseBag() {
        const bagLink = document.querySelector('[data-bs-target="#offcanvasCart"]');
        if (!bagLink) return;
        bagLink.classList.add('bag-pulse');
        setTimeout(() => bagLink.classList.remove('bag-pulse'), 600);
    }

    function swapOffcanvasCart(html) {
        const offcanvasEl = document.getElementById('offcanvasCart');
        if (!offcanvasEl || !html) return;
        const incoming = new DOMParser().parseFromString(html, 'text/html').getElementById('offcanvasCart');
        if (incoming) {
            offcanvasEl.innerHTML = incoming.innerHTML;
        }
    }

    // ─────────────────────────────────────────────────────────────
    // Add to cart (delegated — works for cards rendered later too)
    // ─────────────────────────────────────────────────────────────
    document.addEventListener('submit', async function (e) {
        const form = e.target.closest('.ajax-add-to-cart');
        if (!form) return;
        e.preventDefault();

        const btn = form.querySelector('button');
        const btnText = btn ? btn.querySelector('.btn-text') : null;
        const originalText = btnText ? btnText.innerHTML : null;

        if (btnText) btnText.innerHTML = 'ADDING…';
        if (btn) btn.classList.add('disabled');

        try {
            const { data } = await ajaxPost(form.getAttribute('action'), new FormData(form));

            if (data.status === 'success') {
                // Find best image source for the flying animation
                const card = form.closest('.product-card');
                let productImg = card ? card.querySelector('.img-primary') : null;
                if (!productImg) {
                    productImg = form.closest('.quick-view-body')?.parentElement?.querySelector('.quick-view-media img') || null;
                }
                if (productImg) flyToCart(productImg);

                setTimeout(() => {
                    updateBagCounts(data.cartCount);
                    pulseBag();
                    swapOffcanvasCart(data.html);

                    const isQuickView = form.dataset.source === 'quick-view';

                    if (isQuickView) {
                        const modalEl = document.getElementById('quickViewModal');
                        if (modalEl) {
                            const modal = bootstrap.Modal.getInstance(modalEl);
                            if (modal) modal.hide();
                        }
                    }

                    // Friendlier add-to-bag pattern: don't auto-open the offcanvas.
                    // Pulse the bag + show a toast with a "View Bag" affordance so
                    // the user can keep browsing without dismissing a panel.
                    showNotification('Added to your selection', 'success', {
                        duration: 3000,
                        action: {
                            label: 'View',
                            onClick: () => {
                                const offcanvasEl = document.getElementById('offcanvasCart');
                                if (offcanvasEl) {
                                    bootstrap.Offcanvas.getOrCreateInstance(offcanvasEl).show();
                                }
                            }
                        }
                    });
                }, 800);
            } else {
                showNotification(data.message || 'Error adding to bag', 'error');
            }
        } catch (err) {
            console.error(err);
            showNotification('Network error occurred', 'error');
        } finally {
            if (btnText && originalText !== null) btnText.innerHTML = originalText;
            if (btn) btn.classList.remove('disabled');
        }
    });

    // ─────────────────────────────────────────────────────────────
    // Remove from cart (delegated, with Undo toast — A6)
    // ─────────────────────────────────────────────────────────────
    document.addEventListener('click', async function (e) {
        const btn = e.target.closest('.remove-item');
        if (!btn) return;

        const productId = btn.getAttribute('data-id');

        // Capture qty BEFORE removal so we can restore via Undo
        const row = btn.closest('.cart-item');
        const qtyEl = row ? row.querySelector('[data-qty]') : null;
        const previousQty = qtyEl ? parseInt(qtyEl.textContent, 10) : 1;

        const originalHTML = btn.innerHTML;
        btn.innerHTML = '<i class="bi bi-hourglass-split"></i>';
        btn.classList.add('disabled');

        try {
            const { data } = await ajaxPost(`${window.location.origin}/cart/remove/${productId}`);
            if (data.status === 'success') {
                updateBagCounts(data.cartCount);
                swapOffcanvasCart(data.html);

                showNotification('Item removed from selection', 'success', {
                    duration: 5000,
                    action: {
                        label: 'Undo',
                        onClick: async () => {
                            try {
                                const { data: undoData } = await ajaxPost(
                                    `${window.location.origin}/cart/update-qty/${productId}`,
                                    { qty: previousQty }
                                );
                                if (undoData.status === 'success') {
                                    updateBagCounts(undoData.cartCount);
                                    swapOffcanvasCart(undoData.html);
                                    showNotification('Restored to your selection');
                                } else {
                                    showNotification(undoData.message || 'Could not restore item', 'error');
                                }
                            } catch (err) {
                                console.error(err);
                                showNotification('Network error during undo', 'error');
                            }
                        }
                    }
                });
            }
        } catch (err) {
            console.error(err);
            btn.innerHTML = originalHTML;
            btn.classList.remove('disabled');
            showNotification('Could not remove item', 'error');
        }
    });

    // ─────────────────────────────────────────────────────────────
    // A4 — Cart quantity stepper (delegated, works inside offcanvas)
    // ─────────────────────────────────────────────────────────────
    document.addEventListener('click', async function (e) {
        const btn = e.target.closest('.cart-qty-btn');
        if (!btn || btn.classList.contains('is-loading') || btn.disabled) return;

        const productId = btn.getAttribute('data-id');
        const delta = parseInt(btn.getAttribute('data-delta'), 10) || 0;
        const row = btn.closest('.cart-item');
        const valueEl = row ? row.querySelector('[data-qty]') : null;
        const subtotalEl = row ? row.querySelector('[data-subtotal]') : null;

        // Optimistic UI
        const previousQty = valueEl ? parseInt(valueEl.textContent, 10) : 0;
        const stock = row ? parseInt(row.getAttribute('data-stock'), 10) : 0;
        const optimistic = Math.max(0, previousQty + delta);
        if (valueEl) {
            valueEl.textContent = optimistic;
            valueEl.classList.add('is-pulse');
        }
        btn.classList.add('is-loading');

        try {
            const { data } = await ajaxPost(`${window.location.origin}/cart/update-qty/${productId}`, { delta });

            if (data.status === 'success') {
                updateBagCounts(data.cartCount);

                if (data.line) {
                    if (valueEl) valueEl.textContent = data.line.qty;
                    if (subtotalEl) {
                        subtotalEl.textContent = 'Rp ' + Number(data.line.subtotal).toLocaleString('id-ID');
                    }
                    // Refresh disabled state on plus/minus
                    const plus = row.querySelector('.cart-qty-increase');
                    const minus = row.querySelector('.cart-qty-decrease');
                    if (plus) plus.disabled = data.line.qty >= stock;
                    if (minus) minus.disabled = false;
                } else {
                    // Item dropped to 0 → re-render whole offcanvas
                    swapOffcanvasCart(data.html);
                }

                if (data.capped) {
                    showNotification('Reached available stock limit.', 'error');
                }
            } else {
                showNotification(data.message || 'Could not update quantity', 'error');
                if (valueEl) valueEl.textContent = previousQty;
            }
        } catch (err) {
            console.error(err);
            if (valueEl) valueEl.textContent = previousQty;
            showNotification('Network error', 'error');
        } finally {
            btn.classList.remove('is-loading');
            if (valueEl) {
                setTimeout(() => valueEl.classList.remove('is-pulse'), 200);
            }
        }
    });

    // ─────────────────────────────────────────────────────────────
    // A1 — Quick View modal
    // ─────────────────────────────────────────────────────────────
    function initialQuickViewSkeleton() {
        return `
            <div class="quick-view-skeleton">
                <div class="qv-skel-media"></div>
                <div class="qv-skel-body">
                    <div class="qv-skel-line w-25"></div>
                    <div class="qv-skel-line w-75"></div>
                    <div class="qv-skel-line w-50"></div>
                    <div class="qv-skel-line w-100 mt-4"></div>
                    <div class="qv-skel-line w-100"></div>
                    <div class="qv-skel-button"></div>
                </div>
            </div>`;
    }

    document.addEventListener('click', async function (e) {
        const trigger = e.target.closest('.quick-view-trigger');
        if (!trigger) return;
        e.preventDefault();

        const productId = trigger.getAttribute('data-product-id');
        const modalEl = document.getElementById('quickViewModal');
        const body = document.getElementById('quickViewBody');
        if (!modalEl || !body || !productId) return;

        body.innerHTML = initialQuickViewSkeleton();
        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();

        try {
            const { response, data } = await ajaxGet(`${window.location.origin}/products/${productId}/quick-view`);
            refreshCsrf(data);

            if (response.ok && data.status === 'success' && data.html) {
                body.innerHTML = data.html;
            } else {
                body.innerHTML = `
                    <div class="p-5 text-center">
                        <p class="font-serif italic">Could not load product preview.</p>
                    </div>`;
            }
        } catch (err) {
            console.error(err);
            body.innerHTML = `
                <div class="p-5 text-center">
                    <p class="font-serif italic">Network error. Please try again.</p>
                </div>`;
        }
    });

    // ─────────────────────────────────────────────────────────────
    // A2 — Live Search overlay
    // ─────────────────────────────────────────────────────────────
    const searchOverlay = document.getElementById('searchOverlay');
    const searchInput = document.getElementById('searchOverlayInput');
    const searchResults = document.getElementById('searchOverlayResults');
    const searchHint = document.getElementById('searchOverlayHint');
    const searchTriggers = document.querySelectorAll('.nav-search-trigger');
    const searchCloseBtn = searchOverlay ? searchOverlay.querySelector('.search-overlay-close') : null;

    function openSearchOverlay() {
        if (!searchOverlay) return;
        searchOverlay.classList.add('is-open');
        searchOverlay.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
        setTimeout(() => searchInput && searchInput.focus(), 80);
        // Pre-load trending on open if input is empty
        if (searchInput && searchInput.value.trim() === '') {
            runSearch('');
        }
    }

    function closeSearchOverlay() {
        if (!searchOverlay) return;
        searchOverlay.classList.remove('is-open');
        searchOverlay.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
    }

    searchTriggers.forEach((btn) => btn.addEventListener('click', openSearchOverlay));
    if (searchCloseBtn) searchCloseBtn.addEventListener('click', closeSearchOverlay);
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && searchOverlay && searchOverlay.classList.contains('is-open')) {
            closeSearchOverlay();
        }
    });

    function priceFmt(n) {
        return 'Rp ' + Number(n).toLocaleString('id-ID');
    }

    function renderSearchResults(payload) {
        if (!searchResults || !searchHint) return;

        searchHint.classList.remove('is-error');

        if (!payload || !payload.query || payload.query.length < 2) {
            // Empty query — render trending if backend sent it
            const trending = payload && payload.trending ? payload.trending : [];
            if (trending.length > 0) {
                searchHint.textContent = 'Trending searches';
                searchResults.innerHTML = '<div class="search-trending">' + trending.map((t) => `
                    <button type="button" class="search-trending-pill" data-search-fill="${escapeHtml(t.query)}">
                        ${escapeHtml(t.query)}
                        <span class="font-serif italic ms-2" style="opacity: 0.55;">×${t.count}</span>
                    </button>
                `).join('') + '</div>';
            } else {
                searchHint.textContent = 'Type at least 2 characters to see suggestions.';
                searchResults.innerHTML = '';
            }
            return;
        }

        if (!payload.results || payload.results.length === 0) {
            searchHint.textContent = `No results found for "${payload.query}".`;
            searchHint.classList.add('is-error');
            searchResults.innerHTML = `
                <div class="search-result-empty">
                    Try a different keyword or browse the
                    <a href="/collection" class="text-dark fw-bold text-decoration-underline">full collection</a>.
                </div>`;
            return;
        }

        searchHint.textContent = `${payload.results.length} match${payload.results.length === 1 ? '' : 'es'} for "${payload.query}"`;

        searchResults.innerHTML = payload.results
            .map((r) => `
                <a class="search-result-item" href="${r.url}">
                    <div class="search-result-thumb">
                        <img src="${r.image}" alt="${escapeHtml(r.name)}" loading="lazy">
                    </div>
                    <div class="search-result-meta">
                        <div class="search-result-name">${escapeHtml(r.name)}</div>
                        <div class="search-result-price">${priceFmt(r.price)} ${
                            r.stock < 1 ? '<span class="ms-2 text-uppercase small fw-bold" style="letter-spacing:.1em;color:#b00020;">Sold out</span>' : ''
                        }</div>
                    </div>
                    <span class="search-result-arrow">→</span>
                </a>`)
            .join('');
    }

    function escapeHtml(str) {
        return String(str).replace(/[&<>"']/g, (c) => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        }[c]));
    }

    let searchAbort = null;
    const runSearch = debounce(async function (query) {
        if (!query || query.length < 2) {
            // Fetch trending list (no query → backend returns trending payload)
            try {
                const response = await fetch(`${window.location.origin}/products/search`, {
                    method: 'GET',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                });
                const data = await response.json();
                renderSearchResults(data);
            } catch (err) {
                renderSearchResults({ query, results: [] });
            }
            return;
        }

        if (searchAbort) searchAbort.abort();
        searchAbort = new AbortController();

        try {
            const response = await fetch(`${window.location.origin}/products/search?q=${encodeURIComponent(query)}`, {
                method: 'GET',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                signal: searchAbort.signal
            });
            const data = await response.json();
            renderSearchResults(data);
        } catch (err) {
            if (err.name === 'AbortError') return;
            console.error(err);
            if (searchHint) {
                searchHint.textContent = 'Search is unavailable right now.';
                searchHint.classList.add('is-error');
            }
        }
    }, 250);

    if (searchInput) {
        searchInput.addEventListener('input', (e) => runSearch(e.target.value.trim()));
    }

    // Trending pill click → fill input and search
    if (searchResults) {
        searchResults.addEventListener('click', (e) => {
            const pill = e.target.closest('[data-search-fill]');
            if (!pill || !searchInput) return;
            searchInput.value = pill.getAttribute('data-search-fill');
            searchInput.dispatchEvent(new Event('input'));
            searchInput.focus();
        });
    }

    // ─────────────────────────────────────────────────────────────
    // A3 — Filter & Sort (collection page, AJAX with pushState)
    // ─────────────────────────────────────────────────────────────
    const filterPanel = document.getElementById('filterPanel');
    const filterToggle = document.querySelector('.filter-toggle');
    const filterForm = document.getElementById('filterForm');
    const filterTotalCount = document.getElementById('filterTotalCount');
    const productsGrid = document.getElementById('productsGrid');
    const productsPagerWrap = document.getElementById('productsPagerWrap');
    const gridSkeletonOverlay = document.getElementById('gridSkeletonOverlay');

    function setFilterChip(name, value) {
        if (!filterForm) return;
        const hidden = filterForm.querySelector(`input[type="hidden"][name="${name}"]`);
        if (hidden) hidden.value = value;
        filterForm.querySelectorAll(`.filter-chip[data-filter-name="${name}"]`).forEach((chip) => {
            chip.classList.toggle('is-active', chip.getAttribute('data-filter-value') === value);
        });
    }

    if (filterToggle && filterPanel) {
        filterToggle.addEventListener('click', () => {
            const isOpen = filterPanel.classList.toggle('is-open');
            filterPanel.hidden = false; // ensure visible to allow CSS height transition
            filterToggle.classList.toggle('is-open', isOpen);
            filterToggle.setAttribute('aria-expanded', String(isOpen));
            const txt = filterToggle.querySelector('.filter-toggle-text');
            if (txt) txt.textContent = isOpen ? 'Close Filters' : 'Filter + Sort';
        });
    }

    function showGridSkeleton(visible) {
        if (!gridSkeletonOverlay || !productsGrid) return;
        gridSkeletonOverlay.hidden = !visible;
        productsGrid.style.opacity = visible ? '0.25' : '1';
    }

    let filterAbort = null;
    async function runFilter(pushHistory = true) {
        if (!filterForm || !productsGrid) return;
        const fd = new FormData(filterForm);
        const params = new URLSearchParams();
        for (const [key, value] of fd.entries()) {
            if (value !== '' && value !== null) params.append(key, value);
        }
        const qs = params.toString();
        const url = `${window.location.pathname}${qs ? '?' + qs : ''}`;

        if (filterAbort) filterAbort.abort();
        filterAbort = new AbortController();

        showGridSkeleton(true);

        try {
            const response = await fetch(url, {
                method: 'GET',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                signal: filterAbort.signal
            });
            const data = await response.json();

            if (data.status === 'success') {
                productsGrid.innerHTML = data.gridHtml;
                if (productsPagerWrap) productsPagerWrap.innerHTML = data.pagerHtml || '';
                if (filterTotalCount) {
                    filterTotalCount.textContent = `${data.count} item${data.count === 1 ? '' : 's'}`;
                }
                if (pushHistory) {
                    window.history.pushState({ nexgearFilter: true }, '', url);
                }
            } else {
                showNotification('Could not apply filters.', 'error');
            }
        } catch (err) {
            if (err.name === 'AbortError') return;
            console.error(err);
            showNotification('Network error while filtering.', 'error');
        } finally {
            showGridSkeleton(false);
        }
    }

    const debouncedFilter = debounce(runFilter, 350);

    if (filterForm) {
        // Chip clicks (sort + stock)
        filterForm.addEventListener('click', (e) => {
            const chip = e.target.closest('.filter-chip');
            if (!chip) return;
            const name = chip.getAttribute('data-filter-name');
            const value = chip.getAttribute('data-filter-value');
            setFilterChip(name, value);
            runFilter();
        });

        // Price input changes
        filterForm.querySelectorAll('.filter-price-input').forEach((input) => {
            input.addEventListener('input', debouncedFilter);
        });

        // Reset filter
        filterForm.addEventListener('reset', (e) => {
            e.preventDefault();
            filterForm.querySelectorAll('input[type="hidden"]').forEach((h) => {
                if (h.name === 'sort') h.value = 'newest';
                else if (h.name === 'q') return; // keep search query
                else h.value = '';
            });
            filterForm.querySelectorAll('.filter-price-input').forEach((i) => (i.value = ''));
            filterForm.querySelectorAll('.filter-chip').forEach((c) => c.classList.remove('is-active'));
            const newest = filterForm.querySelector('.filter-chip[data-filter-name="sort"][data-filter-value="newest"]');
            const allStock = filterForm.querySelector('.filter-chip[data-filter-name="stock"][data-filter-value=""]');
            if (newest) newest.classList.add('is-active');
            if (allStock) allStock.classList.add('is-active');
            runFilter();
        });
    }

    // Empty-state "Clear Filters" button (delegated)
    document.addEventListener('click', (e) => {
        const clear = e.target.closest('[data-filter-clear]');
        if (!clear || !filterForm) return;
        e.preventDefault();
        filterForm.dispatchEvent(new Event('reset'));
    });

    // Browser back/forward — re-fetch based on current URL
    window.addEventListener('popstate', (e) => {
        if (!filterForm || !productsGrid) return;
        const params = new URLSearchParams(window.location.search);

        // Sync form state from URL
        filterForm.querySelectorAll('input[type="hidden"]').forEach((h) => {
            const v = params.get(h.name);
            if (h.name === 'sort') h.value = v || 'newest';
            else h.value = v || '';
        });
        filterForm.querySelectorAll('.filter-price-input').forEach((i) => {
            i.value = params.get(i.name) || '';
        });
        filterForm.querySelectorAll('.filter-chip').forEach((c) => {
            const n = c.getAttribute('data-filter-name');
            const v = c.getAttribute('data-filter-value');
            const current = (filterForm.querySelector(`input[type="hidden"][name="${n}"]`) || {}).value || '';
            c.classList.toggle('is-active', current === v);
        });

        runFilter(false);
    });

    // ─────────────────────────────────────────────────────────────
    // A5 — Product gallery & zoom
    // ─────────────────────────────────────────────────────────────
    (function initGallery() {
        const stage = document.getElementById('galleryStage');
        const stageImg = document.getElementById('galleryStageImage');
        const thumbs = document.getElementById('galleryThumbs');
        if (!stage || !stageImg || !thumbs) return;

        thumbs.addEventListener('click', (e) => {
            const btn = e.target.closest('.gallery-thumb');
            if (!btn) return;
            const full = btn.getAttribute('data-full');
            if (!full || full === stageImg.src) return;
            stageImg.src = full;
            thumbs.querySelectorAll('.gallery-thumb').forEach((t) => t.classList.remove('is-active'));
            btn.classList.add('is-active');
        });

        // Zoom on hover via background-position trick
        let zooming = false;
        const startZoom = () => {
            zooming = true;
            stage.classList.add('is-zooming');
        };
        const stopZoom = () => {
            zooming = false;
            stage.classList.remove('is-zooming');
            stage.style.removeProperty('--zoom-x');
            stage.style.removeProperty('--zoom-y');
        };

        stage.addEventListener('mouseenter', startZoom);
        stage.addEventListener('mouseleave', stopZoom);
        stage.addEventListener('mousemove', (e) => {
            if (!zooming) return;
            const rect = stage.getBoundingClientRect();
            const x = ((e.clientX - rect.left) / rect.width) * 100;
            const y = ((e.clientY - rect.top) / rect.height) * 100;
            stage.style.setProperty('--zoom-x', x + '%');
            stage.style.setProperty('--zoom-y', y + '%');
        });

        // Click to toggle zoom (mobile-friendly)
        stage.addEventListener('click', () => {
            if (zooming) stopZoom();
            else startZoom();
        });
    })();

    // ─────────────────────────────────────────────────────────────
    // A11 — Live stock counter + simulated "viewers now"
    // ─────────────────────────────────────────────────────────────
    (function initLiveStock() {
        const stockEl = document.querySelector('.live-stock');
        const viewersEl = document.querySelector('[data-viewers]');
        if (!stockEl && !viewersEl) return;

        // Viewers — purely cosmetic, persists per session in sessionStorage.
        if (viewersEl) {
            const countEl = viewersEl.querySelector('[data-viewers-count]');
            const productId = stockEl ? stockEl.getAttribute('data-product-id') : 'home';
            const key = `nexgear_viewers_${productId}`;
            let n = parseInt(sessionStorage.getItem(key) || '0', 10);
            if (!n || n < 3) {
                n = 3 + Math.floor(Math.random() * 12); // 3..14
                sessionStorage.setItem(key, String(n));
            }
            if (countEl) countEl.textContent = n;

            // Drift the number every 25-45s for a "live" feel
            const drift = () => {
                const cur = parseInt(sessionStorage.getItem(key) || String(n), 10);
                const delta = Math.random() < 0.5 ? -1 : 1;
                const next = Math.max(2, Math.min(28, cur + delta));
                sessionStorage.setItem(key, String(next));
                if (countEl) {
                    countEl.textContent = next;
                }
                setTimeout(drift, 25000 + Math.random() * 20000);
            };
            setTimeout(drift, 25000 + Math.random() * 20000);
        }

        if (!stockEl) return;
        const productId = stockEl.getAttribute('data-product-id');
        const textEl = stockEl.querySelector('.live-stock-text');
        let lastStock = parseInt(stockEl.getAttribute('data-stock'), 10);

        function applyStock(stock) {
            stockEl.classList.toggle('is-low', stock > 0 && stock <= 5);
            stockEl.classList.toggle('is-out', stock <= 0);

            const newText = stock <= 0 ? 'Sold out' : `${stock} unit${stock === 1 ? '' : 's'} in vault`;
            if (textEl && textEl.textContent !== newText) {
                textEl.textContent = newText;
                textEl.classList.add('flash');
                setTimeout(() => textEl.classList.remove('flash'), 600);
            }
            stockEl.setAttribute('data-stock', String(stock));
        }

        applyStock(lastStock);

        async function poll() {
            // Only poll when the tab is visible
            if (document.hidden) return;
            try {
                const { response, data } = await ajaxGet(`${window.location.origin}/products/${productId}/stock`);
                if (response.ok && data.status === 'success') {
                    if (data.stock !== lastStock) {
                        lastStock = data.stock;
                        applyStock(lastStock);
                    }
                }
            } catch (err) {
                // Silently swallow — live counter is best-effort
            }
        }

        // Poll every 30s
        setInterval(poll, 30000);
        // Also re-poll when user returns to the tab
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) poll();
        });
    })();

    // ─────────────────────────────────────────────────────────────
    // A14 — Form validation realtime
    // ─────────────────────────────────────────────────────────────
    (function initValidator() {
        const forms = document.querySelectorAll('form[data-validate]');
        if (forms.length === 0) return;

        const validators = {
            required: (val) => val.trim() !== '' || 'This field is required.',
            email: (val) => {
                if (val.trim() === '') return 'Email is required.';
                return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val) || 'Enter a valid email address.';
            },
            phone: (val) => {
                if (val.trim() === '') return 'Phone number is required.';
                if (val.length < 8 || val.length > 20) return 'Phone must be 8–20 characters.';
                return /^[\d\+\-\s]+$/.test(val) || 'Use only digits, +, -, or spaces.';
            },
            postal: (val) => {
                if (val.trim() === '') return 'Postal code is required.';
                if (val.length < 3 || val.length > 10) return 'Postal must be 3–10 characters.';
                return /^[\d\-]+$/.test(val) || 'Use digits and hyphens only.';
            },
            password: (val) => {
                if (val.length < 8) return 'Use at least 8 characters.';
                return true;
            },
        };

        function ruleFor(input) {
            const raw = input.getAttribute('data-rule');
            if (!raw) return null;

            if (raw.startsWith('min:')) {
                const n = parseInt(raw.split(':')[1], 10) || 0;
                return (val) => (val.trim().length >= n) || `Must be at least ${n} characters.`;
            }
            if (raw.startsWith('match:')) {
                const target = raw.split(':')[1];
                return (val, form) => {
                    const other = form.querySelector(`[name="${target}"]`);
                    if (!other) return true;
                    return val === other.value || 'Passwords do not match.';
                };
            }
            return validators[raw] || null;
        }

        function setState(field, valid, message) {
            field.classList.toggle('is-valid', valid === true);
            field.classList.toggle('is-invalid', valid === false);
            const errEl = field.querySelector('[data-error]');
            if (errEl) errEl.textContent = valid === false ? message : '';
        }

        function passwordStrength(val) {
            // returns {score 0..4, label, color}
            let score = 0;
            if (val.length >= 8) score++;
            if (val.length >= 12) score++;
            if (/[A-Z]/.test(val) && /[a-z]/.test(val)) score++;
            if (/\d/.test(val) && /[^\w\s]/.test(val)) score++;
            const map = [
                { label: 'Too short', color: '#b00020', pct: 12 },
                { label: 'Weak', color: '#ef4444', pct: 30 },
                { label: 'Fair', color: '#f59e0b', pct: 55 },
                { label: 'Good', color: '#10b981', pct: 80 },
                { label: 'Strong', color: '#000', pct: 100 },
            ];
            return { score, ...map[score] };
        }

        forms.forEach((form) => {
            const fields = form.querySelectorAll('[data-field]');

            const validateField = (field, opts = {}) => {
                const input = field.querySelector('input, textarea');
                if (!input) return true;

                const rule = ruleFor(input);
                if (!rule) {
                    // Just required check via HTML attribute
                    if (input.required && input.value.trim() === '') {
                        if (opts.live === false) return false;
                        setState(field, false, 'This field is required.');
                        return false;
                    }
                    setState(field, true, '');
                    return true;
                }

                const result = rule(input.value, form);
                if (result === true) {
                    setState(field, true, '');
                    return true;
                }
                setState(field, false, result);
                return false;
            };

            fields.forEach((field) => {
                const input = field.querySelector('input, textarea');
                if (!input) return;

                // Validate on blur (less noisy than on input)
                input.addEventListener('blur', () => {
                    if (input.value.trim() !== '' || field.classList.contains('is-invalid')) {
                        validateField(field);
                    }
                });

                // Live re-check while user is correcting an invalid field
                input.addEventListener('input', () => {
                    if (field.classList.contains('is-invalid')) {
                        validateField(field);
                    }

                    // Password strength meter
                    const meter = field.querySelector('[data-strength]');
                    if (meter && input.type === 'password' && input.name === 'password') {
                        meter.hidden = input.value.length === 0;
                        const { label, color, pct } = passwordStrength(input.value);
                        meter.style.setProperty('--strength', pct + '%');
                        meter.style.setProperty('--strength-color', color);
                        const labelEl = meter.querySelector('[data-strength-label]');
                        if (labelEl) labelEl.textContent = label;
                    }

                    // When password changes, re-validate confirm field
                    if (input.name === 'password') {
                        const confirmField = form.querySelector('[name="password_confirm"]');
                        if (confirmField) {
                            const cf = confirmField.closest('[data-field]');
                            if (cf && (cf.classList.contains('is-invalid') || confirmField.value !== '')) {
                                validateField(cf);
                            }
                        }
                    }
                });
            });

            form.addEventListener('submit', (e) => {
                let allValid = true;
                fields.forEach((field) => {
                    const ok = validateField(field);
                    if (!ok) allValid = false;
                });
                if (!allValid) {
                    e.preventDefault();
                    const firstInvalid = form.querySelector('[data-field].is-invalid input, [data-field].is-invalid textarea');
                    if (firstInvalid) firstInvalid.focus();
                }
            });
        });
    })();

    // ─────────────────────────────────────────────────────────────
    // A15 — Theme toggle (light / dark)
    // ─────────────────────────────────────────────────────────────
    (function initThemeToggle() {
        const toggle = document.getElementById('themeToggle');
        if (!toggle) return;
        const icon = toggle.querySelector('[data-theme-icon]');

        function applyIcon(theme) {
            if (!icon) return;
            icon.classList.remove('bi-moon', 'bi-sun');
            icon.classList.add(theme === 'dark' ? 'bi-sun' : 'bi-moon');
        }

        const initial = document.documentElement.getAttribute('data-theme') || 'light';
        applyIcon(initial);

        toggle.addEventListener('click', () => {
            const current = document.documentElement.getAttribute('data-theme') || 'light';
            const next = current === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-theme', next);
            try { localStorage.setItem('nexgear_theme', next); } catch (e) { /* ignore */ }
            applyIcon(next);
        });
    })();

    // ─────────────────────────────────────────────────────────────
    // A12 — Context-aware cursor + magnetic CTAs
    // ─────────────────────────────────────────────────────────────
    (function initCursorAndMagnetic() {
        const cursor = document.getElementById('customCursor');
        if (!cursor) return;

        // Skip on touch devices
        if (window.matchMedia && window.matchMedia('(pointer: coarse)').matches) return;

        const contextMap = [
            { selector: '.product-card',           label: 'VIEW',   cls: 'is-link' },
            { selector: '.gallery-stage',          label: 'ZOOM',   cls: 'is-zoom' },
            { selector: '.ajax-add-to-cart button',label: 'ADD',    cls: 'is-add' },
            { selector: '.quick-view-trigger',     label: 'QUICK',  cls: 'is-link' },
            { selector: '.recently-viewed-card',   label: 'OPEN',   cls: 'is-link' },
            { selector: '.nav-search-trigger',     label: 'SEARCH', cls: 'is-search' },
            { selector: 'a[href]:not(.nav-link)',  label: 'LINK',   cls: 'is-link' }
        ];

        function clearContextClasses() {
            cursor.classList.remove('is-add', 'is-zoom', 'is-link', 'is-search');
        }

        function findContext(target) {
            for (const ctx of contextMap) {
                if (target.closest(ctx.selector)) return ctx;
            }
            return null;
        }

        document.addEventListener('mousemove', (e) => {
            cursor.style.left = e.clientX + 'px';
            cursor.style.top = e.clientY + 'px';
            cursor.style.marginLeft = '-30px';
            cursor.style.marginTop = '-30px';

            const ctx = findContext(e.target);
            if (ctx) {
                if (cursor.textContent !== ctx.label) cursor.textContent = ctx.label;
                clearContextClasses();
                cursor.classList.add(ctx.cls, 'active');
            } else {
                if (cursor.textContent !== 'VIEW') cursor.textContent = 'VIEW';
                clearContextClasses();
                cursor.classList.remove('active');
            }
        });

        document.addEventListener('mouseleave', () => {
            cursor.classList.remove('active');
        });

        // Magnetic effect on tagged buttons (e.g. big CTAs)
        const magneticEls = document.querySelectorAll('.btn-magnetic, .btn-primary-glow, .btn-dark.w-100');
        magneticEls.forEach((el) => {
            const strength = 0.25;
            el.classList.add('btn-magnetic');
            el.addEventListener('mousemove', (e) => {
                const rect = el.getBoundingClientRect();
                const x = e.clientX - rect.left - rect.width / 2;
                const y = e.clientY - rect.top - rect.height / 2;
                el.style.transform = `translate(${x * strength}px, ${y * strength}px)`;
            });
            el.addEventListener('mouseleave', () => {
                el.style.transform = '';
            });
        });
    })();

    // ─────────────────────────────────────────────────────────────
    // A9 — Soft page transition (fade + progress bar on internal nav)
    // ─────────────────────────────────────────────────────────────
    (function initPageTransition() {
        const progress = document.getElementById('pageProgress');
        const main = document.querySelector('main');
        if (!progress || !main) return;

        function startProgress() {
            progress.style.transition = 'none';
            progress.style.width = '0';
            // force reflow then animate
            void progress.offsetWidth;
            progress.style.transition = 'width 1.2s cubic-bezier(0.16, 1, 0.3, 1)';
            progress.style.width = '85%';
        }

        function isInternalLink(a) {
            if (!a || !a.href) return false;
            if (a.target && a.target !== '_self') return false;
            if (a.hasAttribute('download')) return false;
            if (a.dataset.noTransition === '1') return false;

            try {
                const url = new URL(a.href, window.location.origin);
                if (url.origin !== window.location.origin) return false;
                if (url.pathname === window.location.pathname && url.search === window.location.search) return false;
                if (a.getAttribute('href').startsWith('#')) return false;
                return true;
            } catch (e) {
                return false;
            }
        }

        document.addEventListener('click', (e) => {
            if (e.defaultPrevented) return;
            if (e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
            if (e.button !== 0) return;

            const a = e.target.closest('a');
            if (!isInternalLink(a)) return;

            // Skip Bootstrap/JS triggers
            if (a.hasAttribute('data-bs-toggle') || a.hasAttribute('data-bs-target')) return;
            if (a.classList.contains('quick-view-trigger')) return;
            if (a.classList.contains('compare-toggle')) return;
            if (a.hasAttribute('data-wishlist-toggle')) return;

            // Begin transition
            startProgress();
            main.classList.add('is-leaving');
        });

        // Fade in on load
        window.addEventListener('pageshow', () => {
            main.classList.remove('is-leaving');
            if (progress) {
                progress.style.transition = 'width 0.25s ease';
                progress.style.width = '100%';
                setTimeout(() => {
                    progress.style.transition = 'none';
                    progress.style.width = '0';
                }, 280);
            }
        });
    })();

    // ─────────────────────────────────────────────────────────────
    // A10 — Product comparison (localStorage)
    // ─────────────────────────────────────────────────────────────
    (function initCompare() {
        const STORAGE_KEY = 'nexgear_compare';
        const MAX_ITEMS = 3;

        const tray = document.getElementById('compareTray');
        const trayCount = tray ? tray.querySelector('[data-compare-count]') : null;
        const trayThumbs = document.getElementById('compareTrayThumbs');
        const trayClear = document.getElementById('compareTrayClear');
        const trayGo = document.getElementById('compareTrayGo');

        function read() {
            try {
                const raw = localStorage.getItem(STORAGE_KEY);
                if (!raw) return [];
                const parsed = JSON.parse(raw);
                return Array.isArray(parsed) ? parsed.filter((x) => Number.isInteger(x)) : [];
            } catch (e) {
                return [];
            }
        }

        function write(list) {
            try {
                localStorage.setItem(STORAGE_KEY, JSON.stringify(list));
            } catch (e) { /* ignore */ }
        }

        function getThumbForId(id) {
            const card = document.querySelector(`.product-card .compare-toggle[data-compare-toggle="${id}"]`);
            if (!card) return null;
            const article = card.closest('.product-card');
            const img = article ? article.querySelector('.img-primary, .product-media img') : null;
            return img ? img.src : null;
        }

        function refreshUI() {
            const ids = read();

            // Sync toggles state
            document.querySelectorAll('[data-compare-toggle]').forEach((btn) => {
                const id = parseInt(btn.getAttribute('data-compare-toggle'), 10);
                btn.classList.toggle('is-active', ids.includes(id));
                btn.setAttribute('title', ids.includes(id) ? 'Remove from compare' : 'Add to compare');
            });

            if (!tray) return;

            if (ids.length === 0) {
                tray.classList.remove('is-visible');
                tray.setAttribute('aria-hidden', 'true');
                if (trayThumbs) trayThumbs.innerHTML = '';
                if (trayCount) trayCount.textContent = '0';
                return;
            }

            tray.hidden = false;
            requestAnimationFrame(() => tray.classList.add('is-visible'));
            tray.setAttribute('aria-hidden', 'false');

            if (trayCount) trayCount.textContent = String(ids.length);

            if (trayThumbs) {
                trayThumbs.innerHTML = ids.map((id) => {
                    const thumb = getThumbForId(id);
                    const fallback = 'https://images.unsplash.com/photo-1603481546238-487240415921?q=80&w=80&auto=format&fit=crop';
                    return `<button type="button" class="compare-tray-thumb" data-compare-thumb="${id}" aria-label="Remove from compare">
                        <img src="${thumb || fallback}" alt="">
                    </button>`;
                }).join('');
            }

            if (trayGo) {
                if (ids.length < 1) {
                    trayGo.setAttribute('aria-disabled', 'true');
                } else {
                    trayGo.removeAttribute('aria-disabled');
                }
                trayGo.setAttribute('href', `${window.location.origin}/products/compare?ids=${ids.join(',')}`);
            }
        }

        function add(id) {
            let list = read();
            if (list.includes(id)) return;
            if (list.length >= MAX_ITEMS) {
                showNotification(`You can compare up to ${MAX_ITEMS} products.`, 'error');
                return;
            }
            list.push(id);
            write(list);
            refreshUI();
            showNotification(`Added to compare (${list.length}/${MAX_ITEMS}).`, 'success', { duration: 2000 });
        }

        function remove(id) {
            let list = read().filter((x) => x !== id);
            write(list);
            refreshUI();
        }

        function clear() {
            write([]);
            refreshUI();
        }

        // Wire toggles (delegated, works for AJAX-loaded grids)
        document.addEventListener('click', (e) => {
            const toggle = e.target.closest('[data-compare-toggle]');
            if (toggle) {
                const id = parseInt(toggle.getAttribute('data-compare-toggle'), 10);
                if (read().includes(id)) remove(id);
                else add(id);
                return;
            }

            const trayThumb = e.target.closest('[data-compare-thumb]');
            if (trayThumb) {
                remove(parseInt(trayThumb.getAttribute('data-compare-thumb'), 10));
                return;
            }

            const removeBtn = e.target.closest('[data-compare-remove]');
            if (removeBtn) {
                const id = parseInt(removeBtn.getAttribute('data-compare-remove'), 10);
                remove(id);
                // On compare page, redirect to refreshed URL
                if (window.location.pathname.endsWith('/products/compare')) {
                    const ids = read();
                    window.location.href = ids.length > 0
                        ? `${window.location.pathname}?ids=${ids.join(',')}`
                        : window.location.pathname;
                }
                return;
            }
        });

        if (trayClear) trayClear.addEventListener('click', clear);

        // Sync across tabs
        window.addEventListener('storage', (e) => {
            if (e.key === STORAGE_KEY) refreshUI();
        });

        // Initial paint
        refreshUI();

        // Re-sync after AJAX filter swap (the grid is replaced)
        const grid = document.getElementById('productsGrid');
        if (grid) {
            const observer = new MutationObserver(() => refreshUI());
            observer.observe(grid, { childList: true, subtree: false });
        }
    })();

    // ─────────────────────────────────────────────────────────────
    // B8 — Stock alert subscribe (AJAX on sold-out product detail)
    // ─────────────────────────────────────────────────────────────
    document.addEventListener('submit', async function (e) {
        const form = e.target.closest('[data-stock-alert]');
        if (!form) return;
        e.preventDefault();

        const hint = form.querySelector('[data-stock-alert-hint]');
        const btn = form.querySelector('button[type="submit"]');
        const original = btn ? btn.textContent : '';
        if (btn) { btn.disabled = true; btn.textContent = 'SENDING…'; }
        form.classList.remove('is-success', 'is-error');

        try {
            const { data } = await ajaxPost(form.action, new FormData(form));
            if (data.status === 'success') {
                form.classList.add('is-success');
                if (hint) hint.textContent = data.message;
                showNotification(data.message, 'success', { duration: 2200 });
            } else {
                form.classList.add('is-error');
                if (hint) hint.textContent = data.message || 'Could not save alert.';
                showNotification(data.message || 'Subscription failed', 'error');
            }
        } catch (err) {
            console.error(err);
            showNotification('Network error', 'error');
        } finally {
            if (btn) { btn.disabled = false; btn.textContent = original; }
        }
    });

    // ─────────────────────────────────────────────────────────────
    // TOP-CRITICAL #1 — Mobile tap-to-flip on product card image
    // On touch devices, hover doesn't fire so the secondary image
    // never shows. We listen for tap-on-image and toggle .is-flipped
    // for ~1.5s before reverting. The anchor click still fires for
    // navigation — we only intercept long-press / first-tap on cards
    // that have a secondary image.
    // ─────────────────────────────────────────────────────────────
    (function initMobileCardFlip() {
        const isTouch = window.matchMedia && window.matchMedia('(hover: none) and (pointer: coarse)').matches;
        if (!isTouch) return;

        let flippedCard = null;
        let revertTimer = null;

        document.addEventListener('touchstart', (e) => {
            const card = e.target.closest('.product-card');
            if (!card) return;
            // Skip if no secondary image to show
            if (!card.querySelector('.img-secondary')) return;
            // Skip if user tapped a real button/link inside the card
            if (e.target.closest('button, a:not(.product-media-container)')) return;

            if (flippedCard && flippedCard !== card) {
                flippedCard.classList.remove('is-flipped');
            }
            flippedCard = card;
            card.classList.add('is-flipped');

            clearTimeout(revertTimer);
            revertTimer = setTimeout(() => {
                if (flippedCard === card) {
                    card.classList.remove('is-flipped');
                    flippedCard = null;
                }
            }, 2000);
        }, { passive: true });
    })();

    // ─────────────────────────────────────────────────────────────
    // TOP-CRITICAL #3 — Mobile bottom nav search trigger
    // ─────────────────────────────────────────────────────────────
    (function initMobileBottomNavSearch() {
        const trigger = document.querySelector('[data-mb-search-trigger]');
        if (!trigger) return;
        trigger.addEventListener('click', (e) => {
            e.preventDefault();
            if (window.NexGear && typeof window.NexGear.openSearchOverlay === 'function') {
                window.NexGear.openSearchOverlay();
            }
        });
    })();

    // ─────────────────────────────────────────────────────────────
    // D4 — First-visit onboarding modal
    // ─────────────────────────────────────────────────────────────
    (function initOnboarding() {
        const overlay = document.getElementById('onboardingOverlay');
        if (!overlay) return;

        const STORAGE_KEY = 'nexgear_onboarded';
        const flag = (() => {
            try { return localStorage.getItem(STORAGE_KEY); } catch { return null; }
        })();
        const hasOnboarded = flag === '1';

        // Don't pop on /login, /register, /admin, /account during the auth dance
        const path = window.location.pathname;
        const skipPaths = ['/login', '/register', '/login/2fa', '/logout'];
        const skipPrefixes = ['/admin', '/account', '/checkout'];
        const shouldSkip = skipPaths.includes(path)
            || skipPrefixes.some((p) => path === p || path.startsWith(p + '/'));

        function persistDismiss() {
            try { localStorage.setItem(STORAGE_KEY, '1'); } catch { /* ignore */ }
        }

        function show() {
            overlay.hidden = false;
            requestAnimationFrame(() => overlay.classList.add('is-open'));
            overlay.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
        }

        function hide() {
            overlay.classList.remove('is-open');
            overlay.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
            persistDismiss();
            // Remove from DOM after fade so it doesn't intercept tab focus
            setTimeout(() => { overlay.hidden = true; }, 400);
        }

        // Auto-show on first visit, with a small delay so the page paints first
        if (!hasOnboarded && !shouldSkip) {
            setTimeout(show, 800);
        }

        // Dismiss handlers
        overlay.addEventListener('click', (e) => {
            // Click on backdrop (overlay itself) closes
            if (e.target === overlay) {
                hide();
                return;
            }
            const dismiss = e.target.closest('[data-onboarding-dismiss]');
            if (dismiss) hide();

            // Track category click then close
            const tracked = e.target.closest('[data-onboarding-track]');
            if (tracked) persistDismiss();
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && overlay.classList.contains('is-open')) {
                hide();
            }
        });

        // Expose for the help page to re-trigger if needed
        window.NexGear = window.NexGear || {};
        window.NexGear.showOnboarding = () => {
            try { localStorage.removeItem(STORAGE_KEY); } catch {}
            show();
        };
    })();

    // ─────────────────────────────────────────────────────────────
    // B22 — Service worker registration (PWA)
    // ─────────────────────────────────────────────────────────────
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('/service-worker.js').catch((err) => {
                console.warn('Service worker registration failed:', err);
            });
        });
    }

    // ─────────────────────────────────────────────────────────────
    // B11 — Newsletter footer subscribe (AJAX)
    // ─────────────────────────────────────────────────────────────
    (function initNewsletter() {
        const form = document.getElementById('newsletterForm');
        if (!form) return;
        const hint = form.querySelector('[data-newsletter-hint]');
        const btn = form.querySelector('button[type="submit"]');
        const input = form.querySelector('input[name="email"]');

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            if (!input.value.trim()) return;
            btn.disabled = true;
            const original = btn.innerHTML;
            btn.textContent = 'SENDING…';

            try {
                const { data } = await ajaxPost(form.action, new FormData(form));
                if (hint) {
                    hint.textContent = data.message || (data.status === 'success' ? 'Thanks for subscribing.' : 'Could not subscribe.');
                    hint.classList.toggle('is-success', data.status === 'success');
                    hint.classList.toggle('is-error', data.status !== 'success');
                }
                if (data.status === 'success') {
                    input.value = '';
                    showNotification(data.message, 'success', { duration: 2200 });
                } else {
                    showNotification(data.message || 'Subscription failed', 'error');
                }
            } catch (err) {
                console.error(err);
                showNotification('Network error', 'error');
            } finally {
                btn.disabled = false;
                btn.innerHTML = original;
            }
        });
    })();

    // ─────────────────────────────────────────────────────────────
    // B7 — Coupon apply / remove (AJAX on cart page)
    // ─────────────────────────────────────────────────────────────
    (function initCoupon() {
        const block = document.getElementById('couponBlock');
        if (!block) return;

        const applyForm = document.getElementById('couponApplyForm');
        const input = document.getElementById('couponInput');
        const hint = document.getElementById('couponHint');

        function fmtRp(n) {
            return 'Rp ' + Number(n || 0).toLocaleString('id-ID');
        }

        function paintSummary(data) {
            const subtotalEl = document.querySelector('[data-summary-subtotal]');
            const discountEl = document.querySelector('[data-summary-discount]');
            const discountRow = document.querySelector('[data-summary-discount-row]');
            const totalEl = document.querySelector('[data-summary-total]');

            if (subtotalEl) subtotalEl.textContent = fmtRp(data.subtotal);
            if (discountEl) discountEl.textContent = '− ' + fmtRp(data.discount);
            if (discountRow) discountRow.classList.toggle('d-none', !(data.discount > 0));
            if (totalEl) totalEl.textContent = fmtRp(data.total);
        }

        function rerenderForm(applied) {
            // Toggle Apply ↔ Remove button + readonly state on the input
            const btn = applyForm.querySelector('button');
            if (applied) {
                input.value = applied;
                input.setAttribute('readonly', 'readonly');
                btn.id = 'couponRemoveBtn';
                btn.type = 'button';
                btn.textContent = 'Remove';
                btn.classList.remove('btn-dark');
                btn.classList.add('btn-outline-dark');
            } else {
                input.removeAttribute('readonly');
                btn.id = '';
                btn.type = 'submit';
                btn.textContent = 'Apply';
                btn.classList.remove('btn-outline-dark');
                btn.classList.add('btn-dark');
            }
        }

        applyForm.addEventListener('submit', async (e) => {
            const isRemove = e.submitter && e.submitter.id === 'couponRemoveBtn';
            e.preventDefault();
            block.classList.remove('is-success', 'is-error');

            try {
                const url = isRemove ? '/coupon/remove' : '/coupon/apply';
                const body = isRemove ? null : { code: input.value };
                const { data } = await ajaxPost(`${window.location.origin}${url}`, body);

                if (data.status === 'success') {
                    block.classList.add('is-success');
                    paintSummary(data);
                    rerenderForm(data.code || null);
                    if (hint) hint.textContent = data.message;
                    showNotification(data.message, 'success', { duration: 1800 });
                } else {
                    block.classList.add('is-error');
                    if (hint) hint.textContent = data.message || 'Could not apply coupon.';
                    showNotification(data.message || 'Coupon error', 'error');
                }
            } catch (err) {
                console.error(err);
                showNotification('Network error', 'error');
            }
        });

        // Wire the Remove button (rendered server-side too) via delegation
        applyForm.addEventListener('click', (e) => {
            const btn = e.target.closest('#couponRemoveBtn');
            if (!btn) return;
            applyForm.requestSubmit(btn);
        });
    })();

    // ─────────────────────────────────────────────────────────────
    // B9 — Saved address chip → hydrate checkout form
    // ─────────────────────────────────────────────────────────────
    document.addEventListener('click', async function (e) {
        const chip = e.target.closest('.address-chip');
        if (!chip) return;

        const id = chip.getAttribute('data-address-id');
        try {
            const { response, data } = await ajaxGet(`${window.location.origin}/account/addresses/${id}/fetch`);
            if (response.ok && data.status === 'success' && data.address) {
                document.querySelectorAll('.address-chip').forEach((c) => c.classList.remove('is-active'));
                chip.classList.add('is-active');

                const map = {
                    shipping_name: data.address.name,
                    shipping_phone: data.address.phone,
                    shipping_address: data.address.address,
                    shipping_city: data.address.city,
                    shipping_postal_code: data.address.postal_code,
                };
                Object.entries(map).forEach(([name, value]) => {
                    const field = document.querySelector(`[name="${name}"]`);
                    if (field) {
                        field.value = value;
                        field.dispatchEvent(new Event('input', { bubbles: true }));
                    }
                });
                showNotification('Address loaded', 'success', { duration: 1500 });
            }
        } catch (err) {
            console.error(err);
            showNotification('Could not load address', 'error');
        }
    });

    // ─────────────────────────────────────────────────────────────
    // B4 — Star rating input (delegated)
    // ─────────────────────────────────────────────────────────────
    (function initRatingInput() {
        document.querySelectorAll('[data-rating-input]').forEach((group) => {
            const stars = Array.from(group.querySelectorAll('.rating-input-star'));
            // RTL ordering means stars[0] is value 5, stars[4] is value 1.
            // Sort by input value ascending (1..5) for predictable highlight.
            stars.sort((a, b) => parseInt(a.querySelector('input').value, 10) - parseInt(b.querySelector('input').value, 10));

            function applyHighlight(value) {
                stars.forEach((star) => {
                    const v = parseInt(star.querySelector('input').value, 10);
                    star.classList.toggle('is-active', v <= value);
                });
            }

            const checked = group.querySelector('input:checked');
            if (checked) applyHighlight(parseInt(checked.value, 10));

            stars.forEach((star) => {
                const input = star.querySelector('input');
                star.addEventListener('mouseenter', () => applyHighlight(parseInt(input.value, 10)));
                star.addEventListener('click', () => {
                    input.checked = true;
                    applyHighlight(parseInt(input.value, 10));
                });
            });
            group.addEventListener('mouseleave', () => {
                const c = group.querySelector('input:checked');
                applyHighlight(c ? parseInt(c.value, 10) : 0);
            });
        });
    })();

    // ─────────────────────────────────────────────────────────────
    // B3 — Wishlist toggle (delegated)
    // ─────────────────────────────────────────────────────────────
    document.addEventListener('click', async function (e) {
        const btn = e.target.closest('[data-wishlist-toggle]');
        if (!btn || btn.classList.contains('is-loading')) return;
        e.preventDefault();
        e.stopPropagation();

        const productId = btn.getAttribute('data-wishlist-toggle');
        const icon = btn.querySelector('.bi');

        btn.classList.add('is-loading');

        try {
            const { data } = await ajaxPost(`${window.location.origin}/wishlist/toggle/${productId}`);
            if (data.status === 'success') {
                const isActive = data.state === 'added';
                btn.classList.toggle('is-active', isActive);
                btn.setAttribute('aria-label', isActive ? 'Remove from wishlist' : 'Save to wishlist');
                if (icon) {
                    icon.classList.toggle('bi-heart-fill', isActive);
                    icon.classList.toggle('bi-heart', !isActive);
                }
                btn.classList.add('pulse');
                setTimeout(() => btn.classList.remove('pulse'), 500);
                showNotification(data.message, 'success', { duration: 1800 });

                // Sync any other heart toggles for the same product on the page
                document.querySelectorAll(`[data-wishlist-toggle="${productId}"]`).forEach((el) => {
                    if (el === btn) return;
                    el.classList.toggle('is-active', isActive);
                    const otherIcon = el.querySelector('.bi');
                    if (otherIcon) {
                        otherIcon.classList.toggle('bi-heart-fill', isActive);
                        otherIcon.classList.toggle('bi-heart', !isActive);
                    }
                });
            } else {
                showNotification(data.message || 'Could not update wishlist', 'error');
            }
        } catch (err) {
            console.error(err);
            showNotification('Network error', 'error');
        } finally {
            btn.classList.remove('is-loading');
        }
    });

    // ─────────────────────────────────────────────────────────────
    // (Old simple cursor binding removed — superseded by A12 above)
    // ─────────────────────────────────────────────────────────────

    // Expose for debugging / future modules
    window.NexGear = window.NexGear || {};
    window.NexGear.ajaxPost = ajaxPost;
    window.NexGear.ajaxGet = ajaxGet;
    window.NexGear.showNotification = showNotification;
    window.NexGear.openSearchOverlay = openSearchOverlay;
})();

import * as bootstrap from 'bootstrap';
window.bootstrap = bootstrap;

// Initialize tooltips and popovers
document.addEventListener('DOMContentLoaded', () => {
    // Tooltips
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));

    // Toast Notification helper
    window.showToast = function(message, type = 'success') {
        let toastContainer = document.getElementById('toast-container');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.id = 'toast-container';
            toastContainer.className = 'toast-container position-fixed bottom-0 end-0 p-3';
            toastContainer.style.zIndex = '1090';
            document.body.appendChild(toastContainer);
        }

        const bgClass = type === 'success' ? 'text-bg-dark border-0' : (type === 'error' ? 'text-bg-danger' : 'text-bg-primary');
        const icon = type === 'success' ? 'bi-check-circle-fill text-success' : (type === 'error' ? 'bi-exclamation-triangle-fill' : 'bi-info-circle-fill');

        const toastEl = document.createElement('div');
        toastEl.className = `toast align-items-center shadow-lg ${bgClass}`;
        toastEl.setAttribute('role', 'alert');
        toastEl.setAttribute('aria-live', 'assertive');
        toastEl.setAttribute('aria-atomic', 'true');
        toastEl.innerHTML = `
            <div class="d-flex p-2">
                <div class="toast-body d-flex align-items-center gap-2">
                    <i class="bi ${icon} fs-5"></i>
                    <span>${message}</span>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        `;
        toastContainer.appendChild(toastEl);
        const bsToast = new bootstrap.Toast(toastEl, { delay: 4000 });
        bsToast.show();
        toastEl.addEventListener('hidden.bs.toast', () => toastEl.remove());
    };

    // Global AJAX Add to Cart Handler
    document.addEventListener('submit', async (e) => {
        const form = e.target.closest('.ajax-add-to-cart');
        if (!form) return;

        e.preventDefault();
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalHtml = submitBtn ? submitBtn.innerHTML : '';
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span> Agregando...';
        }

        try {
            const formData = new FormData(form);
            const response = await fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();
            if (data.success) {
                window.showToast(data.message, 'success');
                // Update badge counts
                document.querySelectorAll('.cart-badge-count').forEach(el => {
                    el.textContent = data.cartCount;
                    el.classList.remove('d-none');
                });

                // Update offcanvas cart if open or reload mini-cart
                if (typeof window.refreshMiniCart === 'function') {
                    window.refreshMiniCart();
                }

                // Open offcanvas cart drawer
                const offcanvasEl = document.getElementById('offcanvasCart');
                if (offcanvasEl && !form.dataset.noDrawer) {
                    const bsOffcanvas = bootstrap.Offcanvas.getOrCreateInstance(offcanvasEl);
                    bsOffcanvas.show();
                }
            } else {
                window.showToast(data.message || 'Error al agregar el producto', 'error');
            }
        } catch (error) {
            console.error('Error adding to cart:', error);
            window.showToast('Hubo un problema al procesar la solicitud', 'error');
        } finally {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalHtml;
            }
        }
    });

    // Refresh MiniCart function
    window.refreshMiniCart = async function() {
        try {
            const res = await fetch('/cart/mini', {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            });
            const data = await res.json();
            
            // Update counts
            document.querySelectorAll('.cart-badge-count').forEach(el => {
                el.textContent = data.cartCount;
                if (data.cartCount > 0) {
                    el.classList.remove('d-none');
                } else {
                    el.classList.add('d-none');
                }
            });

            const miniCartList = document.getElementById('offcanvasCartItems');
            const miniCartEmpty = document.getElementById('offcanvasCartEmpty');
            const miniCartFooter = document.getElementById('offcanvasCartFooter');
            const miniCartSubtotal = document.getElementById('offcanvasCartSubtotal');

            if (miniCartList && miniCartEmpty && miniCartFooter && miniCartSubtotal) {
                if (data.items.length === 0) {
                    miniCartList.innerHTML = '';
                    miniCartEmpty.classList.remove('d-none');
                    miniCartFooter.classList.add('d-none');
                } else {
                    miniCartEmpty.classList.add('d-none');
                    miniCartFooter.classList.remove('d-none');
                    const formatCOP = (num) => '$ ' + Math.round(num).toLocaleString('es-CO');
                    miniCartSubtotal.textContent = formatCOP(data.subtotal);

                    let html = '';
                    data.items.forEach(item => {
                        html += `
                            <div class="cart-item d-flex gap-3 align-items-center">
                                <img src="${item.image}" alt="${item.name}" class="rounded-3 object-fit-cover" style="width: 64px; height: 64px;">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1 text-truncate" style="max-width: 180px;">${item.name}</h6>
                                    <div class="text-muted small">${item.quantity} x <strong class="text-primary">${formatCOP(item.price)}</strong></div>
                                </div>
                                <button type="button" class="btn btn-sm text-danger remove-cart-item-btn" data-id="${item.id}" title="Eliminar">
                                    <i class="bi bi-trash3"></i>
                                </button>
                            </div>
                        `;
                    });
                    miniCartList.innerHTML = html;

                    // Bind delete handlers
                    miniCartList.querySelectorAll('.remove-cart-item-btn').forEach(btn => {
                        btn.addEventListener('click', async () => {
                            const id = btn.dataset.id;
                            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                            await fetch(`/cart/remove/${id}`, {
                                method: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': csrfToken,
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'Accept': 'application/json'
                                }
                            });
                            window.refreshMiniCart();
                        });
                    });
                }
            }
        } catch (e) {
            console.error('Error refreshing mini cart:', e);
        }
    };

    // Initialize mini cart bindings
    const offcanvasEl = document.getElementById('offcanvasCart');
    if (offcanvasEl) {
        offcanvasEl.addEventListener('show.bs.offcanvas', () => {
            window.refreshMiniCart();
        });
    }
});

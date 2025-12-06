// resources/js/cart.js

class CartDropdown {
    constructor() {
        this.isOpen = false;
        this.isLoading = false;
        this.lastCount = 0;
        this.init();
    }

    init() {
        this.cacheElements();
        this.bindEvents();
        this.loadInitialCount();
    }

    cacheElements() {
        this.dropdown = $('#cartDropdown');
        this.menu = $('.cart-dropdown-menu');
        this.itemsContainer = $('.cart-dropdown-items');
        this.countElement = $('.cart-count');
        this.countDisplay = $('.cart-count-display');
    }

    bindEvents() {
        $(document).off('click.cartDropdown').on('click.cartDropdown', '#cartDropdown', (e) => {
            e.preventDefault();
            e.stopImmediatePropagation();
            this.toggleDropdown();
        });

        $(document).on('click', (e) => {
            if (this.isOpen && !$(e.target).closest('.cart-dropdown').length) {
                this.closeDropdown();
            }
        });

        $(document).on('keyup', (e) => {
            if (e.key === 'Escape' && this.isOpen) this.closeDropdown();
        });

        this.menu.off('click').on('click', e => e.stopPropagation());
    }

    toggleDropdown() {
        this.isOpen ? this.closeDropdown() : this.openDropdown();
    }

    openDropdown() {
        this.isOpen = true;
        this.menu.fadeIn(200);
        this.loadSessionData();
    }

    closeDropdown() {
        this.isOpen = false;
        this.menu.fadeOut(200);
    }

    updateCountImmediately(count) {
        if (count === 0 && this.hasItemsInSession()) return;
        this.lastCount = count;
        $('.cart-count').text(count);
        $('.cart-count-display').text(`(${count})`);
        this.itemsContainer.attr('data-session-count', count);
    }

    hasItemsInSession() {
        const items = this.itemsContainer.attr('data-session-items');
        return items && items !== '[]' && items !== 'null';
    }

    loadInitialCount() {
        const count = this.itemsContainer.attr('data-session-count');
        if (count && parseInt(count) > 0) {
            this.updateCountImmediately(parseInt(count));
        }
    }

    loadSessionData() {
        const itemsJson = this.itemsContainer.attr('data-session-items');
        const total = this.itemsContainer.attr('data-session-total');
        const count = this.itemsContainer.attr('data-session-count');

        if (itemsJson && itemsJson !== '[]') {
            try {
                const items = JSON.parse(itemsJson);
                if (items.length > 0) {
                    this.renderItems(items, parseFloat(total));
                    this.updateCountImmediately(parseInt(count));
                    return;
                }
            } catch (e) { /* ignore */ }
        }

        this.loadFromServer();
    }

    async loadFromServer() {
        if (this.isLoading) return;
        this.isLoading = true;
        this.renderLoading();

        try {
            const res = await $.get('/cart/dropdown-items');
            if (res.success) {
                this.renderItems(res.items, res.total);
                this.updateCountImmediately(res.count);
                this.updateSessionData(res.items, res.total, res.count);
            } else {
                this.renderEmpty();
            }
        } catch (e) {
            this.renderEmpty();
        } finally {
            this.isLoading = false;
        }
    }

    updateSessionData(items, total, count) {
        this.itemsContainer
            .attr('data-session-items', JSON.stringify(items))
            .attr('data-session-total', total)
            .attr('data-session-count', count);
    }

    renderLoading() {
        this.itemsContainer.html(`
            <div class="text-center p-4 text-muted">
                <i class="fas fa-spinner fa-spin fa-2x mb-2"></i>
                <p>جاري التحميل...</p>
            </div>
        `);
    }

    renderItems(items, total) {
        const itemsHtml = items.map(item => `
            <div class="cart-dropdown-item d-flex align-items-center p-3 border-bottom">
                <img src="/storage/uploads/products/${item.image}" alt="${item.name}"
                     class="rounded" style="width:65px;height:65px;object-fit:cover;"
                     onerror="this.src='/images/placeholder.jpg'">
                <div class="flex-grow-1 ms-3">
                    <h6 class="mb-1 fw-bold">${item.name}</h6>
                    <small class="text-muted">${item.quantity} × ${item.unit_price} ج.م</small>
                    <strong class="text-pink d-block">${item.subtotal} ج.م</strong>
                </div>
            </div>
        `).join('');

        const footer = `
            <div class="p-3 border-top bg-light">
                <div class="d-flex justify-content-between mb-2">
                    <span class="fw-bold">الإجمالي:</span>
                    <span class="fw-bold text-pink">${parseFloat(total).toFixed(2)} ج.م</span>
                </div>
                <a href="/cart" class="btn btn-pink btn-sm w-100">عرض السلة</a>
            </div>
        `;

        this.itemsContainer.html(itemsHtml + footer);
    }

    renderEmpty() {
        this.itemsContainer.html(`
            <div class="text-center p-5 text-muted">
                <i class="fas fa-shopping-cart fa-2x mb-3"></i>
                <p>سلة التسوق فارغة</p>
            </div>
        `);
    }
}


window.addToCart = function(productId, quantity = 1, button = null) {
    if (!productId) return;

    const $btn = button ? $(button) : $(`.add-to-cart-btn[data-product-id="${productId}"]`);
    if ($btn.length === 0 || $btn.hasClass('loading')) return;

    $btn.addClass('loading').prop('disabled', true);

    $.ajax({
        url: `/cart/add/${productId}`,
        method: 'POST',
        data: {
            quantity: quantity,
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        success: function(res) {
            if (res.success) {
                // تحديث العداد فورًا
                if (window.cartDropdown) {
                    window.cartDropdown.updateCountOnly(res.cart_count);
                }

                // تحديث الـ dropdown إذا كان مفتوحًا
                if (window.cartDropdown && window.cartDropdown.isOpen) {
                    window.cartDropdown.refresh();
                }

                showToast(`تم إضافة "${res.product_name}" إلى السلة`, 'success');
            } else {
                showToast(res.message || 'فشل في الإضافة', 'error');
            }
        },
        error: function() {
            showToast('حدث خطأ في الاتصال', 'error');
        },
        complete: function() {
            $btn.removeClass('loading').prop('disabled', false);
        }
    });
};

// دالة الـ Toast (إذا مش موجودة)
function showToast(message, type = 'success') {
    const bg = type === 'success' ? 'bg-success' : 'bg-danger';
    const toast = $(`
        <div class="toast align-items-center text-white ${bg} border-0 position-fixed" style="top: 20px; right: 20px; z-index: 9999;">
            <div class="d-flex">
                <div class="toast-body">${message}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    `);
    $('body').append(toast);
    new bootstrap.Toast(toast[0], { delay: 3000 }).show();
    toast.on('hidden.bs.toast', () => toast.remove());
}

// cart.js - الملف الكامل مع التحديثات

// جعل الدوال عامة لتكون متاحة من أي مكان
window.initializeCart = function() {
    let isUpdating = false;

    // زيادة الكمية
    $(document).on('click', '.quantity-increase', function(e) {
        e.preventDefault();
        if (isUpdating) return;
        const input = $(this).siblings('.quantity-input');
        updateQuantity(input, 1);
    });

    // تقليل الكمية
    $(document).on('click', '.quantity-decrease', function(e) {
        e.preventDefault();
        if (isUpdating) return;
        const input = $(this).siblings('.quantity-input');
        updateQuantity(input, -1);
    });

    // إدخال يدوي
    $(document).on('change', '.quantity-input', function(e) {
        if (isUpdating) return;
        const quantity = parseInt($(this).val());
        if (quantity < 1) return $(this).val(1);
        if (quantity > 100) return $(this).val(100);
        updateCartItem($(this).data('item-id'), quantity, $(this));
    });

    // حذف العنصر
    $(document).on('click', '.remove-item-btn', function(e) {
        e.preventDefault();
        const itemId = $(this).data('item-id');
        const productName = $(this).closest('.cart-item').find('h6').text().trim();
        showDeleteConfirmation(itemId, productName);
    });

    // تفريغ السلة
    $(document).on('click', '#clear-cart-btn', function(e) {
        e.preventDefault();
        showClearCartConfirmation();
    });

    function updateQuantity(input, change) {
        const currentVal = parseInt(input.val());
        const newVal = Math.max(1, currentVal + change);
        input.val(newVal);
        updateCartItem(input.data('item-id'), newVal, input);
    }

    function updateCartItem(itemId, quantity, input) {
        isUpdating = true;

        const price = parseFloat(input.data('price'));
        const discount = parseFloat(input.data('discount')) || 0;
        const finalPrice = price - discount;
        const subtotal = finalPrice * quantity;

        input.closest('.cart-item').find('.item-subtotal').text(subtotal.toFixed(2) + ' ج.م');

        $.ajax({
            url: `/cart/update/${itemId}`,
            method: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                quantity: quantity
            },
            success: function(response) {
                if(response.success) {
                    updateCartSummary(response.cart_total, response.cart_count);
                    showToast('success', response.message);

                    // تحديث الـ UI الإضافي
                    if (response.item) {
                        input.closest('.cart-item').find('.item-subtotal').text(response.item.subtotal.toFixed(2) + ' ج.م');
                    }

                    sessionStorage.setItem('cart_count', response.cart_count);
                    updateCartCountUI(response.cart_count);
                } else {
                    showToast('error', response.message || 'حدث خطأ أثناء التحديث');
                }
            },
            error: function(xhr) {
                let errorMessage = 'حدث خطأ أثناء التحديث';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                showToast('error', errorMessage);

                // إعادة تحميل الصفحة في حالة الخطأ
                setTimeout(() => {
                    location.reload();
                }, 2000);
            },
            complete: function() {
                isUpdating = false;
            }
        });
    }

    function showDeleteConfirmation(itemId, productName) {
        Swal.fire({
            title: 'تأكيد الحذف',
            html: `<p>هل تريد حذف المنتج <strong>${productName}</strong> من السلة؟</p>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'نعم، احذف',
            cancelButtonText: 'إلغاء',
            confirmButtonColor: '#e91e63',
            cancelButtonColor: '#6c757d'
        }).then((result) => {
            if (result.isConfirmed) removeCartItem(itemId);
        });
    }

    function removeCartItem(itemId) {
        $.ajax({
            url: `/cart/remove/${itemId}`,
            method: 'DELETE',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if(response.success) {
                    updateCartSummary(
                        response.cart_subtotal,
                        response.cart_total_discount,
                        response.cart_grand_total,
                        response.cart_count
                    );

                    sessionStorage.setItem('cart_count', response.cart_count);
                    updateCartCountUI(response.cart_count);

                    $(`.remove-item-btn[data-item-id="${itemId}"]`).closest('.cart-item').fadeOut(300, function() {
                        $(this).remove();
                        checkEmptyCart();
                    });

                    showToast('success', 'تم حذف المنتج من السلة');
                }
            },
            error: function(xhr) {
                let errorMessage = 'حدث خطأ أثناء الحذف';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                showToast('error', errorMessage);
            }
        });
    }

   function checkEmptyCart() {
    if ($('.cart-item').length === 0) {
        $('#cart-items-container').html(`
            <div class="text-center text-light py-5">
                <i class="fas fa-shopping-cart fa-3x mb-3 text-muted"></i>
                <h4>سلة التسوق فارغة</h4>
                <p class="text-muted">لم تقم بإضافة أي منتجات إلى السلة بعد</p>
                <a href="/products" class="btn btn-primary mt-3">
                    <i class="fas fa-shopping-bag me-2"></i>
                    ابدأ التسوق
                </a>
            </div>
        `);
        $('#cart-summary').hide();
    }
}

    function showClearCartConfirmation() {
        Swal.fire({
            title: 'تأكيد تفريغ السلة',
            text: 'سيتم حذف جميع المنتجات من السلة',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'نعم، افرغ السلة',
            cancelButtonText: 'إلغاء',
            confirmButtonColor: '#e91e63',
            cancelButtonColor: '#6c757d'
        }).then((result) => {
            if (result.isConfirmed) clearCart();
        });
    }

    function clearCart() {
        $.ajax({
            url: `/cart/clear`,
            method: 'DELETE',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if(response.success) {
                    $('#cart-items-container').html(`
                        <div class="text-center text-light py-5">
                            <i class="fas fa-shopping-cart fa-3x mb-3 text-muted"></i>
                            <h4>سلة التسوق فارغة</h4>
                            <p class="text-muted">لم تقم بإضافة أي منتجات إلى السلة بعد</p>
                            <a href="{{ route('products.index') }}" class="btn btn-primary mt-3">
                                <i class="fas fa-shopping-bag me-2"></i>
                                ابدأ التسوق
                            </a>
                        </div>
                    `);
                    $('#cart-summary').hide();

                    sessionStorage.setItem('cart_count', 0);
                    updateCartCountUI(0);

                    showToast('success', 'تم تفريغ السلة بنجاح');
                }
            },
            error: function(xhr) {
                let errorMessage = 'حدث خطأ أثناء تفريغ السلة';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                showToast('error', errorMessage);
            }
        });
    }

    function updateCartSummary(cartTotal, cartCount) {
        $('#cart-subtotal').text(parseFloat(cartTotal).toFixed(2) + ' ج.م');
        $('#cart-grand-total').text(parseFloat(cartTotal).toFixed(2) + ' ج.م');
        $('.cart-count').text(cartCount);
    }

    // التحقق من السلة الفارغة عند التحميل
    checkEmptyCart();
};

// ✅ دالة عامة لتحديث العداد في أي مكان (navbar مثلاً)
window.updateCartCountUI = function(count) {
    // 1. تحديث جميع العناصر التي تحمل class cart-count
    $('.cart-count').each(function() {
        $(this).text(count);
    });

    // 2. تحديث cart-count-display إذا موجود
    $('.cart-count-display').text('(' + count + ')');

    // 3. تحديث data-session-count في الـ dropdown
    $('.cart-dropdown-items').attr('data-session-count', count);

    // 4. إذا كان العدد 0، أخفِ الـ badge (اختياري)
    if (count === 0) {
        $('.cart-count').hide();
    } else {
        $('.cart-count').show();
    }
};

// ✅ دالة عرض الرسائل
window.showToast = function(type, message) {
    // إزالة أي رسائل سابقة
    $('.custom-toast').remove();

    const positionClass = $('html').attr('dir') === 'rtl' ? 'start-0' : 'end-0';

    // ✅ تحديد اللون والأيقونة حسب النوع
    let bgColor = 'primary';
    let icon = 'fa-info-circle';

    switch(type) {
        case 'success':
            bgColor = 'success';
            icon = 'fa-check-circle';
            break;
        case 'error':
            bgColor = 'danger';
            icon = 'fa-exclamation-circle';
            break;
        case 'warning':
            bgColor = 'warning';
            icon = 'fa-exclamation-triangle';
            break;
        case 'info':
            bgColor = 'info';
            icon = 'fa-info-circle';
            break;
        default:
            bgColor = 'primary';
            icon = 'fa-info-circle';
    }

    // ✅ إذا كانت الرسالة عن المخزون حتى لو النوع 'error'، حولها لـ 'warning'
    if (message.includes('غير متوفر') || message.includes('مخزون') || message.includes('غير متاح')) {
        bgColor = 'warning';
        icon = 'fa-boxes';
    }

    const toastHtml = `
        <div class="custom-toast toast align-items-center text-white bg-${bgColor} border-0 position-fixed bottom-0 ${positionClass} m-3" role="alert" style="z-index: 9999;">
            <div class="d-flex">
                <div class="toast-body">
                    <i class="fas ${icon} me-2"></i>
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    `;

    $('body').append(toastHtml);
    const toastElement = $('.custom-toast').last();

    const bsToast = new bootstrap.Toast(toastElement[0], {
        autohide: true,
        delay: 3000
    });

    bsToast.show();

    toastElement.on('hidden.bs.toast', function() {
        $(this).remove();
    });
};

// ✅ دالة إضافة منتج للسلة - النسخة النهائية
// ✅ دالة إضافة منتج للسلة - التحديث الفوري
window.addToCart = function(productId, productName, button = null, quantity = 1) {
    let originalHTML = '';
    if (button) {
        originalHTML = button.innerHTML;
        button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>جاري الإضافة...';
        button.disabled = true;
    }

    // ✅ احصل على اللغة الحالية من الـ HTML أو الـ URL
    const getCurrentLocale = function() {
        // 1. من الـ html tag
        const htmlLang = document.documentElement.lang;
        if (htmlLang && (htmlLang === 'ar' || htmlLang === 'en')) {
            return htmlLang;
        }

        // 2. من الـ URL
        const path = window.location.pathname;
        const localeFromUrl = path.split('/')[1];
        if (localeFromUrl === 'ar' || localeFromUrl === 'en') {
            return localeFromUrl;
        }

        // 3. من الـ meta tag
        const metaLocale = document.querySelector('meta[name="locale"]');
        if (metaLocale && metaLocale.content) {
            return metaLocale.content;
        }

        // 4. افتراضي عربي
        return 'ar';
    };

    const currentLocale = getCurrentLocale();

    // ✅ بناء الـ URL مع الـ locale
    const url = `/${currentLocale}/cart/add/${productId}`;

    $.ajax({
        url: url, // ✅ استخدم الـ URL مع الـ locale
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-Localization': currentLocale // ✅ أضف header للغة
        },
        data: {
            _token: $('meta[name="csrf-token"]').attr('content'),
            quantity: quantity
        },
        success: function(response) {
            if (response.success) {
                // ✅ التحديث الفوري المباشر للعداد
                const newCount = response.cart_count;
                // 1. تحديث جميع عناصر الـ cart-count في الصفحة
                document.querySelectorAll('.cart-count').forEach(element => {
                    element.textContent = newCount;
                });

                // 2. تحديث الـ cart-count-display
                document.querySelectorAll('.cart-count-display').forEach(element => {
                    element.textContent = '(' + newCount + ')';
                });

                // 3. تحديث data-session-count
                const dropdownItems = document.querySelector('.cart-dropdown-items');
                if (dropdownItems) {
                    dropdownItems.setAttribute('data-session-count', newCount);
                }

                // 4. حفظ في sessionStorage
                sessionStorage.setItem('cart_count', newCount);

                // 5. إظهار الـ badge إذا كان مخفي
                document.querySelectorAll('.cart-count').forEach(element => {
                    element.style.display = 'flex';
                });

                // ✅ حالة النجاح للزر
                if (button) {
                    button.innerHTML = '<i class="fas fa-check me-2"></i>تمت الإضافة!';
                    button.style.background = '#28a745';

                    setTimeout(() => {
                        button.innerHTML = originalHTML;
                        button.disabled = false;
                        button.style.background = '';
                    }, 2000);
                }

                // ✅ استخدم الـ product_name من الـ response بدل parameter
                const finalProductName = response.product_name || productName;
                showToast('success', `تم إضافة "${finalProductName}" إلى السلة`);

            } else {
                // ✅ معالجة الأخطاء
                let toastType = 'error';
                let errorMessage = response.message;

                if (response.message && (response.message.includes('غير متوفر') || response.message.includes('مخزون'))) {
                    toastType = 'warning';
                    errorMessage = 'المنتج غير متوفر في المخزن حالياً';
                }

                if (button) {
                    button.innerHTML = originalHTML;
                    button.disabled = false;
                }

                showToast(toastType, errorMessage);
            }
        },
        error: function(xhr) {
            console.error('❌ خطأ في الإضافة:', xhr);

            if (button) {
                button.innerHTML = originalHTML;
                button.disabled = false;
            }

            showToast('error', 'حدث خطأ في الإتصال بالخادم');
        }
    });
};
// ✅ دالة الحصول على عدد العناصر في السلة
window.getCartCount = function() {
    return parseInt(sessionStorage.getItem('cart_count')) || 0;
};

// ✅ دالة تحديث عداد السلة من السيرفر
window.refreshCartCount = function() {
    $.ajax({
        url: '/cart/count',
        method: 'GET',
        success: function(response) {
            if (response.count !== undefined) {
                sessionStorage.setItem('cart_count', response.count);
                updateCartCountUI(response.count);
            }
        },
        error: function() {
            console.error('فشل في تحديث عداد السلة');
        }
    });
};

// ✅ دالة تحميل عناصر السلة للقائمة المنسدلة
window.loadCartDropdown = function() {
    $.ajax({
        url: '/cart/dropdown-items',
        method: 'GET',
        success: function(response) {
            if (response.success) {
                updateCartDropdown(response.items, response.total, response.count);
            }
        },
        error: function() {
            console.error('فشل في تحميل عناصر السلة');
        }
    });
};

// ✅ دالة تحديث القائمة المنسدلة للسلة
function updateCartDropdown(items, total, count) {
    const dropdown = $('#cart-dropdown');
    if (!dropdown.length) return;

    let itemsHtml = '';

    if (items && items.length > 0) {
        items.forEach(item => {
            itemsHtml += `
                <div class="dropdown-item cart-dropdown-item">
                    <div class="d-flex align-items-center">
                        <img src="${item.image}" alt="${item.name}" class="rounded" width="40" height="40">
                        <div class="ms-3 flex-grow-1">
                            <h6 class="mb-0">${item.name}</h6>
                            <small class="text-muted">${item.quantity} × ${item.price} ج.م</small>
                        </div>
                        <button class="btn btn-sm btn-outline-danger remove-dropdown-item" data-item-id="${item.id}">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            `;
        });

        itemsHtml += `
            <div class="dropdown-divider"></div>
            <div class="px-3 py-2">
                <div class="d-flex justify-content-between align-items-center">
                    <strong>المجموع:</strong>
                    <span class="text-primary">${parseFloat(total).toFixed(2)} ج.م</span>
                </div>
                <div class="d-grid gap-2 mt-2">
                    <a href="/cart" class="btn btn-primary btn-sm">
                        <i class="fas fa-shopping-cart me-1"></i>
                        عرض السلة
                    </a>
                    <a href="/checkout" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-credit-card me-1"></i>
                        اتمام الشراء
                    </a>
                </div>
            </div>
        `;
    } else {
        itemsHtml = `
            <div class="text-center py-3">
                <i class="fas fa-shopping-cart fa-2x text-muted mb-2"></i>
                <p class="text-muted mb-0">السلة فارغة</p>
            </div>
        `;
    }

    dropdown.html(itemsHtml);

    // إضافة event listeners للعناصر في القائمة المنسدلة
    $('.remove-dropdown-item').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        const itemId = $(this).data('item-id');
        removeCartItem(itemId);
    });
}

// التهيئة عند تحميل الصفحة
$(document).ready(function() {
    // امسح كل الـ events القديمة الخاصة بالكرت
    $(document).off('click', '.remove-item-btn');
    $(document).off('click', '.quantity-increase');
    $(document).off('click', '.quantity-decrease');
    $(document).off('change', '.quantity-input');
    $(document).off('click', '#clear-cart-btn');
    $(document).off('click', '.remove-dropdown-item');

    // ثم شغل الكود الجديد
    window.initializeCart();

    // ✅ تحميل العداد المخزن عند فتح الصفحة
    const savedCount = sessionStorage.getItem('cart_count');
    if (savedCount !== null) {
        window.updateCartCountUI(parseInt(savedCount));
    } else {
        // إذا لم يكن هناك قيمة مخزنة، جلبها من السيرفر
        window.refreshCartCount();
    }

    // ✅ تحميل عناصر السلة للقائمة المنسدلة
    window.loadCartDropdown();
});

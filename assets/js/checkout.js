jQuery(document).ready(function($) {

    function refreshCheckout($checkout) {
        var $qtyInput = $checkout.find('.wpb-qty-input');
        var productId = $qtyInput.data('product-id');
        var cartKey = $qtyInput.data('cart-key');
        var quantity = $qtyInput.val();

        $.post(wpbAdmin.ajaxUrl, {
            action: 'wpb_update_cart_quantity',
            _wpnonce: wpbAdmin.toggleNonce,
            product_id: productId || 0,
            cart_item_key: cartKey || '',
            quantity: quantity
        })
        .done(function(response) {
            if (response.success) {
                if (typeof wpbUpdateCart === 'function') {
                    wpbUpdateCart();
                }
                var $totals = $checkout.find('.wpb-cart-totals');
                if ($totals.length && response.data) {
                    if (response.data.total) {
                        $totals.find('.wpb-cart-total:last-child span:last-child').html(response.data.total);
                    }
                    if (response.data.subtotal) {
                        $totals.find('.wpb-cart-total:first-child span:last-child').html(response.data.subtotal);
                    }
                }
            }
        })
        .fail(function() {
            if (typeof wpbUpdateCart === 'function') {
                wpbUpdateCart();
            }
        });
    }

    $(document).on('click', '.wpb-qty-plus', function(e) {
        e.preventDefault();
        var $input = $(this).siblings('.wpb-qty-input');
        var max = parseInt($input.attr('max'));
        var current = parseInt($input.val());
        if (max && current >= max) return;
        $input.val(current + 1);
        var $checkout = $input.closest('.wpb-checkout-form');
        refreshCheckout($checkout);
    });

    $(document).on('click', '.wpb-qty-minus', function(e) {
        e.preventDefault();
        var $input = $(this).siblings('.wpb-qty-input');
        var current = parseInt($input.val());
        if (current <= 1) return;
        $input.val(current - 1);
        var $checkout = $input.closest('.wpb-checkout-form');
        refreshCheckout($checkout);
    });

    $(document).on('change', '.wpb-qty-input', function() {
        var $input = $(this);
        var min = parseInt($input.attr('min'));
        var max = parseInt($input.attr('max'));
        var value = parseInt($input.val()) || min;
        if (min && value < min) value = min;
        if (max && value > max) value = max;
        $input.val(value);
        var $checkout = $input.closest('.wpb-checkout-form');
        refreshCheckout($checkout);
    });

    $(document).on('submit', '.wpb-checkout-form-inner', function(e) {
        e.preventDefault();
        var $form = $(this);
        var $checkout = $form.closest('.wpb-checkout-form');
        var $message = $checkout.find('.wpb-checkout-message');
        var $submit = $form.find('.wpb-checkout-submit');

        $submit.prop('disabled', true).addClass('loading');
        $message.hide().removeClass('success error');

        var formData = new FormData($form[0]);
        formData.append('action', 'wpb_quick_checkout');
        formData.append('wpb_checkout_nonce', $form.find('input[name="wpb_checkout_nonce"]').val());

        $.ajax({
            url: wpbAdmin.ajaxUrl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                $submit.prop('disabled', false).removeClass('loading');
                if (response.success) {
                    $message.removeClass('error').addClass('success').text(response.data.message || 'Order placed successfully!').show();
                    if (response.data.redirect) {
                        setTimeout(function() {
                            window.location.href = response.data.redirect;
                        }, 1500);
                    }
                } else {
                    $message.removeClass('success').addClass('error').text(response.data.message || 'Something went wrong.').show();
                }
            },
            error: function(xhr, status, error) {
                $submit.prop('disabled', false).removeClass('loading');
                var errMsg = 'Network error. Please try again.';
                if (xhr && xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) {
                    errMsg = xhr.responseJSON.data.message;
                } else if (xhr && xhr.statusText) {
                    errMsg = 'Request failed: ' + xhr.statusText;
                }
                $message.removeClass('success').addClass('error').text(errMsg).show();
            }
        });
    });
});
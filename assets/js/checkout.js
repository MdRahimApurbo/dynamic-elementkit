(function($) {
    'use strict';

    function splitFullName($form) {
        var fullName = $.trim($form.find('[name="billing_full_name"]').val() || '');
        var parts = fullName.split(/\s+/).filter(Boolean);
        var firstName = parts.shift() || '';
        var lastName = parts.join(' ');

        $form.find('[name="billing_first_name"]').val(firstName);
        $form.find('[name="billing_last_name"]').val(lastName);
    }

    function setOrderButtonLoading($form, isLoading) {
        var $button = $form.find('#place_order');

        $button
            .toggleClass('wpb-order-loading', isLoading)
            .prop('disabled', isLoading)
            .attr('aria-busy', isLoading ? 'true' : 'false');
    }

    function setOrderStatus($form, type, message) {
        var $status = $form.find('.wpb-order-status');

        if (!$status.length) {
            return;
        }

        $status
            .removeClass('is-processing is-error')
            .addClass(type ? 'is-' + type : '')
            .text(message || '')
            .prop('hidden', !message);
    }

    function showCheckoutError($checkout, message) {
        var $group = $checkout.find('.woocommerce-NoticeGroup-checkout');
        var safeMessage = $('<div>').text(message).html();

        $group.html(
            '<ul class="woocommerce-error" role="alert"><li>' + safeMessage + '</li></ul>'
        );
    }

    function updateSummary($checkout, data) {
        var fields = {
            subtotal: data.subtotal,
            shipping: data.shipping,
            discount: data.discount,
            total: data.total
        };

        $.each(fields, function(row, value) {
            if (typeof value !== 'undefined') {
                $checkout.find('[data-total-row="' + row + '"] strong').html(value);
            }
        });

        if (data.item_subtotal && data.cart_item_key) {
            $checkout
                .find('.wpb-cart-item[data-cart-key="' + data.cart_item_key + '"] .wpb-cart-item-line-total')
                .html(data.item_subtotal);
        }

        if (typeof data.coupons !== 'undefined') {
            var $coupons = $checkout.find('[data-coupon-codes]');
            $coupons.text(data.coupons).prop('hidden', !data.coupons);
        }

        if (typeof data.has_shipping_methods !== 'undefined') {
            var hasShippingMethods = Boolean(data.has_shipping_methods);
            $checkout
                .find('[data-shipping-methods]')
                .html(hasShippingMethods ? (data.shipping_methods_html || '') : '');
            $checkout.find('[data-shipping-section]').prop('hidden', !hasShippingMethods);
            $checkout.find('[data-total-row="shipping"]').prop('hidden', !hasShippingMethods);
        }
    }

    function reloadSummary($checkout) {
        $.post(wpbAdmin.ajaxUrl, {
            action: 'wpb_get_checkout_summary',
            _wpnonce: wpbAdmin.toggleNonce
        }).done(function(response) {
            if (response && response.success) {
                updateSummary($checkout, response.data || {});
            }
        });
    }

    function refreshCheckout($checkout, $input) {
        var cartKey = $input.data('cart-key') || '';
        var quantity = parseInt($input.val(), 10) || 1;

        $checkout.addClass('wpb-is-updating');

        $.post(wpbAdmin.ajaxUrl, {
            action: 'wpb_update_cart_quantity',
            _wpnonce: wpbAdmin.toggleNonce,
            cart_item_key: cartKey,
            quantity: quantity
        })
        .done(function(response) {
            if (!response || !response.success) {
                showCheckoutError(
                    $checkout,
                    response && response.data && response.data.message
                        ? response.data.message
                        : 'Unable to update the cart.'
                );
                return;
            }

            updateSummary($checkout, response.data || {});
            $(document.body).trigger('update_checkout');
            $(document.body).trigger('wc_fragment_refresh');
        })
        .fail(function(xhr) {
            var message = xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message
                ? xhr.responseJSON.data.message
                : 'Unable to update the cart. Please refresh and try again.';
            showCheckoutError($checkout, message);
        })
        .always(function() {
            $checkout.removeClass('wpb-is-updating');
        });
    }

    $(document).on('input change', '.wpb-checkout-form [name="billing_full_name"]', function() {
        splitFullName($(this).closest('form.checkout'));
    });

    $(document).on('click', '.wpb-qty-plus, .wpb-qty-minus', function(event) {
        event.preventDefault();

        var $button = $(this);
        var $input = $button.siblings('.wpb-qty-input');
        var current = parseInt($input.val(), 10) || 1;
        var min = parseInt($input.attr('min'), 10) || 1;
        var max = parseInt($input.attr('max'), 10) || 999;
        var next = $button.hasClass('wpb-qty-plus') ? current + 1 : current - 1;

        next = Math.max(min, Math.min(max, next));
        if (next === current) {
            return;
        }

        $input.val(next);
        refreshCheckout($input.closest('.wpb-checkout-form'), $input);
    });

    $(document).on('change', '.wpb-checkout-form .wpb-qty-input', function() {
        var $input = $(this);
        var min = parseInt($input.attr('min'), 10) || 1;
        var max = parseInt($input.attr('max'), 10) || 999;
        var value = parseInt($input.val(), 10) || min;

        value = Math.max(min, Math.min(max, value));
        $input.val(value);
        refreshCheckout($input.closest('.wpb-checkout-form'), $input);
    });

    $(document.body).on('updated_checkout', function() {
        $('.wpb-checkout-form').each(function() {
            reloadSummary($(this));
        });
    });

    $(document.body).on('checkout_error', function() {
        $('form.wpb-checkout-form-inner').each(function() {
            var $form = $(this);
            var $status = $form.find('.wpb-order-status');
            var message = $.trim($form.find('.woocommerce-error li').first().text()) || $status.data('error-text');

            setOrderButtonLoading($form, false);
            setOrderStatus($form, 'error', message);
        });
    });

    $(function() {
        $('form.wpb-checkout-form-inner').each(function() {
            var $form = $(this);
            splitFullName($form);

            // WooCommerce fires this event with triggerHandler(), so it must
            // be registered directly on the form rather than delegated.
            $form.on('checkout_place_order.wpbCheckout', function() {
                splitFullName($form);
                setOrderButtonLoading($form, true);
                setOrderStatus(
                    $form,
                    'processing',
                    $form.find('.wpb-order-status').data('processing-text')
                );

                // A payment gateway can cancel submission after this handler.
                // Restore the button when WooCommerce did not enter processing.
                window.setTimeout(function() {
                    if (!$form.hasClass('processing')) {
                        setOrderButtonLoading($form, false);
                        setOrderStatus($form, '', '');
                    }
                }, 0);

                return true;
            });
        });
    });
})(jQuery);

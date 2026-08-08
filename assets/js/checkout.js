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
        var cartIsEmpty = $form.closest('.wpb-checkout-form').attr('data-cart-empty') === '1';

        $button
            .toggleClass('wpb-order-loading', isLoading)
            .prop('disabled', isLoading || cartIsEmpty)
            .attr('aria-disabled', cartIsEmpty ? 'true' : 'false')
            .attr('aria-busy', isLoading ? 'true' : 'false');
    }

    function setCheckoutEmptyState($checkout, isEmpty) {
        var $form = $checkout.find('form.wpb-checkout-form-inner');

        $checkout
            .toggleClass('wpb-checkout-empty', isEmpty)
            .attr('data-cart-empty', isEmpty ? '1' : '0');
        $form.find('#place_order')
            .prop('disabled', isEmpty)
            .attr('aria-disabled', isEmpty ? 'true' : 'false');
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

    function getCheckoutContext($checkout) {
        var context = {
            dek_checkout_widget: 1,
            dek_checkout_cart_mode: $checkout.find('[name="dek_checkout_cart_mode"]').val() || 'cart',
            dek_checkout_product_id: $checkout.find('[name="dek_checkout_product_id"]').val() || 0,
            dek_checkout_product_quantity: $checkout.find('[name="dek_checkout_product_quantity"]').val() || 1,
            dek_checkout_nonce: $checkout.find('[name="dek_checkout_nonce"]').val() || '',
            dek_checkout_payload: $checkout.find('[name="dek_checkout_payload"]').val() || '',
            dek_checkout_signature: $checkout.find('[name="dek_checkout_signature"]').val() || ''
        };

        $checkout.find('[name^="shipping_method"]:checked').each(function() {
            context[$(this).attr('name')] = $(this).val();
        });

        return context;
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

        if (typeof data.checkout_payload !== 'undefined') {
            $checkout.find('[name="dek_checkout_payload"]').val(data.checkout_payload);
        }
        if (typeof data.checkout_signature !== 'undefined') {
            $checkout.find('[name="dek_checkout_signature"]').val(data.checkout_signature);
        }
    }

    function reloadSummary($checkout) {
        $.post(dekAdmin.ajaxUrl, $.extend({
            action: 'dek_get_checkout_summary'
        }, getCheckoutContext($checkout))).done(function(response) {
            if (response && response.success) {
                updateSummary($checkout, response.data || {});
            }
        });
    }

    function refreshCheckout($checkout, $input) {
        var cartKey = $input.data('cart-key') || '';
        var quantity = parseInt($input.val(), 10) || 1;

        $checkout.addClass('wpb-is-updating');

        $.post(dekAdmin.ajaxUrl, $.extend({
            action: 'dek_update_cart_quantity',
            cart_item_key: cartKey,
            quantity: quantity
        }, getCheckoutContext($checkout)))
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
        splitFullName($(this).closest('form.wpb-checkout-form-inner'));
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

    $(document).on('change', '.wpb-checkout-form [name^="shipping_method"]', function() {
        reloadSummary($(this).closest('.wpb-checkout-form'));
    });

    $(document).on('click', '.wpb-checkout-form .wpb-checkout-remove-item', function(event) {
        event.preventDefault();

        var $button = $(this);
        var $checkout = $button.closest('.wpb-checkout-form');

        if ($checkout.hasClass('wpb-is-updating')) {
            return;
        }

        $checkout.addClass('wpb-is-updating');
        $button.prop('disabled', true).attr('aria-busy', 'true');

        $.post(dekAdmin.ajaxUrl, $.extend({
            action: 'dek_remove_checkout_item',
            cart_item_key: $button.data('cart-key') || ''
        }, getCheckoutContext($checkout)))
        .done(function(response) {
            if (!response || !response.success) {
                showCheckoutError(
                    $checkout,
                    response && response.data && response.data.message
                        ? response.data.message
                        : 'Unable to remove the product.'
                );
                return;
            }

            if (response.data.order_review_html) {
                $checkout.find('.wpb-order-review').replaceWith(response.data.order_review_html);
            }

            updateSummary($checkout, response.data || {});
            setCheckoutEmptyState($checkout, Boolean(response.data.is_empty));
            $(document.body).trigger('wc_fragment_refresh');

            if (!response.data.is_empty) {
                $(document.body).trigger('update_checkout');
            }
        })
        .fail(function(xhr) {
            var message = xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message
                ? xhr.responseJSON.data.message
                : 'Unable to remove the product. Please try again.';
            showCheckoutError($checkout, message);
        })
        .always(function() {
            $checkout.removeClass('wpb-is-updating');
            $button.prop('disabled', false).attr('aria-busy', 'false');
        });
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
            var $checkout = $form.closest('.wpb-checkout-form');
            splitFullName($form);
            setCheckoutEmptyState($checkout, $checkout.attr('data-cart-empty') === '1');

            $form.on('submit.wpbCustomCheckout', function(event) {
                event.preventDefault();

                if ($checkout.attr('data-cart-empty') === '1') {
                    return false;
                }

                splitFullName($form);
                setOrderButtonLoading($form, true);
                setOrderStatus(
                    $form,
                    'processing',
                    $form.find('.wpb-order-status').data('processing-text')
                );

                var requestData = $form.serializeArray();
                requestData.push({
                    name: 'action',
                    value: 'dek_process_custom_checkout'
                });

                $.ajax({
                    url: dekAdmin.ajaxUrl,
                    type: 'POST',
                    dataType: 'json',
                    data: requestData,
                    xhrFields: {
                        withCredentials: true
                    }
                })
                .done(function(response) {
                    if (response && response.success && response.data && response.data.redirect) {
                        window.location.assign(response.data.redirect);
                        return;
                    }

                    var message = response && response.data && response.data.message
                        ? response.data.message
                        : 'Unable to place the order. Please try again.';
                    showCheckoutError($checkout, message);
                    setOrderStatus($form, 'error', message);
                })
                .fail(function(xhr) {
                    var message = xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message
                        ? xhr.responseJSON.data.message
                        : 'Unable to place the order. Please try again.';
                    showCheckoutError($checkout, message);
                    setOrderStatus($form, 'error', message);
                })
                .always(function() {
                    setOrderButtonLoading($form, false);
                });

                return false;
            });
        });
    });
})(jQuery);

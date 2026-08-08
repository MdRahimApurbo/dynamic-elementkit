jQuery(document).ready(function($) {

    function refreshCart($cart) {
        var ajaxUrl = typeof dekAdmin !== 'undefined' ? dekAdmin.ajaxUrl : '';
        if (!ajaxUrl) return;

        var nonce = $cart.data('nonce') || (typeof dekAdmin !== 'undefined' ? dekAdmin.toggleNonce : '');

        $.post(ajaxUrl, {
            action: 'dek_get_cart_fragments',
            _wpnonce: nonce,
            empty_text: $cart.data('empty-text') || '',
            continue_text: $cart.data('continue-text') || '',
            subtotal_text: $cart.data('subtotal-text') || '',
            shipping_text: $cart.data('shipping-text') || '',
            checkout_text: $cart.data('checkout-text') || ''
        })
        .done(function(response) {
            if (response.success && response.data) {
                var data = response.data;
                var $count = $cart.find('.wpb-cart-count');
                if ($count.length) {
                    if (data.count > 0) {
                        $count.text(data.count).css('display', 'inline-flex');
                    } else {
                        $count.hide();
                    }
                }
                $cart.find('.wpb-cart-subtotal').html(data.subtotal);
                $cart.find('.wpb-cart-subtotal-trigger').html(data.subtotal);
                if (typeof data.items_html !== 'undefined') {
                    $cart.find('.wpb-cart-items').html(data.items_html);
                }
                if (typeof data.footer_html !== 'undefined') {
                    var $currentFooter = $cart.find('.wpb-cart-panel-footer, .wpb-cart-panel-footer-empty');
                    if ($currentFooter.length) {
                        $currentFooter.replaceWith(data.footer_html);
                    } else {
                        $cart.find('.wpb-cart-panel').append(data.footer_html);
                    }
                }
            }
        });
    }

    function closeAllPanels() {
        $('.wpb-cart.wpb-cart-open').removeClass('wpb-cart-open');
        $('.wpb-cart-trigger').attr('aria-expanded', 'false');
        $('body').removeClass('wpb-cart-panel-open');
    }

    $(document).on('click', '.wpb-cart-trigger', function(e) {
        e.preventDefault();
        e.stopPropagation();

        var $cart = $(this).closest('.wpb-cart');
        var style = $cart.data('cart-style') || 'panel';

        if (style === 'inline') {
            return;
        }

        var isOpen = $cart.hasClass('wpb-cart-open');
        closeAllPanels();

        if (!isOpen) {
            $cart.addClass('wpb-cart-open');
            $cart.find('.wpb-cart-trigger').attr('aria-expanded', 'true');
            $('body').addClass('wpb-cart-panel-open');
            refreshCart($cart);
        }
    });

    // Close button for slide-in panel
    $(document).on('click', '.wpb-cart-close', function(e) {
        e.preventDefault();
        closeAllPanels();
    });

    // Click overlay to close panel
    $(document).on('click', '.wpb-cart-panel-overlay', function(e) {
        e.preventDefault();
        closeAllPanels();
    });

    // Click outside to close
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.wpb-cart').length) {
            closeAllPanels();
        }
    });

    // ESC to close panel
    $(document).on('keydown', function(e) {
        if (e.key === 'Escape') {
            closeAllPanels();
        }
    });

    // Remove item
    $(document).on('click', '.wpb-cart-remove', function(e) {
        e.preventDefault();
        e.stopPropagation();
        var $cart = $(this).closest('.wpb-cart');
        var key = $(this).data('key');
        var $item = $(this).closest('.wpb-cart-item');

        if (!key) {
            return;
        }

        $item.css('opacity', '.5');

        var ajaxUrl = typeof dekAdmin !== 'undefined' ? dekAdmin.ajaxUrl : '';
        var nonce = $cart.data('nonce') || (typeof dekAdmin !== 'undefined' ? dekAdmin.toggleNonce : '');

        $.post(ajaxUrl, {
            action: 'dek_cart_remove_item',
            cart_item_key: key,
            _wpnonce: nonce
        })
        .done(function() {
            if (typeof wpbUpdateCart === 'function') {
                wpbUpdateCart();
            }
            refreshCart($cart);
        });
    });

    // Keep mini-cart in sync with global WooCommerce cart updates
    if (typeof wc_cart_fragments_params !== 'undefined') {
        $(document.body).on('wc_fragments_refreshed', function() {
            $('.wpb-cart').each(function() {
                refreshCart($(this));
            });
        });
    }

    // Sync cross-widget count after any add-to-cart
    $(document).ajaxComplete(function(event, xhr, settings) {
        if (settings && settings.data && typeof settings.data === 'string' &&
            (settings.data.indexOf('dek_add_to_cart') !== -1 ||
             settings.data.indexOf('dek_update_cart_quantity') !== -1)) {
            $('.wpb-cart').each(function() {
                refreshCart($(this));
            });
        }
    });
});

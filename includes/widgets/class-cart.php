<?php
if (!defined('ABSPATH')) exit;

class WPB_Cart_Widget extends \Elementor\Widget_Base {

    public function get_name() {
        return 'wpb-cart';
    }

    public function get_title() {
        return __('Cart Icon', 'woocommerce-page-builder');
    }

    public function get_icon() {
        return 'eicon-shopping-cart';
    }

    public function get_categories() {
        return ['wpb-woo-page-builder'];
    }

    public function get_keywords() {
        return ['cart', 'icon', 'shop'];
    }

    protected function register_controls() {
        $this->start_controls_section(
            'content_section',
            [
                'label' => __('Content', 'woocommerce-page-builder'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'cart_text',
            [
                'label' => __('Cart Text', 'woocommerce-page-builder'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => 'Cart',
            ]
        );

        $this->add_control(
            'show_count',
            [
                'label' => __('Show Count', 'woocommerce-page-builder'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'woocommerce-page-builder'),
                'label_off' => __('No', 'woocommerce-page-builder'),
                'return' => false,
                'default' => 'yes',
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'icon_style_section',
            [
                'label' => __('Cart Icon', 'woocommerce-page-builder'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'icon_size',
            [
                'label' => __('Icon Size', 'woocommerce-page-builder'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'default' => [
                    'size' => 24,
                ],
                'range' => [
                    'px' => [
                        'min' => 12,
                        'max' => 64,
                    ],
                ],
                'selectors' => [
                    '{{SELECTOR}} .wpb-cart-trigger svg' => 'width: {{SIZE}}{{UNIT}} !important; height: {{SIZE}}{{UNIT}} !important;',
                ],
            ]
        );

        $this->add_control(
            'icon_color',
            [
                'label' => __('Icon Color', 'woocommerce-page-builder'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#222222',
                'selectors' => [
                    '{{SELECTOR}} .wpb-cart-trigger svg path' => 'fill: {{VALUE}} !important;',
                    '{{SELECTOR}} .wpb-cart-trigger svg' => 'color: {{VALUE}} !important;',
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        $cart_text = !empty($settings['cart_text']) ? $settings['cart_text'] : 'Cart';
        $show_count = !empty($settings['show_count']) ? $settings['show_count'] : 'yes';
        $icon_size = !empty($settings['icon_size']['size']) ? absint($settings['icon_size']['size']) : 24;
        $icon_color = !empty($settings['icon_color']) ? sanitize_hex_color($settings['icon_color']) : '#222222';
        $count = class_exists('WooCommerce') && WC()->cart ? WC()->cart->get_cart_contents_count() : 0;
        $nonce = wp_create_nonce('wpb_toggle_active');
        if (!class_exists('WooCommerce') || !WC()->cart || WC()->cart->is_empty()) {
            $items_html = '<p>' . esc_html__('Your cart is empty.', 'woocommerce-page-builder') . '</p>';
        } else {
            $items_html = '';
            foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
                $product = $cart_item['data'];
                if (!$product) continue;
                $thumbnail = $product->get_image(array(60, 60), array('style' => 'width:60px;height:60px;object-fit:cover;border-radius:6px;'));
                $price = WC()->cart->get_product_price($product);
                $items_html .= '<div class="wpb-cart-item" data-key="' . esc_attr($cart_item_key) . '" style="position:relative;">';
                $items_html .= '<div class="wpb-cart-item-thumb">' . $thumbnail . '</div>';
                $items_html .= '<div class="wpb-cart-item-info">';
                $items_html .= '<div style="font-weight:600;font-size:14px;color:#1f2937;margin-bottom:4px;padding-right:24px;">' . esc_html($product->get_name()) . '</div>';
                $items_html .= '<div style="font-size:13px;color:#6b7280;">' . esc_html__('Quantity:', 'woocommerce-page-builder') . ' ' . esc_html($cart_item['quantity']) . '</div>';
                $items_html .= '<div class="wpb-cart-item-price" style="font-weight:700;color:#1f2937;font-size:14px;margin-top:4px;">' . wp_kses_post($price) . '</div>';
                $items_html .= '</div>';
                $items_html .= '<button type="button" class="wpb-cart-remove" data-key="' . esc_attr($cart_item_key) . '" aria-label="' . esc_attr__('Remove', 'woocommerce-page-builder') . '" style="position:absolute;top:8px;right:4px;background:none;border:none;font-size:16px;cursor:pointer;color:#9ca3af;width:24px;height:24px;display:flex;align-items:center;justify-content:center;border-radius:50%;transition:all 0.2s;">&times;</button>';
                $items_html .= '</div>';
            }
        }
        ?>
        <div class="wpb-cart" style="display:inline-block;position:relative;" data-nonce="<?php echo esc_attr($nonce); ?>" data-cart-style="panel">
            <button class="wpb-cart-trigger" type="button">
                <svg xmlns="http://www.w3.org/2000/svg" width="<?php echo esc_attr($icon_size); ?>" height="<?php echo esc_attr($icon_size); ?>" viewBox="0 0 16 16" style="display:block;width:<?php echo esc_attr($icon_size); ?>px;height:<?php echo esc_attr($icon_size); ?>px;"><path d="M0 1.5A.5.5 0 0 1 .5 1H2a.5.5 0 0 1 .485.379L2.89 3H14.5a.5.5 0 0 1 .491.592l-1.5 8A.5.5 0 0 1 13 12H4a.5.5 0 0 1-.491-.408L2.01 3.607 1.61 2H.5a.5.5 0 0 1-.5-.5M5 12a2 2 0 1 0 0 4 2 2 0 0 0 0-4m7 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4m-7 1a1 1 0 1 1 0 2 1 1 0 0 1 0-2m7 0a1 1 0 1 1 0 2 1 1 0 0 1 0-2" style="fill:<?php echo esc_attr($icon_color); ?> !important;"/></svg>
                <span class="wpb-cart-label"><?php echo esc_html($cart_text); ?></span>
                <?php if ($show_count === 'yes'): ?>
                    <span class="wpb-cart-count" style="display:inline-flex;align-items:center;justify-content:center;min-width:18px;height:18px;padding:0 2px !important;background:#1f2937;color:#ffffff;border-radius:9px;font-size:11px;font-weight:600;box-shadow:none !important;"><?php echo esc_html($count); ?></span>
                <?php endif; ?>
            </button>
            <div class="wpb-cart-panel-overlay" style="position:fixed;top:32px;left:0;width:100%;height:calc(100% - 32px);background:rgba(0,0,0,0.5);z-index:9998;display:none;"></div>
            <div class="wpb-cart-panel" style="position:fixed;top:32px;right:0;width:380px;max-width:100%;height:calc(100% - 32px);background:#ffffff;z-index:9999;display:flex;flex-direction:column;transform:translateX(100%);transition:transform .3s ease, opacity .3s ease;opacity:0;visibility:hidden;pointer-events:none;">
                <div class="wpb-cart-panel-header" style="padding:16px;border-bottom:1px solid #eee;display:flex;justify-content:space-between;align-items:center;">
                    <span style="font-size:18px;font-weight:600;"><?php esc_html_e('Your Cart', 'woocommerce-page-builder'); ?></span>
                    <button type="button" class="wpb-cart-close" aria-label="<?php esc_attr_e('Close', 'woocommerce-page-builder'); ?>" style="background:none;border:none;font-size:22px;cursor:pointer;color:#6b7280;width:28px;height:28px;display:flex;align-items:center;justify-content:center;border-radius:50%;transition:background 0.2s;margin-top:-2px;">&times;</button>
                </div>
                <div class="wpb-cart-panel-items wpb-cart-items" style="flex:1;overflow-y:auto;padding:16px;">
                    <?php echo $items_html; ?>
                </div>
                <?php if (!class_exists('WooCommerce') || !WC()->cart || WC()->cart->is_empty()): ?>
                    <div class="wpb-cart-panel-footer-empty" style="padding:16px;border-top:1px solid #eee;background:#f9fafb;">
                        <a href="<?php echo esc_url(wc_get_cart_url()); ?>" style="display:block;width:100%;padding:12px;text-align:center;background:#1f2937;color:#ffffff;text-decoration:none;border-radius:8px;font-weight:600;"><?php esc_html_e('Start Shopping', 'woocommerce-page-builder'); ?></a>
                    </div>
                <?php else: ?>
                    <div class="wpb-cart-panel-footer" style="padding:16px;border-top:1px solid #eee;background:#f9fafb;">
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;font-size:15px;">
                            <span style="color:#6b7280;"><?php esc_html_e('Subtotal', 'woocommerce-page-builder'); ?></span>
                            <span class="wpb-cart-subtotal" style="font-weight:700;color:#1f2937;"><?php echo WC()->cart->get_cart_subtotal(); ?></span>
                        </div>
                        <span class="wpb-cart-subtotal-trigger" style="display:none;"><?php echo WC()->cart->get_cart_subtotal(); ?></span>
                        <p style="font-size:12px;color:#6b7280;margin-bottom:12px;"><?php esc_html_e('Shipping and taxes calculated at checkout.', 'woocommerce-page-builder'); ?></p>
                        <a href="<?php echo esc_url(wc_get_cart_url()); ?>" style="display:block;width:100%;padding:14px;text-align:center;background:#1f2937;color:#ffffff;text-decoration:none;border-radius:8px;font-weight:600;font-size:15px;"><?php esc_html_e('Checkout', 'woocommerce-page-builder'); ?></a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <style>
        .wpb-cart-trigger {
            display: inline-flex !important;
            align-items: center !important;
            gap: 5px !important;
            background: none !important;
            border: none !important;
            padding: 0 !important;
            box-shadow: none !important;
            position: relative !important;
        }
        .wpb-cart-panel {
            box-shadow: none !important;
        }
        .wpb-cart-count {
            box-shadow: none !important;
            right: -4px !important;
            display: inline-flex !important;
            opacity: 1 !important;
            visibility: visible !important;
        }
        body.admin-bar .wpb-cart-panel-overlay,
        body.admin-bar .wpb-cart-panel {
            top: 32px !important;
            height: calc(100% - 32px) !important;
        }
        .wpb-cart-remove:hover {
            background: #fee2e2 !important;
            color: #ef4444 !important;
        }
        </style>
        <script>
        (function() {
            var carts = document.querySelectorAll('.wpb-cart');
            carts.forEach(function(wrapper) {
                var trigger = wrapper.querySelector('.wpb-cart-trigger');
                var overlay = wrapper.querySelector('.wpb-cart-panel-overlay');
                var panel = wrapper.querySelector('.wpb-cart-panel');
                var closeBtn = wrapper.querySelector('.wpb-cart-close');
                var nonce = wrapper.getAttribute('data-nonce') || '';
                var ajaxUrl = typeof wpbAdmin !== 'undefined' && wpbAdmin.ajaxUrl ? wpbAdmin.ajaxUrl : '';

                function openPanel() {
                    if (!panel || !overlay) return;
                    overlay.style.display = 'block';
                    panel.style.display = 'flex';
                    panel.style.opacity = '1';
                    panel.style.visibility = 'visible';
                    panel.style.pointerEvents = 'auto';
                    panel.style.transform = 'translateX(0)';
                }

                function closePanel() {
                    if (!panel || !overlay) return;
                    panel.style.transform = 'translateX(100%)';
                    panel.style.opacity = '0';
                    panel.style.visibility = 'hidden';
                    panel.style.pointerEvents = 'none';
                    overlay.style.display = 'none';
                }

                function refreshCart() {
                    if (!ajaxUrl || !nonce) return;
                    var xhr = new XMLHttpRequest();
                    xhr.open('POST', ajaxUrl, true);
                    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                    xhr.onreadystatechange = function() {
                        if (xhr.readyState === 4 && xhr.status === 200) {
                            try {
                                var res = JSON.parse(xhr.responseText);
                                if (res.success && res.data) {
                                    var data = res.data;
                                    var countEl = wrapper.querySelector('.wpb-cart-count');
                                    if (countEl) {
                                        countEl.textContent = data.count;
                                        countEl.style.display = 'inline-flex';
                                    }
                                    var subtotalEl = wrapper.querySelector('.wpb-cart-subtotal');
                                    if (subtotalEl && data.subtotal) {
                                        subtotalEl.textContent = data.subtotal;
                                    }
                                    var itemsContainer = wrapper.querySelector('.wpb-cart-items');
                                    if (itemsContainer && data.items_html) {
                                        itemsContainer.innerHTML = data.items_html;
                                    }
                                    if (data.footer_html) {
                                        var currentFooter = wrapper.querySelector('.wpb-cart-panel-footer, .wpb-cart-panel-footer-empty');
                                        if (currentFooter) {
                                            currentFooter.outerHTML = data.footer_html;
                                        } else if (panel) {
                                            panel.insertAdjacentHTML('beforeend', data.footer_html);
                                        }
                                    }
                                    bindRemoveButtons();
                                }
                            } catch (e) {}
                        }
                    };
                    xhr.send('action=wpb_get_cart_fragments&_wpnonce=' + encodeURIComponent(nonce));
                }

                function bindRemoveButtons() {
                    var btns = wrapper.querySelectorAll('.wpb-cart-remove');
                    btns.forEach(function(btn) {
                        btn.addEventListener('click', function(e) {
                            e.preventDefault();
                            e.stopPropagation();
                            if (!ajaxUrl || !nonce) return;
                            var key = btn.getAttribute('data-key');
                            if (!key) return;
                            var item = btn.closest('.wpb-cart-item');
                            if (item) item.style.opacity = '0.5';
                            var xhr = new XMLHttpRequest();
                            xhr.open('POST', ajaxUrl, true);
                            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                            xhr.onreadystatechange = function() {
                                if (xhr.readyState === 4 && xhr.status === 200) {
                                    try {
                                        var res = JSON.parse(xhr.responseText);
                                        if (res.success) {
                                            refreshCart();
                                        }
                                    } catch (e) {}
                                }
                            };
                            xhr.send('action=wpb_cart_remove_item&cart_item_key=' + encodeURIComponent(key) + '&_wpnonce=' + encodeURIComponent(nonce));
                        });
                    });
                }

                if (trigger) {
                    trigger.addEventListener('click', function(e) {
                        e.preventDefault();
                        if (panel && (panel.style.transform === 'translateX(0px)' || panel.style.transform === 'translateX(0)')) {
                            closePanel();
                        } else {
                            openPanel();
                            refreshCart();
                        }
                    });
                }

                if (closeBtn) {
                    closeBtn.addEventListener('click', function(e) {
                        e.preventDefault();
                        closePanel();
                    });
                }

                if (overlay) {
                    overlay.addEventListener('click', function() {
                        closePanel();
                    });
                }

                bindRemoveButtons();
            });
        })();
        </script>
        <?php
    }

    protected function content_template() {
        ?>
        <#
        var cartText = settings.cart_text || 'Cart';
        var showCount = settings.show_count !== 'no';
        var iconSize = (settings.icon_size && settings.icon_size.size) ? settings.icon_size.size : 24;
        var iconColor = settings.icon_color ? settings.icon_color : '#222222';
        #>
        <div class="wpb-cart" style="display:inline-block;position:relative;">
            <button class="wpb-cart-trigger">
                <svg xmlns="http://www.w3.org/2000/svg" width="<%= iconSize %>" height="<%= iconSize %>" viewBox="0 0 16 16" style="display:inline-block;width:<%= iconSize %>px;height:<%= iconSize %>px;"><path d="M0 1.5A.5.5 0 0 1 .5 1H2a.5.5 0 0 1 .485.379L2.89 3H14.5a.5.5 0 0 1 .491.592l-1.5 8A.5.5 0 0 1 13 12H4a.5.5 0 0 1-.491-.408L2.01 3.607 1.61 2H.5a.5.5 0 0 1-.5-.5M5 12a2 2 0 1 0 0 4 2 2 0 0 0 0-4m7 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4m-7 1a1 1 0 1 1 0 2 1 1 0 0 1 0-2m7 0a1 1 0 1 1 0 2 1 1 0 0 1 0-2" style="fill:<%= iconColor %>"/></svg>
                <span class="wpb-cart-label">{{{ cartText }}}</span>
                <# if (showCount) { #>
                    <span class="wpb-cart-count" style="display:inline-flex;align-items:center;justify-content:center;min-width:18px;height:18px;padding:0 2px !important;background:#1f2937;color:#ffffff;border-radius:9px;font-size:11px;font-weight:600;box-shadow:none !important;">0</span>
                <# } #>
            </button>
            <div class="wpb-cart-panel-overlay" style="position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:9998;display:none;"></div>
            <div class="wpb-cart-panel" style="position:fixed;top:0;right:0;width:380px;max-width:100%;height:100%;background:#ffffff;z-index:9999;transform:translateX(100%) !important;transition:transform 0.3s ease;opacity:0;visibility:hidden;pointer-events:none;display:flex;flex-direction:column;">
                <div class="wpb-cart-panel-header" style="padding:16px;border-bottom:1px solid #eee;display:flex;justify-content:space-between;align-items:center;">
                    <span style="font-size:18px;font-weight:600;">Your Cart</span>
                    <button class="wpb-cart-close" style="background:none;border:none;font-size:22px;cursor:pointer;color:#6b7280;width:28px;height:28px;display:flex;align-items:center;justify-content:center;border-radius:50%;transition:background 0.2s;margin-top:-2px;">&times;</button>
                </div>
                <div class="wpb-cart-panel-items" style="flex:1;overflow-y:auto;padding:16px;">
                    <p>Your cart is empty.</p>
                </div>
                <div class="wpb-cart-panel-footer" style="padding:16px;border-top:1px solid #eee;background:#f9fafb;">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;font-size:15px;">
                        <span style="color:#6b7280;">Subtotal</span>
                        <span style="font-weight:700;color:#1f2937;">$0.00</span>
                    </div>
                    <p style="font-size:12px;color:#6b7280;margin-bottom:12px;">Shipping and taxes calculated at checkout.</p>
                    <a href="#" style="display:block;width:100%;padding:14px;text-align:center;background:#1f2937;color:#ffffff;text-decoration:none;border-radius:8px;font-weight:600;font-size:15px;">Checkout</a>
                </div>
            </div>
        </div>
        <?php
    }
}




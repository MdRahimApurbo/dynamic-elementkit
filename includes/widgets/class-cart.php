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

        $this->add_control('drawer_title', [
            'label' => __('Cart Drawer Title', 'woocommerce-page-builder'),
            'type' => \Elementor\Controls_Manager::TEXT,
            'default' => __('আপনার কার্ট', 'woocommerce-page-builder'),
            'separator' => 'before',
        ]);

        $this->add_control('empty_text', [
            'label' => __('Empty Cart Message', 'woocommerce-page-builder'),
            'type' => \Elementor\Controls_Manager::TEXT,
            'default' => __('আপনার শপিং কার্টটি খালি।', 'woocommerce-page-builder'),
        ]);

        $this->add_control('continue_text', [
            'label' => __('Continue Shopping Text', 'woocommerce-page-builder'),
            'type' => \Elementor\Controls_Manager::TEXT,
            'default' => __('কেনাকাটা চালিয়ে যান', 'woocommerce-page-builder'),
        ]);

        $this->add_control('subtotal_text', [
            'label' => __('Subtotal Label', 'woocommerce-page-builder'),
            'type' => \Elementor\Controls_Manager::TEXT,
            'default' => __('মোট পণ্যমূল্য', 'woocommerce-page-builder'),
        ]);

        $this->add_control('shipping_text', [
            'label' => __('Shipping Message', 'woocommerce-page-builder'),
            'type' => \Elementor\Controls_Manager::TEXTAREA,
            'default' => __('চেকআউটের সময় ডেলিভারি চার্জ হিসাব করা হবে।', 'woocommerce-page-builder'),
        ]);

        $this->add_control('checkout_text', [
            'label' => __('Checkout Button Text', 'woocommerce-page-builder'),
            'type' => \Elementor\Controls_Manager::TEXT,
            'default' => __('অর্ডার সম্পন্ন করুন', 'woocommerce-page-builder'),
        ]);

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
        $drawer_title = !empty($settings['drawer_title']) ? $settings['drawer_title'] : __('আপনার কার্ট', 'woocommerce-page-builder');
        $empty_text = !empty($settings['empty_text']) ? $settings['empty_text'] : __('আপনার শপিং কার্টটি খালি।', 'woocommerce-page-builder');
        $continue_text = !empty($settings['continue_text']) ? $settings['continue_text'] : __('কেনাকাটা চালিয়ে যান', 'woocommerce-page-builder');
        $subtotal_text = !empty($settings['subtotal_text']) ? $settings['subtotal_text'] : __('মোট পণ্যমূল্য', 'woocommerce-page-builder');
        $shipping_text = !empty($settings['shipping_text']) ? $settings['shipping_text'] : __('চেকআউটের সময় ডেলিভারি চার্জ হিসাব করা হবে।', 'woocommerce-page-builder');
        $checkout_text = !empty($settings['checkout_text']) ? $settings['checkout_text'] : __('অর্ডার সম্পন্ন করুন', 'woocommerce-page-builder');
        $count = class_exists('WooCommerce') && WC()->cart ? WC()->cart->get_cart_contents_count() : 0;
        $nonce = wp_create_nonce('wpb_toggle_active');
        if (!class_exists('WooCommerce') || !WC()->cart || WC()->cart->is_empty()) {
            $items_html = '<div class="wpb-cart-empty">' . esc_html($empty_text) . '</div>';
        } else {
            $items_html = '';
            foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
                $product = $cart_item['data'];
                if (!$product) continue;
                $thumbnail = $product->get_image(array(72, 72));
                $price = WC()->cart->get_product_price($product);
                $items_html .= '<div class="wpb-cart-item" data-key="' . esc_attr($cart_item_key) . '">';
                $items_html .= '<div class="wpb-cart-item-thumb">' . $thumbnail . '</div>';
                $items_html .= '<div class="wpb-cart-item-info">';
                $items_html .= '<span class="wpb-cart-item-title">' . esc_html($product->get_name()) . '</span>';
                $items_html .= '<span class="wpb-cart-item-qty">' . esc_html__('পরিমাণ:', 'woocommerce-page-builder') . ' ' . esc_html($cart_item['quantity']) . '</span>';
                $items_html .= '<span class="wpb-cart-item-price">' . wp_kses_post($price) . '</span>';
                $items_html .= '</div>';
                $items_html .= '<button type="button" class="wpb-cart-remove" data-key="' . esc_attr($cart_item_key) . '" aria-label="' . esc_attr__('কার্ট থেকে সরান', 'woocommerce-page-builder') . '"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M9 7V4h6v3M7 7l1 13h8l1-13M10 11v5M14 11v5"></path></svg></button>';
                $items_html .= '</div>';
            }
        }
        ?>
        <div class="wpb-cart" style="display:inline-block;position:relative;"
             data-nonce="<?php echo esc_attr($nonce); ?>" data-cart-style="panel"
             data-empty-text="<?php echo esc_attr($empty_text); ?>"
             data-continue-text="<?php echo esc_attr($continue_text); ?>"
             data-subtotal-text="<?php echo esc_attr($subtotal_text); ?>"
             data-shipping-text="<?php echo esc_attr($shipping_text); ?>"
             data-checkout-text="<?php echo esc_attr($checkout_text); ?>">
            <button class="wpb-cart-trigger" type="button">
                <svg xmlns="http://www.w3.org/2000/svg" width="<?php echo esc_attr($icon_size); ?>" height="<?php echo esc_attr($icon_size); ?>" viewBox="0 0 16 16" style="display:block;width:<?php echo esc_attr($icon_size); ?>px;height:<?php echo esc_attr($icon_size); ?>px;"><path d="M0 1.5A.5.5 0 0 1 .5 1H2a.5.5 0 0 1 .485.379L2.89 3H14.5a.5.5 0 0 1 .491.592l-1.5 8A.5.5 0 0 1 13 12H4a.5.5 0 0 1-.491-.408L2.01 3.607 1.61 2H.5a.5.5 0 0 1-.5-.5M5 12a2 2 0 1 0 0 4 2 2 0 0 0 0-4m7 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4m-7 1a1 1 0 1 1 0 2 1 1 0 0 1 0-2m7 0a1 1 0 1 1 0 2 1 1 0 0 1 0-2" style="fill:<?php echo esc_attr($icon_color); ?> !important;"/></svg>
                <span class="wpb-cart-label"><?php echo esc_html($cart_text); ?></span>
                <?php if ($show_count === 'yes'): ?>
                    <span class="wpb-cart-count" style="display:inline-flex;align-items:center;justify-content:center;min-width:18px;height:18px;padding:0 2px !important;background:#1f2937;color:#ffffff;border-radius:9px;font-size:11px;font-weight:600;box-shadow:none !important;"><?php echo esc_html($count); ?></span>
                <?php endif; ?>
            </button>
            <div class="wpb-cart-panel-overlay"></div>
            <div class="wpb-cart-panel">
                <div class="wpb-cart-panel-header">
                    <span class="wpb-cart-panel-title"><?php echo esc_html($drawer_title); ?></span>
                    <button type="button" class="wpb-cart-close" aria-label="<?php esc_attr_e('কার্ট বন্ধ করুন', 'woocommerce-page-builder'); ?>">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"></path></svg>
                    </button>
                </div>
                <div class="wpb-cart-panel-items wpb-cart-items">
                    <?php echo $items_html; ?>
                </div>
                <?php if (!class_exists('WooCommerce') || !WC()->cart || WC()->cart->is_empty()): ?>
                    <div class="wpb-cart-panel-footer-empty wpb-cart-footer">
                        <a class="wpb-cart-checkout-btn" href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>"><?php echo esc_html($continue_text); ?></a>
                    </div>
                <?php else: ?>
                    <div class="wpb-cart-panel-footer wpb-cart-footer">
                        <div class="wpb-cart-subtotal-row">
                            <span><?php echo esc_html($subtotal_text); ?></span>
                            <span class="wpb-cart-subtotal"><?php echo WC()->cart->get_cart_subtotal(); ?></span>
                        </div>
                        <span class="wpb-cart-subtotal-trigger" style="display:none;"><?php echo WC()->cart->get_cart_subtotal(); ?></span>
                        <p class="wpb-cart-shipping-note"><?php echo esc_html($shipping_text); ?></p>
                        <a class="wpb-cart-checkout-btn" href="<?php echo esc_url(wc_get_checkout_url()); ?>"><?php echo esc_html($checkout_text); ?></a>
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
            box-shadow: -18px 0 45px rgba(15, 23, 42, .18) !important;
            width: min(420px, 100vw) !important;
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
        .wpb-cart .wpb-cart-remove,
        .wpb-cart .wpb-cart-remove:hover,
        .wpb-cart .wpb-cart-remove:focus {
            display: inline-grid !important;
            width: 32px !important;
            min-width: 32px !important;
            max-width: 32px !important;
            height: 32px !important;
            min-height: 32px !important;
            padding: 0 !important;
            place-items: center !important;
            background: #fee2e2 !important;
            border: 1px solid #fca5a5 !important;
            border-radius: 50% !important;
            color: #b91c1c !important;
            box-shadow: none !important;
        }
        .wpb-cart .wpb-cart-close,
        .wpb-cart .wpb-cart-close:hover,
        .wpb-cart .wpb-cart-close:focus {
            display: inline-grid !important;
            width: 36px !important;
            min-width: 36px !important;
            max-width: 36px !important;
            height: 36px !important;
            min-height: 36px !important;
            padding: 0 !important;
            place-items: center !important;
            color: #344054 !important;
            background: transparent !important;
            border: 0 !important;
            border-radius: 50% !important;
            box-shadow: none !important;
            outline: 0 !important;
        }
        .wpb-cart .wpb-cart-footer {
            position: sticky !important;
            bottom: 0 !important;
            display: block !important;
            flex: 0 0 auto !important;
            width: 100% !important;
            background: #fff !important;
            opacity: 1 !important;
            visibility: visible !important;
        }
        .wpb-cart .wpb-cart-checkout-btn {
            display: flex !important;
            width: 100% !important;
            min-height: 50px !important;
            color: #fff !important;
            background: #222 !important;
            opacity: 1 !important;
            visibility: visible !important;
        }
        </style>
        <script type="application/json" class="wpb-cart-legacy-script-disabled">
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
            <div class="wpb-cart-panel" style="position:fixed;top:0;right:0;width:min(420px,100vw);max-width:100%;height:100%;background:#ffffff;z-index:9999;transform:translateX(100%) !important;transition:transform 0.3s ease;opacity:0;visibility:hidden;pointer-events:none;display:flex;flex-direction:column;">
                <div class="wpb-cart-panel-header" style="padding:16px;border-bottom:1px solid #eee;display:flex;justify-content:space-between;align-items:center;">
                    <span style="font-size:18px;font-weight:600;">Your Cart</span>
                    <button class="wpb-cart-close" type="button" aria-label="<?php esc_attr_e('কার্ট বন্ধ করুন', 'woocommerce-page-builder'); ?>"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"></path></svg></button>
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




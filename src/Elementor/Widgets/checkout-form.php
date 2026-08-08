<?php
defined('ABSPATH') || exit;

class DEK_Checkout_Form_Widget extends \Elementor\Widget_Base {

    public function get_name() {
        return 'wpb-checkout-form';
    }

    public function get_title() {
        return __('Checkout Form', 'dynamic-elementkit');
    }

    public function get_icon() {
        return 'eicon-checkout';
    }

    public function get_categories() {
        return ['wpb-woo-page-builder'];
    }

    public function get_keywords() {
        return ['checkout', 'order', 'woocommerce', 'form', 'payment'];
    }

    protected function register_controls() {
        $this->start_controls_section(
            'form_section',
            [
                'label' => __('Checkout Fields', 'dynamic-elementkit'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'essential_fields_notice',
            [
                'type' => \Elementor\Controls_Manager::RAW_HTML,
                'raw' => esc_html__('নাম, মোবাইল নম্বর এবং ঠিকানা সবসময় দেখানো হবে এবং আবশ্যক।', 'dynamic-elementkit'),
                'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
            ]
        );

        $this->add_control(
            'submit_text',
            [
                'label' => __('Place Order Button Text', 'dynamic-elementkit'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __('Place Order', 'dynamic-elementkit'),
            ]
        );

        $this->add_control(
            'product_source',
            [
                'label' => __('Product Source', 'dynamic-elementkit'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'cart',
                'options' => [
                    'cart' => __('Use Existing Cart', 'dynamic-elementkit'),
                    'current' => __('Current Product (Dynamic)', 'dynamic-elementkit'),
                    'manual' => __('Manual / Dynamic Product ID', 'dynamic-elementkit'),
                ],
                'separator' => 'before',
                'description' => __('Use Current Product on a product template, or enter an ID for a one-page landing checkout.', 'dynamic-elementkit'),
            ]
        );

        $this->add_control(
            'product_id',
            [
                'label' => __('Product or Variation ID', 'dynamic-elementkit'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'min' => 1,
                'dynamic' => ['active' => true],
                'condition' => ['product_source' => 'manual'],
                'description' => __('Enter a product ID, variation ID, or connect an Elementor dynamic value.', 'dynamic-elementkit'),
            ]
        );

        $this->add_control(
            'product_quantity',
            [
                'label' => __('Initial Quantity', 'dynamic-elementkit'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 1,
                'min' => 1,
                'max' => 999,
                'condition' => ['product_source!' => 'cart'],
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        if (!class_exists('WooCommerce') || !function_exists('WC') || !WC()->cart) {
            echo '<p>' . esc_html__('WooCommerce checkout is not available.', 'dynamic-elementkit') . '</p>';
            return;
        }

        $settings = $this->get_settings_for_display();
        $product_source = in_array(($settings['product_source'] ?? 'cart'), ['cart', 'current', 'manual'], true)
            ? $settings['product_source']
            : 'cart';
        $request_has_direct_checkout = $this->has_direct_checkout_request();
        $request_product_id = $this->resolve_request_product_id();
        $configured_product_id = $request_product_id ?: $this->resolve_configured_product_id($product_source, $settings);
        $configured_quantity = $request_product_id
            ? $this->resolve_request_quantity()
            : max(1, absint($settings['product_quantity'] ?? 1));
        $configured_product_missing = $request_has_direct_checkout && !$configured_product_id;
        $cart_error = $configured_product_missing
            ? __('No product found.', 'dynamic-elementkit')
            : $this->maybe_use_configured_product($configured_product_id, $configured_quantity);
        $no_product_found = (bool) $cart_error;

        WC()->cart->calculate_totals();
        $cart_is_empty = $no_product_found || WC()->cart->is_empty();

        $checkout = WC()->checkout();
        $customer = WC()->customer;
        $css_id = !empty($settings['_element_id']) ? sanitize_html_class($settings['_element_id']) : $this->get_id();
        $full_name = trim($customer->get_billing_first_name() . ' ' . $customer->get_billing_last_name());
        $checkout_payload = function_exists('dek_checkout_create_signed_payload')
            ? dek_checkout_create_signed_payload($no_product_found ? [] : WC()->cart->get_cart())
            : ['payload' => '', 'signature' => ''];

        // The compact widget intentionally has no coupon card. Temporarily
        // detach only WooCommerce's native coupon form while retaining other
        // callbacks registered on the before-checkout hook.
        $coupon_form_removed = remove_action('woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 10);
        do_action('woocommerce_before_checkout_form', $checkout);
        if ($coupon_form_removed) {
            add_action('woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 10);
        }
        ?>
        <div class="wpb-checkout-form<?php echo $cart_is_empty ? ' wpb-checkout-empty' : ''; ?>"
             id="<?php echo esc_attr($css_id); ?>"
             data-cart-empty="<?php echo $cart_is_empty ? '1' : '0'; ?>">
            <form name="dek_custom_checkout" method="post"
                  class="woocommerce-checkout wpb-checkout-form-inner"
                  action=""
                  enctype="multipart/form-data"
                  aria-label="<?php esc_attr_e('Checkout', 'dynamic-elementkit'); ?>">

                <div class="woocommerce-NoticeGroup woocommerce-NoticeGroup-checkout"></div>

                <div class="wpb-checkout-layout">
                    <section class="wpb-checkout-form-section">
                        <div class="wpb-section-heading">
                            <h3><?php esc_html_e('Customer Information', 'dynamic-elementkit'); ?></h3>
                        </div>

                        <?php do_action('woocommerce_checkout_before_customer_details'); ?>

                        <?php
                        $this->render_field(
                            'billing_full_name',
                            __('নাম', 'dynamic-elementkit'),
                            'text',
                            $full_name,
                            true,
                            'name',
                            __('আপনার নাম লিখুন', 'dynamic-elementkit')
                        );
                        $this->render_field(
                            'billing_phone',
                            __('মোবাইল নম্বর', 'dynamic-elementkit'),
                            'tel',
                            $customer->get_billing_phone(),
                            true,
                            'tel',
                            __('আপনার মোবাইল নম্বর লিখুন', 'dynamic-elementkit')
                        );
                        $this->render_field(
                            'billing_address_1',
                            __('ঠিকানা', 'dynamic-elementkit'),
                            'text',
                            $customer->get_billing_address_1(),
                            true,
                            'street-address',
                            __('আপনার সম্পূর্ণ ঠিকানা লিখুন', 'dynamic-elementkit')
                        );

                        $country = $customer->get_billing_country() ?: WC()->countries->get_base_country();
                        ?>

                        <input type="hidden" name="billing_country" id="billing_country" value="<?php echo esc_attr($country); ?>">
                        <input type="hidden" name="billing_first_name" value="<?php echo esc_attr($customer->get_billing_first_name()); ?>">
                        <input type="hidden" name="billing_last_name" value="<?php echo esc_attr($customer->get_billing_last_name()); ?>">
                        <input type="hidden" name="dek_checkout_widget" value="1">
                        <input type="hidden" name="dek_checkout_cart_mode" value="<?php echo $configured_product_id ? 'isolated' : 'cart'; ?>">
                        <input type="hidden" name="dek_checkout_product_id" value="<?php echo esc_attr($configured_product_id); ?>">
                        <input type="hidden" name="dek_checkout_product_quantity" value="<?php echo esc_attr($configured_quantity); ?>">
                        <input type="hidden" name="dek_checkout_nonce" value="<?php echo esc_attr(wp_create_nonce('dek_checkout')); ?>">
                        <input type="hidden" name="dek_checkout_payload" value="<?php echo esc_attr($checkout_payload['payload']); ?>">
                        <input type="hidden" name="dek_checkout_signature" value="<?php echo esc_attr($checkout_payload['signature']); ?>">
                        <input type="hidden" name="dek_order_button_text" value="<?php echo esc_attr($settings['submit_text'] ?? __('Place Order', 'dynamic-elementkit')); ?>">

                        <?php do_action('woocommerce_checkout_after_customer_details'); ?>
                    </section>

                    <section class="wpb-checkout-review-section">
                        <?php do_action('woocommerce_checkout_before_order_review_heading'); ?>

                        <?php self::render_order_summary($no_product_found, !$configured_product_id); ?>

                        <?php do_action('woocommerce_checkout_before_order_review'); ?>

                        <div class="wpb-payment-methods">
                            <div class="wpb-section-heading">
                                <h3><?php esc_html_e('Payment & Confirmation', 'dynamic-elementkit'); ?></h3>
                            </div>

                            <?php
                            $button_text = !empty($settings['submit_text'])
                                ? $settings['submit_text']
                                : __('Place Order', 'dynamic-elementkit');
                            $button_filter = static function() use ($button_text) {
                                return $button_text;
                            };
                            $button_html_filter = static function($button_html) use ($cart_is_empty) {
                                if (!$cart_is_empty) {
                                    return $button_html;
                                }

                                return preg_replace(
                                    '/<button\b/',
                                    '<button disabled aria-disabled="true"',
                                    $button_html,
                                    1
                                );
                            };
                            add_filter('woocommerce_order_button_text', $button_filter);
                            add_filter('woocommerce_order_button_html', $button_html_filter);
                            ob_start();
                            woocommerce_checkout_payment();
                            $payment_html = ob_get_clean();
                            remove_filter('woocommerce_order_button_html', $button_html_filter);
                            remove_filter('woocommerce_order_button_text', $button_filter);
                            $payment_html = preg_replace(
                                '/<input\b[^>]*\bname=["\'](?:woocommerce-process-checkout-nonce|_wp_http_referer)["\'][^>]*>/i',
                                '',
                                $payment_html
                            );
                            echo $payment_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- WooCommerce template output with native nonce fields removed.
                            ?>
                            <div class="wpb-order-status" role="status" aria-live="polite" hidden
                                 data-processing-text="<?php esc_attr_e('Your order is being placed. Please wait…', 'dynamic-elementkit'); ?>"
                                 data-error-text="<?php esc_attr_e('Please check your details and try again.', 'dynamic-elementkit'); ?>"></div>
                        </div>

                        <?php do_action('woocommerce_checkout_after_order_review'); ?>
                    </section>
                </div>
            </form>
        </div>
        <?php
        do_action('woocommerce_after_checkout_form', $checkout);
        if ($configured_product_id && function_exists('dek_checkout_restore_normal_cart')) {
            dek_checkout_restore_normal_cart();
        }
    }

    private function resolve_configured_product_id($source, $settings) {
        if ('cart' === $source) {
            return 0;
        }

        if ('manual' === $source) {
            return !empty($settings['product_id']) ? absint($settings['product_id']) : 0;
        }

        if (function_exists('is_product') && is_product()) {
            return absint(get_queried_object_id());
        }

        if (function_exists('dek_get_landing_product')) {
            $landing_product = dek_get_landing_product();
            if ($landing_product instanceof \WC_Product) {
                return $landing_product->get_id();
            }
        }

        global $product;
        return $product instanceof \WC_Product ? $product->get_id() : 0;
    }

    private function resolve_request_product_id() {
        $request = array_merge($_GET, $_POST);
        $direct_markers = $this->get_direct_checkout_markers();

        foreach ($direct_markers as $marker) {
            if (empty($request[$marker])) {
                continue;
            }

            if (is_array($request[$marker])) {
                continue;
            }

            $value = absint(wp_unslash($request[$marker]));
            if ($value > 1) {
                return $value;
            }
        }

        if (!$this->has_direct_checkout_request()) {
            return 0;
        }

        foreach (['product_id', 'variation_id', 'add-to-cart'] as $key) {
            if (!empty($request[$key])) {
                if (is_array($request[$key])) {
                    continue;
                }

                return absint(wp_unslash($request[$key]));
            }
        }

        return 0;
    }

    private function has_direct_checkout_request() {
        $request = array_merge($_GET, $_POST);

        foreach ($this->get_direct_checkout_markers() as $marker) {
            if (!empty($request[$marker])) {
                return true;
            }
        }

        return false;
    }

    private function get_direct_checkout_markers() {
        return [
            'dek_checkout_product_id',
            'dek_quick_checkout',
            'dek_direct_checkout',
            'dek_buy_now',
            'quick_checkout',
            'direct_checkout',
            'buy_now',
        ];
    }

    private function resolve_request_quantity() {
        $request = array_merge($_GET, $_POST);
        foreach (['dek_checkout_product_quantity', 'quantity', 'qty'] as $key) {
            if (!empty($request[$key])) {
                if (is_array($request[$key])) {
                    continue;
                }

                return max(1, absint(wp_unslash($request[$key])));
            }
        }

        return 1;
    }

    private function maybe_use_configured_product($product_id, $quantity) {
        if (!$product_id) {
            if (function_exists('dek_checkout_clear_isolated_cart')) {
                dek_checkout_clear_isolated_cart();
            }
            return '';
        }

        $product = wc_get_product($product_id);
        if (!$product || !$product->exists() || 'publish' !== $product->get_status() || !$product->is_purchasable()) {
            return __('No product found.', 'dynamic-elementkit');
        }

        if ($product->is_type('variable')) {
            return __('No product found.', 'dynamic-elementkit');
        }

        if (
            !function_exists('dek_checkout_activate_isolated_cart') ||
            !dek_checkout_activate_isolated_cart($product->get_id(), $quantity)
        ) {
            return __('No product found.', 'dynamic-elementkit');
        }

        return '';
    }

    private function render_field($name, $label, $type, $value = '', $required = false, $autocomplete = '', $placeholder = '') {
        $field_classes = 'form-row wpb-form-field';
        if ('billing_address_1' === $name) {
            $field_classes .= ' address-field update_totals_on_change';
        }
        ?>
        <p class="<?php echo esc_attr($field_classes); ?><?php if ($required): ?> validate-required<?php endif; ?>" id="<?php echo esc_attr($name); ?>_field">
            <label for="<?php echo esc_attr($name); ?>">
                <?php echo esc_html($label); ?>
                <?php if ($required): ?><span class="required" aria-hidden="true">*</span><?php endif; ?>
            </label>
            <span class="wpb-input-wrap">
                <span class="wpb-field-icon" aria-hidden="true"><?php echo $this->get_field_icon($name); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
                <?php if ('billing_address_1' === $name): ?>
                    <textarea id="<?php echo esc_attr($name); ?>"
                              name="<?php echo esc_attr($name); ?>"
                              class="input-text"
                              rows="3"
                              placeholder="<?php echo esc_attr($placeholder); ?>"
                              <?php if ($required): ?>required aria-required="true"<?php endif; ?>
                              <?php if ($autocomplete): ?>autocomplete="<?php echo esc_attr($autocomplete); ?>"<?php endif; ?>><?php echo esc_textarea($value); ?></textarea>
                <?php else: ?>
                    <input type="<?php echo esc_attr($type); ?>"
                           id="<?php echo esc_attr($name); ?>"
                           name="<?php echo esc_attr($name); ?>"
                           class="input-text"
                           value="<?php echo esc_attr($value); ?>"
                           placeholder="<?php echo esc_attr($placeholder); ?>"
                           <?php if ($required): ?>required aria-required="true"<?php endif; ?>
                           <?php if ($autocomplete): ?>autocomplete="<?php echo esc_attr($autocomplete); ?>"<?php endif; ?>>
                <?php endif; ?>
            </span>
        </p>
        <?php
    }

    private function get_field_icon($name) {
        if ('billing_phone' === $name) {
            return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" focusable="false"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>';
        }

        if ('billing_address_1' === $name) {
            return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" focusable="false"><path d="M21 10c0 7-9 12-9 12S3 17 3 10a9 9 0 1 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>';
        }

        return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" focusable="false"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>';
    }

    public static function render_order_summary($no_product_found = false, $allow_item_removal = true) {
        $cart = WC()->cart;
        $cart_is_empty = $no_product_found || $cart->is_empty();
        $shipping_methods_html = function_exists('dek_get_checkout_shipping_methods_html')
            ? dek_get_checkout_shipping_methods_html()
            : '';
        ?>
        <div class="wpb-order-review">
            <?php if ($no_product_found): ?>
                <div class="woocommerce-info"><?php esc_html_e('No products available for checkout.', 'dynamic-elementkit'); ?></div>
            <?php endif; ?>

            <?php if (!$cart_is_empty): ?>
                <div class="wpb-section-heading">
                    <h3><?php esc_html_e('Product Summary', 'dynamic-elementkit'); ?></h3>
                </div>
            <?php endif; ?>

            <div class="wpb-cart-items">
                <?php if (!$no_product_found): ?>
                <?php foreach ($cart->get_cart() as $cart_item_key => $cart_item): ?>
                    <?php
                    $product = $cart_item['data'] ?? false;
                    if (!$product instanceof \WC_Product || !$product->exists()) {
                        continue;
                    }
                    $quantity = max(1, (int) $cart_item['quantity']);
                    $max_quantity = $product->get_max_purchase_quantity();
                    $max_quantity = $max_quantity > 0 ? $max_quantity : 999;
                    $image = apply_filters('woocommerce_cart_item_thumbnail', $product->get_image('woocommerce_thumbnail'), $cart_item, $cart_item_key);
                    ?>
                    <article class="wpb-cart-item" data-cart-key="<?php echo esc_attr($cart_item_key); ?>">
                        <div class="wpb-cart-item-image"><?php echo wp_kses_post($image); ?></div>
                        <div class="wpb-cart-item-details">
                            <div class="wpb-cart-item-heading">
                                <span class="wpb-cart-item-name"><?php echo esc_html($product->get_name()); ?></span>
                                <span class="wpb-cart-item-line-total"><?php echo wp_kses_post($cart->get_product_subtotal($product, $quantity)); ?></span>
                                <?php if ($allow_item_removal): ?>
                                    <button type="button"
                                            class="wpb-checkout-remove-item"
                                            data-cart-key="<?php echo esc_attr($cart_item_key); ?>"
                                            aria-label="<?php esc_attr_e('Remove product', 'dynamic-elementkit'); ?>">
                                        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                            <path d="M4 7h16M9 7V4h6v3M7 7l1 13h8l1-13M10 11v5M14 11v5"></path>
                                        </svg>
                                    </button>
                                <?php endif; ?>
                            </div>

                            <?php $variation_data = wc_get_formatted_cart_item_data($cart_item, true); ?>
                            <?php if ($variation_data): ?>
                                <div class="wpb-variation-data"><?php echo wp_kses_post($variation_data); ?></div>
                            <?php endif; ?>

                            <div class="wpb-cart-item-meta">
                                <span class="wpb-cart-item-price"><?php echo wp_kses_post($cart->get_product_price($product)); ?></span>
                                <div class="wpb-quantity-field" aria-label="<?php esc_attr_e('Quantity', 'dynamic-elementkit'); ?>">
                                    <button type="button" class="wpb-qty-minus" aria-label="<?php esc_attr_e('Decrease quantity', 'dynamic-elementkit'); ?>">−</button>
                                    <input type="number" class="wpb-qty-input"
                                           name="dek_checkout_quantities[<?php echo esc_attr($cart_item_key); ?>]"
                                           value="<?php echo esc_attr($quantity); ?>"
                                           min="1" max="<?php echo esc_attr($max_quantity); ?>"
                                           data-cart-key="<?php echo esc_attr($cart_item_key); ?>"
                                           aria-label="<?php esc_attr_e('Product quantity', 'dynamic-elementkit'); ?>">
                                    <button type="button" class="wpb-qty-plus" aria-label="<?php esc_attr_e('Increase quantity', 'dynamic-elementkit'); ?>">+</button>
                                </div>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <?php if ($no_product_found): ?>
            <?php elseif ($cart_is_empty): ?>
                <div class="woocommerce-info"><?php esc_html_e('No products available for checkout.', 'dynamic-elementkit'); ?></div>
            <?php else: ?>
                <div class="wpb-shipping-section" data-shipping-section <?php if (!$shipping_methods_html): ?>hidden<?php endif; ?>>
                    <span class="wpb-shipping-title"><?php esc_html_e('Shipping method', 'dynamic-elementkit'); ?></span>
                    <div class="wpb-shipping-methods" data-shipping-methods>
                        <?php echo $shipping_methods_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- generated and escaped by dek_get_checkout_shipping_methods_html(). ?>
                    </div>
                </div>

                <div class="wpb-cart-totals">
                    <div class="wpb-cart-total" data-total-row="subtotal">
                        <span><?php esc_html_e('Subtotal', 'dynamic-elementkit'); ?></span>
                        <strong><?php echo wp_kses_post($cart->get_cart_subtotal()); ?></strong>
                    </div>
                    <div class="wpb-cart-total" data-total-row="shipping" <?php if (!$shipping_methods_html): ?>hidden<?php endif; ?>>
                        <span><?php esc_html_e('Shipping', 'dynamic-elementkit'); ?></span>
                        <strong><?php echo wp_kses_post($cart->get_cart_shipping_total()); ?></strong>
                    </div>
                    <div class="wpb-cart-total wpb-cart-grand-total" data-total-row="total">
                        <span><?php esc_html_e('Total', 'dynamic-elementkit'); ?></span>
                        <strong><?php echo wp_kses_post($cart->get_cart_total()); ?></strong>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }

    private static function render_recommendations() {
        $products = wc_get_products([
            'status' => 'publish',
            'limit' => 12,
            'stock_status' => 'instock',
            'orderby' => 'popularity',
            'order' => 'DESC',
            'return' => 'objects',
        ]);
        $products = array_values(array_filter($products, static function($product) {
            return $product instanceof \WC_Product && $product->is_visible() && $product->is_purchasable();
        }));
        $products = array_slice($products, 0, 3);

        if (!$products) {
            return;
        }
        ?>
        <div class="wpb-checkout-recommendations">
            <div class="wpb-section-heading">
                <h3><?php esc_html_e('Recommended Products', 'dynamic-elementkit'); ?></h3>
            </div>
            <div class="wpb-product-grid">
                <div class="wpb-products" style="--wpb-cols-desktop: 3; --wpb-cols-tablet: 3; --wpb-cols-mobile: 1;">
                    <?php foreach ($products as $product): ?>
                        <div class="wpb-product">
                            <div class="wpb-product-image">
                                <a href="<?php echo esc_url($product->get_permalink()); ?>">
                                    <?php echo wp_kses_post($product->get_image('woocommerce_thumbnail')); ?>
                                </a>
                            </div>
                            <div class="wpb-product-details">
                                <h3 class="wpb-product-title">
                                    <a href="<?php echo esc_url($product->get_permalink()); ?>"><?php echo esc_html($product->get_name()); ?></a>
                                </h3>
                                <div class="wpb-product-price"><?php echo wp_kses_post($product->get_price_html()); ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php
    }
}

<?php
defined('ABSPATH') || exit;

class WPB_Checkout_Form_Widget extends \Elementor\Widget_Base {

    public function get_name() {
        return 'wpb-checkout-form';
    }

    public function get_title() {
        return __('Checkout Form', 'woocommerce-page-builder');
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
                'label' => __('Checkout Fields', 'woocommerce-page-builder'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'essential_fields_notice',
            [
                'type' => \Elementor\Controls_Manager::RAW_HTML,
                'raw' => esc_html__('নাম, মোবাইল নম্বর এবং ঠিকানা সবসময় দেখানো হবে এবং আবশ্যক।', 'woocommerce-page-builder'),
                'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
            ]
        );

        $this->add_control(
            'submit_text',
            [
                'label' => __('Place Order Button Text', 'woocommerce-page-builder'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __('Place Order', 'woocommerce-page-builder'),
            ]
        );

        $this->add_control(
            'product_source',
            [
                'label' => __('Product Source', 'woocommerce-page-builder'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'cart',
                'options' => [
                    'cart' => __('Use Existing Cart', 'woocommerce-page-builder'),
                    'current' => __('Current Product (Dynamic)', 'woocommerce-page-builder'),
                    'manual' => __('Manual / Dynamic Product ID', 'woocommerce-page-builder'),
                ],
                'separator' => 'before',
                'description' => __('Use Current Product on a product template, or enter an ID for a one-page landing checkout.', 'woocommerce-page-builder'),
            ]
        );

        $this->add_control(
            'product_id',
            [
                'label' => __('Product or Variation ID', 'woocommerce-page-builder'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'min' => 1,
                'dynamic' => ['active' => true],
                'condition' => ['product_source' => 'manual'],
                'description' => __('Enter a product ID, variation ID, or connect an Elementor dynamic value.', 'woocommerce-page-builder'),
            ]
        );

        $this->add_control(
            'product_quantity',
            [
                'label' => __('Initial Quantity', 'woocommerce-page-builder'),
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
            echo '<p>' . esc_html__('WooCommerce checkout is not available.', 'woocommerce-page-builder') . '</p>';
            return;
        }

        $settings = $this->get_settings_for_display();
        $product_source = in_array(($settings['product_source'] ?? 'cart'), ['cart', 'current', 'manual'], true)
            ? $settings['product_source']
            : 'cart';
        $configured_product_id = $this->resolve_configured_product_id($product_source, $settings);
        $configured_quantity = max(1, absint($settings['product_quantity'] ?? 1));
        $cart_error = $this->maybe_use_configured_product($configured_product_id, $configured_quantity);

        if ($cart_error) {
            echo '<div class="woocommerce-error" role="alert">' . esc_html($cart_error) . '</div>';
            return;
        }

        if (WC()->cart->is_empty()) {
            echo '<div class="woocommerce-info">' . esc_html__('Your cart is empty.', 'woocommerce-page-builder') . '</div>';
            return;
        }

        WC()->cart->calculate_totals();

        wp_enqueue_script('wc-checkout');

        $checkout = WC()->checkout();
        $customer = WC()->customer;
        $css_id = !empty($settings['_element_id']) ? sanitize_html_class($settings['_element_id']) : $this->get_id();
        $full_name = trim($customer->get_billing_first_name() . ' ' . $customer->get_billing_last_name());

        // The compact widget intentionally has no coupon card. Temporarily
        // detach only WooCommerce's native coupon form while retaining other
        // callbacks registered on the before-checkout hook.
        $coupon_form_removed = remove_action('woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 10);
        do_action('woocommerce_before_checkout_form', $checkout);
        if ($coupon_form_removed) {
            add_action('woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 10);
        }
        ?>
        <div class="wpb-checkout-form" id="<?php echo esc_attr($css_id); ?>">
            <form name="checkout" method="post"
                  class="checkout woocommerce-checkout wpb-checkout-form-inner"
                  action="<?php echo esc_url(wc_get_checkout_url()); ?>"
                  enctype="multipart/form-data"
                  aria-label="<?php esc_attr_e('Checkout', 'woocommerce-page-builder'); ?>">

                <div class="woocommerce-NoticeGroup woocommerce-NoticeGroup-checkout"></div>

                <div class="wpb-checkout-layout">
                    <section class="wpb-checkout-form-section">
                        <div class="wpb-section-heading">
                            <h3><?php esc_html_e('Customer Information', 'woocommerce-page-builder'); ?></h3>
                        </div>

                        <?php do_action('woocommerce_checkout_before_customer_details'); ?>

                        <?php
                        $this->render_field(
                            'billing_full_name',
                            __('নাম', 'woocommerce-page-builder'),
                            'text',
                            $full_name,
                            true,
                            'name',
                            __('আপনার নাম লিখুন', 'woocommerce-page-builder')
                        );
                        $this->render_field(
                            'billing_phone',
                            __('মোবাইল নম্বর', 'woocommerce-page-builder'),
                            'tel',
                            $customer->get_billing_phone(),
                            true,
                            'tel',
                            __('আপনার মোবাইল নম্বর লিখুন', 'woocommerce-page-builder')
                        );
                        $this->render_field(
                            'billing_address_1',
                            __('ঠিকানা', 'woocommerce-page-builder'),
                            'text',
                            $customer->get_billing_address_1(),
                            true,
                            'street-address',
                            __('আপনার সম্পূর্ণ ঠিকানা লিখুন', 'woocommerce-page-builder')
                        );

                        $country = $customer->get_billing_country() ?: WC()->countries->get_base_country();
                        ?>

                        <input type="hidden" name="billing_country" id="billing_country" value="<?php echo esc_attr($country); ?>">
                        <input type="hidden" name="billing_first_name" value="<?php echo esc_attr($customer->get_billing_first_name()); ?>">
                        <input type="hidden" name="billing_last_name" value="<?php echo esc_attr($customer->get_billing_last_name()); ?>">
                        <input type="hidden" name="wpb_checkout_widget" value="1">
                        <input type="hidden" name="wpb_order_button_text" value="<?php echo esc_attr($settings['submit_text'] ?? __('Place Order', 'woocommerce-page-builder')); ?>">

                        <?php do_action('woocommerce_checkout_after_customer_details'); ?>
                    </section>

                    <section class="wpb-checkout-review-section">
                        <?php do_action('woocommerce_checkout_before_order_review_heading'); ?>

                        <?php $this->render_order_summary(); ?>

                        <?php do_action('woocommerce_checkout_before_order_review'); ?>

                        <div class="wpb-payment-methods">
                            <div class="wpb-section-heading">
                                <h3><?php esc_html_e('Payment & Confirmation', 'woocommerce-page-builder'); ?></h3>
                            </div>

                            <?php
                            $button_text = !empty($settings['submit_text'])
                                ? $settings['submit_text']
                                : __('Place Order', 'woocommerce-page-builder');
                            $button_filter = static function() use ($button_text) {
                                return $button_text;
                            };
                            add_filter('woocommerce_order_button_text', $button_filter);
                            woocommerce_checkout_payment();
                            remove_filter('woocommerce_order_button_text', $button_filter);
                            ?>
                            <div class="wpb-order-status" role="status" aria-live="polite" hidden
                                 data-processing-text="<?php esc_attr_e('Your order is being placed. Please wait…', 'woocommerce-page-builder'); ?>"
                                 data-error-text="<?php esc_attr_e('Please check your details and try again.', 'woocommerce-page-builder'); ?>"></div>
                        </div>

                        <?php do_action('woocommerce_checkout_after_order_review'); ?>
                    </section>
                </div>
            </form>
        </div>
        <?php
        do_action('woocommerce_after_checkout_form', $checkout);
        if ($configured_product_id && function_exists('wpb_checkout_restore_normal_cart')) {
            wpb_checkout_restore_normal_cart();
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

        global $product;
        return $product instanceof \WC_Product ? $product->get_id() : 0;
    }

    private function maybe_use_configured_product($product_id, $quantity) {
        if (!$product_id) {
            if (function_exists('wpb_checkout_clear_isolated_cart')) {
                wpb_checkout_clear_isolated_cart();
            }
            return '';
        }

        $product = wc_get_product($product_id);
        if (!$product || !$product->exists() || !$product->is_purchasable()) {
            return __('The configured product is unavailable or cannot be purchased.', 'woocommerce-page-builder');
        }

        if ($product->is_type('variable')) {
            return __('Select a specific variation ID for variable products.', 'woocommerce-page-builder');
        }

        if (
            !function_exists('wpb_checkout_activate_isolated_cart') ||
            !wpb_checkout_activate_isolated_cart($product->get_id(), $quantity)
        ) {
            return __('The selected product could not be added to checkout.', 'woocommerce-page-builder');
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

    private function render_order_summary() {
        $cart = WC()->cart;
        $featured_item = null;
        foreach ($cart->get_cart() as $cart_item) {
            if (!empty($cart_item['data']) && $cart_item['data'] instanceof \WC_Product && $cart_item['data']->exists()) {
                $featured_item = $cart_item;
                break;
            }
        }
        $shipping_methods_html = function_exists('wpb_get_checkout_shipping_methods_html')
            ? wpb_get_checkout_shipping_methods_html()
            : '';
        ?>
        <div class="wpb-order-review">
            <?php if ($featured_item):
                $featured_product = $featured_item['data'];
                $featured_permalink = $featured_product->is_visible() ? $featured_product->get_permalink($featured_item) : '';
            ?>
                <div class="wpb-checkout-product-hero">
                    <?php if ($featured_permalink): ?><a href="<?php echo esc_url($featured_permalink); ?>" tabindex="-1"><?php endif; ?>
                        <?php echo wp_kses_post($featured_product->get_image('woocommerce_single', ['loading' => 'eager'])); ?>
                    <?php if ($featured_permalink): ?></a><?php endif; ?>
                    <div class="wpb-checkout-product-hero-caption">
                        <span><?php esc_html_e('Your order', 'woocommerce-page-builder'); ?></span>
                        <strong><?php echo esc_html($featured_product->get_name()); ?></strong>
                    </div>
                </div>
            <?php endif; ?>

            <div class="wpb-section-heading">
                <span class="wpb-section-kicker"><?php esc_html_e('Your cart', 'woocommerce-page-builder'); ?></span>
                <h3><?php esc_html_e('Product Summary', 'woocommerce-page-builder'); ?></h3>
            </div>

            <div class="wpb-cart-items">
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
                            </div>

                            <?php $variation_data = wc_get_formatted_cart_item_data($cart_item, true); ?>
                            <?php if ($variation_data): ?>
                                <div class="wpb-variation-data"><?php echo wp_kses_post($variation_data); ?></div>
                            <?php endif; ?>

                            <div class="wpb-cart-item-meta">
                                <span class="wpb-cart-item-price"><?php echo wp_kses_post($cart->get_product_price($product)); ?></span>
                                <div class="wpb-quantity-field" aria-label="<?php esc_attr_e('Quantity', 'woocommerce-page-builder'); ?>">
                                    <button type="button" class="wpb-qty-minus" aria-label="<?php esc_attr_e('Decrease quantity', 'woocommerce-page-builder'); ?>">−</button>
                                    <input type="number" class="wpb-qty-input"
                                           value="<?php echo esc_attr($quantity); ?>"
                                           min="1" max="<?php echo esc_attr($max_quantity); ?>"
                                           data-cart-key="<?php echo esc_attr($cart_item_key); ?>"
                                           aria-label="<?php esc_attr_e('Product quantity', 'woocommerce-page-builder'); ?>">
                                    <button type="button" class="wpb-qty-plus" aria-label="<?php esc_attr_e('Increase quantity', 'woocommerce-page-builder'); ?>">+</button>
                                </div>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>

            <div class="wpb-shipping-section" data-shipping-section <?php if (!$shipping_methods_html): ?>hidden<?php endif; ?>>
                <span class="wpb-shipping-title"><?php esc_html_e('Shipping method', 'woocommerce-page-builder'); ?></span>
                <div class="wpb-shipping-methods" data-shipping-methods>
                    <?php echo $shipping_methods_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- generated and escaped by wpb_get_checkout_shipping_methods_html(). ?>
                </div>
            </div>

            <div class="wpb-cart-totals">
                <div class="wpb-cart-total" data-total-row="subtotal">
                    <span><?php esc_html_e('Subtotal', 'woocommerce-page-builder'); ?></span>
                    <strong><?php echo wp_kses_post($cart->get_cart_subtotal()); ?></strong>
                </div>
                <div class="wpb-cart-total" data-total-row="shipping" <?php if (!$shipping_methods_html): ?>hidden<?php endif; ?>>
                    <span><?php esc_html_e('Shipping', 'woocommerce-page-builder'); ?></span>
                    <strong><?php echo wp_kses_post($cart->get_cart_shipping_total()); ?></strong>
                </div>
                <div class="wpb-cart-total wpb-cart-grand-total" data-total-row="total">
                    <span><?php esc_html_e('Total', 'woocommerce-page-builder'); ?></span>
                    <strong><?php echo wp_kses_post($cart->get_cart_total()); ?></strong>
                </div>
            </div>
        </div>
        <?php
    }
}

<?php
if (!defined('ABSPATH')) exit;

class WPB_Checkout_Form_Widget extends \Elementor\Widget_Base {

    public function get_name() {
        return 'wpb-checkout-form';
    }

    public function get_title() {
        return __('Quick Checkout', 'woocommerce-page-builder');
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

    public function get_custom_help_url() {
        return 'https://example.com/';
    }

    protected function register_controls() {
        $this->start_controls_section(
            'form_section',
            [
                'label' => __('Form Fields', 'woocommerce-page-builder'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

$this->add_control(
            'show_name',
            [
                'label' => __('Show Name', 'woocommerce-page-builder'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'woocommerce-page-builder'),
                'label_off' => __('No', 'woocommerce-page-builder'),
                'return' => false,
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'show_phone',
            [
                'label' => __('Show Phone', 'woocommerce-page-builder'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'woocommerce-page-builder'),
                'label_off' => __('No', 'woocommerce-page-builder'),
                'return' => false,
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'show_address',
            [
                'label' => __('Show Address', 'woocommerce-page-builder'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'woocommerce-page-builder'),
                'label_off' => __('No', 'woocommerce-page-builder'),
                'return' => false,
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'show_order_review',
            [
                'label' => __('Show Order Review', 'woocommerce-page-builder'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'woocommerce-page-builder'),
                'label_off' => __('No', 'woocommerce-page-builder'),
                'return' => false,
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'show_payment_methods',
            [
                'label' => __('Show Payment Methods', 'woocommerce-page-builder'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'woocommerce-page-builder'),
                'label_off' => __('No', 'woocommerce-page-builder'),
                'return' => false,
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'submit_text',
            [
                'label' => __('Submit Button Text', 'woocommerce-page-builder'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __('অর্ডার কনফার্ম করুন', 'woocommerce-page-builder'),
            ]
        );

        $this->add_control(
            'product_id',
            [
                'label' => __('Product ID (optional)', 'woocommerce-page-builder'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => '',
                'description' => __('Leave empty on shop/cart pages, or enter a product ID for single product checkout.', 'woocommerce-page-builder'),
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        $css_id = !empty($settings['_element_id']) ? sanitize_text_field($settings['_element_id']) : $this->get_id();
        $product_id = !empty($settings['product_id']) ? (int) $settings['product_id'] : 0;

        if (!class_exists('WooCommerce') || !function_exists('WC')) {
            echo '<p>' . esc_html__('WooCommerce is required.', 'woocommerce-page-builder') . '</p>';
            return;
        }

        $cart = WC()->cart;
        $cart_items = $cart ? $cart->get_cart() : [];

        if (empty($cart_items) && !$product_id) {
            echo '<p>' . esc_html__('Your cart is empty.', 'woocommerce-page-builder') . '</p>';
            return;
        }

        wp_enqueue_script('wc-cart-fragments');
        wp_enqueue_script('woocommerce');
        ?>
        <div class="wpb-checkout-form" id="<?php echo esc_attr($css_id); ?>">
            <form class="wpb-checkout-form-inner" method="post">
                <div class="wpb-checkout-layout">
                    <div class="wpb-checkout-form-section">
                        <h3><?php esc_html_e('Customer Information', 'woocommerce-page-builder'); ?></h3>

                        <?php if ($settings['show_name'] === 'yes'): ?>
                            <div class="wpb-form-field">
                                <label><?php esc_html_e('আপনার নাম', 'woocommerce-page-builder'); ?> <span class="required">*</span></label>
                                <div class="wpb-input-wrap">
                                    <span class="wpb-field-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></span>
                                    <input type="text" name="customer_name" required placeholder="<?php esc_attr_e('আপনার নাম লিখুন', 'woocommerce-page-builder'); ?>">
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if ($settings['show_name'] === 'yes'): ?>
                            <div class="wpb-form-field">
                                <label><?php esc_html_e('ইমেইল ঠিকানা', 'woocommerce-page-builder'); ?> <span class="required">*</span></label>
                                <div class="wpb-input-wrap">
                                    <span class="wpb-field-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg></span>
                                    <input type="email" name="customer_email" required placeholder="<?php esc_attr_e('আপনার ইমেইল দিন', 'woocommerce-page-builder'); ?>">
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if ($settings['show_phone'] === 'yes'): ?>
                            <div class="wpb-form-field">
                                <label><?php esc_html_e('ফোন নাম্বার', 'woocommerce-page-builder'); ?> <span class="required">*</span></label>
                                <div class="wpb-input-wrap">
                                    <span class="wpb-field-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg></span>
                                    <input type="tel" name="customer_phone" required placeholder="<?php esc_attr_e('ফোন নাম্বার দিন', 'woocommerce-page-builder'); ?>">
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if ($settings['show_address'] === 'yes'): ?>
                            <div class="wpb-form-field">
                                <label><?php esc_html_e('ঠিকানা লিখুন', 'woocommerce-page-builder'); ?> <span class="required">*</span></label>
                                <textarea name="customer_address" rows="3" required placeholder="<?php esc_attr_e('আপনার ঠিকানা লিখুন', 'woocommerce-page-builder'); ?>"></textarea>
                            </div>
                        <?php endif; ?>

                        <input type="hidden" name="wpb_checkout_nonce" value="<?php echo wp_create_nonce('wpb_checkout'); ?>">
                        <input type="hidden" name="action" value="wpb_quick_checkout">
                        <input type="hidden" name="product_id" value="<?php echo esc_attr($product_id); ?>">
                    </div>

                    <div class="wpb-checkout-review-section">
                        <?php if ($settings['show_order_review'] === 'yes'): ?>
                            <div class="wpb-order-review">
                                <h3><?php esc_html_e('Order Review', 'woocommerce-page-builder'); ?></h3>

                                <?php if ($product_id && function_exists('wc_get_product')): ?>
                                    <?php $product = wc_get_product($product_id); ?>
                                    <?php if ($product): ?>
                                        <div class="wpb-cart-item" data-product-id="<?php echo esc_attr($product_id); ?>">
                                            <div class="wpb-cart-item-image">
                                                <?php echo $product->get_image('thumbnail'); ?>
                                            </div>
                                            <div class="wpb-cart-item-details">
                                                <span class="wpb-cart-item-name"><?php echo esc_html($product->get_name()); ?></span>
                                                <span class="wpb-cart-item-price"><?php echo wp_kses_post($product->get_price_html()); ?></span>
                                                <div class="wpb-quantity-field">
                                                    <button type="button" class="wpb-qty-minus" data-product-id="<?php echo esc_attr($product_id); ?>">-</button>
                                                    <input type="number" name="quantity" class="wpb-qty-input" value="1" min="1" max="<?php echo esc_attr($product->get_stock_quantity() ? $product->get_stock_quantity() : 99); ?>" data-product-id="<?php echo esc_attr($product_id); ?>">
                                                    <button type="button" class="wpb-qty-plus" data-product-id="<?php echo esc_attr($product_id); ?>">+</button>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                <?php elseif ($cart && !empty($cart_items)): ?>
                                    <div class="wpb-cart-items">
                                        <?php foreach ($cart_items as $cart_item_key => $cart_item): ?>
                                            <?php $product = $cart_item['data']; ?>
                                            <?php if (!$product) continue; ?>
                                            <div class="wpb-cart-item" data-cart-key="<?php echo esc_attr($cart_item_key); ?>">
                                                <div class="wpb-cart-item-image">
                                                    <?php echo $product->get_image('thumbnail'); ?>
                                                </div>
                                                <div class="wpb-cart-item-details">
                                                    <span class="wpb-cart-item-name"><?php echo esc_html($product->get_name()); ?></span>
                                                    <span class="wpb-cart-item-price"><?php echo wp_kses_post(WC()->cart->get_product_price($product)); ?></span>
                                                    <div class="wpb-quantity-field">
                                                        <button type="button" class="wpb-qty-minus" data-cart-key="<?php echo esc_attr($cart_item_key); ?>">-</button>
                                                        <input type="number" class="wpb-qty-input" value="<?php echo esc_attr($cart_item['quantity']); ?>" min="1" max="<?php echo esc_attr($product->get_stock_quantity() ? $product->get_stock_quantity() : 99); ?>" data-cart-key="<?php echo esc_attr($cart_item_key); ?>">
                                                        <button type="button" class="wpb-qty-plus" data-cart-key="<?php echo esc_attr($cart_item_key); ?>">+</button>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>

                                <div class="wpb-cart-totals">
                                    <div class="wpb-cart-total">
                                        <span><?php esc_html_e('Subtotal', 'woocommerce-page-builder'); ?></span>
                                        <span><?php echo wp_kses_post($cart ? $cart->get_cart_subtotal() : wc_price(0)); ?></span>
                                    </div>
                                    <div class="wpb-cart-total">
                                        <span><?php esc_html_e('Total', 'woocommerce-page-builder'); ?></span>
                                        <span><?php echo wp_kses_post($cart ? $cart->get_cart_total() : wc_price(0)); ?></span>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if ($settings['show_payment_methods'] === 'yes'):
                                $available_gateways = function_exists('WC') && WC()->payment_gateways() ? WC()->payment_gateways()->get_available_payment_gateways() : [];
                            ?>
                            <div class="wpb-payment-methods">
                                <h3><?php esc_html_e('Payment Method', 'woocommerce-page-builder'); ?></h3>
                                <?php if (!empty($available_gateways)): ?>
                                    <div class="wpb-payment-options">
                                        <?php foreach ($available_gateways as $gateway): ?>
                                            <div class="wpb-payment-option" data-gateway-id="<?php echo esc_attr($gateway->id); ?>">
                                                <input type="radio" id="wpb-gateway-<?php echo esc_attr($gateway->id); ?>" name="payment_method" value="<?php echo esc_attr($gateway->id); ?>" <?php checked($gateway->chosen, true); ?> class="wpb-payment-radio">
                                                <label for="wpb-gateway-<?php echo esc_attr($gateway->id); ?>" class="wpb-payment-option-label">
                                                    <span class="wpb-payment-option-title"><?php echo esc_html($gateway->get_title()); ?></span>
                                                </label>
                                                <?php if ($gateway->has_fields() || $gateway->get_description()): ?>
                                                    <div class="wpb-payment-option-content">
                                                        <?php $gateway->payment_fields(); ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <p><?php esc_html_e('No payment methods available.', 'woocommerce-page-builder'); ?></p>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <button type="submit" class="wpb-checkout-submit"><span class="spinner"></span><span class="btn-text"><?php echo esc_html($settings['submit_text']); ?></span></button>
                    </div>
                </div>
            </form>

            <div class="wpb-checkout-message"></div>
        </div>
        <?php
    }
}

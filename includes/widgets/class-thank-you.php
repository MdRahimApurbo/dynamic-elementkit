<?php
defined('ABSPATH') || exit;

class WPB_Thank_You_Widget extends \Elementor\Widget_Base {

    public function get_name() {
        return 'wpb-thank-you';
    }

    public function get_title() {
        return __('Thank You / Order Confirmation', 'woocommerce-page-builder');
    }

    public function get_icon() {
        return 'eicon-check-circle';
    }

    public function get_categories() {
        return ['wpb-woo-page-builder'];
    }

    public function get_keywords() {
        return ['thank you', 'order received', 'confirmation', 'woocommerce'];
    }

    protected function register_controls() {
        $this->start_controls_section('content_section', [
            'label' => __('Content', 'woocommerce-page-builder'),
            'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
        ]);

        $this->add_control('title', [
            'label' => __('Success title', 'woocommerce-page-builder'),
            'type' => \Elementor\Controls_Manager::TEXT,
            'default' => __('Thank you for your order!', 'woocommerce-page-builder'),
        ]);

        $this->add_control('message', [
            'label' => __('Success message', 'woocommerce-page-builder'),
            'type' => \Elementor\Controls_Manager::TEXTAREA,
            'default' => __('Your order has been received and is now being processed.', 'woocommerce-page-builder'),
        ]);

        $this->add_control('continue_text', [
            'label' => __('Continue shopping text', 'woocommerce-page-builder'),
            'type' => \Elementor\Controls_Manager::TEXT,
            'default' => __('Continue shopping', 'woocommerce-page-builder'),
        ]);

        $this->add_control('show_customer_details', [
            'label' => __('Show customer details', 'woocommerce-page-builder'),
            'type' => \Elementor\Controls_Manager::SWITCHER,
            'label_on' => __('Show', 'woocommerce-page-builder'),
            'label_off' => __('Hide', 'woocommerce-page-builder'),
            'return_value' => 'yes',
            'default' => 'yes',
        ]);

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        $order = $this->get_current_order();

        if (
            !$order &&
            !empty(\Elementor\Plugin::$instance->editor) &&
            \Elementor\Plugin::$instance->editor->is_edit_mode()
        ) {
            $this->render_preview($settings);
            return;
        }

        if (!$order) {
            echo '<div class="wpb-thank-you wpb-thank-you-empty">' . esc_html__('Order details are unavailable. Please check the link in your confirmation email.', 'woocommerce-page-builder') . '</div>';
            return;
        }

        $this->render_order($order, $settings);
    }

    private function get_current_order() {
        if (!function_exists('wc_get_order')) {
            return false;
        }

        $order_id = absint(get_query_var('order-received'));
        $order_key = isset($_GET['key']) ? wc_clean(wp_unslash($_GET['key'])) : '';
        if (!$order_id && $order_key && function_exists('wc_get_order_id_by_order_key')) {
            $order_id = wc_get_order_id_by_order_key($order_key);
        }

        $order = $order_id ? wc_get_order($order_id) : false;
        if (!$order || !$order_key || !hash_equals((string) $order->get_order_key(), (string) $order_key)) {
            return false;
        }

        return $order;
    }

    private function render_order($order, $settings) {
        $title = !empty($settings['title']) ? $settings['title'] : __('Thank you for your order!', 'woocommerce-page-builder');
        $message = !empty($settings['message']) ? $settings['message'] : __('Your order has been received and is now being processed.', 'woocommerce-page-builder');
        $continue_text = !empty($settings['continue_text']) ? $settings['continue_text'] : __('Continue shopping', 'woocommerce-page-builder');
        ?>
        <div class="wpb-thank-you">
            <header class="wpb-thank-you-hero">
                <span class="wpb-thank-you-check" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none"><path d="m7 12 3 3 7-7"/></svg>
                </span>
                <span class="wpb-thank-you-eyebrow"><?php esc_html_e('Order confirmed', 'woocommerce-page-builder'); ?></span>
                <h1><?php echo esc_html($title); ?></h1>
                <p><?php echo esc_html($message); ?></p>
            </header>

            <section class="wpb-thank-you-meta" aria-label="<?php esc_attr_e('Order information', 'woocommerce-page-builder'); ?>">
                <?php $this->render_meta(__('Order number', 'woocommerce-page-builder'), '#' . $order->get_order_number()); ?>
                <?php $this->render_meta(__('Date', 'woocommerce-page-builder'), wc_format_datetime($order->get_date_created(), wc_date_format())); ?>
                <?php $this->render_meta(__('Total', 'woocommerce-page-builder'), $order->get_formatted_order_total(), true); ?>
                <?php $this->render_meta(__('Payment method', 'woocommerce-page-builder'), $order->get_payment_method_title() ?: __('Not specified', 'woocommerce-page-builder')); ?>
            </section>

            <?php $this->render_gateway_content($order); ?>

            <div class="wpb-thank-you-grid">
                <section class="wpb-thank-you-card wpb-thank-you-order">
                    <div class="wpb-thank-you-card-heading">
                        <h2><?php esc_html_e('Order summary', 'woocommerce-page-builder'); ?></h2>
                        <span><?php echo esc_html(sprintf(_n('%d item', '%d items', $order->get_item_count(), 'woocommerce-page-builder'), $order->get_item_count())); ?></span>
                    </div>
                    <div class="wpb-thank-you-items">
                        <?php foreach ($order->get_items() as $item):
                            $product = $item->get_product(); ?>
                            <div class="wpb-thank-you-item">
                                <div class="wpb-thank-you-thumb"><?php echo $product ? wp_kses_post($product->get_image('woocommerce_thumbnail')) : ''; ?></div>
                                <div class="wpb-thank-you-item-name">
                                    <strong><?php echo esc_html($item->get_name()); ?></strong>
                                    <span><?php echo esc_html(sprintf(__('Quantity: %d', 'woocommerce-page-builder'), $item->get_quantity())); ?></span>
                                </div>
                                <strong class="wpb-thank-you-line-total"><?php echo wp_kses_post($order->get_formatted_line_subtotal($item)); ?></strong>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="wpb-thank-you-totals">
                        <?php foreach ($order->get_order_item_totals() as $total): ?>
                            <div><span><?php echo esc_html(wp_strip_all_tags($total['label'])); ?></span><strong><?php echo wp_kses_post($total['value']); ?></strong></div>
                        <?php endforeach; ?>
                    </div>
                </section>

                <?php if (($settings['show_customer_details'] ?? 'yes') === 'yes'): ?>
                    <aside class="wpb-thank-you-card wpb-thank-you-customer">
                        <h2><?php esc_html_e('Customer details', 'woocommerce-page-builder'); ?></h2>
                        <dl>
                            <div><dt><?php esc_html_e('Name', 'woocommerce-page-builder'); ?></dt><dd><?php echo esc_html(trim($order->get_formatted_billing_full_name())); ?></dd></div>
                            <?php if ($order->get_billing_phone()): ?><div><dt><?php esc_html_e('Phone', 'woocommerce-page-builder'); ?></dt><dd><?php echo esc_html($order->get_billing_phone()); ?></dd></div><?php endif; ?>
                            <?php if ($order->get_billing_email()): ?><div><dt><?php esc_html_e('Email', 'woocommerce-page-builder'); ?></dt><dd><?php echo esc_html($order->get_billing_email()); ?></dd></div><?php endif; ?>
                            <div><dt><?php esc_html_e('Billing address', 'woocommerce-page-builder'); ?></dt><dd><?php echo wp_kses_post($order->get_formatted_billing_address() ?: __('Not provided', 'woocommerce-page-builder')); ?></dd></div>
                        </dl>
                    </aside>
                <?php endif; ?>
            </div>

            <footer class="wpb-thank-you-actions">
                <a class="wpb-thank-you-primary" href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>"><?php echo esc_html($continue_text); ?></a>
                <?php if (is_user_logged_in()): ?>
                    <a class="wpb-thank-you-secondary" href="<?php echo esc_url($order->get_view_order_url()); ?>"><?php esc_html_e('View order', 'woocommerce-page-builder'); ?></a>
                <?php endif; ?>
            </footer>
        </div>
        <?php
    }

    private function render_meta($label, $value, $allow_html = false) {
        echo '<div><span>' . esc_html($label) . '</span><strong>' . ($allow_html ? wp_kses_post($value) : esc_html($value)) . '</strong></div>';
    }

    private function render_gateway_content($order) {
        static $rendered = [];
        $order_id = $order->get_id();
        if (isset($rendered[$order_id])) {
            return;
        }
        $rendered[$order_id] = true;
        echo '<div class="wpb-thank-you-gateway">';
        do_action('woocommerce_thankyou_' . $order->get_payment_method(), $order_id);
        $details_removed = remove_action('woocommerce_thankyou', 'woocommerce_order_details_table', 10);
        do_action('woocommerce_thankyou', $order_id);
        if ($details_removed) {
            add_action('woocommerce_thankyou', 'woocommerce_order_details_table', 10);
        }
        echo '</div>';
    }

    private function render_preview($settings) {
        $title = !empty($settings['title']) ? $settings['title'] : __('Thank you for your order!', 'woocommerce-page-builder');
        ?>
        <div class="wpb-thank-you wpb-thank-you-preview">
            <header class="wpb-thank-you-hero"><span class="wpb-thank-you-check" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none"><path d="m7 12 3 3 7-7"/></svg></span><span class="wpb-thank-you-eyebrow"><?php esc_html_e('Order confirmed', 'woocommerce-page-builder'); ?></span><h1><?php echo esc_html($title); ?></h1><p><?php echo esc_html($settings['message'] ?? ''); ?></p></header>
            <section class="wpb-thank-you-meta"><div><span><?php esc_html_e('Order number', 'woocommerce-page-builder'); ?></span><strong>#1042</strong></div><div><span><?php esc_html_e('Date', 'woocommerce-page-builder'); ?></span><strong><?php echo esc_html(wp_date(get_option('date_format'))); ?></strong></div><div><span><?php esc_html_e('Total', 'woocommerce-page-builder'); ?></span><strong><?php echo wp_kses_post(wc_price(1250)); ?></strong></div><div><span><?php esc_html_e('Payment method', 'woocommerce-page-builder'); ?></span><strong><?php esc_html_e('Cash on delivery', 'woocommerce-page-builder'); ?></strong></div></section>
            <div class="wpb-thank-you-card wpb-thank-you-preview-card"><h2><?php esc_html_e('Live order details appear here', 'woocommerce-page-builder'); ?></h2><p><?php esc_html_e('Products, totals, payment instructions, and customer information are populated securely after checkout.', 'woocommerce-page-builder'); ?></p></div>
        </div>
        <?php
    }
}

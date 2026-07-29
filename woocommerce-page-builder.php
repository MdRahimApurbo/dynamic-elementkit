<?php
/**
 * Plugin Name: WooCommerce Page Builder
 * Plugin URI: https://github.com/mdrahimapurbo/woocommerce-page-builder
 * Description: Design WooCommerce shop, cart, checkout, and account pages with Elementor.
 * Version: 2.0.0
 * Author: Md Rahim Apurbo
 * Author URI: https://github.com/mdrahimapurbo
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: woocommerce-page-builder
 * Domain Path: /languages
 * Requires at least: 5.8
 * Tested up to: 6.4
 * WC requires at least: 6.0
 * WC tested up to: 8.5
 * Requires Plugins: woocommerce, elementor
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Plugin constants
define('WPB_VERSION', '2.0.0');
define('WPB_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('WPB_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WPB_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Declare WooCommerce compatibility
 */
add_action('before_woocommerce_init', function() {
    if (class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class)) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('cart_checkout_blocks', __FILE__, true);
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('product_blocks', __FILE__, true);
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('settings_pages', __FILE__, true);
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('analytics', __FILE__, true);
    }
});

/**
 * Register custom post type for templates
 */
add_action('init', function() {
    register_post_type('wpb_template', [
        'label'           => __('Woo Templates', 'woocommerce-page-builder'),
        'public'          => true,
        'show_ui'         => true,
        'show_in_menu'    => false,
        'show_in_admin_bar' => false,
        'capability_type' => 'post',
        'map_meta_cap'    => true,
        'hierarchical'    => false,
        'supports'        => ['title', 'editor', 'elementor', 'author'],
        'has_archive'     => false,
        'rewrite'         => false,
        'query_var'       => false,
        'can_export'      => true,
        'publicly_queryable' => true,
    ]);
});

/**
 * AJAX: Toggle active template
 */
add_action('wp_ajax_wpb_toggle_active', function() {
    $nonce = isset($_POST['_wpnonce']) ? $_POST['_wpnonce'] : '';
    if (!wp_verify_nonce($nonce, 'wpb_toggle_active')) {
        wp_send_json_error(['message' => __('Security check failed.', 'woocommerce-page-builder')]);
    }

    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => __('Permission denied.', 'woocommerce-page-builder')]);
    }

    $post_id = isset($_POST['post_id']) ? (int) $_POST['post_id'] : 0;
    $post = get_post($post_id);

    if (!$post || $post->post_type !== 'wpb_template') {
        wp_send_json_error(['message' => __('Invalid template.', 'woocommerce-page-builder')]);
    }

    $current_active = get_post_meta($post_id, '_wpb_template_active', true);
    $new_active = $current_active ? '0' : '1';

    $template_type = get_post_meta($post_id, '_wpb_template_type', true);

    if ($new_active === '1') {
        $args = [
            'post_type'   => 'wpb_template',
            'post_status' => ['publish', 'draft'],
            'meta_key'    => '_wpb_template_type',
            'meta_value'  => $template_type,
            'fields'      => 'ids',
            'nopaging'    => true,
        ];
        $same_type = get_posts($args);
        foreach ($same_type as $id) {
            if ((int) $id !== (int) $post_id) {
                update_post_meta($id, '_wpb_template_active', '0');
            }
        }
    }

    update_post_meta($post_id, '_wpb_template_active', $new_active);

    wp_send_json_success(['active' => (bool) $new_active]);
});

/**
 * AJAX: Add to cart
 */
add_action('wp_ajax_wpb_add_to_cart', function() {
    $nonce = isset($_POST['_wpnonce']) ? $_POST['_wpnonce'] : '';
    if (!wp_verify_nonce($nonce, 'wpb_toggle_active')) {
        wp_send_json_error(['message' => __('Security check failed.', 'woocommerce-page-builder')]);
    }

    $product_id = isset($_POST['product_id']) ? (int) $_POST['product_id'] : 0;
    if (!$product_id) {
        wp_send_json_error(['message' => __('Invalid product.', 'woocommerce-page-builder')]);
    }

    if (!class_exists('WooCommerce') || !WC()->cart) {
        wp_send_json_error(['message' => __('WooCommerce not available.', 'woocommerce-page-builder')]);
    }

    $result = WC()->cart->add_to_cart($product_id);

    if ($result) {
        wp_send_json_success(['cart_contents_updated' => true]);
    } else {
        wp_send_json_error(['message' => __('Could not add to cart.', 'woocommerce-page-builder')]);
    }
});
add_action('wp_ajax_nopriv_wpb_add_to_cart', function() {
    $nonce = isset($_POST['_wpnonce']) ? $_POST['_wpnonce'] : '';
    if (!wp_verify_nonce($nonce, 'wpb_toggle_active')) {
        wp_send_json_error(['message' => __('Security check failed.', 'woocommerce-page-builder')]);
    }

    $product_id = isset($_POST['product_id']) ? (int) $_POST['product_id'] : 0;
    if (!$product_id) {
        wp_send_json_error(['message' => __('Invalid product.', 'woocommerce-page-builder')]);
    }

    if (!class_exists('WooCommerce') || !WC()->cart) {
        wp_send_json_error(['message' => __('WooCommerce not available.', 'woocommerce-page-builder')]);
    }

    $result = WC()->cart->add_to_cart($product_id);

    if ($result) {
        wp_send_json_success(['cart_contents_updated' => true]);
    } else {
        wp_send_json_error(['message' => __('Could not add to cart.', 'woocommerce-page-builder')]);
    }
});

/**
 * AJAX: Get cart fragments (count, subtotal, items html)
 */
add_action('wp_ajax_wpb_get_cart_fragments', 'wpb_ajax_get_cart_fragments');
add_action('wp_ajax_nopriv_wpb_get_cart_fragments', 'wpb_ajax_get_cart_fragments');

function wpb_ajax_get_cart_fragments() {
    $nonce = isset($_POST['_wpnonce']) ? $_POST['_wpnonce'] : '';
    if (!wp_verify_nonce($nonce, 'wpb_toggle_active')) {
        wp_send_json_error(['message' => __('Security check failed.', 'woocommerce-page-builder')]);
    }

    if (!class_exists('WooCommerce') || !WC()->cart) {
        wp_send_json_error(['message' => __('WooCommerce not available.', 'woocommerce-page-builder')]);
    }

    $show_thumbnail = isset($_POST['show_thumbnail']) ? sanitize_text_field($_POST['show_thumbnail']) : 'yes';
    $empty_text = isset($_POST['empty_text']) ? sanitize_text_field($_POST['empty_text']) : __('Your cart is empty.', 'woocommerce-page-builder');
    $count = WC()->cart->get_cart_contents_count();
    $subtotal = WC()->cart->get_cart_subtotal();

    ob_start();
    if (WC()->cart->is_empty()) {
        echo '<div class="wpb-cart-empty">' . esc_html($empty_text) . '</div>';
    } else {
        foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
            $product = $cart_item['data'];
            if (!$product) {
                continue;
            }
            $qty = $cart_item['quantity'];
            $item_price = WC()->cart->get_product_price($product);
            $permalink = $product->get_permalink($cart_item);
            ?>
            <div class="wpb-cart-item" data-key="<?php echo esc_attr($cart_item_key); ?>" style="position:relative;display:flex;gap:12px;align-items:center;padding:12px 0;border-bottom:1px solid #f0f0f0;">
                <?php if ($show_thumbnail === 'yes'): ?>
                    <a href="<?php echo esc_url($permalink); ?>" class="wpb-cart-item-thumb" style="flex-shrink:0;">
                        <?php echo $product->get_image('thumbnail'); ?>
                    </a>
                <?php endif; ?>
                <div class="wpb-cart-item-info" style="flex:1;min-width:0;padding-right:24px;">
                    <a href="<?php echo esc_url($permalink); ?>" class="wpb-cart-item-title" style="display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-weight:600;font-size:14px;color:#1f2937;text-decoration:none;"><?php echo esc_html($product->get_name()); ?></a>
                    <span class="wpb-cart-item-qty" style="font-size:13px;color:#6b7280;">×<?php echo esc_html($qty); ?></span>
                    <span class="wpb-cart-item-price" style="font-weight:700;color:#1f2937;font-size:14px;margin-top:4px;display:block;"><?php echo wp_kses_post($item_price); ?></span>
                </div>
                <button type="button" class="wpb-cart-remove" data-key="<?php echo esc_attr($cart_item_key); ?>" aria-label="<?php esc_attr_e('Remove', 'woocommerce-page-builder'); ?>" style="position:absolute;top:8px;right:4px;background:none;border:none;font-size:16px;cursor:pointer;color:#9ca3af;width:24px;height:24px;display:flex;align-items:center;justify-content:center;border-radius:50%;transition:all 0.2s;flex-shrink:0;">&times;</button>
            </div>
            <?php
        }
    }
    $items_html = ob_get_clean();

    ob_start();
    if (WC()->cart->is_empty()) {
        ?>
        <div class="wpb-cart-panel-footer-empty" style="padding:16px;border-top:1px solid #eee;background:#f9fafb;text-align:center;">
            <a href="<?php echo esc_url(wc_get_cart_url()); ?>" style="display:block;width:100%;padding:12px;text-align:center;background:#1f2937;color:#ffffff;text-decoration:none;border-radius:8px;font-weight:600;"><?php esc_html_e('Start Shopping', 'woocommerce-page-builder'); ?></a>
        </div>
        <?php
    } else {
        ?>
        <div class="wpb-cart-panel-footer" style="padding:16px;border-top:1px solid #eee;background:#f9fafb;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;font-size:15px;">
                <span style="color:#6b7280;"><?php esc_html_e('Subtotal', 'woocommerce-page-builder'); ?></span>
                <span style="font-weight:700;color:#1f2937;"><?php echo WC()->cart->get_cart_subtotal(); ?></span>
            </div>
            <span class="wpb-cart-subtotal-trigger" style="display:none;"><?php echo WC()->cart->get_cart_subtotal(); ?></span>
            <p style="font-size:12px;color:#6b7280;margin-bottom:12px;"><?php esc_html_e('Shipping and taxes calculated at checkout.', 'woocommerce-page-builder'); ?></p>
            <a href="<?php echo esc_url(wc_get_cart_url()); ?>" style="display:block;width:100%;padding:14px;text-align:center;background:#1f2937;color:#ffffff;text-decoration:none;border-radius:8px;font-weight:600;font-size:15px;"><?php esc_html_e('Checkout', 'woocommerce-page-builder'); ?></a>
        </div>
        <?php
    }
    $footer_html = ob_get_clean();

    wp_send_json_success([
        'count' => (int) $count,
        'subtotal' => $subtotal,
        'items_html' => $items_html,
        'footer_html' => $footer_html,
    ]);
}

/**
 * AJAX: Remove item from cart
 */
add_action('wp_ajax_wpb_cart_remove_item', 'wpb_ajax_cart_remove_item');
add_action('wp_ajax_nopriv_wpb_cart_remove_item', 'wpb_ajax_cart_remove_item');

function wpb_ajax_cart_remove_item() {
    $nonce = isset($_POST['_wpnonce']) ? $_POST['_wpnonce'] : '';
    if (!wp_verify_nonce($nonce, 'wpb_toggle_active')) {
        wp_send_json_error(['message' => __('Security check failed.', 'woocommerce-page-builder')]);
    }

    if (!class_exists('WooCommerce') || !WC()->cart) {
        wp_send_json_error(['message' => __('WooCommerce not available.', 'woocommerce-page-builder')]);
    }

    $cart_item_key = isset($_POST['cart_item_key']) ? sanitize_text_field($_POST['cart_item_key']) : '';
    if (!$cart_item_key) {
        wp_send_json_error(['message' => __('Invalid item.', 'woocommerce-page-builder')]);
    }

    WC()->cart->remove_cart_item($cart_item_key);
    WC_AJAX::get_refreshed_fragments();

    wp_send_json_success(['removed' => true]);
}

/**
 * AJAX: Update cart quantity
 */
add_action('wp_ajax_wpb_update_cart_quantity', 'wpb_ajax_update_cart_quantity');
add_action('wp_ajax_nopriv_wpb_update_cart_quantity', 'wpb_ajax_update_cart_quantity');

function wpb_ajax_update_cart_quantity() {
    $nonce = isset($_POST['_wpnonce']) ? $_POST['_wpnonce'] : '';
    if (!wp_verify_nonce($nonce, 'wpb_toggle_active')) {
        wp_send_json_error(['message' => __('Security check failed.', 'woocommerce-page-builder')]);
    }

    if (!class_exists('WooCommerce') || !WC()->cart) {
        wp_send_json_error(['message' => __('WooCommerce not available.', 'woocommerce-page-builder')]);
    }

    $cart_item_key = isset($_POST['cart_item_key']) ? sanitize_text_field($_POST['cart_item_key']) : '';
    $quantity = isset($_POST['quantity']) ? (int) $_POST['quantity'] : 1;
    $product_id = isset($_POST['product_id']) ? (int) $_POST['product_id'] : 0;

    if ($product_id > 0 && empty($cart_item_key)) {
        $added = WC()->cart->add_to_cart($product_id, $quantity);
        if (!$added) {
            wp_send_json_error(['message' => __('Could not add to cart.', 'woocommerce-page-builder')]);
        }
        $cart_item_key = $added;
    } elseif ($cart_item_key) {
        $cart = WC()->cart;
        $cart->set_quantity($cart_item_key, $quantity, false);
        $cart->calculate_totals();
    }

    $subtotal = WC()->cart->get_cart_subtotal();
    $total = WC()->cart->get_cart_total();
    $count = WC()->cart->get_cart_contents_count();

    wp_send_json_success([
        'subtotal' => wp_kses_post($subtotal),
        'total' => wp_kses_post($total),
        'count' => $count,
    ]);
}

/**
 * AJAX: Quick checkout
 */
add_action('wp_ajax_wpb_quick_checkout', 'wpb_ajax_quick_checkout');
add_action('wp_ajax_nopriv_wpb_quick_checkout', 'wpb_ajax_quick_checkout');

function wpb_ajax_quick_checkout() {
    $nonce = isset($_POST['wpb_checkout_nonce']) ? $_POST['wpb_checkout_nonce'] : '';
    if (!wp_verify_nonce($nonce, 'wpb_checkout')) {
        wp_send_json_error(['message' => __('Security check failed.', 'woocommerce-page-builder')]);
    }

    if (!class_exists('WooCommerce') || !WC()->cart) {
        wp_send_json_error(['message' => __('WooCommerce cart is not available.', 'woocommerce-page-builder')]);
    }

    $product_id = isset($_POST['product_id']) ? (int) $_POST['product_id'] : 0;

    if (WC()->cart->is_empty() && !$product_id) {
        wp_send_json_error(['message' => __('Your cart is empty.', 'woocommerce-page-builder')]);
    }

    if ($product_id && WC()->cart->is_empty()) {
        $product = wc_get_product($product_id);
        if ($product && $product->is_purchasable()) {
            WC()->cart->empty_cart();
            WC()->cart->add_to_cart($product_id, 1);
        }
    }

    $name = isset($_POST['customer_name']) ? sanitize_text_field($_POST['customer_name']) : '';
    $phone = isset($_POST['customer_phone']) ? sanitize_text_field($_POST['customer_phone']) : '';
    $address = isset($_POST['customer_address']) ? sanitize_text_field($_POST['customer_address']) : '';
    $payment_method = isset($_POST['payment_method']) ? sanitize_text_field($_POST['payment_method']) : '';
    $gateway_prefix = str_replace('woo_', '', $payment_method);
    $transaction_id = isset($_POST[$gateway_prefix . '_trans_id']) ? sanitize_text_field($_POST[$gateway_prefix . '_trans_id']) : (isset($_POST['bkash_trans_id']) ? sanitize_text_field($_POST['bkash_trans_id']) : '');
    $account_number = isset($_POST[$gateway_prefix . '_acc_no']) ? sanitize_text_field($_POST[$gateway_prefix . '_acc_no']) : (isset($_POST['bkash_acc_no']) ? sanitize_text_field($_POST['bkash_acc_no']) : '');

    if (empty($name) || empty($phone) || empty($address)) {
        wp_send_json_error(['message' => __('Please fill all required fields.', 'woocommerce-page-builder')]);
    }

    if (empty($payment_method)) {
        wp_send_json_error(['message' => __('Please select a payment method.', 'woocommerce-page-builder')]);
    }

    $gateways = WC()->payment_gateways()->get_available_payment_gateways();
    if (!isset($gateways[$payment_method])) {
        wp_send_json_error(['message' => __('Invalid payment method.', 'woocommerce-page-builder')]);
    }

    $gateway = $gateways[$payment_method];

    WC()->cart->calculate_totals();

    $order_id = wc_create_order([
        'status' => 'pending',
        'customer_id' => get_current_user_id(),
    ]);

    if (!$order_id) {
        wp_send_json_error(['message' => __('Could not create order.', 'woocommerce-page-builder')]);
    }

    $order = wc_get_order($order_id);

    foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
        $product = $cart_item['data'];
        if (!$product) continue;
        $order->add_product($product, $cart_item['quantity'], [
            'subtotal' => $cart_item['line_subtotal'],
            'subtotal_tax' => $cart_item['line_subtotal_tax'],
            'total' => $cart_item['line_total'],
            'total_tax' => $cart_item['line_tax'],
        ]);
    }

$order->set_address([
        'first_name' => $name,
        'last_name' => '',
        'company' => '',
        'phone' => $phone,
        'address_1' => $address,
        'address_2' => '',
        'city' => '',
        'state' => '',
        'postcode' => '',
        'country' => '',
    ], 'billing');

    $order->set_address([
        'first_name' => $name,
        'last_name' => '',
        'company' => '',
        'phone' => $phone,
        'address_1' => $address,
        'address_2' => '',
        'city' => '',
        'state' => '',
        'postcode' => '',
        'country' => '',
    ], 'shipping');

    if (!empty($transaction_id)) {
        $order->add_order_note(sprintf(__('%s Transaction ID: %s', 'woocommerce-page-builder'), $gateway->get_title(), $transaction_id));
    }

    $order->set_payment_method($gateway);
    $order->set_payment_method_title($gateway->get_title());
    $order->calculate_totals();

    do_action('woocommerce_checkout_process', '');
    do_action('woocommerce_checkout_update_order_meta', $order_id);

    $redirect_url = '';
    $success_message = __('Thank you! Your order has been placed successfully.', 'woocommerce-page-builder');

    if ($payment_method === 'cod') {
        $order->update_status('processing', __('Order placed via Quick Checkout.', 'woocommerce-page-builder'));
        WC()->cart->empty_cart();
        $redirect_url = wc_get_checkout_url();
        $success_message = __('Thank you! Your order has been placed successfully. We will contact you soon.', 'woocommerce-page-builder');
    } else {
        $result = $gateway->process_payment($order_id);

        if (is_wp_error($result)) {
            wp_send_json_error(['message' => $result->get_error_message()]);
        }

        if (isset($result['result']) && $result['result'] === 'success') {
            WC()->cart->empty_cart();
            if (isset($result['redirect']) && $result['redirect']) {
                $redirect_url = $result['redirect'];
            } else {
                $redirect_url = $order->get_checkout_order_received_url();
            }
        } else {
            wp_send_json_error(['message' => __('Payment processing failed. Please try again.', 'woocommerce-page-builder')]);
        }
    }

    wp_send_json_success([
        'message' => $success_message,
        'redirect' => $redirect_url,
    ]);
}

/**
 * AJAX: Filter products by category
 */
add_action('wp_ajax_wpb_filter_products', function() {
    wpb_ajax_filter_products();
});
add_action('wp_ajax_nopriv_wpb_filter_products', function() {
    wpb_ajax_filter_products();
});

function wpb_ajax_filter_products() {
    $nonce = isset($_POST['_wpnonce']) ? $_POST['_wpnonce'] : '';
    if (!wp_verify_nonce($nonce, 'wpb_toggle_active')) {
        wp_send_json_error(['message' => __('Security check failed.', 'woocommerce-page-builder')]);
    }

    $connect_id = isset($_POST['connect_id']) ? sanitize_text_field($_POST['connect_id']) : '';
    $category_id = isset($_POST['category_id']) ? (int) $_POST['category_id'] : 0;
    $category_slug = isset($_POST['category_slug']) ? sanitize_text_field($_POST['category_slug']) : '';
    $category_slugs_raw = isset($_POST['category_slugs']) ? sanitize_text_field($_POST['category_slugs']) : '';
    $category_slugs = array_filter(explode(',', $category_slugs_raw), function($slug) { return !empty($slug); });
    $min_price = isset($_POST['min_price']) ? floatval($_POST['min_price']) : '';
    $max_price = isset($_POST['max_price']) ? floatval($_POST['max_price']) : '';
    $posts_per_page = isset($_POST['posts_per_page']) ? (int) $_POST['posts_per_page'] : 12;
    $order_by = isset($_POST['order_by']) ? sanitize_text_field($_POST['order_by']) : 'date';
    $order = isset($_POST['order']) ? sanitize_text_field($_POST['order']) : 'desc';
    $show_title = isset($_POST['show_title']) ? sanitize_text_field($_POST['show_title']) : 'yes';
    $show_price = isset($_POST['show_price']) ? sanitize_text_field($_POST['show_price']) : 'yes';
    $show_add_to_cart = isset($_POST['show_add_to_cart']) ? sanitize_text_field($_POST['show_add_to_cart']) : 'yes';
    $ajax_add_to_cart = isset($_POST['ajax_add_to_cart']) ? sanitize_text_field($_POST['ajax_add_to_cart']) : 'yes';
    $columns = isset($_POST['columns']) ? (int) $_POST['columns'] : 4;
    $tablet_columns = isset($_POST['tablet_columns']) ? (int) $_POST['tablet_columns'] : max(2, (int) ($columns / 2));
    $mobile_columns = isset($_POST['mobile_columns']) ? (int) $_POST['mobile_columns'] : 1;
    $show_badge = isset($_POST['show_badge']) ? sanitize_text_field($_POST['show_badge']) : 'no';
    $badge_text = isset($_POST['badge_text']) ? sanitize_text_field($_POST['badge_text']) : 'Sale';
    $auto_sale_badge = isset($_POST['auto_sale_badge']) ? sanitize_text_field($_POST['auto_sale_badge']) : 'no';
    $show_discount_percentage = isset($_POST['show_discount_percentage']) ? sanitize_text_field($_POST['show_discount_percentage']) : 'no';
    $button_text = isset($_POST['button_text']) ? sanitize_text_field($_POST['button_text']) : __('Add to Cart', 'woocommerce-page-builder');
    $variable_button_text = isset($_POST['variable_button_text']) ? sanitize_text_field($_POST['variable_button_text']) : __('Select options', 'woocommerce-page-builder');
    $added_button_text = isset($_POST['added_button_text']) ? sanitize_text_field($_POST['added_button_text']) : __('Added!', 'woocommerce-page-builder');
    $target_id = isset($_POST['target_id']) ? sanitize_text_field($_POST['target_id']) : ($connect_id ?: 'wpb-products');

    $query_args = [
        'post_type' => 'product',
        'posts_per_page' => $posts_per_page,
        'post_status' => 'publish',
        'orderby' => $order_by,
        'order' => $order,
    ];

    $resolved_category_ids = [];
    if (!empty($category_slugs)) {
        foreach ($category_slugs as $slug) {
            $term = get_term_by('slug', $slug, 'product_cat');
            if ($term && !is_wp_error($term)) {
                $resolved_category_ids[] = (int) $term->term_id;
            }
        }
        $resolved_category_ids = array_filter($resolved_category_ids, function($id) { return $id > 0; });
    }

    if (!empty($resolved_category_ids)) {
        $query_args['tax_query'] = [
            [
                'taxonomy' => 'product_cat',
                'field' => 'term_id',
                'terms' => count($resolved_category_ids) === 1 ? $resolved_category_ids[0] : $resolved_category_ids,
                'operator' => 'IN',
            ],
        ];
    } elseif ($category_slug) {
        $term = get_term_by('slug', $category_slug, 'product_cat');
        if ($term && !is_wp_error($term)) {
            $query_args['tax_query'] = [
                [
                    'taxonomy' => 'product_cat',
                    'field' => 'term_id',
                    'terms' => (int) $term->term_id,
                ],
            ];
        }
    } elseif ($category_id > 0) {
        $query_args['tax_query'] = [
            [
                'taxonomy' => 'product_cat',
                'field' => 'term_id',
                'terms' => $category_id,
            ],
        ];
    }

    if ($min_price !== '' || $max_price !== '') {
        $meta_query = [];
        if ($min_price !== '') {
            $meta_query[] = [
                'key' => '_price',
                'value' => $min_price,
                'compare' => '>=',
                'type' => 'DECIMAL',
            ];
        }
        if ($max_price !== '') {
            $meta_query[] = [
                'key' => '_price',
                'value' => $max_price,
                'compare' => '<=',
                'type' => 'DECIMAL',
            ];
        }
        $query_args['meta_query'] = $meta_query;
    }

    $products = new WP_Query($query_args);

    ob_start();

    if ($products->have_posts()) {
        ?>
        <div class="wpb-products" id="wpb-grid-<?php echo esc_attr($target_id); ?>" style="--wpb-cols-desktop: <?php echo esc_attr($columns); ?>; --wpb-cols-tablet: <?php echo esc_attr($tablet_columns); ?>; --wpb-cols-mobile: <?php echo esc_attr($mobile_columns); ?>;">
            <?php while ($products->have_posts()): $products->the_post(); ?>
                <?php global $product; $product = wc_get_product(get_the_ID()); ?>
                    <div class="wpb-product">
                        <div class="wpb-product-image">
                            <a href="<?php the_permalink(); ?>">
                                <?php echo $product ? $product->get_image() : ''; ?>
                            </a>
                            <?php wpb_product_watermark_overlay(); ?>
                        <?php if ($show_badge === 'yes' && !empty($badge_text)): ?>
                            <div class="wpb-product-badge">
                                <?php echo esc_html($badge_text); ?>
                            </div>
                        <?php endif; ?>
                        <?php if ($auto_sale_badge === 'yes' && $product && $product->is_on_sale()): ?>
                            <div class="wpb-product-badge wpb-sale-badge">
                                <?php esc_html_e('Sale', 'woocommerce-page-builder'); ?>
                            </div>
                        <?php endif; ?>
                        <?php if ($show_discount_percentage === 'yes' && $product && $product->is_on_sale()): ?>
                            <?php
                            $regular_price = $product->get_regular_price();
                            $sale_price = $product->get_sale_price();
                            if ($regular_price && $sale_price && $regular_price > $sale_price):
                                $discount_percentage = round((($regular_price - $sale_price) / $regular_price) * 100);
                            ?>
                                <div class="wpb-product-badge wpb-discount-badge">
                                    -<?php echo esc_html($discount_percentage); ?>%
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                    <div class="wpb-product-details">
                        <div class="wpb-product-content-wrapper">
                            <?php if ($show_title === 'yes'): ?>
                                <h3 class="wpb-product-title">
                                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                </h3>
                            <?php endif; ?>
                            <?php if ($show_price === 'yes' && $product): ?>
                                <span class="wpb-product-price">
                                    <?php
                                    if ($product->is_type('variable')) {
                                        $min_price = $product->get_variation_price('min', true);
                                        $max_price = $product->get_variation_price('max', true);
                                        if ($min_price !== $max_price) {
                                            echo wp_kses_post('<span class="wpb-price-from">' . __('From:', 'woocommerce-page-builder') . '</span> ' . wc_price($min_price));
                                        } else {
                                            echo wc_price($min_price);
                                        }
                                    } else {
                                        echo $product->get_price_html();
                                    }
                                    ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        <?php if ($show_add_to_cart === 'yes' && $product): ?>
                            <div class="wpb-product-add-to-cart">
                                <?php if ($product->is_type('variable')): ?>
                                    <a href="<?php the_permalink(); ?>" class="wpb-add-to-cart button wpb-variation-button">
                                        <span class="wpb-button-text"><?php echo esc_html($variable_button_text); ?></span>
                                        <span class="wpb-button-spinner" aria-hidden="true"></span>
                                    </a>
                                <?php else: ?>
                                    <button class="wpb-add-to-cart button" data-product-id="<?php echo esc_attr($product->get_id()); ?>">
                                        <span class="wpb-button-text"><?php echo esc_html($button_text); ?></span>
                                        <span class="wpb-button-spinner" aria-hidden="true"></span>
                                    </button>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endwhile; wp_reset_postdata(); ?>
        </div>
        <?php
    } else {
        echo '<p>' . __('No products found.', 'woocommerce-page-builder') . '</p>';
    }

    wp_send_json_success(['html' => ob_get_clean()]);
}

/**
 * AJAX: Search products (live dropdown)
 */
add_action('wp_ajax_wpb_search_products', 'wpb_ajax_search_products');
add_action('wp_ajax_nopriv_wpb_search_products', 'wpb_ajax_search_products');

function wpb_ajax_search_products() {
    $nonce = isset($_POST['_wpnonce']) ? $_POST['_wpnonce'] : '';
    if (!wp_verify_nonce($nonce, 'wpb_toggle_active')) {
        wp_send_json_error(['message' => __('Security check failed.', 'woocommerce-page-builder')]);
    }

    if (!class_exists('WooCommerce')) {
        wp_send_json_error(['message' => __('WooCommerce not available.', 'woocommerce-page-builder')]);
    }

    $term = isset($_POST['term']) ? sanitize_text_field($_POST['term']) : '';
    $per_page = isset($_POST['per_page']) ? (int) $_POST['per_page'] : 8;
    $show_image = isset($_POST['show_image']) ? sanitize_text_field($_POST['show_image']) : 'yes';
    $show_price = isset($_POST['show_price']) ? sanitize_text_field($_POST['show_price']) : 'yes';
    $show_no_result = isset($_POST['show_no_result']) ? sanitize_text_field($_POST['show_no_result']) : 'yes';
    $no_result_text = isset($_POST['no_result_text']) ? sanitize_text_field($_POST['no_result_text']) : __('No products found.', 'woocommerce-page-builder');
    $connect_id = isset($_POST['connect_id']) ? sanitize_text_field($_POST['connect_id']) : '';

    $term = trim($term);
    $per_page = max(1, min(30, $per_page));

    if ($term === '') {
        wp_send_json_success(['html' => '', 'count' => 0]);
    }

    $query_args = [
        'post_type' => 'product',
        'post_status' => 'publish',
        'posts_per_page' => $per_page,
        's' => $term,
    ];

    $products = new WP_Query($query_args);

    ob_start();

    if ($products->have_posts()) {
        ?>
        <ul class="wpb-search-results-list">
            <?php while ($products->have_posts()): $products->the_post(); global $product; ?>
                <li class="wpb-search-result-item">
                    <a href="<?php the_permalink(); ?>" class="wpb-search-result-link">
                        <?php if ($show_image === 'yes'): ?>
                            <span class="wpb-search-result-image">
                                <?php echo $product ? $product->get_image('thumbnail') : ''; ?>
                            </span>
                        <?php endif; ?>
                        <span class="wpb-search-result-content">
                            <span class="wpb-search-result-title"><?php the_title(); ?></span>
                            <?php if ($show_price === 'yes' && $product): ?>
                                <span class="wpb-search-result-price"><?php echo $product->get_price_html(); ?></span>
                            <?php endif; ?>
                        </span>
                    </a>
                </li>
            <?php endwhile; wp_reset_postdata(); ?>
        </ul>
        <?php
    } else {
        if ($show_no_result === 'yes') {
            echo '<div class="wpb-search-no-results">' . esc_html($no_result_text) . '</div>';
        }
    }

    wp_send_json_success([
        'html' => ob_get_clean(),
        'count' => (int) $products->found_posts,
        'connect_id' => $connect_id,
    ]);
}

/**
 * Get archive title for WooCommerce pages
 */
function wpb_get_archive_title() {
    if (is_product_category()) {
        $category = get_queried_object();
        return $category ? $category->name : '';
    } elseif (is_product_tag()) {
        $tag = get_queried_object();
        return $tag ? $tag->name : '';
    } elseif (is_shop()) {
        return get_the_title(get_option('woocommerce_shop_page_id'));
    } elseif (is_cart()) {
        return get_the_title(get_option('woocommerce_cart_page_id'));
    } elseif (is_checkout()) {
        return get_the_title(get_option('woocommerce_checkout_page_id'));
    } elseif (is_account_page()) {
        return get_the_title(get_option('woocommerce_myaccount_page_id'));
    }
    return '';
}

/**
 * Get active template for a WooCommerce page type
 */
function wpb_get_active_template($type) {
    $args = [
        'post_type'   => 'wpb_template',
        'post_status' => ['publish', 'draft'],
        'meta_key'    => '_wpb_template_type',
        'meta_value'  => $type,
        'meta_query'  => [
            [
                'key'   => '_wpb_template_active',
                'value' => '1',
            ],
        ],
        'posts_per_page' => 1,
    ];

    $templates = get_posts($args);
    return $templates ? $templates[0] : false;
}

/**
 * Resolve the template assigned to the current WooCommerce request.
 *
 * This must be available before Elementor runs its frontend style pass so
 * dynamic template documents can participate in Elementor's normal asset
 * discovery and atomic CSS generation.
 */
function wpb_get_current_active_template() {
    if (!function_exists('is_woocommerce')) {
        return false;
    }
    $type = null;
    if (is_product()) {
        $type = 'product';
    } elseif (is_product_category()) {
        $type = 'product-category';
    } elseif (is_product_tag()) {
        $type = 'product-tag';
    } elseif (is_shop()) {
        $type = 'shop';
    } elseif (is_cart()) {
        $type = 'cart';
    } elseif (is_checkout()) {
        $type = 'checkout';
    } elseif (is_account_page()) {
        $type = 'myaccount';
    }
    if (!$type) {
        return false;
    }
    $active_template = wpb_get_active_template($type);
    if (!$active_template && in_array($type, ['shop', 'product-category', 'product-tag'], true)) {
        $active_template = wpb_get_active_template('archive');
    }
    return $active_template;
}

/**
 * Register the dynamic document before Elementor's frontend enqueue pass.
 *
 * Elementor 4 collects document IDs from `elementor/post/render`, then creates
 * and enqueues local/global atomic CSS during
 * `elementor/frontend/after_enqueue_post_styles`. Rendering the template after
 * get_header() is too late because wp_head() and that style pass have already
 * happened.
 */
function wpb_register_elementor_template_document() {
    if (!class_exists('\Elementor\Plugin') || !\Elementor\Plugin::$instance->frontend) {
        return;
    }
    $active_template = wpb_get_current_active_template();
    if (!$active_template) {
        return;
    }
    $template_id = (int) $active_template->ID;
    $document = \Elementor\Plugin::$instance->documents->get($template_id);
    if (!$document || !$document->is_built_with_elementor()) {
        return;
    }
    // Discover conditional widget assets before Elementor enqueues them.
    $document->update_runtime_elements();
    // Let Elementor (including its v4 atomic-style manager) own CSS generation.
    do_action('elementor/post/render', $template_id);
    global $wpb_elementor_template_id;
    $wpb_elementor_template_id = $template_id;
}

add_action('wp_enqueue_scripts', 'wpb_register_elementor_template_document', 1);

/**
 * Run Elementor's normal style pass after all other integrations have had a
 * chance to register their documents. Header/footer and Theme Builder plugins
 * commonly register template IDs later in `wp_enqueue_scripts`; calling
 * Elementor's one-shot style pass before them drops their generated CSS.
 *
 * WooCommerce endpoints usually are not Elementor documents, so Elementor does
 * not schedule this pass by itself for these requests.
 */
function wpb_enqueue_elementor_template_styles() {
    global $wpb_elementor_template_id;
    if (empty($wpb_elementor_template_id) || !class_exists('\Elementor\Plugin')) {
        return;
    }
    \Elementor\Plugin::$instance->frontend->enqueue_styles();
    // Keep legacy/v3 post CSS working on mixed Elementor documents.
    \Elementor\Core\Files\CSS\Post::create((int) $wpb_elementor_template_id)->enqueue();
}

add_action('wp_enqueue_scripts', 'wpb_enqueue_elementor_template_styles', PHP_INT_MAX);

/**
 * Override WooCommerce templates
 */
add_filter('template_include', function($template) {
    $active_template = wpb_get_current_active_template();
    if (!$active_template) {
        return $template;
    }
    global $wpb_active_template;
    $wpb_active_template = $active_template;
    return WPB_PLUGIN_PATH . 'templates/override.php';
}, 9999);

/**
 * Get the watermark logo URL based on the selected source.
 */
function wpb_get_watermark_logo() {
    $source = get_option('wpb_watermark_logo_source', 'custom');

    if ($source === 'site') {
        $logo = '';
        if (function_exists('get_custom_logo') && has_custom_logo()) {
            $logo_id = get_theme_mod('custom_logo');
            $logo = wp_get_attachment_image_url($logo_id, 'full');
        }
        if (!$logo) {
            $logo = get_site_icon_url();
        }
        return $logo ? $logo : '';
    }

    return get_option('wpb_watermark_logo', '');
}

/**
 * Render the product watermark overlay on single product pages.
 * Injects the overlay inside the gallery image figure so it positions correctly.
 */
add_filter('woocommerce_single_product_image_thumbnail_html', function ($html) {
    if (!get_option('wpb_watermark_enabled', 0)) {
        return $html;
    }

    $logo = wpb_get_watermark_logo();
    if (!$logo) {
        return $html;
    }

    $opacity = (float) get_option('wpb_watermark_opacity', 0.3);
    $opacity = max(0.05, min(1, $opacity));

    $overlay = '<span class="wpb-product-watermark wpb-single-watermark" aria-hidden="true" style="background-image:url(\'' . esc_url($logo) . '\');opacity:' . esc_attr($opacity) . ';"></span>';

    return $html . $overlay;
});

/**
 * Render the product watermark overlay if enabled.
 */
function wpb_product_watermark_overlay() {
    if (!get_option('wpb_watermark_enabled', 0)) {
        return;
    }

    $logo = wpb_get_watermark_logo();
    if (!$logo) {
        return;
    }

    $opacity = (float) get_option('wpb_watermark_opacity', 0.3);
    $opacity = max(0.05, min(1, $opacity));
    ?>
    <span class="wpb-product-watermark" aria-hidden="true" style="background-image:url('<?php echo esc_url($logo); ?>');opacity:<?php echo esc_attr($opacity); ?>;"></span>
    <?php
}

/**
 * Main plugin class
 */
class WooCommerce_Page_Builder {

    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->includes();
        $this->init_hooks();
        register_activation_hook(__FILE__, [$this, 'activate']);
        register_deactivation_hook(__FILE__, [$this, 'deactivate']);
    }

    private function includes() {
        require_once WPB_PLUGIN_PATH . 'includes/class-admin.php';
        require_once WPB_PLUGIN_PATH . 'includes/class-template-list-table.php';
        require_once WPB_PLUGIN_PATH . 'includes/widgets/class-widgets-loader.php';
    }

    private function init_hooks() {
        add_action('plugins_loaded', [$this, 'load_textdomain']);
        add_action('elementor/dynamic_tags/register', [$this, 'register_dynamic_tags']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_frontend_assets']);
        add_action('elementor/editor/before_enqueue_scripts', [$this, 'enqueue_frontend_assets']);
        add_action('elementor/editor/after_enqueue_styles', [$this, 'enqueue_frontend_assets']);
    }

    public function register_dynamic_tags( $dynamic_tags_manager ) {
        $dynamic_tags_manager->register_group( 'wpb_product', [
            'title' => __( 'WooCommerce Page Builder', 'woocommerce-page-builder' ),
        ] );

        require_once WPB_PLUGIN_PATH . 'includes/dynamic-tags/tags/product-title-tag.php';
        require_once WPB_PLUGIN_PATH . 'includes/dynamic-tags/tags/product-description-tag.php';

        $dynamic_tags_manager->register( new \WooCommerce_Page_Builder\Dynamic_Tags\Tags\Product_Title_Tag() );
        $dynamic_tags_manager->register( new \WooCommerce_Page_Builder\Dynamic_Tags\Tags\Product_Description_Tag() );
    }

    public function enqueue_frontend_assets() {
        $embla_version = '8.0.1';
        wp_enqueue_style('wpb-embla', WPB_PLUGIN_URL . 'assets/css/embla-carousel.css', [], $embla_version);
        wp_enqueue_style('wpb-embla-theme', WPB_PLUGIN_URL . 'assets/css/embla-dots.css', [], $embla_version);

        wp_enqueue_style('wpb-product-grid', WPB_PLUGIN_URL . 'assets/css/product-grid.css', ['wpb-embla', 'wpb-embla-theme'], WPB_VERSION);
        wp_enqueue_script('wpb-embla', WPB_PLUGIN_URL . 'assets/vendors/embla-carousel.js', [], WPB_VERSION, true);
        wp_enqueue_script('wpb-product-grid', WPB_PLUGIN_URL . 'assets/js/product-grid.js', ['jquery', 'wpb-embla'], WPB_VERSION, true);

        $cart_css_version = file_exists(WPB_PLUGIN_PATH . 'assets/css/cart.css') ? filemtime(WPB_PLUGIN_PATH . 'assets/css/cart.css') : WPB_VERSION;
        $cart_js_version = file_exists(WPB_PLUGIN_PATH . 'assets/js/cart.js') ? filemtime(WPB_PLUGIN_PATH . 'assets/js/cart.js') : WPB_VERSION;

        wp_enqueue_style('wpb-cart', WPB_PLUGIN_URL . 'assets/css/cart.css', [], $cart_css_version);
        wp_enqueue_script('wpb-cart', WPB_PLUGIN_URL . 'assets/js/cart.js', ['jquery'], $cart_js_version, true);

        $checkout_css_version = file_exists(WPB_PLUGIN_PATH . 'assets/css/checkout.css') ? filemtime(WPB_PLUGIN_PATH . 'assets/css/checkout.css') : WPB_VERSION;

        wp_enqueue_style('wpb-checkout', WPB_PLUGIN_URL . 'assets/css/checkout.css', [], $checkout_css_version);
        wp_enqueue_script('wpb-checkout', WPB_PLUGIN_URL . 'assets/js/checkout.js', ['jquery', 'wpb-product-grid'], WPB_VERSION, true);

        $product_media_css_version = file_exists(WPB_PLUGIN_PATH . 'assets/css/product-media.css') ? filemtime(WPB_PLUGIN_PATH . 'assets/css/product-media.css') : WPB_VERSION;

        wp_enqueue_style('wpb-product-media', WPB_PLUGIN_URL . 'assets/css/product-media.css', [], $product_media_css_version);

        $product_media_js_version = file_exists(WPB_PLUGIN_PATH . 'assets/js/product-media.js') ? filemtime(WPB_PLUGIN_PATH . 'assets/js/product-media.js') : WPB_VERSION;
        wp_enqueue_script('wpb-product-media', WPB_PLUGIN_URL . 'assets/js/product-media.js', [], $product_media_js_version, true);

        wp_localize_script('wpb-product-grid', 'wpbAdmin', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'toggleNonce' => wp_create_nonce('wpb_toggle_active'),
            'addedText' => __('Added!', 'woocommerce-page-builder'),
            'addToCartText' => __('Add to Cart', 'woocommerce-page-builder'),
        ]);

        wp_localize_script('wpb-cart', 'wpbAdmin', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'toggleNonce' => wp_create_nonce('wpb_toggle_active'),
            'addedText' => __('Added!', 'woocommerce-page-builder'),
            'addToCartText' => __('Add to Cart', 'woocommerce-page-builder'),
        ]);
    }

    public function load_textdomain() {
        load_plugin_textdomain('woocommerce-page-builder', false, dirname(WPB_PLUGIN_BASENAME) . '/languages/');
    }

    public function activate() {
        flush_rewrite_rules();
    }

    public function deactivate() {
        flush_rewrite_rules();
    }
}

function wpb_init() {
    return WooCommerce_Page_Builder::get_instance();
}
add_action('plugins_loaded', 'wpb_init');


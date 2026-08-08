<?php

/**

 * Plugin Name: Dynamic ElementKit

 * Plugin URI: https://github.com/mdrahimapurbo/dynamic-elementkit

 * Description: Build Elementor templates for WordPress pages, headers, footers, and WooCommerce layouts.

 * Version: 2.1.4

 * Author: Md Rahim Apurbo

 * Author URI: https://github.com/mdrahimapurbo

 * License: GPL-2.0+

 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt

 * Text Domain: dynamic-elementkit

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

define('DEK_VERSION', '2.1.4');

define('DEK_PLUGIN_PATH', plugin_dir_path(__FILE__));

define('DEK_PLUGIN_URL', plugin_dir_url(__FILE__));

define('DEK_PLUGIN_BASENAME', plugin_basename(__FILE__));



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

    register_post_type('dek_template', [

        'label'           => __('Dynamic ElementKit Templates', 'dynamic-elementkit'),

        'public'          => true,

        'show_ui'         => true,

        'show_in_menu'    => false,

        'show_in_admin_bar' => false,

        'capability_type' => 'post',

        'map_meta_cap'    => true,

        'hierarchical'    => false,

        'supports'        => ['title', 'editor', 'elementor', 'author'],

        'has_archive'     => false,

        'rewrite'         => [
            'slug'       => 'landing',
            'with_front' => false,
        ],

        'query_var'       => true,

        'can_export'      => true,

        'publicly_queryable' => true,

    ]);

});

// Make Dynamic ElementKit templates available in Elementor's editor.
add_filter('elementor/cpt_support', function($post_types) {
    if (is_array($post_types) && !in_array('dek_template', $post_types, true)) {
        $post_types[] = 'dek_template';
    }

    return $post_types;
});

// Refresh rewrite rules once after an upgrade so /landing/{slug}/ works.
add_action('init', function() {
    if (get_option('dek_rewrite_version') === DEK_VERSION) {
        return;
    }

    flush_rewrite_rules(false);
    update_option('dek_rewrite_version', DEK_VERSION);
}, 99);



/**

 * AJAX: Toggle active template

 */

add_action('wp_ajax_dek_toggle_active', function() {

    $nonce = isset($_POST['_wpnonce']) ? $_POST['_wpnonce'] : '';

    if (!wp_verify_nonce($nonce, 'dek_toggle_active')) {

        wp_send_json_error(['message' => __('Security check failed.', 'dynamic-elementkit')]);

    }



    if (!current_user_can('manage_options')) {

        wp_send_json_error(['message' => __('Permission denied.', 'dynamic-elementkit')]);

    }



    $post_id = isset($_POST['post_id']) ? (int) $_POST['post_id'] : 0;

    $post = get_post($post_id);



    if (!$post || $post->post_type !== 'dek_template') {

        wp_send_json_error(['message' => __('Invalid template.', 'dynamic-elementkit')]);

    }



    $current_active = get_post_meta($post_id, '_dek_template_active', true);

    $new_active = $current_active ? '0' : '1';



    $template_type = get_post_meta($post_id, '_dek_template_type', true);



    if ($new_active === '1') {

        $args = [

            'post_type'   => 'dek_template',

            'post_status' => ['publish', 'draft'],

            'meta_key'    => '_dek_template_type',

            'meta_value'  => $template_type,

            'fields'      => 'ids',

            'nopaging'    => true,

        ];

        $same_type = get_posts($args);

        foreach ($same_type as $id) {

            if ((int) $id !== (int) $post_id) {

                update_post_meta($id, '_dek_template_active', '0');

            }

        }

    }



    update_post_meta($post_id, '_dek_template_active', $new_active);



    wp_send_json_success(['active' => (bool) $new_active]);

});



/**

 * AJAX: Add to cart

 */

add_action('wp_ajax_dek_add_to_cart', function() {

    $nonce = isset($_POST['_wpnonce']) ? $_POST['_wpnonce'] : '';

    if (!wp_verify_nonce($nonce, 'dek_toggle_active')) {

        wp_send_json_error(['message' => __('Security check failed.', 'dynamic-elementkit')]);

    }



    $product_id = isset($_POST['product_id']) ? (int) $_POST['product_id'] : 0;

    if (!$product_id) {

        wp_send_json_error(['message' => __('Invalid product.', 'dynamic-elementkit')]);

    }



    if (!class_exists('WooCommerce') || !WC()->cart) {

        wp_send_json_error(['message' => __('WooCommerce not available.', 'dynamic-elementkit')]);

    }



    $result = WC()->cart->add_to_cart($product_id);



    if ($result) {

        wp_send_json_success(['cart_contents_updated' => true]);

    } else {

        wp_send_json_error(['message' => __('Could not add to cart.', 'dynamic-elementkit')]);

    }

});

add_action('wp_ajax_nopriv_dek_add_to_cart', function() {

    $nonce = isset($_POST['_wpnonce']) ? $_POST['_wpnonce'] : '';

    if (!wp_verify_nonce($nonce, 'dek_toggle_active')) {

        wp_send_json_error(['message' => __('Security check failed.', 'dynamic-elementkit')]);

    }



    $product_id = isset($_POST['product_id']) ? (int) $_POST['product_id'] : 0;

    if (!$product_id) {

        wp_send_json_error(['message' => __('Invalid product.', 'dynamic-elementkit')]);

    }



    if (!class_exists('WooCommerce') || !WC()->cart) {

        wp_send_json_error(['message' => __('WooCommerce not available.', 'dynamic-elementkit')]);

    }



    $result = WC()->cart->add_to_cart($product_id);



    if ($result) {

        wp_send_json_success(['cart_contents_updated' => true]);

    } else {

        wp_send_json_error(['message' => __('Could not add to cart.', 'dynamic-elementkit')]);

    }

});



/**

 * AJAX: Get cart fragments (count, subtotal, items html)

 */

add_action('wp_ajax_dek_get_cart_fragments', 'dek_ajax_get_cart_fragments');

add_action('wp_ajax_nopriv_dek_get_cart_fragments', 'dek_ajax_get_cart_fragments');



function dek_ajax_get_cart_fragments() {

    $nonce = isset($_POST['_wpnonce']) ? $_POST['_wpnonce'] : '';

    if (!wp_verify_nonce($nonce, 'dek_toggle_active')) {

        wp_send_json_error(['message' => __('Security check failed.', 'dynamic-elementkit')]);

    }



    if (!class_exists('WooCommerce') || !WC()->cart) {

        wp_send_json_error(['message' => __('WooCommerce not available.', 'dynamic-elementkit')]);

    }



    $show_thumbnail = isset($_POST['show_thumbnail']) ? sanitize_text_field($_POST['show_thumbnail']) : 'yes';

    $empty_text = !empty($_POST['empty_text']) ? sanitize_text_field(wp_unslash($_POST['empty_text'])) : __('আপনার শপিং কার্টটি খালি।', 'dynamic-elementkit');
    $continue_text = !empty($_POST['continue_text']) ? sanitize_text_field(wp_unslash($_POST['continue_text'])) : __('কেনাকাটা চালিয়ে যান', 'dynamic-elementkit');
    $subtotal_text = !empty($_POST['subtotal_text']) ? sanitize_text_field(wp_unslash($_POST['subtotal_text'])) : __('মোট পণ্যমূল্য', 'dynamic-elementkit');
    $shipping_text = !empty($_POST['shipping_text']) ? sanitize_text_field(wp_unslash($_POST['shipping_text'])) : __('চেকআউটের সময় ডেলিভারি চার্জ হিসাব করা হবে।', 'dynamic-elementkit');
    $checkout_text = !empty($_POST['checkout_text']) ? sanitize_text_field(wp_unslash($_POST['checkout_text'])) : __('অর্ডার সম্পন্ন করুন', 'dynamic-elementkit');
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

            <div class="wpb-cart-item<?php echo 'yes' === $show_thumbnail ? '' : ' wpb-cart-item-no-thumb'; ?>" data-key="<?php echo esc_attr($cart_item_key); ?>">
                <?php if ($show_thumbnail === 'yes'): ?>
                    <a href="<?php echo esc_url($permalink); ?>" class="wpb-cart-item-thumb">
                        <?php echo $product->get_image('thumbnail'); ?>

                    </a>

                <?php endif; ?>

                <div class="wpb-cart-item-info">
                    <a href="<?php echo esc_url($permalink); ?>" class="wpb-cart-item-title"><?php echo esc_html($product->get_name()); ?></a>
                    <span class="wpb-cart-item-qty">×<?php echo esc_html($qty); ?></span>
                    <span class="wpb-cart-item-price"><?php echo wp_kses_post($item_price); ?></span>
                </div>
                <button type="button" class="wpb-cart-remove" data-key="<?php echo esc_attr($cart_item_key); ?>" aria-label="<?php esc_attr_e('কার্ট থেকে সরান', 'dynamic-elementkit'); ?>"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M9 7V4h6v3M7 7l1 13h8l1-13M10 11v5M14 11v5"></path></svg></button>
            </div>

            <?php

        }

    }

    $items_html = ob_get_clean();



    ob_start();

    if (WC()->cart->is_empty()) {

        ?>

        <div class="wpb-cart-panel-footer-empty wpb-cart-footer">
            <a class="wpb-cart-checkout-btn" href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>"><?php echo esc_html($continue_text); ?></a>
        </div>

        <?php

    } else {

        ?>

        <div class="wpb-cart-panel-footer wpb-cart-footer">
            <div class="wpb-cart-subtotal-row">
                <span><?php echo esc_html($subtotal_text); ?></span>
                <span class="wpb-cart-subtotal"><?php echo WC()->cart->get_cart_subtotal(); ?></span>
            </div>
            <span class="wpb-cart-subtotal-trigger" style="display:none;"><?php echo WC()->cart->get_cart_subtotal(); ?></span>
            <p class="wpb-cart-shipping-note"><?php echo esc_html($shipping_text); ?></p>
            <a class="wpb-cart-checkout-btn" href="<?php echo esc_url(wc_get_checkout_url()); ?>"><?php echo esc_html($checkout_text); ?></a>
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

add_action('wp_ajax_dek_cart_remove_item', 'dek_ajax_cart_remove_item');

add_action('wp_ajax_nopriv_dek_cart_remove_item', 'dek_ajax_cart_remove_item');



function dek_ajax_cart_remove_item() {

    $nonce = isset($_POST['_wpnonce']) ? $_POST['_wpnonce'] : '';

    if (!wp_verify_nonce($nonce, 'dek_toggle_active')) {

        wp_send_json_error(['message' => __('Security check failed.', 'dynamic-elementkit')]);

    }



    if (!class_exists('WooCommerce') || !WC()->cart) {

        wp_send_json_error(['message' => __('WooCommerce not available.', 'dynamic-elementkit')]);

    }



    $cart_item_key = isset($_POST['cart_item_key']) ? sanitize_text_field($_POST['cart_item_key']) : '';

    if (!$cart_item_key) {

        wp_send_json_error(['message' => __('Invalid item.', 'dynamic-elementkit')]);

    }



    WC()->cart->remove_cart_item($cart_item_key);

    WC_AJAX::get_refreshed_fragments();



    wp_send_json_success(['removed' => true]);

}



/**
 * Build a request-local cart solely for rendering a configured product. Nothing
 * is loaded from or persisted to the WooCommerce cart session.
 */
function dek_checkout_activate_isolated_cart($product_id = 0, $quantity = 1) {
    global $dek_checkout_isolated_cart_active, $dek_checkout_normal_cart_snapshot;

    if (!empty($dek_checkout_isolated_cart_active)) {
        return true;
    }

    if (!function_exists('WC') || !WC()->cart) {
        return false;
    }

    $cart = WC()->cart;
    $product_id = absint($product_id);

    if (!$product_id) {
        return false;
    }

    $product = wc_get_product($product_id);
    if (!$product || !$product->exists() || !$product->is_purchasable() || $product->is_type('variable')) {
        return false;
    }

    $dek_checkout_isolated_cart_active = true;
    $dek_checkout_normal_cart_snapshot = [
        'contents' => $cart->get_cart(),
        'removed' => $cart->get_removed_cart_contents(),
        'coupons' => $cart->get_applied_coupons(),
        'totals' => $cart->get_totals(),
        'chosen_shipping_methods' => WC()->session ? WC()->session->get('chosen_shipping_methods', null) : null,
    ];

    $cart->set_cart_contents([]);
    $cart->set_removed_cart_contents([]);
    $cart->set_applied_coupons([]);
    if (WC()->session) {
        WC()->session->set('chosen_shipping_methods', []);
    }

    $quantity = max(1, absint($quantity));
    $added = $product->is_type('variation')
        ? $cart->add_to_cart(
            $product->get_parent_id(),
            $quantity,
            $product->get_id(),
            $product->get_variation_attributes()
        )
        : $cart->add_to_cart($product->get_id(), $quantity);

    if (!$added) {
        $cart->set_cart_contents($dek_checkout_normal_cart_snapshot['contents']);
        $cart->set_removed_cart_contents($dek_checkout_normal_cart_snapshot['removed']);
        $cart->set_applied_coupons($dek_checkout_normal_cart_snapshot['coupons']);
        $cart->set_totals($dek_checkout_normal_cart_snapshot['totals']);
        $dek_checkout_isolated_cart_active = false;
        $dek_checkout_normal_cart_snapshot = [];
        return false;
    }

    $cart->calculate_totals();
    add_action('shutdown', 'dek_checkout_restore_normal_cart', -1);

    return true;
}

function dek_checkout_restore_normal_cart() {
    global $dek_checkout_isolated_cart_active, $dek_checkout_normal_cart_snapshot;

    if (
        empty($dek_checkout_isolated_cart_active) ||
        empty($dek_checkout_normal_cart_snapshot) ||
        !function_exists('WC') ||
        !WC()->cart
    ) {
        return;
    }

    $cart = WC()->cart;
    $cart->set_cart_contents($dek_checkout_normal_cart_snapshot['contents']);
    $cart->set_removed_cart_contents($dek_checkout_normal_cart_snapshot['removed']);
    $cart->set_applied_coupons($dek_checkout_normal_cart_snapshot['coupons']);
    $cart->set_totals($dek_checkout_normal_cart_snapshot['totals']);

    if (WC()->session) {
        $chosen_shipping_methods = $dek_checkout_normal_cart_snapshot['chosen_shipping_methods'];
        if (null === $chosen_shipping_methods) {
            WC()->session->__unset('chosen_shipping_methods');
        } else {
            WC()->session->set('chosen_shipping_methods', $chosen_shipping_methods);
        }
    }

    $dek_checkout_isolated_cart_active = false;
    $dek_checkout_normal_cart_snapshot = [];
}

function dek_checkout_clear_isolated_cart() {
    // Configured-product checkout state is request-local; there is no custom
    // session data to clear.
}

add_filter('woocommerce_persistent_cart_enabled', function($enabled) {
    global $dek_checkout_isolated_cart_active;
    return !empty($dek_checkout_isolated_cart_active) ? false : $enabled;
});

/**
 * Create a server-signed product snapshot for the custom checkout form.
 *
 * The signature lets the order endpoint trust product identities without a
 * WooCommerce cart session or WooCommerce checkout nonce.
 */
function dek_checkout_create_signed_payload($cart_contents) {
    $items = [];
    $shipping_rates = [];
    $fees = [];

    foreach ((array) $cart_contents as $cart_item_key => $cart_item) {
        $product = $cart_item['data'] ?? false;
        if (!$product instanceof \WC_Product || !$product->exists()) {
            continue;
        }

        $items[] = [
            'key' => (string) $cart_item_key,
            'product_id' => absint($cart_item['product_id'] ?? $product->get_id()),
            'variation_id' => absint($cart_item['variation_id'] ?? 0),
            'variation' => array_map('wc_clean', (array) ($cart_item['variation'] ?? [])),
            'quantity' => max(1, (int) ($cart_item['quantity'] ?? 1)),
        ];
    }

    if (function_exists('WC') && WC()->shipping()) {
        foreach ((array) WC()->shipping()->get_packages() as $package_index => $package) {
            foreach ((array) ($package['rates'] ?? []) as $rate) {
                if (!$rate instanceof \WC_Shipping_Rate) {
                    continue;
                }

                $shipping_rates[(int) $package_index][$rate->get_id()] = [
                    'id' => $rate->get_id(),
                    'label' => $rate->get_label(),
                    'method_id' => $rate->get_method_id(),
                    'instance_id' => $rate->get_instance_id(),
                    'cost' => (string) $rate->get_cost(),
                    'taxes' => array_map('wc_format_decimal', (array) $rate->get_taxes()),
                    'tax_status' => $rate->get_tax_status(),
                ];
            }
        }
    }

    if (function_exists('WC') && WC()->cart) {
        foreach ((array) WC()->cart->get_fees() as $fee) {
            $fees[] = [
                'name' => (string) $fee->name,
                'amount' => (string) $fee->amount,
                'taxable' => !empty($fee->taxable),
                'tax_class' => (string) $fee->tax_class,
            ];
        }
    }

    $json = wp_json_encode([
        'items' => $items,
        'shipping_rates' => $shipping_rates,
        'fees' => $fees,
        'coupons' => function_exists('WC') && WC()->cart ? array_values(WC()->cart->get_applied_coupons()) : [],
    ]);
    $payload = rtrim(strtr(base64_encode($json), '+/', '-_'), '=');

    return [
        'payload' => $payload,
        'signature' => hash_hmac('sha256', $payload, wp_salt('auth')),
    ];
}

function dek_checkout_decode_signed_payload($payload, $signature) {
    $payload = is_string($payload) ? $payload : '';
    $signature = is_string($signature) ? $signature : '';
    $expected_signature = hash_hmac('sha256', $payload, wp_salt('auth'));

    if (!$payload || !$signature || !hash_equals($expected_signature, $signature)) {
        return new \WP_Error('invalid_checkout_payload', __('No product found.', 'dynamic-elementkit'));
    }

    $padding = strlen($payload) % 4;
    if ($padding) {
        $payload .= str_repeat('=', 4 - $padding);
    }
    $json = base64_decode(strtr($payload, '-_', '+/'), true);
    $data = $json ? json_decode($json, true) : null;

    if (!is_array($data) || !isset($data['items']) || !is_array($data['items'])) {
        return new \WP_Error('invalid_checkout_payload', __('No product found.', 'dynamic-elementkit'));
    }

    return $data;
}

/**
 * Verify the CSRF token used by the widget-owned checkout AJAX endpoints.
 * The HMAC payload authenticates the cart snapshot; this nonce authenticates
 * the browser request that is allowed to perform a state-changing action.
 */
function dek_checkout_verify_nonce() {
    $nonce = isset($_POST['dek_checkout_nonce'])
        ? sanitize_text_field(wp_unslash($_POST['dek_checkout_nonce']))
        : '';

    if (!$nonce || !wp_verify_nonce($nonce, 'dek_checkout')) {
        wp_send_json_error(['message' => __('Security check failed.', 'dynamic-elementkit')], 403);
    }
}

function dek_checkout_validate_signed_request() {
    dek_checkout_verify_nonce();

    $payload = isset($_POST['dek_checkout_payload']) ? wc_clean(wp_unslash($_POST['dek_checkout_payload'])) : '';
    $signature = isset($_POST['dek_checkout_signature']) ? wc_clean(wp_unslash($_POST['dek_checkout_signature'])) : '';
    $payload_data = dek_checkout_decode_signed_payload($payload, $signature);

    if (is_wp_error($payload_data)) {
        wp_send_json_error(['message' => __('No product found.', 'dynamic-elementkit')]);
    }

    return $payload_data;
}

/**
 * Rebuild the signed product snapshot in memory for widget-only AJAX. This
 * never reads products from the persisted WooCommerce cart session.
 */
function dek_checkout_activate_signed_cart($payload_data) {
    global $dek_checkout_isolated_cart_active, $dek_checkout_normal_cart_snapshot;

    if (!empty($dek_checkout_isolated_cart_active)) {
        return true;
    }
    if (!function_exists('WC') || !WC()->cart || empty($payload_data['items'])) {
        return false;
    }

    $cart = WC()->cart;
    $dek_checkout_isolated_cart_active = true;
    $dek_checkout_normal_cart_snapshot = [
        'contents' => $cart->get_cart(),
        'removed' => $cart->get_removed_cart_contents(),
        'coupons' => $cart->get_applied_coupons(),
        'totals' => $cart->get_totals(),
        'chosen_shipping_methods' => WC()->session ? WC()->session->get('chosen_shipping_methods', null) : null,
    ];

    $cart->set_cart_contents([]);
    $cart->set_removed_cart_contents([]);
    $cart->set_applied_coupons([]);

    foreach ($payload_data['items'] as $item) {
        $product_id = !empty($item['variation_id'])
            ? absint($item['variation_id'])
            : absint($item['product_id'] ?? 0);
        $product = $product_id ? wc_get_product($product_id) : false;
        if (!$product || !$product->exists() || !$product->is_purchasable()) {
            dek_checkout_restore_normal_cart();
            return false;
        }

        $quantity = max(1, absint($item['quantity'] ?? 1));
        $added = $product->is_type('variation')
            ? $cart->add_to_cart(
                $product->get_parent_id(),
                $quantity,
                $product->get_id(),
                (array) ($item['variation'] ?? $product->get_variation_attributes())
            )
            : $cart->add_to_cart($product->get_id(), $quantity);

        if (!$added) {
            dek_checkout_restore_normal_cart();
            return false;
        }
    }

    foreach ((array) ($payload_data['coupons'] ?? []) as $coupon_code) {
        $cart->apply_coupon(wc_format_coupon_code($coupon_code));
    }
    $cart->calculate_totals();
    add_action('shutdown', 'dek_checkout_restore_normal_cart', -1);

    return true;
}

function dek_checkout_safe_error_message($message) {
    $message = wp_strip_all_tags((string) $message);
    if (
        false !== stripos($message, 'session has expired') ||
        false !== stripos($message, 'return to shop')
    ) {
        return __('Unable to place the order. Please try again.', 'dynamic-elementkit');
    }

    return $message ?: __('Unable to place the order. Please try again.', 'dynamic-elementkit');
}

function dek_get_thank_you_widget_page_id() {
    static $thank_you_widget_page_id = null;

    if (null !== $thank_you_widget_page_id) {
        return $thank_you_widget_page_id;
    }

    $thank_you_widget_page_id = 0;
    $matching_ids = get_posts([
        'post_type' => 'page',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'orderby' => 'modified',
        'order' => 'DESC',
        'fields' => 'ids',
        'no_found_rows' => true,
        'meta_query' => [
            [
                'key' => '_elementor_data',
                'value' => 'wpb-thank-you',
                'compare' => 'LIKE',
            ],
        ],
    ]);

    foreach (array_map('absint', $matching_ids) as $candidate_id) {
        $elementor_data = get_post_meta($candidate_id, '_elementor_data', true);
        $elements = is_string($elementor_data) ? json_decode($elementor_data, true) : $elementor_data;
        if (dek_elementor_data_has_widget($elements, 'wpb-thank-you')) {
            $thank_you_widget_page_id = (int) $candidate_id;
            break;
        }
    }

    return $thank_you_widget_page_id;
}

function dek_checkout_get_order_received_url($order, $fallback_url = '') {
    if (!$order instanceof \WC_Order) {
        return $fallback_url;
    }

    $thank_you_page_id = function_exists('dek_get_thank_you_widget_page_id')
        ? dek_get_thank_you_widget_page_id()
        : 0;

    if (!$thank_you_page_id) {
        return $fallback_url ?: $order->get_checkout_order_received_url();
    }

    $base_url = get_permalink($thank_you_page_id);
    if (!$base_url) {
        return $fallback_url ?: $order->get_checkout_order_received_url();
    }

    $url = function_exists('wc_get_endpoint_url')
        ? wc_get_endpoint_url('order-received', $order->get_id(), $base_url)
        : add_query_arg('order-received', $order->get_id(), $base_url);

    return add_query_arg('key', $order->get_order_key(), $url);
}

/**
 * Widget-owned checkout endpoint. It deliberately bypasses WooCommerce's
 * process-checkout handler and nonce. Persisted cart contents are never used as
 * the order source; only the server-signed snapshot is trusted.
 */
add_action('wp_ajax_dek_process_custom_checkout', 'dek_process_custom_checkout');
add_action('wp_ajax_nopriv_dek_process_custom_checkout', 'dek_process_custom_checkout');

function dek_process_custom_checkout() {
    if (!function_exists('wc_create_order')) {
        wp_send_json_error(['message' => __('Checkout is not available.', 'dynamic-elementkit')]);
    }

    dek_checkout_verify_nonce();

    $payload = isset($_POST['dek_checkout_payload']) ? wc_clean(wp_unslash($_POST['dek_checkout_payload'])) : '';
    $signature = isset($_POST['dek_checkout_signature']) ? wc_clean(wp_unslash($_POST['dek_checkout_signature'])) : '';
    $payload_data = dek_checkout_decode_signed_payload($payload, $signature);
    if (is_wp_error($payload_data)) {
        wp_send_json_error(['message' => __('No product found.', 'dynamic-elementkit')]);
    }

    $posted_quantities = isset($_POST['dek_checkout_quantities']) && is_array($_POST['dek_checkout_quantities'])
        ? wc_clean(wp_unslash($_POST['dek_checkout_quantities']))
        : [];
    $order_items = [];

    foreach ($payload_data['items'] as $item) {
        $item_key = isset($item['key']) ? (string) $item['key'] : '';
        if (!$item_key || !array_key_exists($item_key, $posted_quantities)) {
            continue;
        }

        $product_id = !empty($item['variation_id'])
            ? absint($item['variation_id'])
            : absint($item['product_id'] ?? 0);
        $product = $product_id ? wc_get_product($product_id) : false;
        $quantity = max(1, wc_stock_amount($posted_quantities[$item_key]));
        $is_variation = $product && $product->is_type('variation');
        $parent_product = $is_variation
            ? wc_get_product($product->get_parent_id())
            : null;
        $max_quantity = $product ? $product->get_max_purchase_quantity() : 0;

        if (
            !$product ||
            !$product->exists() ||
            'publish' !== $product->get_status() ||
            ($is_variation && (!$parent_product || !$parent_product->exists() || 'publish' !== $parent_product->get_status())) ||
            !$product->is_purchasable() ||
            !$product->is_in_stock() ||
            ($max_quantity > 0 && $quantity > $max_quantity) ||
            ($product->managing_stock() && !$product->has_enough_stock($quantity))
        ) {
            wp_send_json_error(['message' => __('No product found.', 'dynamic-elementkit')]);
        }

        $order_items[] = [
            'product' => $product,
            'quantity' => $quantity,
            'variation' => array_map('wc_clean', (array) ($item['variation'] ?? [])),
        ];
    }

    if (!$order_items) {
        wp_send_json_error(['message' => __('No product found.', 'dynamic-elementkit')]);
    }

    $active_payload = $payload_data;
    $active_payload['items'] = array_values(array_filter(
        $payload_data['items'],
        static function($item) use ($posted_quantities) {
            return !empty($item['key']) && array_key_exists((string) $item['key'], $posted_quantities);
        }
    ));
    foreach ($active_payload['items'] as &$active_item) {
        $active_item['quantity'] = max(1, wc_stock_amount($posted_quantities[(string) $active_item['key']]));
    }
    unset($active_item);

    if (!dek_checkout_activate_signed_cart($active_payload)) {
        wp_send_json_error(['message' => __('No product found.', 'dynamic-elementkit')]);
    }

    $full_name = isset($_POST['billing_full_name'])
        ? sanitize_text_field(wp_unslash($_POST['billing_full_name']))
        : '';
    $phone = isset($_POST['billing_phone']) ? sanitize_text_field(wp_unslash($_POST['billing_phone'])) : '';
    $address_1 = isset($_POST['billing_address_1']) ? sanitize_text_field(wp_unslash($_POST['billing_address_1'])) : '';
    $country = isset($_POST['billing_country']) ? sanitize_text_field(wp_unslash($_POST['billing_country'])) : '';

    if (!$full_name || !$phone || !$address_1) {
        wp_send_json_error(['message' => __('Please complete all required checkout fields.', 'dynamic-elementkit')]);
    }

    $name_parts = preg_split('/\s+/', trim($full_name), 2);
    $address = [
        'first_name' => $name_parts[0] ?? '',
        'last_name' => $name_parts[1] ?? '',
        'phone' => $phone,
        'address_1' => $address_1,
        'country' => $country ?: WC()->countries->get_base_country(),
    ];
    $payment_method = isset($_POST['payment_method'])
        ? sanitize_text_field(wp_unslash($_POST['payment_method']))
        : '';
    $gateways = WC()->payment_gateways()->payment_gateways();
    $gateway = $payment_method && isset($gateways[$payment_method]) && 'yes' === $gateways[$payment_method]->enabled
        ? $gateways[$payment_method]
        : false;
    if ($gateway && method_exists($gateway, 'validate_fields') && false === $gateway->validate_fields()) {
        $message = wc_print_notices(true);
        wp_send_json_error([
            'message' => $message ? dek_checkout_safe_error_message($message) : __('Please check your payment details.', 'dynamic-elementkit'),
        ]);
    }

    try {
        $order = wc_create_order(['customer_id' => get_current_user_id()]);
        $order->set_created_via('wpb-custom-checkout');
        $order->set_address($address, 'billing');
        $order->set_address($address, 'shipping');

        foreach ($order_items as $order_item) {
            $order->add_product(
                $order_item['product'],
                $order_item['quantity'],
                ['variation' => $order_item['variation']]
            );
        }

        foreach ((array) ($payload_data['fees'] ?? []) as $fee_data) {
            $fee_item = new \WC_Order_Item_Fee();
            $fee_item->set_name(sanitize_text_field($fee_data['name'] ?? ''));
            $fee_item->set_amount(wc_format_decimal($fee_data['amount'] ?? 0));
            $fee_item->set_total(wc_format_decimal($fee_data['amount'] ?? 0));
            $fee_item->set_tax_status(!empty($fee_data['taxable']) ? 'taxable' : 'none');
            $fee_item->set_tax_class(sanitize_text_field($fee_data['tax_class'] ?? ''));
            $order->add_item($fee_item);
        }

        $posted_shipping_methods = isset($_POST['shipping_method']) && is_array($_POST['shipping_method'])
            ? wc_clean(wp_unslash($_POST['shipping_method']))
            : [];
        foreach ($posted_shipping_methods as $package_index => $rate_id) {
            $rate = $payload_data['shipping_rates'][(int) $package_index][$rate_id] ?? null;
            if (!$rate) {
                continue;
            }

            $shipping_item = new \WC_Order_Item_Shipping();
            $shipping_item->set_method_title(sanitize_text_field($rate['label'] ?? ''));
            $shipping_item->set_method_id(sanitize_text_field($rate['method_id'] ?? ''));
            $shipping_item->set_instance_id(absint($rate['instance_id'] ?? 0));
            $shipping_item->set_total(wc_format_decimal($rate['cost'] ?? 0));
            $shipping_item->set_taxes(['total' => array_map('wc_format_decimal', (array) ($rate['taxes'] ?? []))]);
            $order->add_item($shipping_item);
        }

        foreach ((array) ($payload_data['coupons'] ?? []) as $coupon_code) {
            $order->apply_coupon(wc_format_coupon_code($coupon_code));
        }

        if ($gateway) {
            $order->set_payment_method($gateway);
        }

        $order->calculate_taxes($address);
        $order->calculate_totals(false);
        do_action('woocommerce_checkout_create_order', $order, wp_unslash($_POST));
        $order->save();
        do_action('woocommerce_checkout_update_order_meta', $order->get_id(), wp_unslash($_POST));
        do_action('woocommerce_checkout_order_created', $order);

        if ($order->needs_payment()) {
            if (!$gateway) {
                throw new \Exception(__('Please select a valid payment method.', 'dynamic-elementkit'));
            }

            $result = $gateway->process_payment($order->get_id());
            if (!is_array($result) || 'success' !== ($result['result'] ?? '')) {
                $message = wc_print_notices(true);
                throw new \Exception($message ? dek_checkout_safe_error_message($message) : __('Payment could not be processed.', 'dynamic-elementkit'));
            }
            $redirect = !empty($result['redirect']) ? $result['redirect'] : $order->get_checkout_order_received_url();
        } else {
            $order->payment_complete();
            $redirect = $order->get_checkout_order_received_url();
        }

        $redirect = dek_checkout_get_order_received_url($order, $redirect);

        $successful_result = apply_filters(
            'woocommerce_payment_successful_result',
            ['result' => 'success', 'redirect' => $redirect],
            $order->get_id()
        );

        $cart_mode = isset($_POST['dek_checkout_cart_mode'])
            ? sanitize_key(wp_unslash($_POST['dek_checkout_cart_mode']))
            : '';
        dek_checkout_restore_normal_cart();
        if ('cart' === $cart_mode && function_exists('WC') && WC()->cart) {
            WC()->cart->empty_cart();
        }

        $final_redirect = is_array($successful_result) && !empty($successful_result['redirect'])
            ? $successful_result['redirect']
            : $redirect;

        wp_send_json_success([
            'order_id' => $order->get_id(),
            'redirect' => dek_checkout_get_order_received_url($order, $final_redirect),
        ]);
    } catch (\Throwable $error) {
        wp_send_json_error(['message' => dek_checkout_safe_error_message($error->getMessage())]);
    }
}

/**
 * AJAX: Update cart quantity
 */
add_action('wp_ajax_dek_update_cart_quantity', 'dek_ajax_update_cart_quantity');

add_action('wp_ajax_nopriv_dek_update_cart_quantity', 'dek_ajax_update_cart_quantity');



function dek_ajax_update_cart_quantity() {
    if (!class_exists('WooCommerce') || !WC()->cart) {
        wp_send_json_error(['message' => __('WooCommerce not available.', 'dynamic-elementkit')]);
    }

    $payload_data = dek_checkout_validate_signed_request();
    if (!dek_checkout_activate_signed_cart($payload_data)) {
        wp_send_json_error(['message' => __('No product found.', 'dynamic-elementkit')]);
    }
    $cart_item_key = isset($_POST['cart_item_key']) ? wc_clean(wp_unslash($_POST['cart_item_key'])) : '';
    $quantity = isset($_POST['quantity']) ? max(1, wc_stock_amount(wp_unslash($_POST['quantity']))) : 1;
    $cart = WC()->cart;
    $cart_item = $cart_item_key ? $cart->get_cart_item($cart_item_key) : false;

    if (!$cart_item || empty($cart_item['data']) || !$cart_item['data'] instanceof \WC_Product) {
        wp_send_json_error(['message' => __('The cart item is no longer available.', 'dynamic-elementkit')]);
    }

    if (!$cart->set_quantity($cart_item_key, $quantity, true)) {
        wp_send_json_error(['message' => __('The quantity could not be updated.', 'dynamic-elementkit')]);
    }

    $cart->calculate_totals();
    $cart_item = $cart->get_cart_item($cart_item_key);
    $product = $cart_item['data'];
    $updated_payload = dek_checkout_create_signed_payload($cart->get_cart());

    wp_send_json_success([
        'cart_item_key' => $cart_item_key,
        'item_subtotal' => $cart->get_product_subtotal($product, $cart_item['quantity']),
        'subtotal' => $cart->get_cart_subtotal(),
        'shipping' => $cart->get_cart_shipping_total(),
        'discount' => wc_price($cart->get_discount_total()),
        'coupons' => implode(', ', $cart->get_applied_coupons()),
        'total' => $cart->get_cart_total(),
        'count' => $cart->get_cart_contents_count(),
        'checkout_payload' => $updated_payload['payload'],
        'checkout_signature' => $updated_payload['signature'],
    ]);
}

/**
 * AJAX: Remove an item from the checkout widget and return its complete new
 * order-review state. This keeps the same checkout document mounted when the
 * final item is removed.
 */
add_action('wp_ajax_dek_remove_checkout_item', 'dek_ajax_remove_checkout_item');
add_action('wp_ajax_nopriv_dek_remove_checkout_item', 'dek_ajax_remove_checkout_item');

function dek_ajax_remove_checkout_item() {
    if (!function_exists('WC') || !WC()->cart) {
        wp_send_json_error(['message' => __('WooCommerce cart is not available.', 'dynamic-elementkit')]);
    }

    $payload_data = dek_checkout_validate_signed_request();
    if (!dek_checkout_activate_signed_cart($payload_data)) {
        wp_send_json_error(['message' => __('No product found.', 'dynamic-elementkit')]);
    }
    $cart_item_key = isset($_POST['cart_item_key'])
        ? wc_clean(wp_unslash($_POST['cart_item_key']))
        : '';
    if (!$cart_item_key) {
        wp_send_json_error(['message' => __('Invalid item.', 'dynamic-elementkit')]);
    }

    if (!WC()->cart->get_cart_item($cart_item_key)) {
        wp_send_json_error(['message' => __('The cart item is no longer available.', 'dynamic-elementkit')]);
    }

    if (!WC()->cart->remove_cart_item($cart_item_key)) {
        wp_send_json_error(['message' => __('The product could not be removed.', 'dynamic-elementkit')]);
    }

    WC()->cart->calculate_totals();

    if (!class_exists('DEK_Checkout_Form_Widget')) {
        require_once DEK_PLUGIN_PATH . 'src/Elementor/Widgets/checkout-form.php';
    }

    ob_start();
    DEK_Checkout_Form_Widget::render_order_summary();
    $order_review_html = ob_get_clean();
    $updated_payload = dek_checkout_create_signed_payload(WC()->cart->get_cart());

    wp_send_json_success([
        'cart_item_key' => $cart_item_key,
        'count' => WC()->cart->get_cart_contents_count(),
        'is_empty' => WC()->cart->is_empty(),
        'order_review_html' => $order_review_html,
        'checkout_payload' => $updated_payload['payload'],
        'checkout_signature' => $updated_payload['signature'],
    ]);
}

add_action('wp_ajax_dek_get_checkout_summary', 'dek_ajax_get_checkout_summary');
add_action('wp_ajax_nopriv_dek_get_checkout_summary', 'dek_ajax_get_checkout_summary');

function dek_ajax_get_checkout_summary() {
    if (!function_exists('WC') || !WC()->cart) {
        wp_send_json_error(['message' => __('WooCommerce cart is not available.', 'dynamic-elementkit')]);
    }

    $payload_data = dek_checkout_validate_signed_request();
    if (!dek_checkout_activate_signed_cart($payload_data)) {
        wp_send_json_error(['message' => __('No product found.', 'dynamic-elementkit')]);
    }
    if (WC()->session && isset($_POST['shipping_method']) && is_array($_POST['shipping_method'])) {
        WC()->session->set('chosen_shipping_methods', wc_clean(wp_unslash($_POST['shipping_method'])));
    }
    WC()->cart->calculate_totals();
    $shipping_methods_html = dek_get_checkout_shipping_methods_html();

    wp_send_json_success([
        'subtotal' => WC()->cart->get_cart_subtotal(),
        'shipping' => WC()->cart->get_cart_shipping_total(),
        'shipping_methods_html' => $shipping_methods_html,
        'has_shipping_methods' => '' !== $shipping_methods_html,
        'discount' => wc_price(WC()->cart->get_discount_total()),
        'coupons' => implode(', ', WC()->cart->get_applied_coupons()),
        'total' => WC()->cart->get_cart_total(),
    ]);
}

/**
 * Render selectable shipping rates using WooCommerce's canonical field names.
 * The widget refreshes these totals through its signed AJAX endpoint.
 */
function dek_get_checkout_shipping_methods_html() {
    if (
        !function_exists('WC') ||
        !WC()->cart ||
        !WC()->session ||
        !WC()->cart->needs_shipping() ||
        !WC()->cart->show_shipping()
    ) {
        return '';
    }

    $packages = WC()->shipping()->get_packages();
    $packages_with_rates = array_filter($packages, static function($package) {
        return !empty($package['rates']) && is_array($package['rates']);
    });

    if (!$packages_with_rates) {
        return '';
    }

    ob_start();
    foreach ($packages_with_rates as $package_index => $package) {
        $chosen_method = wc_get_chosen_shipping_method_for_package($package_index, $package);
        $package_name = apply_filters(
            'woocommerce_shipping_package_name',
            sprintf(__('Shipping %d', 'woocommerce'), $package_index + 1),
            $package_index,
            $package
        );
        ?>
        <div class="wpb-shipping-package">
            <?php if (count($packages_with_rates) > 1): ?>
                <span class="wpb-shipping-package-name"><?php echo esc_html($package_name); ?></span>
            <?php endif; ?>
            <ul class="woocommerce-shipping-methods">
                <?php foreach ($package['rates'] as $method): ?>
                    <?php
                    $input_id = 'shipping_method_' . $package_index . '_' . sanitize_title($method->id);
                    ?>
                    <li>
                        <input type="radio"
                               name="shipping_method[<?php echo esc_attr($package_index); ?>]"
                               data-index="<?php echo esc_attr($package_index); ?>"
                               id="<?php echo esc_attr($input_id); ?>"
                               value="<?php echo esc_attr($method->id); ?>"
                               class="shipping_method"
                               <?php checked($method->id, $chosen_method); ?>>
                        <label for="<?php echo esc_attr($input_id); ?>">
                            <?php echo wp_kses_post(wc_cart_totals_shipping_method_label($method)); ?>
                        </label>
                        <?php do_action('woocommerce_after_shipping_rate', $method, $package_index); ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php
    }

    return trim(ob_get_clean());
}

/**
 * Retain the compact field set for integrations that inspect widget POST data.
 * The widget-owned order endpoint performs its own validation and order creation.
 */
add_filter('woocommerce_checkout_fields', function($fields) {
    if (empty($_POST['dek_checkout_widget']) && empty($_POST['dek_quick_checkout'])) {
        return $fields;
    }

    $allowed_billing = [
        'billing_first_name',
        'billing_last_name',
        'billing_phone',
        'billing_address_1',
        'billing_country',
    ];

    foreach ($fields['billing'] ?? [] as $key => $field) {
        if (!in_array($key, $allowed_billing, true)) {
            unset($fields['billing'][$key]);
        }
    }

    foreach (['billing_first_name', 'billing_phone', 'billing_address_1'] as $required_key) {
        if (isset($fields['billing'][$required_key])) {
            $fields['billing'][$required_key]['required'] = true;
        }
    }

    if (isset($fields['billing']['billing_last_name'])) {
        $fields['billing']['billing_last_name']['required'] = false;
    }

    if (isset($fields['billing']['billing_country'])) {
        $fields['billing']['billing_country']['required'] = false;
    }

    // The checkout widget uses billing details for shipping unless a future
    // widget version explicitly enables a separate shipping address.
    $fields['shipping'] = [];

    $fields['order'] = [];

    return $fields;
}, 999);

/**
 * Map the widget's single Full Name field onto WooCommerce's canonical order
 * address fields before native checkout validation and order creation.
 */
add_filter('woocommerce_checkout_posted_data', function($data) {
    if ((empty($_POST['dek_checkout_widget']) && empty($_POST['dek_quick_checkout'])) || empty($_POST['billing_full_name'])) {
        return $data;
    }

    $full_name = sanitize_text_field(wp_unslash($_POST['billing_full_name']));
    $parts = preg_split('/\s+/', trim($full_name), 2);

    $data['billing_first_name'] = $parts[0] ?? '';
    $data['billing_last_name'] = $parts[1] ?? '';

    // The compact widget intentionally has no separate shipping form. Copy
    // the enabled billing address into WooCommerce's canonical shipping keys
    // so physical orders, shipping methods, taxes, and fulfillment plugins
    // receive the same address data.
    foreach (['first_name', 'last_name', 'company', 'country', 'address_1', 'address_2', 'city', 'state', 'postcode'] as $field) {
        $billing_key = 'billing_' . $field;
        if (array_key_exists($billing_key, $data)) {
            $data['shipping_' . $field] = $data[$billing_key];
        }
    }

    return $data;
});

add_filter('woocommerce_order_button_text', function($text) {
    $request = $_POST;

    if (!empty($_POST['post_data']) && is_string($_POST['post_data'])) {
        parse_str(wp_unslash($_POST['post_data']), $request);
    }

    if ((empty($request['dek_checkout_widget']) && empty($request['dek_quick_checkout'])) || empty($request['dek_order_button_text'])) {
        return $text;
    }

    return sanitize_text_field(wp_unslash($request['dek_order_button_text']));
});


/**

 * AJAX: Filter products by category

 */

add_action('wp_ajax_dek_filter_products', function() {

    dek_ajax_filter_products();

});

add_action('wp_ajax_nopriv_dek_filter_products', function() {

    dek_ajax_filter_products();

});



function dek_ajax_filter_products() {

    $nonce = isset($_POST['_wpnonce']) ? $_POST['_wpnonce'] : '';

    if (!wp_verify_nonce($nonce, 'dek_toggle_active')) {

        wp_send_json_error(['message' => __('Security check failed.', 'dynamic-elementkit')]);

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
    $button_full_width = isset($_POST['button_full_width']) && 'yes' === sanitize_text_field($_POST['button_full_width']) ? 'yes' : 'no';
    $button_text = isset($_POST['button_text']) ? sanitize_text_field($_POST['button_text']) : __('Add to Cart', 'dynamic-elementkit');

    $variable_button_text = isset($_POST['variable_button_text']) ? sanitize_text_field($_POST['variable_button_text']) : __('Select options', 'dynamic-elementkit');

    $added_button_text = isset($_POST['added_button_text']) ? sanitize_text_field($_POST['added_button_text']) : __('Added!', 'dynamic-elementkit');

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

                            <?php dek_product_watermark_overlay(); ?>

                        <?php if ($show_badge === 'yes' && !empty($badge_text)): ?>

                            <div class="wpb-product-badge">

                                <?php echo esc_html($badge_text); ?>

                            </div>

                        <?php endif; ?>

                        <?php if ($auto_sale_badge === 'yes' && $product && $product->is_on_sale()): ?>

                            <div class="wpb-product-badge wpb-sale-badge">

                                <?php esc_html_e('Sale', 'dynamic-elementkit'); ?>

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

                                            echo wp_kses_post('<span class="wpb-price-from">' . __('From:', 'dynamic-elementkit') . '</span> ' . wc_price($min_price));

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

                                    <a href="<?php the_permalink(); ?>" class="wpb-add-to-cart button wpb-variation-button<?php echo 'yes' === $button_full_width ? ' wpb-full-width' : ''; ?>">
                                        <span class="wpb-button-text"><?php echo esc_html($variable_button_text); ?></span>

                                        <span class="wpb-button-spinner" aria-hidden="true"></span>

                                    </a>

                                <?php else: ?>

                                    <button class="wpb-add-to-cart button<?php echo 'yes' === $button_full_width ? ' wpb-full-width' : ''; ?>" data-product-id="<?php echo esc_attr($product->get_id()); ?>">
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

        echo '<p>' . __('No products found.', 'dynamic-elementkit') . '</p>';

    }



    wp_send_json_success(['html' => ob_get_clean()]);

}



/**

 * AJAX: Search products (live dropdown)

 */

add_action('wp_ajax_dek_search_products', 'dek_ajax_search_products');

add_action('wp_ajax_nopriv_dek_search_products', 'dek_ajax_search_products');



function dek_ajax_search_products() {

    $nonce = isset($_POST['_wpnonce']) ? $_POST['_wpnonce'] : '';

    if (!wp_verify_nonce($nonce, 'dek_toggle_active')) {

        wp_send_json_error(['message' => __('Security check failed.', 'dynamic-elementkit')]);

    }



    if (!class_exists('WooCommerce')) {

        wp_send_json_error(['message' => __('WooCommerce not available.', 'dynamic-elementkit')]);

    }



    $term = isset($_POST['term']) ? sanitize_text_field($_POST['term']) : '';

    $per_page = isset($_POST['per_page']) ? (int) $_POST['per_page'] : 8;

    $show_image = isset($_POST['show_image']) ? sanitize_text_field($_POST['show_image']) : 'yes';

    $show_price = isset($_POST['show_price']) ? sanitize_text_field($_POST['show_price']) : 'yes';

    $show_no_result = isset($_POST['show_no_result']) ? sanitize_text_field($_POST['show_no_result']) : 'yes';

    $no_result_text = isset($_POST['no_result_text']) ? sanitize_text_field($_POST['no_result_text']) : __('No products found.', 'dynamic-elementkit');

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

function dek_get_archive_title() {

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
function dek_get_active_template($type) {
    $args = [
        'post_type'   => 'dek_template',
        'post_status' => ['publish', 'draft'],
        'meta_key'    => '_dek_template_type',
        'meta_value'  => $type,
        'meta_query'  => [
            [
                'key'   => '_dek_template_active',
                'value' => '1',
            ],
        ],
        'posts_per_page' => 1,
    ];

    $templates = get_posts($args);
    return $templates ? $templates[0] : false;
}

/**
 * Render a site-wide Elementor header or footer when one is active.
 */
function dek_render_site_template($type) {
    if (is_admin() || wp_doing_ajax() || !class_exists('Elementor\\Plugin')) {
        return;
    }

    $template = dek_get_active_template($type);
    if (!$template || 'publish' !== $template->post_status) {
        return;
    }

    $content = \Elementor\Plugin::$instance->frontend->get_builder_content_for_display($template->ID, true);
    if ($content) {
        echo '<div class="dek-site-template dek-site-template-' . esc_attr($type) . '" data-template-id="' . absint($template->ID) . '">';
        echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Elementor sanitizes document output.
        echo '</div>';
    }
}

add_action('wp_body_open', function() {
    dek_render_site_template('header');
}, 1);

add_action('wp_footer', function() {
    dek_render_site_template('footer');
}, 1);

/**
 * Resolve a published template opened through /landing/{slug}/.
 */
function dek_get_current_landing_template() {
    if (!is_singular('dek_template')) {
        return false;
    }

    $template = get_queried_object();
    if ($template instanceof \WP_Post && 'publish' === $template->post_status) {
        return $template;
    }

    return false;
}

/**
 * Resolve the WooCommerce product assigned to a public landing template.
 */
function dek_get_landing_product() {
    $template = dek_get_current_landing_template();
    if (!$template) {
        return false;
    }

    $product_id = absint(get_post_meta($template->ID, '_dek_landing_product_id', true));
    if (!$product_id || !function_exists('wc_get_product')) {
        return false;
    }

    $product = wc_get_product($product_id);
    return $product instanceof \WC_Product && $product->exists() ? $product : false;
}

/**
 * Resolve the template assigned to the current WooCommerce request.
 *
 * This must be available before Elementor runs its frontend style pass so
 * dynamic template documents can participate in Elementor's normal asset
 * discovery and atomic CSS generation.
 */
function dek_get_current_active_template() {
    if (!function_exists('is_woocommerce')) {
        return false;
    }

    // Payment is a transactional endpoint and must retain its native screen.
    if (function_exists('is_checkout_pay_page') && is_checkout_pay_page()) {
        return false;
    }

    // The configured checkout page owns its widget output; single-product
    // landing pages with the same widget must stay independent.
    $checkout_widget_page_id = dek_get_checkout_widget_page_id();
    if (
        $checkout_widget_page_id &&
        (int) get_queried_object_id() === $checkout_widget_page_id &&
        function_exists('is_checkout') &&
        is_checkout()
    ) {
        return false;
    }

    $type = null;
    if (function_exists('is_order_received_page') && is_order_received_page()) {
        $type = 'thankyou';
    } elseif (is_product()) {
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
    $active_template = dek_get_active_template($type);
    if (!$active_template && in_array($type, ['shop', 'product-category', 'product-tag'], true)) {
        $active_template = dek_get_active_template('archive');
    }
    return $active_template;
}

/**
 * Checkout responses contain customer-specific cart data and nonces. Prevent
 * page/CDN caches from serving another session or an expired checkout nonce.
 */
function dek_disable_cache_for_dynamic_checkout() {
    $active_template = dek_get_current_active_template();
    $is_checkout_request = function_exists('is_checkout') && is_checkout();
    $document_id = $active_template ? (int) $active_template->ID : (int) get_queried_object_id();
    $elementor_data = $document_id ? get_post_meta($document_id, '_elementor_data', true) : [];
    $elements = is_string($elementor_data) ? json_decode($elementor_data, true) : $elementor_data;

    if (!$is_checkout_request && !dek_elementor_data_has_widget($elements, 'wpb-checkout-form')) {
        return;
    }

    if (!defined('DONOTCACHEPAGE')) {
        define('DONOTCACHEPAGE', true);
    }

    if (function_exists('wc_nocache_headers')) {
        wc_nocache_headers();
    } else {
        nocache_headers();
    }
}

/**
 * Keep pages containing the custom checkout uncached. The widget checkout
 * itself does not initialize or persist a WooCommerce customer session.
 */
function dek_prepare_custom_checkout_request() {
    $is_checkout_request = function_exists('is_checkout') && is_checkout();
    $active_template = dek_get_current_active_template();
    $document_id = $active_template ? (int) $active_template->ID : (int) get_queried_object_id();
    $elementor_data = $document_id ? get_post_meta($document_id, '_elementor_data', true) : [];
    $elements = is_string($elementor_data) ? json_decode($elementor_data, true) : $elementor_data;
    $has_checkout_widget = dek_elementor_data_has_widget($elements, 'wpb-checkout-form');

    if (
        is_admin() ||
        wp_doing_ajax() ||
        (!$is_checkout_request && !$has_checkout_widget) ||
        (function_exists('is_checkout_pay_page') && is_checkout_pay_page()) ||
        (function_exists('is_order_received_page') && is_order_received_page())
    ) {
        return;
    }

    foreach (['DONOTCACHEPAGE', 'DONOTCACHEDB', 'DONOTCACHEOBJECT'] as $constant) {
        if (!defined($constant)) {
            define($constant, true);
        }
    }

    if (function_exists('wc_nocache_headers')) {
        wc_nocache_headers();
    } else {
        nocache_headers();
    }
}

add_action('template_redirect', 'dek_prepare_custom_checkout_request', 0);

function dek_elementor_data_has_widget($elements, $widget_name) {
    if (!is_array($elements)) {
        return false;
    }

    foreach ($elements as $element) {
        if (!is_array($element)) {
            continue;
        }

        if (($element['widgetType'] ?? '') === $widget_name) {
            return true;
        }

        if (!empty($element['elements']) && dek_elementor_data_has_widget($element['elements'], $widget_name)) {
            return true;
        }
    }

    return false;
}

/**
 * Find the WooCommerce-configured checkout page when it owns the widget.
 *
 * Landing pages can also contain the checkout widget for single-product offers,
 * but they must not become the global checkout URL or affect cart checkout.
 */
function dek_get_checkout_widget_page_id() {
    static $checkout_widget_page_id = null;

    if (null !== $checkout_widget_page_id) {
        return $checkout_widget_page_id;
    }

    $checkout_widget_page_id = 0;
    $configured_page_id = absint(get_option('woocommerce_checkout_page_id'));

    if (
        $configured_page_id &&
        'publish' === get_post_status($configured_page_id) &&
        'page' === get_post_type($configured_page_id)
    ) {
        $elementor_data = get_post_meta($configured_page_id, '_elementor_data', true);
        $elements = is_string($elementor_data) ? json_decode($elementor_data, true) : $elementor_data;
        if (dek_elementor_data_has_widget($elements, 'wpb-checkout-form')) {
            $checkout_widget_page_id = $configured_page_id;
        }
    }

    return $checkout_widget_page_id;
}

add_filter('woocommerce_get_checkout_page_id', function($page_id) {
    $widget_page_id = dek_get_checkout_widget_page_id();
    return $widget_page_id ?: $page_id;
});

add_filter('woocommerce_checkout_redirect_empty_cart', function($should_redirect) {
    $widget_page_id = dek_get_checkout_widget_page_id();
    if ($widget_page_id && is_page($widget_page_id)) {
        return false;
    }

    $active_checkout_template = dek_get_active_template('checkout');
    $configured_page_id = absint(get_option('woocommerce_checkout_page_id'));
    if ($active_checkout_template && $configured_page_id && is_page($configured_page_id)) {
        $elementor_data = get_post_meta($active_checkout_template->ID, '_elementor_data', true);
        $elements = is_string($elementor_data) ? json_decode($elementor_data, true) : $elementor_data;
        if (dek_elementor_data_has_widget($elements, 'wpb-checkout-form')) {
            return false;
        }
    }

    return $should_redirect;
});

add_action('template_redirect', 'dek_disable_cache_for_dynamic_checkout', 1);

/**
 * Register the dynamic document before Elementor's frontend enqueue pass.
 *
 * Elementor 4 collects document IDs from `elementor/post/render`, then creates
 * and enqueues local/global atomic CSS during
 * `elementor/frontend/after_enqueue_post_styles`. Rendering the template after
 * get_header() is too late because wp_head() and that style pass have already
 * happened.
 */
function dek_register_elementor_template_document() {
    if (!class_exists('\Elementor\Plugin') || !\Elementor\Plugin::$instance->frontend) {
        return;
    }
    $active_template = dek_get_current_landing_template() ?: dek_get_current_active_template();
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
    global $dek_elementor_template_id;
    $dek_elementor_template_id = $template_id;
}

add_action('wp_enqueue_scripts', 'dek_register_elementor_template_document', 1);

/**
 * Run Elementor's normal style pass after all other integrations have had a
 * chance to register their documents. Header/footer and Theme Builder plugins
 * commonly register template IDs later in `wp_enqueue_scripts`; calling
 * Elementor's one-shot style pass before them drops their generated CSS.
 *
 * WooCommerce endpoints usually are not Elementor documents, so Elementor does
 * not schedule this pass by itself for these requests.
 */
function dek_enqueue_elementor_template_styles() {
    global $dek_elementor_template_id;
    if (empty($dek_elementor_template_id) || !class_exists('\Elementor\Plugin')) {
        return;
    }
    \Elementor\Plugin::$instance->frontend->enqueue_styles();
    // Keep legacy/v3 post CSS working on mixed Elementor documents.
    \Elementor\Core\Files\CSS\Post::create((int) $dek_elementor_template_id)->enqueue();
}

add_action('wp_enqueue_scripts', 'dek_enqueue_elementor_template_styles', PHP_INT_MAX);

/**
 * Override WooCommerce templates
 */
add_filter('template_include', function($template) {
    $active_template = dek_get_current_landing_template() ?: dek_get_current_active_template();
    if (!$active_template) {
        return $template;
    }
    global $dek_active_template;
    $dek_active_template = $active_template;
    return DEK_PLUGIN_PATH . 'templates/override.php';
}, 9999);

/**
 * Get the watermark logo URL based on the selected source.
 */
function dek_get_watermark_logo() {

    $source = get_option('dek_watermark_logo_source', 'custom');



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



    return get_option('dek_watermark_logo', '');

}



/**

 * Render the product watermark overlay on single product pages.

 * Injects the overlay inside the gallery image figure so it positions correctly.

 */

add_filter('woocommerce_single_product_image_thumbnail_html', function ($html) {

    if (!get_option('dek_watermark_enabled', 0)) {

        return $html;

    }



    $logo = dek_get_watermark_logo();

    if (!$logo) {

        return $html;

    }



    $opacity = (float) get_option('dek_watermark_opacity', 0.3);

    $opacity = max(0.05, min(1, $opacity));



    $overlay = '<span class="wpb-product-watermark wpb-single-watermark" aria-hidden="true" style="background-image:url(\'' . esc_url($logo) . '\');opacity:' . esc_attr($opacity) . ';"></span>';



    return $html . $overlay;

});



/**

 * Render the product watermark overlay if enabled.

 */

function dek_product_watermark_overlay() {

    if (!get_option('dek_watermark_enabled', 0)) {

        return;

    }



    $logo = dek_get_watermark_logo();

    if (!$logo) {

        return;

    }



    $opacity = (float) get_option('dek_watermark_opacity', 0.3);

    $opacity = max(0.05, min(1, $opacity));

    ?>

    <span class="wpb-product-watermark" aria-hidden="true" style="background-image:url('<?php echo esc_url($logo); ?>');opacity:<?php echo esc_attr($opacity); ?>;"></span>

    <?php

}



/**

 * Main plugin class

 */

class Dynamic_ElementKit {



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

        require_once DEK_PLUGIN_PATH . 'src/class-admin-page.php';

        require_once DEK_PLUGIN_PATH . 'src/class-template-table.php';

        require_once DEK_PLUGIN_PATH . 'src/Elementor/Widgets/widget-loader.php';

    }



    private function init_hooks() {

        add_action('plugins_loaded', [$this, 'load_textdomain']);

        add_action('elementor/dynamic_tags/register', [$this, 'register_dynamic_tags']);

        add_action('wp_enqueue_scripts', [$this, 'enqueue_frontend_assets']);

        add_action('elementor/editor/before_enqueue_scripts', [$this, 'enqueue_frontend_assets']);

        add_action('elementor/editor/after_enqueue_styles', [$this, 'enqueue_frontend_assets']);

    }



    public function register_dynamic_tags( $dynamic_tags_manager ) {
        $dynamic_tags_manager->register_group( 'dek_product', [

            'title' => __( 'Dynamic ElementKit', 'dynamic-elementkit' ),

        ] );



        require_once DEK_PLUGIN_PATH . 'src/Elementor/DynamicTags/product-data-tags.php';
        require_once DEK_PLUGIN_PATH . 'src/Elementor/DynamicTags/product-title.php';
        require_once DEK_PLUGIN_PATH . 'src/Elementor/DynamicTags/product-description.php';

        $dynamic_tags_manager->register( new \Dynamic_ElementKit\Dynamic_Tags\Tags\Product_Title_Tag() );
        $dynamic_tags_manager->register( new \Dynamic_ElementKit\Dynamic_Tags\Tags\Product_Description_Tag() );
        foreach ([
            'Product_ID_Tag',
            'Product_SKU_Tag',
            'Product_Short_Description_Tag',
            'Product_Price_Tag',
            'Product_Regular_Price_Tag',
            'Product_Sale_Price_Tag',
            'Product_Sale_Percentage_Tag',
            'Product_Stock_Status_Tag',
            'Product_Stock_Quantity_Tag',
            'Product_Type_Tag',
            'Product_Weight_Tag',
            'Product_Dimensions_Tag',
            'Product_Categories_Tag',
            'Product_Tags_Tag',
            'Product_Rating_Tag',
            'Product_Review_Count_Tag',
            'Product_URL_Tag',
            'Product_Image_Tag',
        ] as $tag_class) {
            $class_name = '\\Dynamic_ElementKit\\Dynamic_Tags\\Tags\\' . $tag_class;
            if (class_exists($class_name)) {
                $dynamic_tags_manager->register(new $class_name());
            }
        }
    }


    public function enqueue_frontend_assets() {

        $embla_version = '8.0.1';

        wp_enqueue_style('wpb-embla', DEK_PLUGIN_URL . 'assets/css/embla-carousel.css', [], $embla_version);

        wp_enqueue_style('wpb-embla-theme', DEK_PLUGIN_URL . 'assets/css/embla-dots.css', [], $embla_version);



        wp_enqueue_style('wpb-product-grid', DEK_PLUGIN_URL . 'assets/css/product-grid.css', ['wpb-embla', 'wpb-embla-theme'], DEK_VERSION);

        wp_enqueue_script('wpb-embla', DEK_PLUGIN_URL . 'assets/vendors/embla-carousel.js', [], DEK_VERSION, true);

        wp_enqueue_script('wpb-product-grid', DEK_PLUGIN_URL . 'assets/js/product-grid.js', ['jquery', 'wpb-embla'], DEK_VERSION, true);



        $cart_css_version = file_exists(DEK_PLUGIN_PATH . 'assets/css/cart.css') ? filemtime(DEK_PLUGIN_PATH . 'assets/css/cart.css') : DEK_VERSION;

        $cart_js_version = file_exists(DEK_PLUGIN_PATH . 'assets/js/cart.js') ? filemtime(DEK_PLUGIN_PATH . 'assets/js/cart.js') : DEK_VERSION;



        wp_enqueue_style('wpb-cart', DEK_PLUGIN_URL . 'assets/css/cart.css', [], $cart_css_version);

        wp_enqueue_script('wpb-cart', DEK_PLUGIN_URL . 'assets/js/cart.js', ['jquery'], $cart_js_version, true);

        $sidebar_menu_css_version = file_exists(DEK_PLUGIN_PATH . 'assets/css/sidebar-menu.css') ? filemtime(DEK_PLUGIN_PATH . 'assets/css/sidebar-menu.css') : DEK_VERSION;
        $sidebar_menu_js_version = file_exists(DEK_PLUGIN_PATH . 'assets/js/sidebar-menu.js') ? filemtime(DEK_PLUGIN_PATH . 'assets/js/sidebar-menu.js') : DEK_VERSION;
        wp_enqueue_style('wpb-sidebar-menu', DEK_PLUGIN_URL . 'assets/css/sidebar-menu.css', [], $sidebar_menu_css_version);
        wp_enqueue_script('wpb-sidebar-menu', DEK_PLUGIN_URL . 'assets/js/sidebar-menu.js', [], $sidebar_menu_js_version, true);


        $checkout_css_version = file_exists(DEK_PLUGIN_PATH . 'assets/css/checkout.css') ? filemtime(DEK_PLUGIN_PATH . 'assets/css/checkout.css') : DEK_VERSION;
        $checkout_js_version = file_exists(DEK_PLUGIN_PATH . 'assets/js/checkout.js') ? filemtime(DEK_PLUGIN_PATH . 'assets/js/checkout.js') : DEK_VERSION;

        wp_enqueue_style('wpb-checkout', DEK_PLUGIN_URL . 'assets/css/checkout.css', [], $checkout_css_version);
        wp_enqueue_script('wpb-checkout', DEK_PLUGIN_URL . 'assets/js/checkout.js', ['jquery', 'wpb-product-grid'], $checkout_js_version, true);

        $thank_you_css_version = file_exists(DEK_PLUGIN_PATH . 'assets/css/thank-you.css') ? filemtime(DEK_PLUGIN_PATH . 'assets/css/thank-you.css') : DEK_VERSION;
        wp_enqueue_style('wpb-thank-you', DEK_PLUGIN_URL . 'assets/css/thank-you.css', [], $thank_you_css_version);


        $product_media_css_version = file_exists(DEK_PLUGIN_PATH . 'assets/css/product-media.css') ? filemtime(DEK_PLUGIN_PATH . 'assets/css/product-media.css') : DEK_VERSION;



        wp_enqueue_style('wpb-product-media', DEK_PLUGIN_URL . 'assets/css/product-media.css', [], $product_media_css_version);

        $single_product_css_version = file_exists(DEK_PLUGIN_PATH . 'assets/css/single-product.css') ? filemtime(DEK_PLUGIN_PATH . 'assets/css/single-product.css') : DEK_VERSION;
        wp_enqueue_style('wpb-single-product', DEK_PLUGIN_URL . 'assets/css/single-product.css', [], $single_product_css_version);


        $product_media_js_version = file_exists(DEK_PLUGIN_PATH . 'assets/js/product-media.js') ? filemtime(DEK_PLUGIN_PATH . 'assets/js/product-media.js') : DEK_VERSION;

        wp_enqueue_script('wpb-product-media', DEK_PLUGIN_URL . 'assets/js/product-media.js', [], $product_media_js_version, true);



        wp_localize_script('wpb-product-grid', 'dekAdmin', [

            'ajaxUrl' => admin_url('admin-ajax.php'),

            'toggleNonce' => wp_create_nonce('dek_toggle_active'),

            'addedText' => __('Added!', 'dynamic-elementkit'),

            'addToCartText' => __('Add to Cart', 'dynamic-elementkit'),

        ]);



        wp_localize_script('wpb-cart', 'dekAdmin', [

            'ajaxUrl' => admin_url('admin-ajax.php'),

            'toggleNonce' => wp_create_nonce('dek_toggle_active'),

            'addedText' => __('Added!', 'dynamic-elementkit'),

            'addToCartText' => __('Add to Cart', 'dynamic-elementkit'),

        ]);

    }



    public function load_textdomain() {

        load_plugin_textdomain('dynamic-elementkit', false, dirname(DEK_PLUGIN_BASENAME) . '/languages/');

    }



    public function activate() {

        // Migrate templates created by the previous plugin slug without
        // changing Elementor's legacy wpb-* widget identifiers.
        global $wpdb;
        $wpdb->query(
            "UPDATE {$wpdb->posts} SET post_type = 'dek_template' WHERE post_type = 'wpb_template'"
        );

        flush_rewrite_rules();

    }



    public function deactivate() {

        flush_rewrite_rules();

    }

}



function dek_init() {

    return Dynamic_ElementKit::get_instance();

}

add_action('plugins_loaded', 'dek_init');

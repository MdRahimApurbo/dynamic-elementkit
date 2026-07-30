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
    $empty_text = !empty($_POST['empty_text']) ? sanitize_text_field(wp_unslash($_POST['empty_text'])) : __('আপনার শপিং কার্টটি খালি।', 'woocommerce-page-builder');
    $continue_text = !empty($_POST['continue_text']) ? sanitize_text_field(wp_unslash($_POST['continue_text'])) : __('কেনাকাটা চালিয়ে যান', 'woocommerce-page-builder');
    $subtotal_text = !empty($_POST['subtotal_text']) ? sanitize_text_field(wp_unslash($_POST['subtotal_text'])) : __('মোট পণ্যমূল্য', 'woocommerce-page-builder');
    $shipping_text = !empty($_POST['shipping_text']) ? sanitize_text_field(wp_unslash($_POST['shipping_text'])) : __('চেকআউটের সময় ডেলিভারি চার্জ হিসাব করা হবে।', 'woocommerce-page-builder');
    $checkout_text = !empty($_POST['checkout_text']) ? sanitize_text_field(wp_unslash($_POST['checkout_text'])) : __('অর্ডার সম্পন্ন করুন', 'woocommerce-page-builder');
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
                <button type="button" class="wpb-cart-remove" data-key="<?php echo esc_attr($cart_item_key); ?>" aria-label="<?php esc_attr_e('কার্ট থেকে সরান', 'woocommerce-page-builder'); ?>"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M9 7V4h6v3M7 7l1 13h8l1-13M10 11v5M14 11v5"></path></svg></button>
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
 * Temporarily replace WooCommerce's in-memory cart with the checkout widget's
 * isolated cart. The normal cart is restored before WooCommerce persists the
 * session, so neither cart can overwrite the other.
 */
function wpb_checkout_activate_isolated_cart($product_id = 0, $quantity = 1) {
    global $wpb_checkout_isolated_cart_active, $wpb_checkout_normal_cart_snapshot;

    if (!empty($wpb_checkout_isolated_cart_active)) {
        return true;
    }

    if (!function_exists('WC') || !WC()->cart || !WC()->session) {
        return false;
    }

    $session = WC()->session;
    $cart = WC()->cart;
    $stored_config = $session->get('wpb_checkout_isolated_config', []);
    $stored_product_id = absint($stored_config['product_id'] ?? 0);
    $product_id = absint($product_id ?: $stored_product_id);

    if (!$product_id) {
        return false;
    }

    $product = wc_get_product($product_id);
    if (!$product || !$product->exists() || !$product->is_purchasable() || $product->is_type('variable')) {
        return false;
    }

    $wpb_checkout_isolated_cart_active = true;
    $wpb_checkout_normal_cart_snapshot = [
        'contents' => $cart->get_cart(),
        'removed' => $cart->get_removed_cart_contents(),
        'coupons' => $cart->get_applied_coupons(),
        'totals' => $cart->get_totals(),
        'session' => [],
    ];

    foreach ([
        'cart',
        'cart_totals',
        'applied_coupons',
        'coupon_discount_totals',
        'coupon_discount_tax_totals',
        'removed_cart_contents',
        'chosen_shipping_methods',
    ] as $key) {
        $wpb_checkout_normal_cart_snapshot['session'][$key] = $session->get($key, null);
    }

    $stored_cart = $session->get('wpb_checkout_isolated_cart', []);
    $stored_coupons = $session->get('wpb_checkout_isolated_coupons', []);
    $stored_shipping_methods = $session->get('wpb_checkout_isolated_shipping_methods', []);
    $same_product = $stored_product_id === $product_id;
    $isolated_cart = $same_product ? wpb_checkout_hydrate_cart($stored_cart) : [];

    $cart->set_cart_contents($isolated_cart);
    $cart->set_removed_cart_contents([]);
    $cart->set_applied_coupons($same_product && is_array($stored_coupons) ? $stored_coupons : []);
    $session->set(
        'chosen_shipping_methods',
        $same_product && is_array($stored_shipping_methods) ? $stored_shipping_methods : []
    );

    if (!$isolated_cart) {
        $quantity = max(1, absint($quantity));

        if ($product->is_type('variation')) {
            $added = $cart->add_to_cart(
                $product->get_parent_id(),
                $quantity,
                $product->get_id(),
                $product->get_variation_attributes()
            );
        } else {
            $added = $cart->add_to_cart($product->get_id(), $quantity);
        }

        if (!$added) {
            $cart->set_cart_contents($wpb_checkout_normal_cart_snapshot['contents']);
            $cart->set_removed_cart_contents($wpb_checkout_normal_cart_snapshot['removed']);
            $cart->set_applied_coupons($wpb_checkout_normal_cart_snapshot['coupons']);
            $cart->set_totals($wpb_checkout_normal_cart_snapshot['totals']);
            $wpb_checkout_isolated_cart_active = false;
            $wpb_checkout_normal_cart_snapshot = [];
            return false;
        }
    }

    $session->set('wpb_checkout_isolated_config', ['product_id' => $product_id]);
    $cart->calculate_totals();
    add_action('shutdown', 'wpb_checkout_restore_normal_cart', -1);

    return true;
}

function wpb_checkout_restore_normal_cart() {
    global $wpb_checkout_isolated_cart_active, $wpb_checkout_normal_cart_snapshot;

    if (
        empty($wpb_checkout_isolated_cart_active) ||
        empty($wpb_checkout_normal_cart_snapshot) ||
        !function_exists('WC') ||
        !WC()->cart ||
        !WC()->session
    ) {
        return;
    }

    $cart = WC()->cart;
    $session = WC()->session;

    $session->set('wpb_checkout_isolated_cart', wpb_checkout_cart_for_session($cart->get_cart()));
    $session->set('wpb_checkout_isolated_coupons', $cart->get_applied_coupons());
    $session->set(
        'wpb_checkout_isolated_shipping_methods',
        $session->get('chosen_shipping_methods', [])
    );

    $cart->set_cart_contents($wpb_checkout_normal_cart_snapshot['contents']);
    $cart->set_removed_cart_contents($wpb_checkout_normal_cart_snapshot['removed']);
    $cart->set_applied_coupons($wpb_checkout_normal_cart_snapshot['coupons']);
    $cart->set_totals($wpb_checkout_normal_cart_snapshot['totals']);

    foreach ($wpb_checkout_normal_cart_snapshot['session'] as $key => $value) {
        if (null === $value) {
            $session->__unset($key);
        } else {
            $session->set($key, $value);
        }
    }

    $wpb_checkout_isolated_cart_active = false;
    $wpb_checkout_normal_cart_snapshot = [];
}

function wpb_checkout_cart_for_session($cart_contents) {
    $stored_cart = [];

    foreach ((array) $cart_contents as $cart_item_key => $cart_item) {
        if (!is_array($cart_item)) {
            continue;
        }

        unset($cart_item['data']);
        $stored_cart[$cart_item_key] = $cart_item;
    }

    return $stored_cart;
}

function wpb_checkout_hydrate_cart($stored_cart) {
    $cart_contents = [];

    foreach ((array) $stored_cart as $cart_item_key => $cart_item) {
        if (!is_array($cart_item)) {
            continue;
        }

        $product_id = !empty($cart_item['variation_id'])
            ? absint($cart_item['variation_id'])
            : absint($cart_item['product_id'] ?? 0);
        $product = $product_id ? wc_get_product($product_id) : false;

        if (!$product || !$product->exists() || !$product->is_purchasable()) {
            continue;
        }

        $stored_item = $cart_item;
        $cart_item['data'] = $product;
        $cart_item = apply_filters(
            'woocommerce_get_cart_item_from_session',
            $cart_item,
            $stored_item,
            $cart_item_key
        );

        if (empty($cart_item['data']) || !$cart_item['data'] instanceof \WC_Product) {
            $cart_item['data'] = $product;
        }
        $cart_contents[$cart_item_key] = $cart_item;
    }

    return $cart_contents;
}

function wpb_checkout_clear_isolated_cart() {
    if (!function_exists('WC') || !WC()->session) {
        return;
    }

    WC()->session->__unset('wpb_checkout_isolated_config');
    WC()->session->__unset('wpb_checkout_isolated_cart');
    WC()->session->__unset('wpb_checkout_isolated_coupons');
    WC()->session->__unset('wpb_checkout_isolated_shipping_methods');
}

add_filter('woocommerce_persistent_cart_enabled', function($enabled) {
    global $wpb_checkout_isolated_cart_active;
    return !empty($wpb_checkout_isolated_cart_active) ? false : $enabled;
});

function wpb_checkout_request_is_widget() {
    $request = $_POST;

    if (!empty($_POST['post_data']) && is_string($_POST['post_data'])) {
        parse_str(wp_unslash($_POST['post_data']), $request);
    }

    return !empty($request['wpb_checkout_widget']);
}

function wpb_checkout_activate_isolated_cart_for_request() {
    if (!wpb_checkout_request_is_widget()) {
        return;
    }

    wpb_checkout_activate_isolated_cart();
}

add_action('wc_ajax_update_order_review', 'wpb_checkout_activate_isolated_cart_for_request', 0);
add_action('wc_ajax_checkout', 'wpb_checkout_activate_isolated_cart_for_request', 0);

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

    $cart_item_key = isset($_POST['cart_item_key']) ? wc_clean(wp_unslash($_POST['cart_item_key'])) : '';
    $quantity = isset($_POST['quantity']) ? max(1, wc_stock_amount(wp_unslash($_POST['quantity']))) : 1;
    $isolated_cart = WC()->session ? WC()->session->get('wpb_checkout_isolated_cart', []) : [];
    if ($cart_item_key && isset($isolated_cart[$cart_item_key])) {
        wpb_checkout_activate_isolated_cart();
    }
    $cart = WC()->cart;
    $cart_item = $cart_item_key ? $cart->get_cart_item($cart_item_key) : false;

    if (!$cart_item || empty($cart_item['data']) || !$cart_item['data'] instanceof \WC_Product) {
        wp_send_json_error(['message' => __('The cart item is no longer available.', 'woocommerce-page-builder')]);
    }

    if (!$cart->set_quantity($cart_item_key, $quantity, true)) {
        wp_send_json_error(['message' => __('The quantity could not be updated.', 'woocommerce-page-builder')]);
    }

    $cart->calculate_totals();
    $cart_item = $cart->get_cart_item($cart_item_key);
    $product = $cart_item['data'];

    wp_send_json_success([
        'cart_item_key' => $cart_item_key,
        'item_subtotal' => $cart->get_product_subtotal($product, $cart_item['quantity']),
        'subtotal' => $cart->get_cart_subtotal(),
        'shipping' => $cart->get_cart_shipping_total(),
        'discount' => wc_price($cart->get_discount_total()),
        'coupons' => implode(', ', $cart->get_applied_coupons()),
        'total' => $cart->get_cart_total(),
        'count' => $cart->get_cart_contents_count(),
    ]);
}

add_action('wp_ajax_wpb_get_checkout_summary', 'wpb_ajax_get_checkout_summary');
add_action('wp_ajax_nopriv_wpb_get_checkout_summary', 'wpb_ajax_get_checkout_summary');

function wpb_ajax_get_checkout_summary() {
    $nonce = isset($_POST['_wpnonce']) ? wp_unslash($_POST['_wpnonce']) : '';
    if (!wp_verify_nonce($nonce, 'wpb_toggle_active')) {
        wp_send_json_error(['message' => __('Security check failed.', 'woocommerce-page-builder')]);
    }

    if (!function_exists('WC') || !WC()->cart) {
        wp_send_json_error(['message' => __('WooCommerce cart is not available.', 'woocommerce-page-builder')]);
    }

    wpb_checkout_activate_isolated_cart();
    WC()->cart->calculate_totals();
    $shipping_methods_html = wpb_get_checkout_shipping_methods_html();

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
 *
 * WooCommerce's checkout script watches these inputs and recalculates totals
 * through wc-ajax=update_order_review when the customer changes a method.
 */
function wpb_get_checkout_shipping_methods_html() {
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
 * Limit native checkout validation to the checkout widget's three customer fields.
 * Order creation still runs through WC_Checkout::process_checkout().
 */
add_filter('woocommerce_checkout_fields', function($fields) {
    if (empty($_POST['wpb_checkout_widget']) && empty($_POST['wpb_quick_checkout'])) {
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
    if ((empty($_POST['wpb_checkout_widget']) && empty($_POST['wpb_quick_checkout'])) || empty($_POST['billing_full_name'])) {
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

    if ((empty($request['wpb_checkout_widget']) && empty($request['wpb_quick_checkout'])) || empty($request['wpb_order_button_text'])) {
        return $text;
    }

    return sanitize_text_field(wp_unslash($request['wpb_order_button_text']));
});

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
    $button_full_width = isset($_POST['button_full_width']) && 'yes' === sanitize_text_field($_POST['button_full_width']) ? 'yes' : 'no';
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

    // Payment is a transactional endpoint and must retain its native screen.
    if (function_exists('is_checkout_pay_page') && is_checkout_pay_page()) {
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
    $active_template = wpb_get_active_template($type);
    if (!$active_template && in_array($type, ['shop', 'product-category', 'product-tag'], true)) {
        $active_template = wpb_get_active_template('archive');
    }
    return $active_template;
}

/**
 * Checkout responses contain customer-specific cart data and nonces. Prevent
 * page/CDN caches from serving another session or an expired checkout nonce.
 */
function wpb_disable_cache_for_dynamic_checkout() {
    $active_template = wpb_get_current_active_template();
    $is_checkout_request = function_exists('is_checkout') && is_checkout();
    $document_id = $active_template ? (int) $active_template->ID : (int) get_queried_object_id();
    $elementor_data = $document_id ? get_post_meta($document_id, '_elementor_data', true) : [];
    $elements = is_string($elementor_data) ? json_decode($elementor_data, true) : $elementor_data;

    if (!$is_checkout_request && !wpb_elementor_data_has_widget($elements, 'wpb-checkout-form')) {
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
 * Persist the WooCommerce guest session before the checkout template renders.
 * Private browser windows then keep the same cart for the order AJAX request.
 */
function wpb_prepare_guest_checkout_session() {
    $is_checkout_request = function_exists('is_checkout') && is_checkout();
    $active_template = wpb_get_current_active_template();
    $document_id = $active_template ? (int) $active_template->ID : (int) get_queried_object_id();
    $elementor_data = $document_id ? get_post_meta($document_id, '_elementor_data', true) : [];
    $elements = is_string($elementor_data) ? json_decode($elementor_data, true) : $elementor_data;
    $has_checkout_widget = wpb_elementor_data_has_widget($elements, 'wpb-checkout-form');

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

    if (function_exists('WC') && WC()->session && method_exists(WC()->session, 'set_customer_session_cookie')) {
        WC()->session->set_customer_session_cookie(true);
    }
}

add_action('template_redirect', 'wpb_prepare_guest_checkout_session', 0);

function wpb_elementor_data_has_widget($elements, $widget_name) {
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

        if (!empty($element['elements']) && wpb_elementor_data_has_widget($element['elements'], $widget_name)) {
            return true;
        }
    }

    return false;
}

add_action('template_redirect', 'wpb_disable_cache_for_dynamic_checkout', 1);

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

        require_once WPB_PLUGIN_PATH . 'includes/dynamic-tags/tags/product-data-tags.php';
        require_once WPB_PLUGIN_PATH . 'includes/dynamic-tags/tags/product-title-tag.php';
        require_once WPB_PLUGIN_PATH . 'includes/dynamic-tags/tags/product-description-tag.php';

        $dynamic_tags_manager->register( new \WooCommerce_Page_Builder\Dynamic_Tags\Tags\Product_Title_Tag() );
        $dynamic_tags_manager->register( new \WooCommerce_Page_Builder\Dynamic_Tags\Tags\Product_Description_Tag() );
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
            $class_name = '\\WooCommerce_Page_Builder\\Dynamic_Tags\\Tags\\' . $tag_class;
            if (class_exists($class_name)) {
                $dynamic_tags_manager->register(new $class_name());
            }
        }
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

        $sidebar_menu_css_version = file_exists(WPB_PLUGIN_PATH . 'assets/css/sidebar-menu.css') ? filemtime(WPB_PLUGIN_PATH . 'assets/css/sidebar-menu.css') : WPB_VERSION;
        $sidebar_menu_js_version = file_exists(WPB_PLUGIN_PATH . 'assets/js/sidebar-menu.js') ? filemtime(WPB_PLUGIN_PATH . 'assets/js/sidebar-menu.js') : WPB_VERSION;
        wp_enqueue_style('wpb-sidebar-menu', WPB_PLUGIN_URL . 'assets/css/sidebar-menu.css', [], $sidebar_menu_css_version);
        wp_enqueue_script('wpb-sidebar-menu', WPB_PLUGIN_URL . 'assets/js/sidebar-menu.js', [], $sidebar_menu_js_version, true);

        $checkout_css_version = file_exists(WPB_PLUGIN_PATH . 'assets/css/checkout.css') ? filemtime(WPB_PLUGIN_PATH . 'assets/css/checkout.css') : WPB_VERSION;
        $checkout_js_version = file_exists(WPB_PLUGIN_PATH . 'assets/js/checkout.js') ? filemtime(WPB_PLUGIN_PATH . 'assets/js/checkout.js') : WPB_VERSION;

        wp_enqueue_style('wpb-checkout', WPB_PLUGIN_URL . 'assets/css/checkout.css', [], $checkout_css_version);
        wp_enqueue_script('wpb-checkout', WPB_PLUGIN_URL . 'assets/js/checkout.js', ['jquery', 'wpb-product-grid'], $checkout_js_version, true);

        $thank_you_css_version = file_exists(WPB_PLUGIN_PATH . 'assets/css/thank-you.css') ? filemtime(WPB_PLUGIN_PATH . 'assets/css/thank-you.css') : WPB_VERSION;
        wp_enqueue_style('wpb-thank-you', WPB_PLUGIN_URL . 'assets/css/thank-you.css', [], $thank_you_css_version);

        $product_media_css_version = file_exists(WPB_PLUGIN_PATH . 'assets/css/product-media.css') ? filemtime(WPB_PLUGIN_PATH . 'assets/css/product-media.css') : WPB_VERSION;

        wp_enqueue_style('wpb-product-media', WPB_PLUGIN_URL . 'assets/css/product-media.css', [], $product_media_css_version);

        $single_product_css_version = file_exists(WPB_PLUGIN_PATH . 'assets/css/single-product.css') ? filemtime(WPB_PLUGIN_PATH . 'assets/css/single-product.css') : WPB_VERSION;
        wp_enqueue_style('wpb-single-product', WPB_PLUGIN_URL . 'assets/css/single-product.css', [], $single_product_css_version);

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


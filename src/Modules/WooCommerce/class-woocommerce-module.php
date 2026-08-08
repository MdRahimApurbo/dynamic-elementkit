<?php

defined('ABSPATH') || exit;

final class DEK_WooCommerce_Module implements DEK_Module_Interface {

    public function register() {
        $declare = function() {
            if (!class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class)) {
                return;
            }

            $features = [
                'custom_order_tables',
                'cart_checkout_blocks',
                'product_blocks',
                'settings_pages',
                'analytics',
            ];

            foreach ($features as $feature) {
                \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility($feature, DEK_PLUGIN_PATH . 'dynamic-elementkit.php', true);
            }
        };

        add_action('before_woocommerce_init', $declare);
        if (did_action('before_woocommerce_init')) {
            $declare();
        }
    }
}

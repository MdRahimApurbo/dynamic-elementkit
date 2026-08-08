<?php

namespace Dynamic_ElementKit\Dynamic_Tags;

defined('ABSPATH') || exit;

use Elementor\Modules\DynamicTags\Module as Dynamic_Tags_Module;

/**
 * Registers Dynamic ElementKit's WooCommerce dynamic-tag group.
 *
 * The individual tag classes keep their legacy wpb-* Elementor identifiers so
 * existing Elementor documents continue to render after the plugin rename.
 */
class Module extends Dynamic_Tags_Module {

    public const PRODUCT_GROUP = 'dek_product';

    public function get_name() {
        return 'dek-dynamic-tags';
    }

    public function get_groups() {
        return [
            self::PRODUCT_GROUP => [
                'title' => __('Product', 'dynamic-elementkit'),
            ],
        ];
    }
}

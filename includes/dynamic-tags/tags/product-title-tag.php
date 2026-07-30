<?php

namespace WooCommerce_Page_Builder\Dynamic_Tags\Tags;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Elementor\Core\DynamicTags\Tag;

class Product_Title_Tag extends Tag {

	public function get_name() {
		return 'wpb-product-title';
	}

	public function get_title() {
		return __( 'Product Title', 'woocommerce-page-builder' );
	}

	public function get_group() {
		return 'wpb_product';
	}

	public function get_categories() {
		return array(
			\Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY,
		);
	}

	public function is_settings_required() {
		return false;
	}

	public function render() {
		$product = function_exists( __NAMESPACE__ . '\\wpb_resolve_product' ) ? wpb_resolve_product() : false;

		if ( ! $product ) {
			return;
		}

		$title = $product->get_name();

		if ( $title ) {
			echo esc_html( $title );
		}
	}

}

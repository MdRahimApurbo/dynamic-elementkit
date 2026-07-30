<?php

namespace WooCommerce_Page_Builder\Dynamic_Tags\Tags;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Elementor\Core\DynamicTags\Tag;

class Product_Description_Tag extends Tag {

	public function get_name() {
		return 'wpb-product-description';
	}

	public function get_title() {
		return __( 'Product Description', 'woocommerce-page-builder' );
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

		$description = apply_filters( 'the_content', $product->get_description() );

		if ( $description ) {
			echo wp_kses_post( $description );
		}
	}

}

<?php

namespace Dynamic_ElementKit\Dynamic_Tags\Tags;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Elementor\Core\DynamicTags\Tag;

class Product_Title_Tag extends Tag {

	public function get_name() {
		return 'wpb-product-title';
	}

	public function get_title() {
		return __( 'Product Title', 'dynamic-elementkit' );
	}

	public function get_group() {
		return 'dek_product';
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
		$product = function_exists( __NAMESPACE__ . '\\dek_resolve_product' ) ? dek_resolve_product() : false;

		if ( ! $product ) {
			return;
		}

		$title = $product->get_name();

		if ( $title ) {
			echo esc_html( $title );
		}
	}

}

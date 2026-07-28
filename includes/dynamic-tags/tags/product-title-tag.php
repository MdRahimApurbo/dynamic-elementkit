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
		$product = false;

		if ( is_product() ) {
			$product = wc_get_product( get_the_ID() );
		} elseif ( is_shop() || is_product_category() || is_product_tag() ) {
			global $wp_query;

			if ( ! empty( $wp_query->post ) ) {
				$product = wc_get_product( $wp_query->post->ID );
			}
		} elseif ( is_search() || is_archive() ) {
			$post_id = get_the_ID();

			if ( $post_id ) {
				$product = wc_get_product( $post_id );
			}
		}

		if ( ! $product ) {
			return;
		}

		$title = $product->get_name();

		if ( $title ) {
			echo esc_html( $title );
		}
	}

}

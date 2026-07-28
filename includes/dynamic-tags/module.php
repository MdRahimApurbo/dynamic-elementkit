<?php

namespace WooCommerce_Page_Builder\Dynamic_Tags;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Elementor\Modules\DynamicTags\Module as Dynamic_Tags_Module;

class Module extends Dynamic_Tags_Module {

	const PRODUCT_GROUP = 'wpb_product';

	public function get_name() {
		return 'wpb-dynamic-tags';
	}

	public function get_tag_classes_names() {
		return array(
			'Tags\Product_Title_Tag',
			'Tags\Product_Description_Tag',
		);
	}

	public function get_groups() {
		return array(
			self::PRODUCT_GROUP => array(
				'title' => __( 'Product', 'woocommerce-page-builder' ),
			),
		);
	}

	public function register_tags( $dynamic_tags ) {
		require_once __DIR__ . '/tags/product-title-tag.php';
		require_once __DIR__ . '/tags/product-description-tag.php';

		foreach ( $this->get_tag_classes_names() as $tag_class ) {
			$class_name = $this->get_reflection()->getNamespaceName() . '\\' . $tag_class;

			if ( class_exists( $class_name ) ) {
				$dynamic_tags->register( new $class_name() );
			}
		}

		do_action( 'wpb/dynamic_tags/register', $dynamic_tags, $this );
	}

}

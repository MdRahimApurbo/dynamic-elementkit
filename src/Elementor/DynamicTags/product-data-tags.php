<?php

namespace Dynamic_ElementKit\Dynamic_Tags\Tags;

defined('ABSPATH') || exit;

use Elementor\Core\DynamicTags\Data_Tag;
use Elementor\Core\DynamicTags\Tag;
use Elementor\Modules\DynamicTags\Module;

function dek_resolve_product() {
    if (!function_exists('wc_get_product')) {
        return false;
    }

    if (function_exists('dek_get_landing_product')) {
        $landing_product = dek_get_landing_product();
        if ($landing_product instanceof \WC_Product) {
            return $landing_product;
        }
    }

    if (function_exists('is_product') && is_product()) {
        $product = wc_get_product(get_queried_object_id());
        if ($product instanceof \WC_Product) {
            return $product;
        }
    }

    global $product;
    if ($product instanceof \WC_Product) {
        return $product;
    }

    $post_id = get_the_ID();
    if ($post_id && 'product' === get_post_type($post_id)) {
        $resolved = wc_get_product($post_id);
        if ($resolved instanceof \WC_Product) {
            return $resolved;
        }
    }

    if (class_exists('\\Elementor\\Plugin') && !empty(\Elementor\Plugin::$instance->editor) && \Elementor\Plugin::$instance->editor->is_edit_mode()) {
        $ids = wc_get_products(['limit' => 1, 'status' => 'publish', 'return' => 'ids']);
        return $ids ? wc_get_product($ids[0]) : false;
    }

    return false;
}

function dek_product_term_names($product_id, $taxonomy) {
    $names = wp_get_post_terms($product_id, $taxonomy, ['fields' => 'names']);
    return is_wp_error($names) || !is_array($names) ? '' : implode(', ', $names);
}

function dek_product_numeric_price($product, $price_type) {
    if ($product->is_type('variable')) {
        $prices = $product->get_variation_prices(true);
        $values = isset($prices[$price_type]) ? array_filter($prices[$price_type], 'strlen') : [];
        return $values ? min(array_map('floatval', $values)) : '';
    }

    $getter = 'get_' . $price_type;
    return is_callable([$product, $getter]) ? $product->{$getter}() : '';
}

abstract class Product_Text_Tag_Base extends Tag {
    public function get_group() { return 'dek_product'; }
    public function get_categories() { return [Module::TEXT_CATEGORY]; }
    public function is_settings_required() { return false; }
    abstract protected function product_value($product);
    public function render() {
        $product = dek_resolve_product();
        if (!$product) return;
        $value = $this->product_value($product);
        if (null !== $value && '' !== $value) echo wp_kses_post((string) $value);
    }
}

abstract class Product_Number_Tag_Base extends Product_Text_Tag_Base {
    public function get_categories() { return [Module::NUMBER_CATEGORY, Module::TEXT_CATEGORY]; }
    public function render() {
        $product = dek_resolve_product();
        if (!$product) return;
        $value = $this->product_value($product);
        if (null !== $value && '' !== $value) echo esc_html((string) $value);
    }
}

class Product_ID_Tag extends Product_Number_Tag_Base {
    public function get_name() { return 'wpb-product-id'; }
    public function get_title() { return __('Product ID', 'dynamic-elementkit'); }
    protected function product_value($product) { return $product->get_id(); }
}

class Product_SKU_Tag extends Product_Text_Tag_Base {
    public function get_name() { return 'wpb-product-sku'; }
    public function get_title() { return __('Product SKU', 'dynamic-elementkit'); }
    protected function product_value($product) { return esc_html($product->get_sku()); }
}

class Product_Short_Description_Tag extends Product_Text_Tag_Base {
    public function get_name() { return 'wpb-product-short-description'; }
    public function get_title() { return __('Product Short Description', 'dynamic-elementkit'); }
    protected function product_value($product) { return apply_filters('woocommerce_short_description', $product->get_short_description()); }
}

class Product_Price_Tag extends Product_Text_Tag_Base {
    public function get_name() { return 'wpb-product-price'; }
    public function get_title() { return __('Product Price (Formatted)', 'dynamic-elementkit'); }
    protected function product_value($product) { return $product->get_price_html(); }
}

class Product_Regular_Price_Tag extends Product_Number_Tag_Base {
    public function get_name() { return 'wpb-product-regular-price'; }
    public function get_title() { return __('Product Regular Price', 'dynamic-elementkit'); }
    protected function product_value($product) { return dek_product_numeric_price($product, 'regular_price'); }
}

class Product_Sale_Price_Tag extends Product_Number_Tag_Base {
    public function get_name() { return 'wpb-product-sale-price'; }
    public function get_title() { return __('Product Sale Price', 'dynamic-elementkit'); }
    protected function product_value($product) { return dek_product_numeric_price($product, 'sale_price'); }
}

class Product_Sale_Percentage_Tag extends Product_Number_Tag_Base {
    public function get_name() { return 'wpb-product-sale-percentage'; }
    public function get_title() { return __('Product Sale Percentage', 'dynamic-elementkit'); }
    protected function product_value($product) {
        $regular = (float) dek_product_numeric_price($product, 'regular_price');
        $sale = (float) dek_product_numeric_price($product, 'sale_price');
        return $regular > 0 && $sale >= 0 && $sale < $regular ? round((($regular - $sale) / $regular) * 100) : 0;
    }
}

class Product_Stock_Status_Tag extends Product_Text_Tag_Base {
    public function get_name() { return 'wpb-product-stock-status'; }
    public function get_title() { return __('Product Stock Status', 'dynamic-elementkit'); }
    protected function product_value($product) {
        if ($product->is_on_backorder()) return esc_html__('Available on backorder', 'dynamic-elementkit');
        return $product->is_in_stock() ? esc_html__('In stock', 'dynamic-elementkit') : esc_html__('Out of stock', 'dynamic-elementkit');
    }
}

class Product_Stock_Quantity_Tag extends Product_Number_Tag_Base {
    public function get_name() { return 'wpb-product-stock-quantity'; }
    public function get_title() { return __('Product Stock Quantity', 'dynamic-elementkit'); }
    protected function product_value($product) { return $product->managing_stock() ? $product->get_stock_quantity() : ''; }
}

class Product_Type_Tag extends Product_Text_Tag_Base {
    public function get_name() { return 'wpb-product-type'; }
    public function get_title() { return __('Product Type', 'dynamic-elementkit'); }
    protected function product_value($product) { return esc_html($product->get_type()); }
}

class Product_Weight_Tag extends Product_Text_Tag_Base {
    public function get_name() { return 'wpb-product-weight'; }
    public function get_title() { return __('Product Weight', 'dynamic-elementkit'); }
    protected function product_value($product) { return $product->has_weight() ? esc_html(wc_format_weight($product->get_weight())) : ''; }
}

class Product_Dimensions_Tag extends Product_Text_Tag_Base {
    public function get_name() { return 'wpb-product-dimensions'; }
    public function get_title() { return __('Product Dimensions', 'dynamic-elementkit'); }
    protected function product_value($product) { return $product->has_dimensions() ? esc_html(wc_format_dimensions($product->get_dimensions(false))) : ''; }
}

class Product_Categories_Tag extends Product_Text_Tag_Base {
    public function get_name() { return 'wpb-product-categories'; }
    public function get_title() { return __('Product Categories', 'dynamic-elementkit'); }
    protected function product_value($product) { return esc_html(dek_product_term_names($product->get_id(), 'product_cat')); }
}

class Product_Tags_Tag extends Product_Text_Tag_Base {
    public function get_name() { return 'wpb-product-tags'; }
    public function get_title() { return __('Product Tags', 'dynamic-elementkit'); }
    protected function product_value($product) { return esc_html(dek_product_term_names($product->get_id(), 'product_tag')); }
}

class Product_Rating_Tag extends Product_Number_Tag_Base {
    public function get_name() { return 'wpb-product-rating-value'; }
    public function get_title() { return __('Product Average Rating', 'dynamic-elementkit'); }
    protected function product_value($product) { return $product->get_average_rating(); }
}

class Product_Review_Count_Tag extends Product_Number_Tag_Base {
    public function get_name() { return 'wpb-product-review-count'; }
    public function get_title() { return __('Product Review Count', 'dynamic-elementkit'); }
    protected function product_value($product) { return $product->get_review_count(); }
}

class Product_URL_Tag extends Tag {
    public function get_name() { return 'wpb-product-url'; }
    public function get_title() { return __('Product URL', 'dynamic-elementkit'); }
    public function get_group() { return 'dek_product'; }
    public function get_categories() { return [Module::URL_CATEGORY, Module::TEXT_CATEGORY]; }
    public function is_settings_required() { return false; }
    public function render() { $product = dek_resolve_product(); if ($product) echo esc_url($product->get_permalink()); }
}

class Product_Image_Tag extends Data_Tag {
    public function get_name() { return 'wpb-product-image'; }
    public function get_title() { return __('Product Featured Image', 'dynamic-elementkit'); }
    public function get_group() { return 'dek_product'; }
    public function get_categories() { return [Module::IMAGE_CATEGORY]; }
    public function is_settings_required() { return false; }
    public function get_value(array $options = []) {
        $product = dek_resolve_product();
        $image_id = $product ? $product->get_image_id() : 0;
        return ['id' => $image_id, 'url' => $image_id ? wp_get_attachment_image_url($image_id, 'full') : wc_placeholder_img_src('full')];
    }
}

<?php
defined('ABSPATH') || exit;

abstract class WPB_Single_Product_Widget_Base extends \Elementor\Widget_Base {

    public function get_categories() {
        return ['wpb-woo-page-builder'];
    }

    public function get_keywords() {
        return ['product', 'single product', 'woocommerce', 'shop'];
    }

    public function get_style_depends() {
        return ['wpb-single-product'];
    }

    protected function register_preview_control() {
        $this->start_controls_section('product_preview_section', [
            'label' => __('Product Preview', 'woocommerce-page-builder'),
            'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
        ]);
        $this->add_control('preview_product_id', [
            'label' => __('Preview Product', 'woocommerce-page-builder'),
            'type' => \Elementor\Controls_Manager::SELECT2,
            'options' => $this->get_products(),
            'description' => __('Used in Elementor preview only. The live template always uses the current product.', 'woocommerce-page-builder'),
        ]);
        $this->end_controls_section();
    }

    protected function resolve_product() {
        global $product;

        if (function_exists('is_product') && is_product()) {
            $queried_product = wc_get_product(get_queried_object_id());
            if ($queried_product instanceof \WC_Product) {
                return $queried_product;
            }
        }

        if ($product instanceof \WC_Product) {
            return $product;
        }

        $settings = $this->get_settings_for_display();
        $preview_id = !empty($settings['preview_product_id']) ? absint($settings['preview_product_id']) : 0;
        if ($preview_id) {
            return wc_get_product($preview_id);
        }

        if (!empty(\Elementor\Plugin::$instance->editor) && \Elementor\Plugin::$instance->editor->is_edit_mode()) {
            $ids = wc_get_products(['limit' => 1, 'status' => 'publish', 'return' => 'ids']);
            return $ids ? wc_get_product($ids[0]) : false;
        }

        return false;
    }

    protected function with_product($callback) {
        global $product, $post;
        $resolved = $this->resolve_product();
        if (!$resolved) {
            echo '<p class="wpb-single-product-empty">' . esc_html__('No product is available for preview.', 'woocommerce-page-builder') . '</p>';
            return;
        }

        $previous_product = $product;
        $previous_post = $post;
        $product = $resolved;
        $product_post = get_post($resolved->get_id());
        if ($product_post) {
            $post = $product_post;
            setup_postdata($post);
        }

        call_user_func($callback, $resolved);

        $product = $previous_product;
        $post = $previous_post;
        if ($previous_post instanceof \WP_Post) {
            setup_postdata($previous_post);
        } else {
            wp_reset_postdata();
        }
    }

    private function get_products() {
        $products = wc_get_products(['limit' => 100, 'status' => 'publish', 'orderby' => 'title', 'order' => 'ASC']);
        $options = [];
        foreach ($products as $item) {
            $options[$item->get_id()] = $item->get_name();
        }
        return $options;
    }
}

class WPB_Product_Title_Widget extends WPB_Single_Product_Widget_Base {
    public function get_name() { return 'wpb-product-title'; }
    public function get_title() { return __('Product Title', 'woocommerce-page-builder'); }
    public function get_icon() { return 'eicon-product-title'; }
    protected function register_controls() {
        $this->register_preview_control();
        $this->start_controls_section('title_style', ['label' => __('Title', 'woocommerce-page-builder'), 'tab' => \Elementor\Controls_Manager::TAB_STYLE]);
        $this->add_control('title_color', ['label' => __('Color', 'woocommerce-page-builder'), 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} .wpb-single-product-title' => 'color: {{VALUE}};']]);
        $this->add_group_control(\Elementor\Group_Control_Typography::get_type(), ['name' => 'title_typography', 'selector' => '{{WRAPPER}} .wpb-single-product-title']);
        $this->end_controls_section();
    }
    protected function render() { $this->with_product(function($product) { echo '<h1 class="wpb-single-product-title">' . esc_html($product->get_name()) . '</h1>'; }); }
}

class WPB_Product_Price_Widget extends WPB_Single_Product_Widget_Base {
    public function get_name() { return 'wpb-product-price'; }
    public function get_title() { return __('Product Price', 'woocommerce-page-builder'); }
    public function get_icon() { return 'eicon-product-price'; }
    protected function register_controls() {
        $this->register_preview_control();
        $this->start_controls_section('price_style', ['label' => __('Price', 'woocommerce-page-builder'), 'tab' => \Elementor\Controls_Manager::TAB_STYLE]);
        $this->add_control('price_color', ['label' => __('Color', 'woocommerce-page-builder'), 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} .wpb-single-product-price' => 'color: {{VALUE}};']]);
        $this->add_group_control(\Elementor\Group_Control_Typography::get_type(), ['name' => 'price_typography', 'selector' => '{{WRAPPER}} .wpb-single-product-price']);
        $this->end_controls_section();
    }
    protected function render() { $this->with_product(function($product) { echo '<div class="wpb-single-product-price price">' . wp_kses_post($product->get_price_html()) . '</div>'; }); }
}

class WPB_Product_Rating_Widget extends WPB_Single_Product_Widget_Base {
    public function get_name() { return 'wpb-product-rating'; }
    public function get_title() { return __('Product Rating', 'woocommerce-page-builder'); }
    public function get_icon() { return 'eicon-product-rating'; }
    protected function register_controls() { $this->register_preview_control(); }
    protected function render() { $this->with_product(function() { echo '<div class="wpb-single-product-rating">'; woocommerce_template_single_rating(); echo '</div>'; }); }
}

class WPB_Product_Short_Description_Widget extends WPB_Single_Product_Widget_Base {
    public function get_name() { return 'wpb-product-short-description'; }
    public function get_title() { return __('Product Short Description', 'woocommerce-page-builder'); }
    public function get_icon() { return 'eicon-product-description'; }
    protected function register_controls() {
        $this->register_preview_control();
        $this->start_controls_section('description_style', ['label' => __('Description', 'woocommerce-page-builder'), 'tab' => \Elementor\Controls_Manager::TAB_STYLE]);
        $this->add_control('text_color', ['label' => __('Color', 'woocommerce-page-builder'), 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} .wpb-single-product-description' => 'color: {{VALUE}};']]);
        $this->add_group_control(\Elementor\Group_Control_Typography::get_type(), ['name' => 'description_typography', 'selector' => '{{WRAPPER}} .wpb-single-product-description']);
        $this->end_controls_section();
    }
    protected function render() { $this->with_product(function($product) { echo '<div class="wpb-single-product-description">' . wp_kses_post(apply_filters('woocommerce_short_description', $product->get_short_description())) . '</div>'; }); }
}

class WPB_Product_Add_To_Cart_Widget extends WPB_Single_Product_Widget_Base {
    public function get_name() { return 'wpb-product-add-to-cart'; }
    public function get_title() { return __('Product Add to Cart / Variations', 'woocommerce-page-builder'); }
    public function get_icon() { return 'eicon-product-add-to-cart'; }
    public function get_script_depends() { return ['wc-add-to-cart-variation']; }
    protected function register_controls() {
        $this->register_preview_control();
        $this->start_controls_section('button_style', ['label' => __('Add to Cart', 'woocommerce-page-builder'), 'tab' => \Elementor\Controls_Manager::TAB_STYLE]);
        $this->add_control('button_color', ['label' => __('Button Color', 'woocommerce-page-builder'), 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} .wpb-single-product-cart .single_add_to_cart_button' => 'background-color: {{VALUE}};']]);
        $this->add_control('button_text_color', ['label' => __('Text Color', 'woocommerce-page-builder'), 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} .wpb-single-product-cart .single_add_to_cart_button' => 'color: {{VALUE}};']]);
        $this->end_controls_section();
    }
    protected function render() {
        wp_enqueue_script('wc-add-to-cart-variation');
        $this->with_product(function() {
            echo '<div class="wpb-single-product-cart">';
            woocommerce_template_single_add_to_cart();
            echo '</div>';
        });
    }
}

class WPB_Product_Meta_Widget extends WPB_Single_Product_Widget_Base {
    public function get_name() { return 'wpb-product-meta'; }
    public function get_title() { return __('Product Meta / Information', 'woocommerce-page-builder'); }
    public function get_icon() { return 'eicon-product-meta'; }
    protected function register_controls() { $this->register_preview_control(); }
    protected function render() { $this->with_product(function() { echo '<div class="wpb-single-product-meta">'; woocommerce_template_single_meta(); echo '</div>'; }); }
}

class WPB_Product_Tabs_Widget extends WPB_Single_Product_Widget_Base {
    public function get_name() { return 'wpb-product-tabs'; }
    public function get_title() { return __('Product Data Tabs', 'woocommerce-page-builder'); }
    public function get_icon() { return 'eicon-tabs'; }
    public function get_script_depends() { return ['wc-single-product']; }
    protected function register_controls() { $this->register_preview_control(); }
    protected function render() { $this->with_product(function() { echo '<div class="wpb-single-product-tabs">'; woocommerce_output_product_data_tabs(); echo '</div>'; }); }
}

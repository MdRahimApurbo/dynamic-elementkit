<?php
if (!defined('ABSPATH')) exit;

class DEK_Elementor_Widgets {

    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        if (!class_exists('\Elementor\Plugin')) {
            return;
        }
        add_action('elementor/elements/categories_registered', [$this, 'register_category']);
        add_action('elementor/widgets/register', [$this, 'register_widgets']);
    }

    public function register_category($elements_manager) {
        $elements_manager->add_category(
            'wpb-woo-page-builder',
            [
                'title' => __('Dynamic ElementKit', 'dynamic-elementkit'),
                'icon'  => 'eicon-archive-posts',
            ]
        );
    }

    public function register_widgets($widgets_manager) {
        require_once DEK_PLUGIN_PATH . 'src/Elementor/Widgets/archive-title.php';
        if (class_exists('DEK_Archive_Title_Widget')) {
            $widgets_manager->register(new DEK_Archive_Title_Widget());
        }
        require_once DEK_PLUGIN_PATH . 'src/Elementor/Widgets/product-grid.php';
        if (class_exists('DEK_Product_Grid_Widget')) {
            $widgets_manager->register(new DEK_Product_Grid_Widget());
        }
        require_once DEK_PLUGIN_PATH . 'src/Elementor/Widgets/category-filter.php';
        if (class_exists('DEK_Category_Filter_Widget')) {
            $widgets_manager->register(new DEK_Category_Filter_Widget());
        }
        require_once DEK_PLUGIN_PATH . 'src/Elementor/Widgets/price-range.php';
        if (class_exists('DEK_Price_Range_Widget')) {
            $widgets_manager->register(new DEK_Price_Range_Widget());
        }
        require_once DEK_PLUGIN_PATH . 'src/Elementor/Widgets/product-search.php';
        if (class_exists('DEK_Product_Search_Widget')) {
            $widgets_manager->register(new DEK_Product_Search_Widget());
        }
        require_once DEK_PLUGIN_PATH . 'src/Elementor/Widgets/cart.php';
        if (class_exists('DEK_Cart_Widget')) {
            $widgets_manager->register(new DEK_Cart_Widget());
        }
        require_once DEK_PLUGIN_PATH . 'src/Elementor/Widgets/sidebar-menu.php';
        if (class_exists('DEK_Sidebar_Menu_Widget')) {
            $widgets_manager->register(new DEK_Sidebar_Menu_Widget());
        }
        require_once DEK_PLUGIN_PATH . 'src/Elementor/Widgets/checkout-form.php';
        if (class_exists('DEK_Checkout_Form_Widget')) {
            $widgets_manager->register(new DEK_Checkout_Form_Widget());
        }
        require_once DEK_PLUGIN_PATH . 'src/Elementor/Widgets/thank-you.php';
        if (class_exists('DEK_Thank_You_Widget')) {
            $widgets_manager->register(new DEK_Thank_You_Widget());
        }
        require_once DEK_PLUGIN_PATH . 'src/Elementor/Widgets/product-media.php';
        if (class_exists('DEK_Product_Media_Widget')) {
            $widgets_manager->register(new DEK_Product_Media_Widget());
        }
        require_once DEK_PLUGIN_PATH . 'src/Elementor/Widgets/single-product-elements.php';
        foreach ([
            'DEK_Product_Title_Widget',
            'DEK_Product_Price_Widget',
            'DEK_Product_Rating_Widget',
            'DEK_Product_Short_Description_Widget',
            'DEK_Product_Add_To_Cart_Widget',
            'DEK_Product_Meta_Widget',
            'DEK_Product_Tabs_Widget',
        ] as $widget_class) {
            if (class_exists($widget_class)) {
                $widgets_manager->register(new $widget_class());
            }
        }
    }
}

DEK_Elementor_Widgets::get_instance();

<?php
if (!defined('ABSPATH')) exit;

class WPB_Elementor_Widgets {

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
                'title' => __('WooCommerce Page Builder', 'woocommerce-page-builder'),
                'icon'  => 'eicon-archive-posts',
            ]
        );
    }

    public function register_widgets($widgets_manager) {
        require_once WPB_PLUGIN_PATH . 'includes/widgets/class-archive-title.php';
        if (class_exists('WPB_Archive_Title_Widget')) {
            $widgets_manager->register(new WPB_Archive_Title_Widget());
        }
        require_once WPB_PLUGIN_PATH . 'includes/widgets/class-product-grid.php';
        if (class_exists('WPB_Product_Grid_Widget')) {
            $widgets_manager->register(new WPB_Product_Grid_Widget());
        }
        require_once WPB_PLUGIN_PATH . 'includes/widgets/class-category-filter.php';
        if (class_exists('WPB_Category_Filter_Widget')) {
            $widgets_manager->register(new WPB_Category_Filter_Widget());
        }
        require_once WPB_PLUGIN_PATH . 'includes/widgets/class-price-range.php';
        if (class_exists('WPB_Price_Range_Widget')) {
            $widgets_manager->register(new WPB_Price_Range_Widget());
        }
        require_once WPB_PLUGIN_PATH . 'includes/widgets/class-product-search.php';
        if (class_exists('WPB_Product_Search_Widget')) {
            $widgets_manager->register(new WPB_Product_Search_Widget());
        }
        require_once WPB_PLUGIN_PATH . 'includes/widgets/class-cart.php';
        if (class_exists('WPB_Cart_Widget')) {
            $widgets_manager->register(new WPB_Cart_Widget());
        }
        require_once WPB_PLUGIN_PATH . 'includes/widgets/class-sidebar-menu.php';
        if (class_exists('WPB_Sidebar_Menu_Widget')) {
            $widgets_manager->register(new WPB_Sidebar_Menu_Widget());
        }
        require_once WPB_PLUGIN_PATH . 'includes/widgets/class-checkout-form.php';
        if (class_exists('WPB_Checkout_Form_Widget')) {
            $widgets_manager->register(new WPB_Checkout_Form_Widget());
        }
        require_once WPB_PLUGIN_PATH . 'includes/widgets/class-thank-you.php';
        if (class_exists('WPB_Thank_You_Widget')) {
            $widgets_manager->register(new WPB_Thank_You_Widget());
        }
        require_once WPB_PLUGIN_PATH . 'includes/widgets/class-product-media.php';
        if (class_exists('WPB_Product_Media_Widget')) {
            $widgets_manager->register(new WPB_Product_Media_Widget());
        }
        require_once WPB_PLUGIN_PATH . 'includes/widgets/class-single-product-elements.php';
        foreach ([
            'WPB_Product_Title_Widget',
            'WPB_Product_Price_Widget',
            'WPB_Product_Rating_Widget',
            'WPB_Product_Short_Description_Widget',
            'WPB_Product_Add_To_Cart_Widget',
            'WPB_Product_Meta_Widget',
            'WPB_Product_Tabs_Widget',
        ] as $widget_class) {
            if (class_exists($widget_class)) {
                $widgets_manager->register(new $widget_class());
            }
        }
    }
}

WPB_Elementor_Widgets::get_instance();

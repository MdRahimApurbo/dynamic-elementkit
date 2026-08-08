<?php

defined('ABSPATH') || exit;

final class DEK_Elementor_Module implements DEK_Module_Interface {

    public function register() {
        require_once DEK_PLUGIN_PATH . 'src/Modules/Elementor/class-preview-router.php';
        require_once DEK_PLUGIN_PATH . 'src/Elementor/Widgets/widget-loader.php';

        DEK_Elementor_Preview_Router::register();
    }
}

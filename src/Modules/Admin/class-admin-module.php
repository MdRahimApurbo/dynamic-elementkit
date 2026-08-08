<?php

defined('ABSPATH') || exit;

final class DEK_Admin_Module implements DEK_Module_Interface {

    public function register() {
        require_once DEK_PLUGIN_PATH . 'src/class-admin-page.php';
        require_once DEK_PLUGIN_PATH . 'src/class-template-table.php';
    }
}

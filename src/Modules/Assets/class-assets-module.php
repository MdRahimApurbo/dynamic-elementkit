<?php

defined('ABSPATH') || exit;

final class DEK_Assets_Module implements DEK_Module_Interface {

    private $plugin;

    public function __construct($plugin) {
        $this->plugin = $plugin;
    }

    public function register() {
        add_action('wp_enqueue_scripts', [$this->plugin, 'enqueue_frontend_assets']);
        add_action('elementor/editor/before_enqueue_scripts', [$this->plugin, 'enqueue_frontend_assets']);
        add_action('elementor/editor/after_enqueue_styles', [$this->plugin, 'enqueue_frontend_assets']);
    }
}

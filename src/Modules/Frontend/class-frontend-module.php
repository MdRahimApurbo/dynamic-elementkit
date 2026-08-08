<?php

defined('ABSPATH') || exit;

final class DEK_Frontend_Module implements DEK_Module_Interface {

    public function register() {
        add_action('wp_body_open', function() {
            dek_render_site_template('header');
        }, 1);

        add_action('wp_footer', function() {
            dek_render_site_template('footer');
        }, 1);
    }
}

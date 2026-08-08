<?php

defined('ABSPATH') || exit;

/**
 * Repairs Elementor atomic-style cache entries whose generated files vanished.
 */
final class DEK_Elementor_Style_Manager {

    public static function refresh_missing_atomic_css($template_id, $context = 'frontend') {
        $template_id = absint($template_id);
        $context = 'preview' === $context ? 'preview' : 'frontend';
        if (!$template_id) {
            return;
        }

        $upload_dir = wp_upload_dir();
        if (!empty($upload_dir['error']) || empty($upload_dir['basedir'])) {
            return;
        }

        $css_dir = trailingslashit($upload_dir['basedir']) . 'elementor/css/';
        $base_css = $css_dir . 'base-desktop.css';
        $template_css = $css_dir . 'local-' . $template_id . '-' . $context . '-desktop.css';

        if (!file_exists($base_css)) {
            do_action('elementor/atomic-widgets/styles/clear', ['base']);
        }

        if (!file_exists($template_css)) {
            do_action('elementor/atomic-widgets/styles/clear', ['local', $template_id, $context]);
        }
    }
}

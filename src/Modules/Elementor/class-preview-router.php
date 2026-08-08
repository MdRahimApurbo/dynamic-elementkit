<?php

defined('ABSPATH') || exit;

/**
 * Keeps Elementor editor previews separate from public template permalinks.
 */
final class DEK_Elementor_Preview_Router {

    public static function register() {
        add_filter('request', [self::class, 'route_preview_request'], 1);
        add_filter('elementor/document/urls/preview', [self::class, 'get_preview_url'], 10, 2);
    }

    /**
     * Make the requested template the main query object. Elementor checks the
     * main post ID before it enables preview mode inside the editor iframe.
     */
    public static function route_preview_request($query_vars) {
        $template = self::get_requested_template();
        if (!$template || !current_user_can('edit_post', $template->ID)) {
            return $query_vars;
        }

        foreach (['name', 'pagename', 'page_id', 'attachment', 'attachment_id'] as $query_var) {
            unset($query_vars[$query_var]);
        }

        $query_vars['post_type'] = 'dek_template';
        $query_vars['p'] = (int) $template->ID;

        nocache_headers();
        self::refresh_missing_atomic_css((int) $template->ID);

        return $query_vars;
    }

    /**
     * Avoid /landing/{slug}/ for editor previews. Only landing templates are
     * public, while every template type must remain editable in Elementor.
     */
    public static function get_preview_url($url, $document) {
        if (!is_object($document) || !method_exists($document, 'get_main_id')) {
            return $url;
        }

        $template_id = absint($document->get_main_id());
        $template = $template_id ? get_post($template_id) : false;
        if (!$template instanceof \WP_Post || 'dek_template' !== $template->post_type) {
            return $url;
        }

        return add_query_arg(
            [
                'elementor-preview' => $template_id,
                'ver' => time(),
            ],
            home_url('/')
        );
    }

    private static function get_requested_template() {
        $template_id = isset($_GET['elementor-preview']) ? absint($_GET['elementor-preview']) : 0;
        if (!$template_id) {
            return false;
        }

        $template = get_post($template_id);
        if (
            !$template instanceof \WP_Post ||
            'dek_template' !== $template->post_type ||
            !in_array($template->post_status, ['publish', 'draft', 'pending', 'auto-draft', 'future'], true)
        ) {
            return false;
        }

        return $template;
    }

    /**
     * Elementor 4 can retain valid atomic-style metadata after the generated
     * file has been removed. Invalidate only missing editor CSS branches so
     * Elementor recreates them during its normal enqueue pass.
     */
    private static function refresh_missing_atomic_css($template_id) {
        $upload_dir = wp_upload_dir();
        if (!empty($upload_dir['error']) || empty($upload_dir['basedir'])) {
            return;
        }

        $css_dir = trailingslashit($upload_dir['basedir']) . 'elementor/css/';
        $base_css = $css_dir . 'base-desktop.css';
        $template_css = $css_dir . 'local-' . $template_id . '-preview-desktop.css';

        if (!file_exists($base_css)) {
            do_action('elementor/atomic-widgets/styles/clear', ['base']);
        }

        if (!file_exists($template_css)) {
            do_action('elementor/atomic-widgets/styles/clear', ['local', $template_id, 'preview']);
        }
    }
}

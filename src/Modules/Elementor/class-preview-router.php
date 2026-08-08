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
        DEK_Elementor_Style_Manager::refresh_missing_atomic_css((int) $template->ID, 'preview');

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
}

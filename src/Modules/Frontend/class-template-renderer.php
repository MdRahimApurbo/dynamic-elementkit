<?php

defined('ABSPATH') || exit;

final class DEK_Template_Renderer {

    public static function render($template) {
        if (!$template instanceof \WP_Post) {
            return;
        }

        $template_id = absint($template->ID);
        if (!$template_id) {
            return;
        }

        $is_elementor_preview = isset($_GET['elementor-preview']);
        self::register_body_classes($template_id);

        if (!$is_elementor_preview) {
            get_header();
        }

        self::render_content($template, $template_id);

        if (!$is_elementor_preview) {
            get_footer();
        }
    }

    private static function register_body_classes($template_id) {
        add_filter('body_class', static function($classes) use ($template_id) {
            $classes[] = 'elementor-page';
            $classes[] = 'elementor-page-' . $template_id;
            $classes[] = 'dek-template-' . $template_id;
            return $classes;
        });
    }

    private static function render_content($template, $template_id) {
        $template_post = get_post($template_id);
        if (!$template_post) {
            return;
        }

        setup_postdata($template_post);

        if (class_exists('Elementor\\Plugin') && \Elementor\Plugin::$instance->frontend) {
            echo \Elementor\Plugin::$instance->frontend->get_builder_content_for_display($template_id); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Elementor renders sanitized document output.
        } else {
            echo apply_filters('the_content', $template->post_content); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- WordPress content filters sanitize output.
        }

        wp_reset_postdata();
    }
}

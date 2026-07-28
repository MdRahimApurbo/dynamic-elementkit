<?php
defined('ABSPATH') || exit;

global $wpb_active_template;

if ($wpb_active_template && isset($wpb_active_template->post_content)) {
    add_filter('body_class', function($classes) use ($wpb_active_template) {
        $template_id = (int) $wpb_active_template->ID;
        if ($template_id) {
            $classes[] = 'elementor-page';
            $classes[] = 'elementor-page-' . $template_id;
        }
        return $classes;
    });

    $template_id = (int) $wpb_active_template->ID;
    add_action('wp_head', function() use ($template_id) {
        if (class_exists('\Elementor\Plugin') && \Elementor\Plugin::$instance->frontend) {
            $post_css = \Elementor\Core\Files\CSS\Post::create($template_id);
            $post_css->enqueue();
        }
    }, 1);
}

get_header();

if ($wpb_active_template && isset($wpb_active_template->post_content)) {
    if (class_exists('\Elementor\Plugin') && \Elementor\Plugin::$instance->frontend) {
        $template_id = (int) $wpb_active_template->ID;
        $template_post = get_post($template_id);
        if ($template_post) {
            setup_postdata($template_post);
        }
        echo \Elementor\Plugin::$instance->frontend->get_builder_content_for_display($template_id, true);
        if ($template_post) {
            wp_reset_postdata();
        }
    } else {
        echo apply_filters('the_content', $wpb_active_template->post_content);
    }
} elseif (function_exists('woocommerce_content')) {
    woocommerce_content();
}

get_footer();

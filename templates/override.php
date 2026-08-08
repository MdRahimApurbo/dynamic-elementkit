<?php
defined('ABSPATH') || exit;

global $dek_active_template;

require_once DEK_PLUGIN_PATH . 'src/Modules/Frontend/class-template-renderer.php';

if ($dek_active_template instanceof \WP_Post) {
    DEK_Template_Renderer::render($dek_active_template);
} elseif (function_exists('woocommerce_content')) {
    get_header();
    woocommerce_content();
    get_footer();
}

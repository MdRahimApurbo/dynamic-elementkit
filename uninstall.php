<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @link       https://github.com/yourusername/woocommerce-page-builder
 * @since      1.0.0
 */

// If uninstall not called from WordPress, exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Delete plugin options
delete_option('wpb_license_key');
delete_option('wpb_github_url');
delete_option('wpb_enable_authentication');
delete_option('wpb_google_client_id');
delete_option('wpb_google_client_secret');
delete_option('wpb_facebook_app_id');
delete_option('wpb_facebook_app_secret');

// Delete page designs
global $wpdb;
$wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE 'wpb_page_design_%'");

// Delete custom post types (templates)
$templates = get_posts(array(
    'post_type' => 'wpb_template',
    'posts_per_page' => -1,
    'post_status' => 'any',
));

foreach ($templates as $template) {
    wp_delete_post($template->ID, true);
}

// Delete user meta related to social login
global $wpdb;
$wpdb->query("DELETE FROM $wpdb->usermeta WHERE meta_key LIKE 'wpb\_%'");

// Delete transients
global $wpdb;
$wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE '%wpb%' OR option_name LIKE '%transient_wpb%'");

<?php
if (!defined('ABSPATH')) exit;

class DEK_Admin {

    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->init_hooks();
    }

    private function init_hooks() {
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_menu', [$this, 'add_settings_menu']);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('admin_post_dek_save_watermark_settings', [$this, 'handle_save_watermark_settings']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
        add_action('admin_post_dek_create_template', [$this, 'handle_create_template']);
        add_action('admin_post_dek_toggle_active', [$this, 'handle_toggle_active']);
        add_action('trashed_post', [$this, 'redirect_after_trash']);
        add_action('manage_dek_template_posts_columns', [$this, 'set_custom_columns']);
        add_action('manage_dek_template_posts_custom_column', [$this, 'render_custom_columns'], 10, 2);
        add_action('post_row_actions', [$this, 'modify_row_actions'], 10, 2);
        add_action('admin_footer', [$this, 'render_modal']);
    }

    public function add_admin_menu() {
        add_menu_page(
            __('Dynamic ElementKit', 'dynamic-elementkit'),
            __('Dynamic ElementKit', 'dynamic-elementkit'),
            'manage_options',
            'dynamic-elementkit',
            [$this, 'render_dashboard'],
            'dashicons-welcome-widgets-menus',
            56
        );

        add_submenu_page(
            'dynamic-elementkit',
            __('Dashboard', 'dynamic-elementkit'),
            __('Dashboard', 'dynamic-elementkit'),
            'manage_options',
            'dynamic-elementkit',
            [$this, 'render_dashboard']
        );

        add_submenu_page(
            'dynamic-elementkit',
            __('Templates', 'dynamic-elementkit'),
            __('Templates', 'dynamic-elementkit'),
            'manage_options',
            'dek-templates',
            [$this, 'render_page_builder']
        );
    }

    public function add_settings_menu() {
        add_submenu_page(
            'dynamic-elementkit',
            __('Settings', 'dynamic-elementkit'),
            __('Settings', 'dynamic-elementkit'),
            'manage_options',
            'dek-settings',
            [$this, 'render_settings_page']
        );
    }

    public function register_settings() {
        register_setting('dek_general_settings', 'dek_enable_landing_page', [
            'type'              => 'boolean',
            'default'           => 1,
            'sanitize_callback' => 'absint',
        ]);
        register_setting('dek_watermark_settings', 'dek_watermark_enabled', 'absint');
        register_setting('dek_watermark_settings', 'dek_watermark_logo_source', 'sanitize_text_field');
        register_setting('dek_watermark_settings', 'dek_watermark_logo', 'esc_url_raw');
        register_setting('dek_watermark_settings', 'dek_watermark_opacity', 'floatval');
    }

    public function render_dashboard() {
        if (!get_option('dek_enable_landing_page', 1)) {
            $this->render_page_builder();
            return;
        }

        $template_url = admin_url('admin.php?page=dek-templates');
        $settings_url = admin_url('admin.php?page=dek-settings');
        $shop_url = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/');
        ?>
        <div class="wrap dek-dashboard">
            <div class="dek-dashboard-hero">
                <div>
                    <span class="dek-dashboard-eyebrow">Dynamic ElementKit</span>
                    <h1><?php esc_html_e('Design every part of your website.', 'dynamic-elementkit'); ?></h1>
                    <p><?php esc_html_e('Create Elementor pages, headers, footers, landing pages, and WooCommerce experiences from one organized workspace.', 'dynamic-elementkit'); ?></p>
                </div>
                <span class="dek-dashboard-mark" aria-hidden="true">DEK</span>
            </div>

            <div class="dek-dashboard-grid">
                <a class="dek-dashboard-card" href="<?php echo esc_url($template_url); ?>">
                    <span class="dashicons dashicons-layout" aria-hidden="true"></span>
                    <strong><?php esc_html_e('Manage Templates', 'dynamic-elementkit'); ?></strong>
                    <span><?php esc_html_e('Create pages, headers, footers, landing pages, and WooCommerce layouts.', 'dynamic-elementkit'); ?></span>
                </a>
                <a class="dek-dashboard-card" href="<?php echo esc_url($settings_url); ?>">
                    <span class="dashicons dashicons-admin-generic" aria-hidden="true"></span>
                    <strong><?php esc_html_e('Plugin Settings', 'dynamic-elementkit'); ?></strong>
                    <span><?php esc_html_e('Configure branding, security, and dashboard options.', 'dynamic-elementkit'); ?></span>
                </a>
                <a class="dek-dashboard-card" href="<?php echo esc_url($shop_url); ?>" target="_blank" rel="noopener">
                    <span class="dashicons dashicons-external" aria-hidden="true"></span>
                    <strong><?php esc_html_e('View Storefront', 'dynamic-elementkit'); ?></strong>
                    <span><?php esc_html_e('Preview your public website in a new tab.', 'dynamic-elementkit'); ?></span>
                </a>
            </div>

            <div class="dek-dashboard-panel">
                <div>
                    <h2><?php esc_html_e('Enable landing page', 'dynamic-elementkit'); ?></h2>
                    <p><?php esc_html_e('Show this dashboard whenever you open Dynamic ElementKit from the WordPress menu.', 'dynamic-elementkit'); ?></p>
                </div>
                <form method="post" action="<?php echo esc_url(admin_url('options.php')); ?>">
                    <?php settings_fields('dek_general_settings'); ?>
                    <label class="dek-toggle">
                        <input type="hidden" name="dek_enable_landing_page" value="0">
                        <input type="checkbox" name="dek_enable_landing_page" value="1" <?php checked((bool) get_option('dek_enable_landing_page', 1)); ?> />
                        <span class="dek-toggle-track" aria-hidden="true"></span>
                        <span class="screen-reader-text"><?php esc_html_e('Enable landing page', 'dynamic-elementkit'); ?></span>
                    </label>
                    <?php submit_button(__('Save', 'dynamic-elementkit'), 'secondary', 'submit', false); ?>
                </form>
            </div>
        </div>
        <?php
    }

    public function render_settings_page() {
        $enabled = (bool) get_option('dek_watermark_enabled', 0);
        $source  = get_option('dek_watermark_logo_source', 'custom');
        $logo    = get_option('dek_watermark_logo', '');
        $opacity = (float) get_option('dek_watermark_opacity', 0.3);
        $site_logo_url = '';
        if (function_exists('get_custom_logo') && has_custom_logo()) {
            $site_logo_url = wp_get_attachment_image_url(get_theme_mod('custom_logo'), 'full');
        }
        if (!$site_logo_url) {
            $site_logo_url = get_site_icon_url();
        }
        ?>
        <div class="wrap wpb-wrap">
            <h1><?php _e('Dynamic ElementKit Settings', 'dynamic-elementkit'); ?></h1>

            <?php if (isset($_GET['message']) && $_GET['message'] === 'saved'): ?>
                <div class="notice notice-success is-dismissible"><p><?php _e('Settings saved.', 'dynamic-elementkit'); ?></p></div>
            <?php endif; ?>

            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" enctype="multipart/form-data">
                <input type="hidden" name="action" value="dek_save_watermark_settings" />
                <?php wp_nonce_field('dek_save_watermark_settings', 'dek_watermark_nonce'); ?>

                <h2 class="title"><?php _e('Product Watermark', 'dynamic-elementkit'); ?></h2>
                <p class="description"><?php _e('Add a watermark logo over your product images, like a low-opacity overlay.', 'dynamic-elementkit'); ?></p>

                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Enable Watermark', 'dynamic-elementkit'); ?></th>
                        <td>
                            <label class="wpb-switch">
                                <input type="checkbox" name="dek_watermark_enabled" value="1" <?php checked($enabled, 1); ?> />
                                <span class="wpb-slider round"></span>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="wpb-watermark-logo-source"><?php _e('Logo Source', 'dynamic-elementkit'); ?></label></th>
                        <td>
                            <select id="wpb-watermark-logo-source" name="dek_watermark_logo_source">
                                <option value="custom" <?php selected($source, 'custom'); ?>><?php _e('Custom Upload', 'dynamic-elementkit'); ?></option>
                                <option value="site" <?php selected($source, 'site'); ?>><?php _e('Use Site Default Logo', 'dynamic-elementkit'); ?></option>
                            </select>
                            <p class="description"><?php _e('Choose a custom uploaded logo or use your website\'s default logo.', 'dynamic-elementkit'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="wpb-watermark-logo"><?php _e('Watermark Logo', 'dynamic-elementkit'); ?></label></th>
                        <td>
                            <div class="wpb-watermark-logo-field">
                                <input type="hidden" name="dek_watermark_logo" value="<?php echo esc_attr($logo); ?>" />
                                <div class="wpb-watermark-preview" style="margin-bottom:10px;">
                                    <?php if ($source === 'site' && $site_logo_url): ?>
                                        <img src="<?php echo esc_url($site_logo_url); ?>" alt="site logo" style="max-width:160px;max-height:160px;border:1px solid #ddd;padding:5px;" />
                                    <?php elseif ($logo): ?>
                                        <img src="<?php echo esc_url($logo); ?>" alt="watermark" style="max-width:160px;max-height:160px;border:1px solid #ddd;padding:5px;" />
                                    <?php endif; ?>
                                </div>
                                <input type="file" id="wpb-watermark-logo" name="dek_watermark_logo_file" accept="image/*" />
                                <?php if ($logo): ?>
                                    <label style="margin-left:10px;"><input type="checkbox" name="dek_watermark_logo_remove" value="1" /> <?php _e('Remove current logo', 'dynamic-elementkit'); ?></label>
                                <?php endif; ?>
                                <p class="description"><?php _e('Upload a PNG/JPG logo. It will be used as a low-opacity overlay on product images.', 'dynamic-elementkit'); ?></p>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="wpb-watermark-opacity"><?php _e('Opacity', 'dynamic-elementkit'); ?></label></th>
                        <td>
                            <input type="range" id="wpb-watermark-opacity" name="dek_watermark_opacity" min="0.05" max="1" step="0.05" value="<?php echo esc_attr($opacity); ?>" />
                            <span class="wpb-watermark-opacity-value"><?php echo esc_html($opacity); ?></span>
                            <p class="description"><?php _e('Lower values make the watermark more transparent (like real water).', 'dynamic-elementkit'); ?></p>
                        </td>
                    </tr>
                </table>

                <?php submit_button(__('Save Settings', 'dynamic-elementkit')); ?>
            </form>
        </div>
        <?php
    }

    public function handle_save_watermark_settings() {
        if (!isset($_POST['dek_watermark_nonce']) || !wp_verify_nonce($_POST['dek_watermark_nonce'], 'dek_save_watermark_settings')) {
            wp_die(__('Security check failed', 'dynamic-elementkit'));
        }

        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have permission to do this.', 'dynamic-elementkit'));
        }

        $enabled = isset($_POST['dek_watermark_enabled']) ? 1 : 0;
        $source  = isset($_POST['dek_watermark_logo_source']) ? sanitize_text_field($_POST['dek_watermark_logo_source']) : 'custom';
        $source  = in_array($source, ['custom', 'site'], true) ? $source : 'custom';
        $opacity = isset($_POST['dek_watermark_opacity']) ? (float) $_POST['dek_watermark_opacity'] : 0.3;
        $opacity = max(0.05, min(1, $opacity));

        $logo = isset($_POST['dek_watermark_logo']) ? esc_url_raw($_POST['dek_watermark_logo']) : '';

        if (!empty($_POST['dek_watermark_logo_remove'])) {
            $logo = '';
        }

        if (!empty($_FILES['dek_watermark_logo_file']) && !empty($_FILES['dek_watermark_logo_file']['tmp_name'])) {
            $upload = $_FILES['dek_watermark_logo_file'];
            $allowed_mimes = [
                'jpg|jpeg|jpe' => 'image/jpeg',
                'png'         => 'image/png',
                'webp'        => 'image/webp',
            ];

            if (
                !isset($upload['error'], $upload['size'], $upload['tmp_name']) ||
                UPLOAD_ERR_OK !== (int) $upload['error'] ||
                (int) $upload['size'] > 2 * 1024 * 1024 ||
                !is_uploaded_file($upload['tmp_name'])
            ) {
                wp_die(__('Please upload a valid image smaller than 2 MB.', 'dynamic-elementkit'));
            }

            if (!function_exists('wp_handle_upload')) {
                require_once ABSPATH . 'wp-admin/includes/file.php';
            }
            $uploaded = wp_handle_upload($upload, [
                // This request is handled by admin-post.php, so WordPress's
                // default form-action check would reject the valid nonce.
                'test_form' => false,
                'test_type' => true,
                'mimes'     => $allowed_mimes,
            ]);
            if (!isset($uploaded['error']) && !empty($uploaded['url'])) {
                $logo = esc_url_raw($uploaded['url']);
            } else {
                wp_die(__('The watermark image could not be uploaded.', 'dynamic-elementkit'));
            }
        }

        update_option('dek_watermark_enabled', $enabled);
        update_option('dek_watermark_logo_source', $source);
        update_option('dek_watermark_logo', $logo);
        update_option('dek_watermark_opacity', $opacity);

        wp_redirect(add_query_arg([
            'page'    => 'dek-settings',
            'message' => 'saved',
        ], admin_url('admin.php')));
        exit;
    }

    public function enqueue_admin_assets($hook) {
        if (strpos($hook, 'dynamic-elementkit') === false && strpos($hook, 'dek-templates') === false) {
            return;
        }

        wp_enqueue_style('wpb-admin', DEK_PLUGIN_URL . 'assets/css/admin.css', [], DEK_VERSION);
        wp_enqueue_script('wpb-admin', DEK_PLUGIN_URL . 'assets/js/admin.js', ['jquery'], DEK_VERSION, true);

        wp_localize_script('wpb-admin', 'dekAdmin', [
            'toggleNonce' => wp_create_nonce('dek_toggle_active'),
            'ajaxUrl'     => admin_url('admin-ajax.php'),
            'formAction'  => admin_url('admin.php?page=dek-templates'),
            'strings'     => [
                'confirmDelete'  => __('Are you sure you want to delete this template?', 'dynamic-elementkit'),
                'confirmBulk'    => __('Are you sure you want to move the selected templates to trash?', 'dynamic-elementkit'),
                'nameRequired'   => __('Please enter a template name.', 'dynamic-elementkit'),
                'typeRequired'   => __('Please select a template type.', 'dynamic-elementkit'),
            ],
        ]);
    }

    public function render_page_builder() {
        $action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
        $post_id = isset($_REQUEST['post']) ? (int) $_REQUEST['post'] : 0;

        if ($action === 'delete' && $post_id > 0) {
            $this->handle_delete_permanent();
            return;
        }

        if ($action === 'restore' && $post_id > 0) {
            $this->handle_restore();
            return;
        }

        if ($action === 'trash' && !empty($_REQUEST['dek_template_ids'])) {
            $this->handle_bulk_action();
            return;
        }

        $list_table = new DEK_Template_Table();
        $list_table->prepare_items();
        ?>
        <div class="wrap wpb-wrap">
            <h1 class="wp-heading-inline"><?php _e('Dynamic ElementKit Templates', 'dynamic-elementkit'); ?></h1>
            <a href="#" class="page-title-action" id="wpb-add-new-template-top"><?php _e('Add New Template', 'dynamic-elementkit'); ?></a>
            <hr class="wp-header-end">

            <?php
            if (isset($_GET['message']) && $_GET['message'] === 'created') {
                echo '<div class="notice notice-success is-dismissible"><p>' . __('Template created successfully.', 'dynamic-elementkit') . '</p></div>';
            }
            if (isset($_GET['message']) && $_GET['message'] === 'toggled') {
                echo '<div class="notice notice-success is-dismissible"><p>' . __('Template status updated.', 'dynamic-elementkit') . '</p></div>';
            }
            if (isset($_GET['message']) && $_GET['message'] === 'trashed') {
                echo '<div class="notice notice-success is-dismissible"><p>' . __('Template moved to trash.', 'dynamic-elementkit') . '</p></div>';
            }
            if (isset($_GET['message']) && $_GET['message'] === 'restored') {
                echo '<div class="notice notice-success is-dismissible"><p>' . __('Template restored.', 'dynamic-elementkit') . '</p></div>';
            }
            if (isset($_GET['message']) && $_GET['message'] === 'deleted') {
                echo '<div class="notice notice-success is-dismissible"><p>' . __('Template deleted permanently.', 'dynamic-elementkit') . '</p></div>';
            }
            ?>

            <form id="wpb-templates-filter" method="get" style="margin: 10px 0;">
                <input type="hidden" name="page" value="dek-templates" />
                <?php if (isset($_REQUEST['post_status'])): ?>
                    <input type="hidden" name="post_status" value="<?php echo esc_attr($_REQUEST['post_status']); ?>" />
                <?php endif; ?>
                <?php $list_table->search_box(__('Search Templates', 'dynamic-elementkit'), 'dek_template_search'); ?>
            </form>

            <?php $list_table->display(); ?>
        </div>
        <?php
    }

    public function handle_create_template() {
        if (!isset($_POST['dek_create_nonce']) || !wp_verify_nonce($_POST['dek_create_nonce'], 'dek_create_template')) {
            wp_die(__('Security check failed', 'dynamic-elementkit'));
        }

        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have permission to do this.', 'dynamic-elementkit'));
        }

        $name = isset($_POST['dek_template_name']) ? sanitize_text_field($_POST['dek_template_name']) : '';
        $slug = isset($_POST['dek_template_slug']) ? sanitize_title(wp_unslash($_POST['dek_template_slug'])) : '';
        $type = isset($_POST['dek_template_type']) ? sanitize_text_field($_POST['dek_template_type']) : '';
        $landing_product_id = isset($_POST['dek_landing_product_id']) ? absint($_POST['dek_landing_product_id']) : 0;
        $allowed_types = ['general', 'header', 'footer', 'landing', 'shop', 'cart', 'checkout', 'thankyou', 'myaccount', 'product', 'product-category', 'product-tag', 'archive'];

        if (empty($name)) {
            wp_redirect(add_query_arg(['page' => 'dek-templates', 'error' => 'name_required'], admin_url('admin.php')));
            exit;
        }

        if (empty($type) || !in_array($type, $allowed_types, true)) {
            wp_redirect(add_query_arg(['page' => 'dek-templates', 'error' => 'type_required'], admin_url('admin.php')));
            exit;
        }

        if ('' === $slug) {
            $slug = sanitize_title($name);
        }

        if ('' === $slug) {
            $slug = 'dek-template-' . wp_generate_password(6, false, false);
        }

        $post_id = wp_insert_post([
            'post_title'  => $name,
            'post_name'   => $slug,
            'post_type'   => 'dek_template',
            'post_status' => 'draft',
            'post_author' => get_current_user_id(),
        ]);

        if (is_wp_error($post_id)) {
            wp_redirect(add_query_arg(['page' => 'dek-templates', 'error' => 'create_failed'], admin_url('admin.php')));
            exit;
        }

        update_post_meta($post_id, '_dek_template_type', $type);
        update_post_meta($post_id, '_dek_template_active', '0');
        if ($landing_product_id && function_exists('wc_get_product')) {
            $landing_product = wc_get_product($landing_product_id);
            if ($landing_product instanceof \WC_Product && $landing_product->exists()) {
                update_post_meta($post_id, '_dek_landing_product_id', $landing_product->get_id());
            }
        }

        wp_redirect(admin_url('post.php?post=' . absint($post_id) . '&action=elementor'));
        exit;
    }

    public function handle_toggle_active() {
        if (!isset($_GET['_wpnonce']) || !wp_verify_nonce($_GET['_wpnonce'], 'dek_toggle_active')) {
            wp_die(__('Security check failed', 'dynamic-elementkit'));
        }

        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have permission to do this.', 'dynamic-elementkit'));
        }

        $post_id = isset($_GET['post_id']) ? (int) $_GET['post_id'] : 0;
        $post = get_post($post_id);

        if (!$post || $post->post_type !== 'dek_template') {
            wp_redirect(add_query_arg(['page' => 'dek-templates', 'error' => 'invalid_post'], admin_url('admin.php')));
            exit;
        }

        $current_active = get_post_meta($post_id, '_dek_template_active', true);
        $new_active = $current_active ? '0' : '1';

        $template_type = get_post_meta($post_id, '_dek_template_type', true);

        if ($new_active === '1') {
            $args = [
                'post_type'   => 'dek_template',
                'post_status' => ['publish', 'draft'],
                'meta_key'    => '_dek_template_type',
                'meta_value'  => $template_type,
                'fields'      => 'ids',
                'nopaging'    => true,
            ];
            $same_type = get_posts($args);
            foreach ($same_type as $id) {
                if ((int) $id !== (int) $post_id) {
                    update_post_meta($id, '_dek_template_active', '0');
                }
            }
        }

        update_post_meta($post_id, '_dek_template_active', $new_active);

        wp_redirect(add_query_arg(['page' => 'dek-templates', 'message' => 'toggled'], admin_url('admin.php')));
        exit;
    }

    public function handle_bulk_action() {
        $nonce = isset($_REQUEST['_wpnonce']) ? $_REQUEST['_wpnonce'] : '';
        if (!wp_verify_nonce($nonce, 'dek_templates-bulk')) {
            wp_redirect(add_query_arg(['page' => 'dek-templates', 'error' => 'invalid_nonce'], admin_url('admin.php')));
            exit;
        }

        if (!current_user_can('manage_options')) {
            wp_redirect(add_query_arg(['page' => 'dek-templates', 'error' => 'permission'], admin_url('admin.php')));
            exit;
        }

        $action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
        if ($action === '' && isset($_REQUEST['action2'])) {
            $action = $_REQUEST['action2'];
        }

        $ids = isset($_REQUEST['dek_template_ids']) ? array_map('intval', (array) $_REQUEST['dek_template_ids']) : [];

        if (empty($ids)) {
            wp_redirect(add_query_arg(['page' => 'dek-templates'], admin_url('admin.php')));
            exit;
        }

        if ($action === 'trash') {
            foreach ($ids as $id) {
                $post = get_post($id);
                if ($post && $post->post_type === 'dek_template') {
                    wp_trash_post($id);
                }
            }
            wp_redirect(add_query_arg(['page' => 'dek-templates', 'message' => 'trashed'], admin_url('admin.php')));
            exit;
        }

        wp_redirect(add_query_arg(['page' => 'dek-templates'], admin_url('admin.php')));
        exit;
    }

    public function set_custom_columns($columns) {
        $columns['dek_template_type'] = __('Type', 'dynamic-elementkit');
        $columns['dek_template_active'] = __('Active', 'dynamic-elementkit');
        return $columns;
    }

    public function redirect_after_trash($post_id) {
        $post = get_post($post_id);
        if (!$post || $post->post_type !== 'dek_template') {
            return;
        }

        wp_redirect(add_query_arg(['page' => 'dek-templates', 'message' => 'trashed'], admin_url('admin.php')));
        exit;
    }

    public function handle_delete_permanent() {
        $post_id = isset($_GET['post']) ? (int) $_GET['post'] : 0;

        if (!$post_id || !isset($_GET['_wpnonce'])) {
            wp_redirect(add_query_arg(['page' => 'dek-templates', 'error' => 'invalid_request'], admin_url('admin.php')));
            exit;
        }

        if (!wp_verify_nonce($_GET['_wpnonce'], 'dek_delete_template_' . $post_id)) {
            wp_die(__('Security check failed', 'dynamic-elementkit'));
        }

        $post = get_post($post_id);
        if (!$post || $post->post_type !== 'dek_template') {
            wp_redirect(add_query_arg(['page' => 'dek-templates', 'error' => 'invalid_post'], admin_url('admin.php')));
            exit;
        }

        wp_delete_post($post_id, true);

        wp_redirect(add_query_arg(['page' => 'dek-templates', 'message' => 'deleted'], admin_url('admin.php')));
        exit;
    }

    public function handle_restore() {
        $post_id = isset($_GET['post']) ? (int) $_GET['post'] : 0;

        if (!$post_id || !isset($_GET['_wpnonce'])) {
            wp_redirect(add_query_arg(['page' => 'dek-templates', 'error' => 'invalid_request'], admin_url('admin.php')));
            exit;
        }

        if (!wp_verify_nonce($_GET['_wpnonce'], 'dek_restore_template_' . $post_id)) {
            wp_die(__('Security check failed', 'dynamic-elementkit'));
        }

        $post = get_post($post_id);
        if (!$post || $post->post_type !== 'dek_template') {
            wp_redirect(add_query_arg(['page' => 'dek-templates', 'error' => 'invalid_post'], admin_url('admin.php')));
            exit;
        }

        wp_untrash_post($post_id);

        wp_redirect(add_query_arg(['page' => 'dek-templates', 'message' => 'restored'], admin_url('admin.php')));
        exit;
    }

    public function render_custom_columns($column, $post_id) {
        switch ($column) {
            case 'dek_template_type':
                $type = get_post_meta($post_id, '_dek_template_type', true);
                $types = [
                    'shop'             => __('Shop', 'dynamic-elementkit'),
                    'general'          => __('General Page', 'dynamic-elementkit'),
                    'header'           => __('Site Header', 'dynamic-elementkit'),
                    'footer'           => __('Site Footer', 'dynamic-elementkit'),
                    'landing'          => __('Landing Page', 'dynamic-elementkit'),
                    'cart'             => __('Cart', 'dynamic-elementkit'),
                    'checkout'         => __('Checkout', 'dynamic-elementkit'),
                    'thankyou'         => __('Thank You', 'dynamic-elementkit'),
                    'myaccount'        => __('My Account', 'dynamic-elementkit'),
                    'product'          => __('Single Product', 'dynamic-elementkit'),
                    'product-category' => __('Product Category', 'dynamic-elementkit'),
                    'product-tag'      => __('Product Tag', 'dynamic-elementkit'),
                    'archive'          => __('Archive (Shop/Category/Tag)', 'dynamic-elementkit'),
                ];
                echo isset($types[$type]) ? $types[$type] : esc_html($type);
                break;
            case 'dek_template_active':
                $is_active = get_post_meta($post_id, '_dek_template_active', true);
                $checked = $is_active ? 'checked' : '';
                echo '<label class="wpb-switch">';
                echo '<input type="checkbox" disabled ' . $checked . '>';
                echo '<span class="wpb-slider round"></span>';
                echo '</label>';
                break;
        }
    }

    public function modify_row_actions($actions, $post) {
        if ($post->post_type !== 'dek_template') {
            return $actions;
        }

        if (isset($actions['inline hide-if-no-js'])) {
            unset($actions['inline hide-if-no-js']);
        }

        $edit_url = add_query_arg([
            'post'   => $post->ID,
            'action' => 'elementor',
        ], admin_url('post.php'));

        $actions['edit_elementor'] = sprintf(
            '<a href="%s" aria-label="%s">%s</a>',
            esc_url($edit_url),
            esc_attr(sprintf(__('Edit with Elementor', 'dynamic-elementkit'))),
            __('Edit with Elementor', 'dynamic-elementkit')
        );

        return $actions;
    }

    public function render_modal() {
        $screen = get_current_screen();
        if (
            !$screen ||
            !in_array($screen->id, ['toplevel_page_dynamic-elementkit', 'dynamic-elementkit_page_dek-templates'], true)
        ) {
            return;
        }
        ?>
        <div id="wpb-modal-overlay" class="wpb-modal-overlay" style="display:none;">
            <div class="wpb-modal">
                <div class="wpb-modal-header">
                    <h2><?php _e('Add New Template', 'dynamic-elementkit'); ?></h2>
                    <button type="button" class="wpb-modal-close">&times;</button>
                </div>
                <div class="wpb-modal-body">
                    <form id="wpb-create-template-form" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                        <input type="hidden" name="action" value="dek_create_template" />
                        <input type="hidden" name="dek_create_nonce" value="<?php echo esc_attr(wp_create_nonce('dek_create_template')); ?>" />

                        <table class="form-table">
                            <tr>
                                <th scope="row"><label for="dek_template_name"><?php _e('Name', 'dynamic-elementkit'); ?></label></th>
                                <td><input type="text" id="dek_template_name" name="dek_template_name" class="regular-text" required /></td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="dek_template_slug"><?php _e('Slug', 'dynamic-elementkit'); ?></label></th>
                                <td>
                                    <input type="text" id="dek_template_slug" name="dek_template_slug" class="regular-text" />
                                    <p class="description"><?php _e('Optional URL-friendly slug. Leave blank to generate it from the name.', 'dynamic-elementkit'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="dek_template_type"><?php _e('Template location', 'dynamic-elementkit'); ?></label></th>
                                <td>
                <select id="dek_template_type" name="dek_template_type" required>
                    <option value=""><?php _e('Select Type', 'dynamic-elementkit'); ?></option>
                    <option value="general"><?php _e('General Page', 'dynamic-elementkit'); ?></option>
                    <option value="header"><?php _e('Site Header', 'dynamic-elementkit'); ?></option>
                    <option value="footer"><?php _e('Site Footer', 'dynamic-elementkit'); ?></option>
                    <option value="landing"><?php _e('Landing Page', 'dynamic-elementkit'); ?></option>
                    <option value="shop"><?php _e('Shop', 'dynamic-elementkit'); ?></option>
                    <option value="cart"><?php _e('Cart', 'dynamic-elementkit'); ?></option>
                    <option value="checkout"><?php _e('Checkout', 'dynamic-elementkit'); ?></option>
                    <option value="thankyou"><?php _e('Thank You', 'dynamic-elementkit'); ?></option>
                    <option value="myaccount"><?php _e('My Account', 'dynamic-elementkit'); ?></option>
                    <option value="product"><?php _e('Single Product', 'dynamic-elementkit'); ?></option>
                    <option value="product-category"><?php _e('Product Category', 'dynamic-elementkit'); ?></option>
                    <option value="product-tag"><?php _e('Product Tag', 'dynamic-elementkit'); ?></option>
                    <option value="archive"><?php _e('Archive (Shop/Category/Tag)', 'dynamic-elementkit'); ?></option>
                </select>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="dek_landing_product_id"><?php _e('Landing product', 'dynamic-elementkit'); ?></label></th>
                                <td>
                                    <select id="dek_landing_product_id" name="dek_landing_product_id" class="regular-text">
                                        <option value="0"><?php _e('No assigned product', 'dynamic-elementkit'); ?></option>
                                        <?php
                                        if (function_exists('wc_get_products')) {
                                            $landing_products = wc_get_products([
                                                'limit' => 200,
                                                'status' => 'publish',
                                                'orderby' => 'title',
                                                'order' => 'ASC',
                                            ]);
                                            foreach ($landing_products as $landing_product) {
                                                printf(
                                                    '<option value="%1$d">%2$s (#%1$d)</option>',
                                                    absint($landing_product->get_id()),
                                                    esc_html($landing_product->get_name())
                                                );
                                            }
                                        }
                                        ?>
                                    </select>
                                    <p class="description"><?php _e('Assign a product to this landing page. The Single Product widgets, Checkout Form set to Current Product, and Product dynamic tags will use it automatically.', 'dynamic-elementkit'); ?></p>
                                </td>
                            </tr>
                        </table>
                        <div class="wpb-modal-footer">
                            <button type="submit" class="button button-primary"><?php _e('Save', 'dynamic-elementkit'); ?></button>
                            <button type="button" class="button wpb-modal-close-btn"><?php _e('Cancel', 'dynamic-elementkit'); ?></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <?php
    }
}

DEK_Admin::get_instance();

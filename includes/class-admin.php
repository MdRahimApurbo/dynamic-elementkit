<?php
if (!defined('ABSPATH')) exit;

class WPB_Admin {

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
        add_action('admin_post_wpb_save_watermark_settings', [$this, 'handle_save_watermark_settings']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
        add_action('admin_post_wpb_create_template', [$this, 'handle_create_template']);
        add_action('admin_post_wpb_toggle_active', [$this, 'handle_toggle_active']);
        add_action('trashed_post', [$this, 'redirect_after_trash']);
        add_action('manage_wpb_template_posts_columns', [$this, 'set_custom_columns']);
        add_action('manage_wpb_template_posts_custom_column', [$this, 'render_custom_columns'], 10, 2);
        add_action('post_row_actions', [$this, 'modify_row_actions'], 10, 2);
        add_action('admin_footer', [$this, 'render_modal']);
    }

    public function add_admin_menu() {
        add_menu_page(
            __('Woo Page Builder', 'woocommerce-page-builder'),
            __('Woo Page Builder', 'woocommerce-page-builder'),
            'manage_options',
            'wpb-page-builder',
            [$this, 'render_page_builder'],
            'dashicons-welcome-widgets-menus',
            56
        );
    }

    public function add_settings_menu() {
        add_submenu_page(
            'wpb-page-builder',
            __('Settings', 'woocommerce-page-builder'),
            __('Settings', 'woocommerce-page-builder'),
            'manage_options',
            'wpb-settings',
            [$this, 'render_settings_page']
        );
    }

    public function register_settings() {
        register_setting('wpb_watermark_settings', 'wpb_watermark_enabled', 'absint');
        register_setting('wpb_watermark_settings', 'wpb_watermark_logo_source', 'sanitize_text_field');
        register_setting('wpb_watermark_settings', 'wpb_watermark_logo', 'esc_url_raw');
        register_setting('wpb_watermark_settings', 'wpb_watermark_opacity', 'floatval');
    }

    public function render_settings_page() {
        $enabled = (bool) get_option('wpb_watermark_enabled', 0);
        $source  = get_option('wpb_watermark_logo_source', 'custom');
        $logo    = get_option('wpb_watermark_logo', '');
        $opacity = (float) get_option('wpb_watermark_opacity', 0.3);
        $site_logo_url = '';
        if (function_exists('get_custom_logo') && has_custom_logo()) {
            $site_logo_url = wp_get_attachment_image_url(get_theme_mod('custom_logo'), 'full');
        }
        if (!$site_logo_url) {
            $site_logo_url = get_site_icon_url();
        }
        ?>
        <div class="wrap wpb-wrap">
            <h1><?php _e('Woo Page Builder Settings', 'woocommerce-page-builder'); ?></h1>

            <?php if (isset($_GET['message']) && $_GET['message'] === 'saved'): ?>
                <div class="notice notice-success is-dismissible"><p><?php _e('Settings saved.', 'woocommerce-page-builder'); ?></p></div>
            <?php endif; ?>

            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" enctype="multipart/form-data">
                <input type="hidden" name="action" value="wpb_save_watermark_settings" />
                <?php wp_nonce_field('wpb_save_watermark_settings', 'wpb_watermark_nonce'); ?>

                <h2 class="title"><?php _e('Product Watermark', 'woocommerce-page-builder'); ?></h2>
                <p class="description"><?php _e('Add a watermark logo over your product images, like a low-opacity overlay.', 'woocommerce-page-builder'); ?></p>

                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Enable Watermark', 'woocommerce-page-builder'); ?></th>
                        <td>
                            <label class="wpb-switch">
                                <input type="checkbox" name="wpb_watermark_enabled" value="1" <?php checked($enabled, 1); ?> />
                                <span class="wpb-slider round"></span>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="wpb-watermark-logo-source"><?php _e('Logo Source', 'woocommerce-page-builder'); ?></label></th>
                        <td>
                            <select id="wpb-watermark-logo-source" name="wpb_watermark_logo_source">
                                <option value="custom" <?php selected($source, 'custom'); ?>><?php _e('Custom Upload', 'woocommerce-page-builder'); ?></option>
                                <option value="site" <?php selected($source, 'site'); ?>><?php _e('Use Site Default Logo', 'woocommerce-page-builder'); ?></option>
                            </select>
                            <p class="description"><?php _e('Choose a custom uploaded logo or use your website\'s default logo.', 'woocommerce-page-builder'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="wpb-watermark-logo"><?php _e('Watermark Logo', 'woocommerce-page-builder'); ?></label></th>
                        <td>
                            <div class="wpb-watermark-logo-field">
                                <input type="hidden" name="wpb_watermark_logo" value="<?php echo esc_attr($logo); ?>" />
                                <div class="wpb-watermark-preview" style="margin-bottom:10px;">
                                    <?php if ($source === 'site' && $site_logo_url): ?>
                                        <img src="<?php echo esc_url($site_logo_url); ?>" alt="site logo" style="max-width:160px;max-height:160px;border:1px solid #ddd;padding:5px;" />
                                    <?php elseif ($logo): ?>
                                        <img src="<?php echo esc_url($logo); ?>" alt="watermark" style="max-width:160px;max-height:160px;border:1px solid #ddd;padding:5px;" />
                                    <?php endif; ?>
                                </div>
                                <input type="file" id="wpb-watermark-logo" name="wpb_watermark_logo_file" accept="image/*" />
                                <?php if ($logo): ?>
                                    <label style="margin-left:10px;"><input type="checkbox" name="wpb_watermark_logo_remove" value="1" /> <?php _e('Remove current logo', 'woocommerce-page-builder'); ?></label>
                                <?php endif; ?>
                                <p class="description"><?php _e('Upload a PNG/JPG logo. It will be used as a low-opacity overlay on product images.', 'woocommerce-page-builder'); ?></p>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="wpb-watermark-opacity"><?php _e('Opacity', 'woocommerce-page-builder'); ?></label></th>
                        <td>
                            <input type="range" id="wpb-watermark-opacity" name="wpb_watermark_opacity" min="0.05" max="1" step="0.05" value="<?php echo esc_attr($opacity); ?>" />
                            <span class="wpb-watermark-opacity-value"><?php echo esc_html($opacity); ?></span>
                            <p class="description"><?php _e('Lower values make the watermark more transparent (like real water).', 'woocommerce-page-builder'); ?></p>
                        </td>
                    </tr>
                </table>

                <?php submit_button(__('Save Settings', 'woocommerce-page-builder')); ?>
            </form>
        </div>
        <?php
    }

    public function handle_save_watermark_settings() {
        if (!isset($_POST['wpb_watermark_nonce']) || !wp_verify_nonce($_POST['wpb_watermark_nonce'], 'wpb_save_watermark_settings')) {
            wp_die(__('Security check failed', 'woocommerce-page-builder'));
        }

        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have permission to do this.', 'woocommerce-page-builder'));
        }

        $enabled = isset($_POST['wpb_watermark_enabled']) ? 1 : 0;
        $source  = isset($_POST['wpb_watermark_logo_source']) ? sanitize_text_field($_POST['wpb_watermark_logo_source']) : 'custom';
        $source  = in_array($source, ['custom', 'site'], true) ? $source : 'custom';
        $opacity = isset($_POST['wpb_watermark_opacity']) ? (float) $_POST['wpb_watermark_opacity'] : 0.3;
        $opacity = max(0.05, min(1, $opacity));

        $logo = isset($_POST['wpb_watermark_logo']) ? esc_url_raw($_POST['wpb_watermark_logo']) : '';

        if (!empty($_POST['wpb_watermark_logo_remove'])) {
            $logo = '';
        }

        if (!empty($_FILES['wpb_watermark_logo_file']) && !empty($_FILES['wpb_watermark_logo_file']['tmp_name'])) {
            if (!function_exists('wp_handle_upload')) {
                require_once ABSPATH . 'wp-admin/includes/file.php';
            }
            $uploaded = wp_handle_upload($_FILES['wpb_watermark_logo_file'], ['test_form' => false]);
            if (!isset($uploaded['error']) && !empty($uploaded['url'])) {
                $logo = esc_url_raw($uploaded['url']);
            }
        }

        update_option('wpb_watermark_enabled', $enabled);
        update_option('wpb_watermark_logo_source', $source);
        update_option('wpb_watermark_logo', $logo);
        update_option('wpb_watermark_opacity', $opacity);

        wp_redirect(add_query_arg([
            'page'    => 'wpb-settings',
            'message' => 'saved',
        ], admin_url('admin.php')));
        exit;
    }

    public function enqueue_admin_assets($hook) {
        if (strpos($hook, 'wpb-') === false && strpos($hook, 'wpb_template') === false) {
            return;
        }

        wp_enqueue_style('wpb-admin', WPB_PLUGIN_URL . 'assets/css/admin.css', [], WPB_VERSION);
        wp_enqueue_script('wpb-admin', WPB_PLUGIN_URL . 'assets/js/admin.js', ['jquery'], WPB_VERSION, true);

        wp_localize_script('wpb-admin', 'wpbAdmin', [
            'toggleNonce' => wp_create_nonce('wpb_toggle_active'),
            'ajaxUrl'     => admin_url('admin-ajax.php'),
            'formAction'  => admin_url('admin.php?page=wpb-page-builder'),
            'strings'     => [
                'confirmDelete'  => __('Are you sure you want to delete this template?', 'woocommerce-page-builder'),
                'confirmBulk'    => __('Are you sure you want to move the selected templates to trash?', 'woocommerce-page-builder'),
                'nameRequired'   => __('Please enter a template name.', 'woocommerce-page-builder'),
                'typeRequired'   => __('Please select a template type.', 'woocommerce-page-builder'),
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

        if ($action === 'trash' && !empty($_REQUEST['wpb_template_ids'])) {
            $this->handle_bulk_action();
            return;
        }

        $list_table = new WPB_Template_List_Table();
        $list_table->prepare_items();
        ?>
        <div class="wrap wpb-wrap">
            <h1 class="wp-heading-inline"><?php _e('Woo Page Builder', 'woocommerce-page-builder'); ?></h1>
            <a href="#" class="page-title-action" id="wpb-add-new-template-top"><?php _e('Add New Template', 'woocommerce-page-builder'); ?></a>
            <hr class="wp-header-end">

            <?php
            if (isset($_GET['message']) && $_GET['message'] === 'created') {
                echo '<div class="notice notice-success is-dismissible"><p>' . __('Template created successfully.', 'woocommerce-page-builder') . '</p></div>';
            }
            if (isset($_GET['message']) && $_GET['message'] === 'toggled') {
                echo '<div class="notice notice-success is-dismissible"><p>' . __('Template status updated.', 'woocommerce-page-builder') . '</p></div>';
            }
            if (isset($_GET['message']) && $_GET['message'] === 'trashed') {
                echo '<div class="notice notice-success is-dismissible"><p>' . __('Template moved to trash.', 'woocommerce-page-builder') . '</p></div>';
            }
            if (isset($_GET['message']) && $_GET['message'] === 'restored') {
                echo '<div class="notice notice-success is-dismissible"><p>' . __('Template restored.', 'woocommerce-page-builder') . '</p></div>';
            }
            if (isset($_GET['message']) && $_GET['message'] === 'deleted') {
                echo '<div class="notice notice-success is-dismissible"><p>' . __('Template deleted permanently.', 'woocommerce-page-builder') . '</p></div>';
            }
            ?>

            <form id="wpb-templates-filter" method="get" style="margin: 10px 0;">
                <input type="hidden" name="page" value="wpb-page-builder" />
                <?php if (isset($_REQUEST['post_status'])): ?>
                    <input type="hidden" name="post_status" value="<?php echo esc_attr($_REQUEST['post_status']); ?>" />
                <?php endif; ?>
                <?php $list_table->search_box(__('Search Templates', 'woocommerce-page-builder'), 'wpb_template_search'); ?>
            </form>

            <?php $list_table->display(); ?>
        </div>
        <?php
    }

    public function handle_create_template() {
        if (!isset($_POST['wpb_create_nonce']) || !wp_verify_nonce($_POST['wpb_create_nonce'], 'wpb_create_template')) {
            wp_die(__('Security check failed', 'woocommerce-page-builder'));
        }

        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have permission to do this.', 'woocommerce-page-builder'));
        }

        $name = isset($_POST['wpb_template_name']) ? sanitize_text_field($_POST['wpb_template_name']) : '';
        $type = isset($_POST['wpb_template_type']) ? sanitize_text_field($_POST['wpb_template_type']) : '';

        if (empty($name)) {
            wp_redirect(add_query_arg(['page' => 'wpb-page-builder', 'error' => 'name_required'], admin_url('admin.php')));
            exit;
        }

        if (empty($type)) {
            wp_redirect(add_query_arg(['page' => 'wpb-page-builder', 'error' => 'type_required'], admin_url('admin.php')));
            exit;
        }

        $post_id = wp_insert_post([
            'post_title'  => $name,
            'post_type'   => 'wpb_template',
            'post_status' => 'draft',
            'post_author' => get_current_user_id(),
        ]);

        if (is_wp_error($post_id)) {
            wp_redirect(add_query_arg(['page' => 'wpb-page-builder', 'error' => 'create_failed'], admin_url('admin.php')));
            exit;
        }

        update_post_meta($post_id, '_wpb_template_type', $type);
        update_post_meta($post_id, '_wpb_template_active', '0');

        wp_redirect(add_query_arg([
            'page'    => 'wpb-page-builder',
            'message' => 'created',
            'action'  => 'elementor',
            'post'    => $post_id,
        ], admin_url('admin.php')));
        exit;
    }

    public function handle_toggle_active() {
        if (!isset($_GET['_wpnonce']) || !wp_verify_nonce($_GET['_wpnonce'], 'wpb_toggle_active')) {
            wp_die(__('Security check failed', 'woocommerce-page-builder'));
        }

        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have permission to do this.', 'woocommerce-page-builder'));
        }

        $post_id = isset($_GET['post_id']) ? (int) $_GET['post_id'] : 0;
        $post = get_post($post_id);

        if (!$post || $post->post_type !== 'wpb_template') {
            wp_redirect(add_query_arg(['page' => 'wpb-page-builder', 'error' => 'invalid_post'], admin_url('admin.php')));
            exit;
        }

        $current_active = get_post_meta($post_id, '_wpb_template_active', true);
        $new_active = $current_active ? '0' : '1';

        $template_type = get_post_meta($post_id, '_wpb_template_type', true);

        if ($new_active === '1') {
            $args = [
                'post_type'   => 'wpb_template',
                'post_status' => ['publish', 'draft'],
                'meta_key'    => '_wpb_template_type',
                'meta_value'  => $template_type,
                'fields'      => 'ids',
                'nopaging'    => true,
            ];
            $same_type = get_posts($args);
            foreach ($same_type as $id) {
                if ((int) $id !== (int) $post_id) {
                    update_post_meta($id, '_wpb_template_active', '0');
                }
            }
        }

        update_post_meta($post_id, '_wpb_template_active', $new_active);

        wp_redirect(add_query_arg(['page' => 'wpb-page-builder', 'message' => 'toggled'], admin_url('admin.php')));
        exit;
    }

    public function handle_bulk_action() {
        $nonce = isset($_REQUEST['_wpnonce']) ? $_REQUEST['_wpnonce'] : '';
        if (!wp_verify_nonce($nonce, 'wpb_templates-bulk')) {
            wp_redirect(add_query_arg(['page' => 'wpb-page-builder', 'error' => 'invalid_nonce'], admin_url('admin.php')));
            exit;
        }

        if (!current_user_can('manage_options')) {
            wp_redirect(add_query_arg(['page' => 'wpb-page-builder', 'error' => 'permission'], admin_url('admin.php')));
            exit;
        }

        $action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
        if ($action === '' && isset($_REQUEST['action2'])) {
            $action = $_REQUEST['action2'];
        }

        $ids = isset($_REQUEST['wpb_template_ids']) ? array_map('intval', (array) $_REQUEST['wpb_template_ids']) : [];

        if (empty($ids)) {
            wp_redirect(add_query_arg(['page' => 'wpb-page-builder'], admin_url('admin.php')));
            exit;
        }

        if ($action === 'trash') {
            foreach ($ids as $id) {
                $post = get_post($id);
                if ($post && $post->post_type === 'wpb_template') {
                    wp_trash_post($id);
                }
            }
            wp_redirect(add_query_arg(['page' => 'wpb-page-builder', 'message' => 'trashed'], admin_url('admin.php')));
            exit;
        }

        wp_redirect(add_query_arg(['page' => 'wpb-page-builder'], admin_url('admin.php')));
        exit;
    }

    public function set_custom_columns($columns) {
        $columns['wpb_template_type'] = __('Type', 'woocommerce-page-builder');
        $columns['wpb_template_active'] = __('Active', 'woocommerce-page-builder');
        return $columns;
    }

    public function redirect_after_trash($post_id) {
        $post = get_post($post_id);
        if (!$post || $post->post_type !== 'wpb_template') {
            return;
        }

        wp_redirect(add_query_arg(['page' => 'wpb-page-builder', 'message' => 'trashed'], admin_url('admin.php')));
        exit;
    }

    public function handle_delete_permanent() {
        $post_id = isset($_GET['post']) ? (int) $_GET['post'] : 0;

        if (!$post_id || !isset($_GET['_wpnonce'])) {
            wp_redirect(add_query_arg(['page' => 'wpb-page-builder', 'error' => 'invalid_request'], admin_url('admin.php')));
            exit;
        }

        if (!wp_verify_nonce($_GET['_wpnonce'], 'wpb_delete_template_' . $post_id)) {
            wp_die(__('Security check failed', 'woocommerce-page-builder'));
        }

        $post = get_post($post_id);
        if (!$post || $post->post_type !== 'wpb_template') {
            wp_redirect(add_query_arg(['page' => 'wpb-page-builder', 'error' => 'invalid_post'], admin_url('admin.php')));
            exit;
        }

        wp_delete_post($post_id, true);

        wp_redirect(add_query_arg(['page' => 'wpb-page-builder', 'message' => 'deleted'], admin_url('admin.php')));
        exit;
    }

    public function handle_restore() {
        $post_id = isset($_GET['post']) ? (int) $_GET['post'] : 0;

        if (!$post_id || !isset($_GET['_wpnonce'])) {
            wp_redirect(add_query_arg(['page' => 'wpb-page-builder', 'error' => 'invalid_request'], admin_url('admin.php')));
            exit;
        }

        if (!wp_verify_nonce($_GET['_wpnonce'], 'wpb_restore_template_' . $post_id)) {
            wp_die(__('Security check failed', 'woocommerce-page-builder'));
        }

        $post = get_post($post_id);
        if (!$post || $post->post_type !== 'wpb_template') {
            wp_redirect(add_query_arg(['page' => 'wpb-page-builder', 'error' => 'invalid_post'], admin_url('admin.php')));
            exit;
        }

        wp_untrash_post($post_id);

        wp_redirect(add_query_arg(['page' => 'wpb-page-builder', 'message' => 'restored'], admin_url('admin.php')));
        exit;
    }

    public function render_custom_columns($column, $post_id) {
        switch ($column) {
            case 'wpb_template_type':
                $type = get_post_meta($post_id, '_wpb_template_type', true);
                $types = [
                    'shop'             => __('Shop', 'woocommerce-page-builder'),
                    'cart'             => __('Cart', 'woocommerce-page-builder'),
                    'checkout'         => __('Checkout', 'woocommerce-page-builder'),
                    'myaccount'        => __('My Account', 'woocommerce-page-builder'),
                    'product'          => __('Single Product', 'woocommerce-page-builder'),
                    'product-category' => __('Product Category', 'woocommerce-page-builder'),
                    'product-tag'      => __('Product Tag', 'woocommerce-page-builder'),
                    'archive'          => __('Archive (Shop/Category/Tag)', 'woocommerce-page-builder'),
                ];
                echo isset($types[$type]) ? $types[$type] : esc_html($type);
                break;
            case 'wpb_template_active':
                $is_active = get_post_meta($post_id, '_wpb_template_active', true);
                $checked = $is_active ? 'checked' : '';
                echo '<label class="wpb-switch">';
                echo '<input type="checkbox" disabled ' . $checked . '>';
                echo '<span class="wpb-slider round"></span>';
                echo '</label>';
                break;
        }
    }

    public function modify_row_actions($actions, $post) {
        if ($post->post_type !== 'wpb_template') {
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
            esc_attr(sprintf(__('Edit with Elementor', 'woocommerce-page-builder'))),
            __('Edit with Elementor', 'woocommerce-page-builder')
        );

        return $actions;
    }

    public function render_modal() {
        $screen = get_current_screen();
        if (!$screen || $screen->id !== 'toplevel_page_wpb-page-builder') {
            return;
        }
        ?>
        <div id="wpb-modal-overlay" class="wpb-modal-overlay" style="display:none;">
            <div class="wpb-modal">
                <div class="wpb-modal-header">
                    <h2><?php _e('Add New Template', 'woocommerce-page-builder'); ?></h2>
                    <button type="button" class="wpb-modal-close">&times;</button>
                </div>
                <div class="wpb-modal-body">
                    <form id="wpb-create-template-form" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                        <input type="hidden" name="action" value="wpb_create_template" />
                        <input type="hidden" name="wpb_create_nonce" value="<?php echo esc_attr(wp_create_nonce('wpb_create_template')); ?>" />

                        <table class="form-table">
                            <tr>
                                <th scope="row"><label for="wpb_template_name"><?php _e('Name', 'woocommerce-page-builder'); ?></label></th>
                                <td><input type="text" id="wpb_template_name" name="wpb_template_name" class="regular-text" required /></td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="wpb_template_type"><?php _e('Type', 'woocommerce-page-builder'); ?></label></th>
                                <td>
                <select id="wpb_template_type" name="wpb_template_type" required>
                    <option value=""><?php _e('Select Type', 'woocommerce-page-builder'); ?></option>
                    <option value="shop"><?php _e('Shop', 'woocommerce-page-builder'); ?></option>
                    <option value="cart"><?php _e('Cart', 'woocommerce-page-builder'); ?></option>
                    <option value="checkout"><?php _e('Checkout', 'woocommerce-page-builder'); ?></option>
                    <option value="myaccount"><?php _e('My Account', 'woocommerce-page-builder'); ?></option>
                    <option value="product"><?php _e('Single Product', 'woocommerce-page-builder'); ?></option>
                    <option value="product-category"><?php _e('Product Category', 'woocommerce-page-builder'); ?></option>
                    <option value="product-tag"><?php _e('Product Tag', 'woocommerce-page-builder'); ?></option>
                    <option value="archive"><?php _e('Archive (Shop/Category/Tag)', 'woocommerce-page-builder'); ?></option>
                </select>
                                </td>
                            </tr>
                        </table>
                        <div class="wpb-modal-footer">
                            <button type="submit" class="button button-primary"><?php _e('Save', 'woocommerce-page-builder'); ?></button>
                            <button type="button" class="button wpb-modal-close-btn"><?php _e('Cancel', 'woocommerce-page-builder'); ?></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <?php
    }
}

WPB_Admin::get_instance();

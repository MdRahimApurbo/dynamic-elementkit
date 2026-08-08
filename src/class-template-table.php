<?php
if (!defined('ABSPATH')) exit;

if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class DEK_Template_Table extends WP_List_Table {

    public function __construct() {
        $screen = function_exists('get_current_screen') ? get_current_screen() : null;
        $screen_id = $screen ? $screen->id : 'dynamic-elementkit_page_dek-templates';
        parent::__construct([
            'singular' => 'dek_template',
            'plural'   => 'dek_templates',
            'ajax'     => false,
            'screen'   => $screen_id,
        ]);
    }

    public function get_columns() {
        $columns = [
            'cb'     => '<input type="checkbox" />',
            'name'   => __('Name', 'dynamic-elementkit'),
            'type'   => __('Type', 'dynamic-elementkit'),
            'url'    => __('Landing URL', 'dynamic-elementkit'),
            'active' => __('Active', 'dynamic-elementkit'),
            'date'   => __('Date', 'dynamic-elementkit'),
        ];
        return $columns;
    }

    public function get_sortable_columns() {
        return [
            'name' => ['post_title', false],
            'date' => ['post_date', false],
        ];
    }

    public function get_bulk_actions() {
        return [
            'trash' => __('Move to Trash', 'dynamic-elementkit'),
        ];
    }

    public function get_views() {
        global $wpdb;

        $status = isset($_REQUEST['post_status']) ? $_REQUEST['post_status'] : 'all';
        $current_url = remove_query_arg(['post_status', 'action', 'action2']);

        $all_count = (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $wpdb->posts WHERE post_type = %s AND post_status != 'auto-draft'",
            'dek_template'
        ));

        $trash_count = (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $wpdb->posts WHERE post_type = %s AND post_status = 'trash'",
            'dek_template'
        ));

        $class_all = ($status === 'all' || $status === '') ? ' class="current"' : '';
        $class_trash = ($status === 'trash') ? ' class="current"' : '';

        $views = [
            'all' => sprintf('<a href="%s"%s>%s <span class="count">(%d)</span></a>', esc_url($current_url), $class_all, __('All', 'dynamic-elementkit'), $all_count - $trash_count),
            'trash' => sprintf('<a href="%s"%s>%s <span class="count">(%d)</span></a>', esc_url(add_query_arg('post_status', 'trash', $current_url)), $class_trash, __('Trash', 'dynamic-elementkit'), $trash_count),
        ];

        return $views;
    }

    public function prepare_items() {
        $per_page = 20;
        $current_page = $this->get_pagenum();
        $orderby = isset($_REQUEST['orderby']) ? sanitize_text_field($_REQUEST['orderby']) : 'date';
        $order = isset($_REQUEST['order']) ? sanitize_text_field($_REQUEST['order']) : 'desc';
        $search = isset($_REQUEST['s']) ? sanitize_text_field($_REQUEST['s']) : '';
        $post_status = isset($_REQUEST['post_status']) ? sanitize_text_field($_REQUEST['post_status']) : 'all';

        $args = [
            'post_type'      => 'dek_template',
            'post_status'    => $post_status === 'trash' ? ['trash'] : ['publish', 'draft', 'pending'],
            'posts_per_page' => $per_page,
            'paged'          => $current_page,
            'orderby'        => $orderby === 'name' ? 'title' : $orderby,
            'order'          => $order,
            's'              => $search,
        ];

        if ($post_status === 'all' || $post_status === '') {
            $args['post_status'] = ['publish', 'draft', 'pending', 'trash'];
        }

        $query = new WP_Query($args);
        $this->items = $query->posts;
        $this->set_pagination_args([
            'total_items' => $query->found_posts,
            'per_page'    => $per_page,
        ]);

        $this->_column_headers = [$this->get_columns(), $this->get_sortable_columns(), $this->get_hidden_columns(), $this->get_primary_column_name()];
    }

    public function get_hidden_columns() {
        return [];
    }

    public function get_primary_column_name() {
        return 'name';
    }

    public function column_cb($item) {
        return sprintf('<input type="checkbox" name="dek_template_ids[]" value="%s" />', esc_attr($item->ID));
    }

    public function column_name($item) {
        $edit_url = add_query_arg([
            'post'   => $item->ID,
            'action' => 'elementor',
        ], admin_url('post.php'));

        $title = '<strong><a href="' . esc_url($edit_url) . '" class="row-title">' . esc_html($item->post_title) . '</a></strong>';

        $actions = [];

        if ($item->post_status !== 'trash') {
            $actions['edit'] = sprintf('<a href="%s" aria-label="%s">%s</a>', esc_url($edit_url), esc_attr(sprintf(__('Edit &#8220;%s&#8221;', 'dynamic-elementkit'), $item->post_title)), __('Edit with Elementor', 'dynamic-elementkit'));
            $actions['trash'] = sprintf('<a href="%s" class="submitdelete" aria-label="%s">%s</a>', get_delete_post_link($item->ID), esc_attr(__('Move this item to the Trash', 'dynamic-elementkit')), __('Trash', 'dynamic-elementkit'));
        } else {
            $actions['restore'] = sprintf('<a href="%s" aria-label="%s">%s</a>', wp_nonce_url(admin_url('admin.php?page=dek-templates&action=restore&post=' . $item->ID), 'dek_restore_template_' . $item->ID), esc_attr(__('Restore this item from the Trash', 'dynamic-elementkit')), __('Restore', 'dynamic-elementkit'));
            $actions['delete'] = sprintf('<a href="%s" class="submitdelete" aria-label="%s">%s</a>', wp_nonce_url(admin_url('admin.php?page=dek-templates&action=delete&post=' . $item->ID), 'dek_delete_template_' . $item->ID), esc_attr(__('Delete this item permanently', 'dynamic-elementkit')), __('Delete Permanently', 'dynamic-elementkit'));
        }

        return $title . $this->row_actions($actions);
    }

    public function column_type($item) {
        $type = get_post_meta($item->ID, '_dek_template_type', true);
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
        return isset($types[$type]) ? $types[$type] : esc_html($type);
    }

    public function column_active($item) {
        if ($item->post_status === 'trash') {
            return '&mdash;';
        }

        $is_active = get_post_meta($item->ID, '_dek_template_active', true);
        $checked = $is_active ? 'checked' : '';

        $output  = '<div class="wpb-toggle-wrapper">';
        $output .= '<label class="wpb-switch">';
        $output .= '<input type="checkbox" class="wpb-toggle-active" data-id="' . esc_attr($item->ID) . '" ' . $checked . '>';
        $output .= '<span class="wpb-slider round"></span>';
        $output .= '</label>';
        $output .= '</div>';

        return $output;
    }

    public function column_url($item) {
        $url = get_permalink($item->ID);

        if (!$url) {
            return '&mdash;';
        }

        return sprintf(
            '<a href="%1$s" target="_blank" rel="noopener">%2$s</a>',
            esc_url($url),
            esc_html($url)
        );
    }

    public function column_date($item) {
        if ('0000-00-00 00:00:00' == $item->post_date) {
            return __('Unpublished', 'dynamic-elementkit');
        }
        $date = mysql2date(__('Y/m/d'), $item->post_date);
        if ($item->post_date != $item->post_modified) {
            $modified = mysql2date(__('Y/m/d'), $item->post_modified);
            return '<abbr title="' . esc_attr($modified) . '">' . $date . '</abbr>';
        }
        return $date;
    }

    public function column_default($item, $column_name) {
        switch ($column_name) {
            case 'name':
                return $this->column_name($item);
            case 'type':
                return $this->column_type($item);
            case 'active':
                return $this->column_active($item);
            case 'date':
                return $this->column_date($item);
            default:
                return print_r($item, true);
        }
    }

    public function extra_tablenav($which) {
        if ($which === 'top') {
            ?>
            <div class="alignleft actions">
            </div>
            <?php
        }
    }

    protected function display_tablenav($which) {
        if ('top' === $which) {
            echo '<form id="wpb-templates-filter" method="get" action="' . esc_url(admin_url('admin.php?page=dek-templates')) . '">';
            echo '<input type="hidden" name="page" value="dek-templates" />';
            if (isset($_REQUEST['post_status'])) {
                echo '<input type="hidden" name="post_status" value="' . esc_attr($_REQUEST['post_status']) . '" />';
            }
        }
        parent::display_tablenav($which);
        if ('top' === $which) {
            echo '</form>';
        }
    }

    protected function get_table_classes() {
        return ['widefat', 'striped', 'wpb-templates-table'];
    }
}

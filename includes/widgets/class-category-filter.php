<?php
if (!defined('ABSPATH')) exit;

class WPB_Category_Filter_Widget extends \Elementor\Widget_Base {

    public function get_name() {
        return 'wpb-category-filter';
    }

    public function get_title() {
        return __('Category Filter', 'woocommerce-page-builder');
    }

    public function get_icon() {
        return 'eicon-filter';
    }

    public function get_categories() {
        return ['wpb-woo-page-builder'];
    }

    public function get_keywords() {
        return ['category', 'filter', 'shop', 'woocommerce', 'archive'];
    }

    public function get_custom_help_url() {
        return 'https://example.com/';
    }

    protected function register_controls() {
        $this->start_controls_section(
            'content_section',
            [
                'label' => __('Content', 'woocommerce-page-builder'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'connect_id',
            [
                'label' => __('Connect ID', 'woocommerce-page-builder'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => '',
                'description' => __('Enter the same Connect ID as the Product Grid widget to connect them.', 'woocommerce-page-builder'),
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'categories',
            [
                'label' => __('Categories', 'woocommerce-page-builder'),
                'type' => \Elementor\Controls_Manager::SELECT2,
                'options' => $this->get_terms('product_cat'),
                'multiple' => true,
            ]
        );

        $this->add_control(
            'show_all',
            [
                'label' => __('Show All Button', 'woocommerce-page-builder'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'woocommerce-page-builder'),
                'label_off' => __('No', 'woocommerce-page-builder'),
                'return' => false,
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'all_label',
            [
                'label' => __('All Button Label', 'woocommerce-page-builder'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => 'All',
                'condition' => [
                    'show_all' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'filter_style',
            [
                'label' => __('Filter Style', 'woocommerce-page-builder'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'default',
                'options' => [
                    'default' => __('Default', 'woocommerce-page-builder'),
                    'outline' => __('Outline', 'woocommerce-page-builder'),
                    'fill' => __('Fill', 'woocommerce-page-builder'),
                ],
            ]
        );

        $this->add_control(
            'filter_mode',
            [
                'label' => __('Filter Mode', 'woocommerce-page-builder'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'single',
                'options' => [
                    'single' => __('Single Select', 'woocommerce-page-builder'),
                    'multiple' => __('Multi Select / Checkbox', 'woocommerce-page-builder'),
                ],
                'description' => __('Single select allows only one category at a time. Multi select allows multiple categories with checkboxes.', 'woocommerce-page-builder'),
            ]
        );

        $this->add_control(
            'orientation',
            [
                'label' => __('Orientation', 'woocommerce-page-builder'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'horizontal',
                'options' => [
                    'horizontal' => __('Horizontal', 'woocommerce-page-builder'),
                    'vertical' => __('Vertical', 'woocommerce-page-builder'),
                ],
            ]
        );

        $this->add_control(
            'show_count',
            [
                'label' => __('Show Product Count', 'woocommerce-page-builder'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'woocommerce-page-builder'),
                'label_off' => __('No', 'woocommerce-page-builder'),
                'return_value' => 'yes',
                'default' => 'no',
            ]
        );

        $this->add_control(
            'restrict_to_current',
            [
                'label' => __('Restrict to Current Category', 'woocommerce-page-builder'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'woocommerce-page-builder'),
                'label_off' => __('No', 'woocommerce-page-builder'),
                'return' => false,
                'default' => 'no',
                'description' => __('When enabled, shows only child categories of the current product category archive.', 'woocommerce-page-builder'),
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        $connect_id = !empty($settings['connect_id']) ? sanitize_text_field($settings['connect_id']) : '';
        $css_id = !empty($settings['_element_id']) ? sanitize_text_field($settings['_element_id']) : '';
        $filter_id = $connect_id ? $connect_id : ($css_id ? $css_id : $this->get_id());
        $filter_mode = !empty($settings['filter_mode']) ? sanitize_text_field($settings['filter_mode']) : 'single';
        $orientation = in_array(($settings['orientation'] ?? ''), ['horizontal', 'vertical'], true) ? $settings['orientation'] : 'horizontal';
        $show_count = ($settings['show_count'] ?? 'no') === 'yes';

        $categories = !empty($settings['categories']) ? $settings['categories'] : [];
        if (empty($categories)) {
            $categories = get_terms([
                'taxonomy' => 'product_cat',
                'hide_empty' => true,
                'fields' => 'ids',
            ]);
        }

        if (empty($categories) || is_wp_error($categories)) {
            return;
        }

        $current_category_id = 0;
        $current_category_slug = '';
        if ($settings['restrict_to_current'] === 'yes' && function_exists('is_product_category') && is_product_category()) {
            $term = get_queried_object();
            if ($term && !is_wp_error($term) && isset($term->term_id)) {
                $current_category_id = (int) $term->term_id;
                $current_category_slug = !empty($term->slug) ? $term->slug : '';
                $child_categories = get_terms([
                    'taxonomy' => 'product_cat',
                    'hide_empty' => true,
                    'fields' => 'ids',
                    'parent' => $current_category_id,
                ]);
                if (!is_wp_error($child_categories) && !empty($child_categories)) {
                    $categories = $child_categories;
                }
            }
        }
        ?>
        <div class="wpb-category-filter style-<?php echo esc_attr($settings['filter_style']); ?> orientation-<?php echo esc_attr($orientation); ?> mode-<?php echo esc_attr($filter_mode); ?>" data-widget-id="<?php echo esc_attr($filter_id); ?>" data-connect-id="<?php echo esc_attr($connect_id); ?>" data-filter-mode="<?php echo esc_attr($filter_mode); ?>" data-orientation="<?php echo esc_attr($orientation); ?>" <?php if ($current_category_slug): ?>data-current-category="<?php echo esc_attr($current_category_slug); ?>"<?php endif; ?>>
            <?php if ($settings['show_all'] === 'yes' && $filter_mode === 'single'): ?>
                <button type="button" class="wpb-filter-button <?php echo $current_category_id ? '' : 'active'; ?>" data-category-id="<?php echo esc_attr($current_category_id); ?>" data-category-slug="<?php echo esc_attr($current_category_slug); ?>">
                    <span><?php echo esc_html($settings['all_label']); ?></span>
                </button>
            <?php endif; ?>
            <div class="wpb-filter-items">
                <?php foreach ($categories as $cat_id):
                    $cat = get_term($cat_id);
                    if (!$cat || is_wp_error($cat)) continue;
                ?>
                    <?php if ($filter_mode === 'multiple'): ?>
                        <label class="wpb-filter-checkbox">
                            <input type="checkbox" class="wpb-filter-checkbox-input" value="<?php echo esc_attr($cat->slug); ?>">
                            <span class="wpb-filter-checkbox-box" aria-hidden="true"></span>
                            <span class="wpb-filter-checkbox-label"><?php echo esc_html($cat->name); ?></span>
                            <?php if ($show_count): ?><span class="wpb-filter-count"><?php echo esc_html($cat->count); ?></span><?php endif; ?>
                        </label>
                    <?php else: ?>
                        <button type="button" class="wpb-filter-button" data-category-id="<?php echo esc_attr($cat->term_id); ?>" data-category-slug="<?php echo esc_attr($cat->slug); ?>">
                            <span><?php echo esc_html($cat->name); ?></span>
                            <?php if ($show_count): ?><span class="wpb-filter-count"><?php echo esc_html($cat->count); ?></span><?php endif; ?>
                        </button>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
    }

    private function get_terms($taxonomy) {
        $terms = get_terms([
            'taxonomy' => $taxonomy,
            'hide_empty' => false,
        ]);
        $options = [];
        if (!is_wp_error($terms)) {
            foreach ($terms as $term) {
                $options[$term->term_id] = $term->name;
            }
        }
        return $options;
    }
}

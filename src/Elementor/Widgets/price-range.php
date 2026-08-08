<?php
if (!defined('ABSPATH')) exit;

class DEK_Price_Range_Widget extends \Elementor\Widget_Base {

    public function get_name() {
        return 'wpb-price-range';
    }

    public function get_title() {
        return __('Price Range Filter', 'dynamic-elementkit');
    }

    public function get_icon() {
        return 'eicon-price-range';
    }

    public function get_categories() {
        return ['wpb-woo-page-builder'];
    }

    public function get_keywords() {
        return ['price', 'range', 'filter', 'shop', 'woocommerce'];
    }

    public function get_custom_help_url() {
        return 'https://example.com/';
    }

    protected function register_controls() {
        $this->start_controls_section(
            'content_section',
            [
                'label' => __('Content', 'dynamic-elementkit'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'connect_id',
            [
                'label' => __('Connect ID', 'dynamic-elementkit'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => '',
                'description' => __('Enter the same Connect ID as the Product Grid widget to connect them.', 'dynamic-elementkit'),
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'input_type',
            [
                'label' => __('Input Type', 'dynamic-elementkit'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'inputs',
                'options' => [
                    'inputs' => __('Number Inputs', 'dynamic-elementkit'),
                    'slider' => __('Range Slider', 'dynamic-elementkit'),
                ],
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'min_price_placeholder',
            [
                'label' => __('Min Price Placeholder', 'dynamic-elementkit'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => 'Min price',
                'condition' => [
                    'input_type' => 'inputs',
                ],
            ]
        );

        $this->add_control(
            'max_price_placeholder',
            [
                'label' => __('Max Price Placeholder', 'dynamic-elementkit'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => 'Max price',
                'condition' => [
                    'input_type' => 'inputs',
                ],
            ]
        );

        $this->add_control(
            'slider_min_label',
            [
                'label' => __('Slider Min Label', 'dynamic-elementkit'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => 'Min',
                'condition' => [
                    'input_type' => 'slider',
                ],
            ]
        );

        $this->add_control(
            'slider_max_label',
            [
                'label' => __('Slider Max Label', 'dynamic-elementkit'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => 'Max',
                'condition' => [
                    'input_type' => 'slider',
                ],
            ]
        );

        $this->add_control(
            'auto_filter',
            [
                'label' => __('Auto Filter', 'dynamic-elementkit'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'dynamic-elementkit'),
                'label_off' => __('No', 'dynamic-elementkit'),
                'return' => false,
                'default' => 'yes',
                'description' => __('Filter products automatically when values change.', 'dynamic-elementkit'),
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        $connect_id = !empty($settings['connect_id']) ? sanitize_text_field($settings['connect_id']) : '';
        $css_id = !empty($settings['_element_id']) ? sanitize_text_field($settings['_element_id']) : '';
        $filter_id = $connect_id ? $connect_id : ($css_id ? $css_id : $this->get_id());
        $input_type = !empty($settings['input_type']) ? sanitize_text_field($settings['input_type']) : 'inputs';
        $auto_filter = !empty($settings['auto_filter']) ? sanitize_text_field($settings['auto_filter']) : 'yes';

        $min_price_placeholder = !empty($settings['min_price_placeholder']) ? $settings['min_price_placeholder'] : 'Min price';
        $max_price_placeholder = !empty($settings['max_price_placeholder']) ? $settings['max_price_placeholder'] : 'Max price';
        $slider_min_label = !empty($settings['slider_min_label']) ? $settings['slider_min_label'] : 'Min';
        $slider_max_label = !empty($settings['slider_max_label']) ? $settings['slider_max_label'] : 'Max';

        $price_range = $this->get_price_range();
        $min_val = isset($price_range['min']) ? floatval($price_range['min']) : 0;
        $max_val = isset($price_range['max']) ? floatval($price_range['max']) : 1000;
        $step = max(1, round(($max_val - $min_val) / 100, 2));
        $currency_symbol = function_exists('get_woocommerce_currency_symbol') ? get_woocommerce_currency_symbol() : '';
        ?>
        <div class="wpb-price-range-filter" data-connect-id="<?php echo esc_attr($connect_id); ?>" data-filter-id="<?php echo esc_attr($filter_id); ?>" data-input-type="<?php echo esc_attr($input_type); ?>" data-auto-filter="<?php echo esc_attr($auto_filter); ?>" data-min-val="<?php echo esc_attr($min_val); ?>" data-max-val="<?php echo esc_attr($max_val); ?>">
            <?php if ($input_type === 'inputs'): ?>
                <div class="wpb-price-range-inputs">
                    <input type="number" class="wpb-price-min" placeholder="<?php echo esc_attr($min_price_placeholder); ?>" min="0" step="any">
                    <span class="wpb-price-range-separator">-</span>
                    <input type="number" class="wpb-price-max" placeholder="<?php echo esc_attr($max_price_placeholder); ?>" min="0" step="any">
                </div>
            <?php else: ?>
                <div class="wpb-price-range-slider" data-min="<?php echo esc_attr($min_val); ?>" data-max="<?php echo esc_attr($max_val); ?>" data-step="<?php echo esc_attr($step); ?>" data-currency="<?php echo esc_attr($currency_symbol); ?>">
                    <div class="wpb-slider-labels">
                        <span class="wpb-slider-min-label"><?php echo esc_html($slider_min_label); ?></span>
                        <span class="wpb-slider-max-label"><?php echo esc_html($slider_max_label); ?></span>
                    </div>
                    <div class="wpb-slider-control">
                        <div class="wpb-slider-track"></div>
                        <div class="wpb-slider-range"></div>
                        <input type="range" class="wpb-price-range-min" min="<?php echo esc_attr($min_val); ?>" max="<?php echo esc_attr($max_val); ?>" step="<?php echo esc_attr($step); ?>" value="<?php echo esc_attr($min_val); ?>" aria-label="<?php esc_attr_e('Minimum price', 'dynamic-elementkit'); ?>">
                        <input type="range" class="wpb-price-range-max" min="<?php echo esc_attr($min_val); ?>" max="<?php echo esc_attr($max_val); ?>" step="<?php echo esc_attr($step); ?>" value="<?php echo esc_attr($max_val); ?>" aria-label="<?php esc_attr_e('Maximum price', 'dynamic-elementkit'); ?>">
                    </div>
                    <div class="wpb-slider-values">
                        <output class="wpb-slider-min-value"></output>
                        <output class="wpb-slider-max-value"></output>
                    </div>
                </div>
                <input type="hidden" class="wpb-price-min" value="<?php echo esc_attr($min_val); ?>">
                <input type="hidden" class="wpb-price-max" value="<?php echo esc_attr($max_val); ?>">
            <?php endif; ?>
            <?php if ($auto_filter !== 'yes'): ?>
                <button type="button" class="wpb-price-filter-button"><?php esc_html_e('Apply price filter', 'dynamic-elementkit'); ?></button>
            <?php endif; ?>
        </div>
        <?php
    }

    protected function content_template() {
        ?>
        <div class="wpb-price-range-filter" data-connect-id="" data-filter-id="" data-input-type="inputs" data-auto-filter="yes" data-min-val="0" data-max-val="1000">
            <div class="wpb-price-range-inputs">
                <input type="number" class="wpb-price-min" placeholder="Min price" min="0" step="any">
                <span class="wpb-price-range-separator">-</span>
                <input type="number" class="wpb-price-max" placeholder="Max price" min="0" step="any">
            </div>
        </div>
        <?php
    }

    private function get_price_range() {
        global $wpdb;
        $min = $wpdb->get_var("SELECT MIN(CAST(meta_value AS DECIMAL(10,2))) FROM {$wpdb->postmeta} WHERE meta_key = '_price' AND meta_value != ''");
        $max = $wpdb->get_var("SELECT MAX(CAST(meta_value AS DECIMAL(10,2))) FROM {$wpdb->postmeta} WHERE meta_key = '_price' AND meta_value != ''");

        $min = $min !== null ? floatval($min) : 0;
        $max = $max !== null ? floatval($max) : 1000;

        if ($min === $max) {
            $min = floor($min * 0.8);
            $max = ceil($max * 1.2);
        }

        return [
            'min' => $min,
            'max' => $max,
        ];
    }
}

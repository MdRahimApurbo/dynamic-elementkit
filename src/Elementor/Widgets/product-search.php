<?php
if (!defined('ABSPATH')) exit;

class DEK_Product_Search_Widget extends \Elementor\Widget_Base {

    public function get_name() {
        return 'wpb-product-search';
    }

    public function get_title() {
        return __('AJAX Product Search', 'dynamic-elementkit');
    }

    public function get_icon() {
        return 'eicon-search';
    }

    public function get_categories() {
        return ['wpb-woo-page-builder'];
    }

    public function get_keywords() {
        return ['search', 'product', 'ajax', 'filter', 'shop', 'woocommerce'];
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
            'placeholder',
            [
                'label' => __('Placeholder', 'dynamic-elementkit'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __('Search products...', 'dynamic-elementkit'),
            ]
        );

        $this->add_control(
            'min_chars',
            [
                'label' => __('Minimum Characters', 'dynamic-elementkit'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 3,
                'min' => 1,
                'max' => 10,
                'description' => __('Start searching after this many characters are typed.', 'dynamic-elementkit'),
            ]
        );

        $this->add_control(
            'per_page',
            [
                'label' => __('Results Limit', 'dynamic-elementkit'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 8,
                'min' => 1,
                'max' => 30,
            ]
        );

        $this->add_control(
            'show_image',
            [
                'label' => __('Show Image', 'dynamic-elementkit'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'dynamic-elementkit'),
                'label_off' => __('No', 'dynamic-elementkit'),
                'return' => false,
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'show_price',
            [
                'label' => __('Show Price', 'dynamic-elementkit'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'dynamic-elementkit'),
                'label_off' => __('No', 'dynamic-elementkit'),
                'return' => false,
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'show_no_result',
            [
                'label' => __('Show "No Results" Message', 'dynamic-elementkit'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'dynamic-elementkit'),
                'label_off' => __('No', 'dynamic-elementkit'),
                'return' => false,
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'no_result_text',
            [
                'label' => __('No Results Text', 'dynamic-elementkit'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __('No products found.', 'dynamic-elementkit'),
                'condition' => [
                    'show_no_result' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'connect_id',
            [
                'label' => __('Connect ID', 'dynamic-elementkit'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => '',
                'description' => __('Optional. Enter the same Connect ID as the Product Grid to also filter it. Leave empty for dropdown search only.', 'dynamic-elementkit'),
                'separator' => 'before',
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'style_section',
            [
                'label' => __('Style', 'dynamic-elementkit'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'input_height',
            [
                'label' => __('Input Height', 'dynamic-elementkit'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => [
                        'min' => 30,
                        'max' => 80,
                    ],
                ],
                'default' => [
                    'size' => 48,
                ],
                'selectors' => [
                    '{{WRAPPER}} .wpb-product-search-input' => 'height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'input_bg_color',
            [
                'label' => __('Input Background', 'dynamic-elementkit'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .wpb-product-search-input' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'input_text_color',
            [
                'label' => __('Input Text Color', 'dynamic-elementkit'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .wpb-product-search-input' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'input_border_color',
            [
                'label' => __('Input Border Color', 'dynamic-elementkit'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .wpb-product-search-input' => 'border-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'input_border_radius',
            [
                'label' => __('Input Border Radius', 'dynamic-elementkit'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .wpb-product-search-input' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'dropdown_bg_color',
            [
                'label' => __('Dropdown Background', 'dynamic-elementkit'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .wpb-product-search-results' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'title_typography',
                'label' => __('Title Typography', 'dynamic-elementkit'),
                'selector' => '{{WRAPPER}} .wpb-search-result-title',
            ]
        );

        $this->add_control(
            'title_color',
            [
                'label' => __('Title Color', 'dynamic-elementkit'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .wpb-search-result-title' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'price_color',
            [
                'label' => __('Price Color', 'dynamic-elementkit'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .wpb-search-result-price' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        $placeholder = !empty($settings['placeholder']) ? $settings['placeholder'] : __('Search products...', 'dynamic-elementkit');
        $min_chars = !empty($settings['min_chars']) ? (int) $settings['min_chars'] : 3;
        $per_page = !empty($settings['per_page']) ? (int) $settings['per_page'] : 8;
        $connect_id = !empty($settings['connect_id']) ? sanitize_text_field($settings['connect_id']) : '';
        $show_image = !empty($settings['show_image']) ? sanitize_text_field($settings['show_image']) : 'yes';
        $show_price = !empty($settings['show_price']) ? sanitize_text_field($settings['show_price']) : 'yes';
        $show_no_result = !empty($settings['show_no_result']) ? sanitize_text_field($settings['show_no_result']) : 'yes';
        $no_result_text = !empty($settings['no_result_text']) ? $settings['no_result_text'] : __('No products found.', 'dynamic-elementkit');
        ?>
        <div class="wpb-product-search"
             data-min-chars="<?php echo esc_attr($min_chars); ?>"
             data-per-page="<?php echo esc_attr($per_page); ?>"
             data-show-image="<?php echo esc_attr($show_image); ?>"
             data-show-price="<?php echo esc_attr($show_price); ?>"
             data-show-no-result="<?php echo esc_attr($show_no_result); ?>"
             data-no-result-text="<?php echo esc_attr($no_result_text); ?>"
            data-connect-id="<?php echo esc_attr($connect_id); ?>">
            <form class="wpb-product-search-form" action="<?php echo esc_url(home_url('/')); ?>" method="get" role="search">
                <label class="screen-reader-text" for="wpb-product-search-<?php echo esc_attr($this->get_id()); ?>"><?php esc_html_e('Search products', 'dynamic-elementkit'); ?></label>
                <input id="wpb-product-search-<?php echo esc_attr($this->get_id()); ?>" type="search" class="wpb-product-search-input" name="s" autocomplete="off" placeholder="<?php echo esc_attr($placeholder); ?>" aria-autocomplete="list" />
                <input type="hidden" name="post_type" value="product" />
                <span class="wpb-product-search-loading" aria-hidden="true"></span>
            </form>
            <div class="wpb-product-search-results" style="display:none;"></div>
        </div>
        <?php
    }

    protected function content_template() {
        ?>
        <div class="wpb-product-search" data-min-chars="3" data-per-page="8" data-show-image="yes" data-show-price="yes" data-show-no-result="yes" data-no-result-text="<?php esc_attr_e('No products found.', 'dynamic-elementkit'); ?>" data-connect-id="">
            <form class="wpb-product-search-form" action="#" method="get" role="search">
                <input type="search" class="wpb-product-search-input" name="s" autocomplete="off" placeholder="<?php esc_attr_e('Search products...', 'dynamic-elementkit'); ?>" />
                <input type="hidden" name="post_type" value="product" />
            </form>
            <div class="wpb-product-search-results" style="display:none;"></div>
        </div>
        <?php
    }
}

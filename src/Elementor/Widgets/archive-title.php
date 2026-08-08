<?php
if (!defined('ABSPATH')) exit;

class DEK_Archive_Title_Widget extends \Elementor\Widget_Base {

    public function get_name() {
        return 'wpb-archive-title';
    }

    public function get_title() {
        return __('Archive Title', 'dynamic-elementkit');
    }

    public function get_icon() {
        return 'eicon-archive-posts';
    }

    public function get_categories() {
        return ['wpb-woo-page-builder'];
    }

    public function get_keywords() {
        return ['archive', 'title', 'shop', 'woocommerce'];
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
            'title_tag',
            [
                'label' => __('Title HTML Tag', 'dynamic-elementkit'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'h1',
                'options' => [
                    'h1' => 'H1',
                    'h2' => 'H2',
                    'h3' => 'H3',
                    'h4' => 'H4',
                    'h5' => 'H5',
                    'h6' => 'H6',
                    'div' => 'div',
                    'span' => 'span',
                    'p' => 'p',
                ],
            ]
        );

        $this->add_control(
            'show_breadcrumb',
            [
                'label' => __('Show Breadcrumb', 'dynamic-elementkit'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'dynamic-elementkit'),
                'label_off' => __('No', 'dynamic-elementkit'),
                'return' => false,
                'default' => 'no',
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'style_title_section',
            [
                'label' => __('Title Style', 'dynamic-elementkit'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'title_color',
            [
                'label' => __('Title Color', 'dynamic-elementkit'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .wpb-archive-title' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'title_typography',
                'label' => __('Typography', 'dynamic-elementkit'),
                'selector' => '{{WRAPPER}} .wpb-archive-title',
            ]
        );

        $this->add_control(
            'title_alignment',
            [
                'label' => __('Alignment', 'dynamic-elementkit'),
                'type' => \Elementor\Controls_Manager::CHOOSE,
                'options' => [
                    'left' => [
                        'title' => __('Left', 'dynamic-elementkit'),
                        'icon' => 'eicon-text-align-left',
                    ],
                    'center' => [
                        'title' => __('Center', 'dynamic-elementkit'),
                        'icon' => 'eicon-text-align-center',
                    ],
                    'right' => [
                        'title' => __('Right', 'dynamic-elementkit'),
                        'icon' => 'eicon-text-align-right',
                    ],
                ],
                'default' => 'left',
                'selectors' => [
                    '{{WRAPPER}} .wpb-archive-title-wrapper' => 'text-align: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name' => 'title_background',
                'label' => __('Background', 'dynamic-elementkit'),
                'types' => ['classic', 'gradient'],
                'selector' => '{{WRAPPER}} .wpb-archive-title',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'title_border',
                'label' => __('Border', 'dynamic-elementkit'),
                'selector' => '{{WRAPPER}} .wpb-archive-title',
            ]
        );

        $this->add_control(
            'title_border_radius',
            [
                'label' => __('Border Radius', 'dynamic-elementkit'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .wpb-archive-title' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'title_box_shadow',
                'label' => __('Box Shadow', 'dynamic-elementkit'),
                'selector' => '{{WRAPPER}} .wpb-archive-title',
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'style_breadcrumb_section',
            [
                'label' => __('Breadcrumb Style', 'dynamic-elementkit'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'breadcrumb_color',
            [
                'label' => __('Breadcrumb Color', 'dynamic-elementkit'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .wpb-breadcrumb' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'breadcrumb_alignment',
            [
                'label' => __('Alignment', 'dynamic-elementkit'),
                'type' => \Elementor\Controls_Manager::CHOOSE,
                'options' => [
                    'left' => [
                        'title' => __('Left', 'dynamic-elementkit'),
                        'icon' => 'eicon-text-align-left',
                    ],
                    'center' => [
                        'title' => __('Center', 'dynamic-elementkit'),
                        'icon' => 'eicon-text-align-center',
                    ],
                    'right' => [
                        'title' => __('Right', 'dynamic-elementkit'),
                        'icon' => 'eicon-text-align-right',
                    ],
                ],
                'default' => 'left',
                'selectors' => [
                    '{{WRAPPER}} .wpb-breadcrumb' => 'text-align: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'breadcrumb_typography',
                'label' => __('Typography', 'dynamic-elementkit'),
                'selector' => '{{WRAPPER}} .wpb-breadcrumb',
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        $title = dek_get_archive_title();

        if (empty($title)) {
            return;
        }

        $title_tag = \Elementor\Utils::validate_html_tag($settings['title_tag']);
        ?>
        <div class="wpb-archive-title-wrapper">
            <<?php echo $title_tag; ?> class="wpb-archive-title">
                <?php echo esc_html($title); ?>
            </<?php echo $title_tag; ?>>

            <?php if ($settings['show_breadcrumb'] === 'yes' && function_exists('woocommerce_breadcrumb')): ?>
                <div class="wpb-breadcrumb">
                    <?php woocommerce_breadcrumb(); ?>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }

    protected function content_template() {
        ?>
        <div class="wpb-archive-title-wrapper">
            <h1 class="wpb-archive-title">
                <?php _e('Archive Title', 'dynamic-elementkit'); ?>
            </h1>
        </div>
        <?php
    }
}

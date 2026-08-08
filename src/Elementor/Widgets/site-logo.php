<?php

defined('ABSPATH') || exit;

class DEK_Site_Logo_Widget extends \Elementor\Widget_Base {

    public function get_name() {
        return 'dek-site-logo';
    }

    public function get_title() {
        return __('Site Logo', 'dynamic-elementkit');
    }

    public function get_icon() {
        return 'eicon-site-logo';
    }

    public function get_categories() {
        return ['wpb-woo-page-builder'];
    }

    public function get_keywords() {
        return ['logo', 'site logo', 'branding', 'header', 'identity'];
    }

    public function get_style_depends() {
        return ['dek-site-header'];
    }

    protected function register_controls() {
        $this->start_controls_section('logo_content', [
            'label' => __('Logo', 'dynamic-elementkit'),
            'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
        ]);

        $this->add_control('link_to', [
            'label' => __('Link', 'dynamic-elementkit'),
            'type' => \Elementor\Controls_Manager::SELECT,
            'default' => 'home',
            'options' => [
                'home' => __('Home Page', 'dynamic-elementkit'),
                'custom' => __('Custom URL', 'dynamic-elementkit'),
                'none' => __('None', 'dynamic-elementkit'),
            ],
        ]);

        $this->add_control('custom_link', [
            'label' => __('Custom URL', 'dynamic-elementkit'),
            'type' => \Elementor\Controls_Manager::URL,
            'placeholder' => 'https://example.com',
            'condition' => ['link_to' => 'custom'],
        ]);

        $this->add_control('fallback_title', [
            'label' => __('Show Site Title When No Logo', 'dynamic-elementkit'),
            'type' => \Elementor\Controls_Manager::SWITCHER,
            'label_on' => __('Yes', 'dynamic-elementkit'),
            'label_off' => __('No', 'dynamic-elementkit'),
            'return_value' => 'yes',
            'default' => 'yes',
        ]);

        $this->end_controls_section();

        $this->start_controls_section('logo_style', [
            'label' => __('Logo Style', 'dynamic-elementkit'),
            'tab' => \Elementor\Controls_Manager::TAB_STYLE,
        ]);

        $this->add_responsive_control('alignment', [
            'label' => __('Alignment', 'dynamic-elementkit'),
            'type' => \Elementor\Controls_Manager::CHOOSE,
            'options' => [
                'left' => ['title' => __('Left', 'dynamic-elementkit'), 'icon' => 'eicon-text-align-left'],
                'center' => ['title' => __('Center', 'dynamic-elementkit'), 'icon' => 'eicon-text-align-center'],
                'right' => ['title' => __('Right', 'dynamic-elementkit'), 'icon' => 'eicon-text-align-right'],
            ],
            'default' => 'left',
            'selectors' => ['{{WRAPPER}} .dek-site-logo' => 'text-align: {{VALUE}};'],
        ]);

        $this->add_responsive_control('logo_width', [
            'label' => __('Width', 'dynamic-elementkit'),
            'type' => \Elementor\Controls_Manager::SLIDER,
            'size_units' => ['px', '%', 'vw'],
            'range' => [
                'px' => ['min' => 20, 'max' => 600],
                '%' => ['min' => 5, 'max' => 100],
                'vw' => ['min' => 5, 'max' => 50],
            ],
            'default' => ['size' => 160, 'unit' => 'px'],
            'selectors' => ['{{WRAPPER}} .dek-site-logo-image' => 'width: {{SIZE}}{{UNIT}};'],
        ]);

        $this->add_responsive_control('logo_max_height', [
            'label' => __('Maximum Height', 'dynamic-elementkit'),
            'type' => \Elementor\Controls_Manager::SLIDER,
            'range' => ['px' => ['min' => 20, 'max' => 300]],
            'selectors' => ['{{WRAPPER}} .dek-site-logo-image' => 'max-height: {{SIZE}}{{UNIT}};'],
        ]);

        $this->add_control('title_color', [
            'label' => __('Fallback Title Color', 'dynamic-elementkit'),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => ['{{WRAPPER}} .dek-site-logo-title' => 'color: {{VALUE}};'],
        ]);

        $this->add_group_control(\Elementor\Group_Control_Typography::get_type(), [
            'name' => 'title_typography',
            'selector' => '{{WRAPPER}} .dek-site-logo-title',
        ]);

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        $logo_id = absint(get_theme_mod('custom_logo'));
        $site_name = get_bloginfo('name');
        $link_to = in_array(($settings['link_to'] ?? 'home'), ['home', 'custom', 'none'], true) ? $settings['link_to'] : 'home';

        $this->add_render_attribute('logo', 'class', 'dek-site-logo');
        if ('home' === $link_to) {
            $this->add_render_attribute('link', [
                'class' => 'dek-site-logo-link',
                'href' => home_url('/'),
                'rel' => 'home',
                'aria-label' => sprintf(__('%s home page', 'dynamic-elementkit'), $site_name),
            ]);
        } elseif ('custom' === $link_to && !empty($settings['custom_link']['url'])) {
            $this->add_render_attribute('link', 'class', 'dek-site-logo-link');
            $this->add_link_attributes('link', $settings['custom_link']);
        }

        $has_link = $this->get_render_attribute_string('link');
        ?>
        <div <?php echo $this->get_render_attribute_string('logo'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
            <?php if ($has_link) : ?><a <?php echo $has_link; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php endif; ?>
                <?php
                if ($logo_id) {
                    echo wp_get_attachment_image(
                        $logo_id,
                        'full',
                        false,
                        [
                            'class' => 'dek-site-logo-image',
                            'loading' => 'eager',
                            'decoding' => 'async',
                        ]
                    );
                } elseif ('yes' === ($settings['fallback_title'] ?? 'yes')) {
                    echo '<span class="dek-site-logo-title">' . esc_html($site_name) . '</span>';
                }
                ?>
            <?php if ($has_link) : ?></a><?php endif; ?>
        </div>
        <?php
    }
}

<?php

defined('ABSPATH') || exit;

class DEK_Navigation_Menu_Widget extends \Elementor\Widget_Base {

    public function get_name() {
        return 'dek-navigation-menu';
    }

    public function get_title() {
        return __('Navigation Menu', 'dynamic-elementkit');
    }

    public function get_icon() {
        return 'eicon-nav-menu';
    }

    public function get_categories() {
        return ['wpb-woo-page-builder'];
    }

    public function get_keywords() {
        return ['menu', 'navigation', 'header', 'hamburger', 'mobile', 'drawer'];
    }

    public function get_style_depends() {
        return ['dek-site-header'];
    }

    public function get_script_depends() {
        return ['dek-navigation-menu'];
    }

    protected function register_controls() {
        $this->start_controls_section('menu_content', [
            'label' => __('Navigation', 'dynamic-elementkit'),
            'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
        ]);

        $this->add_control('menu_id', [
            'label' => __('WordPress Menu', 'dynamic-elementkit'),
            'type' => \Elementor\Controls_Manager::SELECT,
            'options' => $this->get_menu_options(),
            'description' => __('Create menus under Appearance → Menus.', 'dynamic-elementkit'),
        ]);

        $this->add_control('mobile_breakpoint', [
            'label' => __('Mobile Menu Breakpoint', 'dynamic-elementkit'),
            'type' => \Elementor\Controls_Manager::SELECT,
            'default' => 'tablet',
            'options' => [
                'tablet' => __('Tablet and Mobile (1024px)', 'dynamic-elementkit'),
                'mobile' => __('Mobile Only (767px)', 'dynamic-elementkit'),
            ],
        ]);

        $this->add_control('drawer_title', [
            'label' => __('Drawer Title', 'dynamic-elementkit'),
            'type' => \Elementor\Controls_Manager::TEXT,
            'default' => __('Menu', 'dynamic-elementkit'),
        ]);

        $this->add_control('drawer_position', [
            'label' => __('Drawer Position', 'dynamic-elementkit'),
            'type' => \Elementor\Controls_Manager::CHOOSE,
            'default' => 'right',
            'options' => [
                'left' => ['title' => __('Left', 'dynamic-elementkit'), 'icon' => 'eicon-h-align-left'],
                'right' => ['title' => __('Right', 'dynamic-elementkit'), 'icon' => 'eicon-h-align-right'],
            ],
            'toggle' => false,
        ]);

        $this->add_control('close_on_link', [
            'label' => __('Close After Link Click', 'dynamic-elementkit'),
            'type' => \Elementor\Controls_Manager::SWITCHER,
            'label_on' => __('Yes', 'dynamic-elementkit'),
            'label_off' => __('No', 'dynamic-elementkit'),
            'return_value' => 'yes',
            'default' => 'yes',
        ]);

        $this->end_controls_section();

        $this->start_controls_section('desktop_style', [
            'label' => __('Desktop Menu', 'dynamic-elementkit'),
            'tab' => \Elementor\Controls_Manager::TAB_STYLE,
        ]);

        $this->add_responsive_control('desktop_alignment', [
            'label' => __('Alignment', 'dynamic-elementkit'),
            'type' => \Elementor\Controls_Manager::CHOOSE,
            'options' => [
                'flex-start' => ['title' => __('Left', 'dynamic-elementkit'), 'icon' => 'eicon-text-align-left'],
                'center' => ['title' => __('Center', 'dynamic-elementkit'), 'icon' => 'eicon-text-align-center'],
                'flex-end' => ['title' => __('Right', 'dynamic-elementkit'), 'icon' => 'eicon-text-align-right'],
            ],
            'default' => 'flex-end',
            'selectors' => ['{{WRAPPER}} .dek-navigation-desktop > .dek-navigation-list' => 'justify-content: {{VALUE}};'],
        ]);

        $this->add_responsive_control('item_gap', [
            'label' => __('Item Gap', 'dynamic-elementkit'),
            'type' => \Elementor\Controls_Manager::SLIDER,
            'range' => ['px' => ['min' => 0, 'max' => 80]],
            'default' => ['size' => 28],
            'selectors' => ['{{WRAPPER}} .dek-navigation-desktop > .dek-navigation-list' => 'gap: {{SIZE}}{{UNIT}};'],
        ]);

        $this->add_control('desktop_color', [
            'label' => __('Text Color', 'dynamic-elementkit'),
            'type' => \Elementor\Controls_Manager::COLOR,
            'default' => '#1d2939',
            'selectors' => ['{{WRAPPER}} .dek-navigation-desktop a' => 'color: {{VALUE}};'],
        ]);

        $this->add_control('desktop_hover_color', [
            'label' => __('Hover and Active Color', 'dynamic-elementkit'),
            'type' => \Elementor\Controls_Manager::COLOR,
            'default' => '#2563eb',
            'selectors' => [
                '{{WRAPPER}} .dek-navigation-desktop a:hover' => 'color: {{VALUE}};',
                '{{WRAPPER}} .dek-navigation-desktop .current-menu-item > a' => 'color: {{VALUE}};',
                '{{WRAPPER}} .dek-navigation-desktop .current-menu-ancestor > a' => 'color: {{VALUE}};',
            ],
        ]);

        $this->add_group_control(\Elementor\Group_Control_Typography::get_type(), [
            'name' => 'desktop_typography',
            'selector' => '{{WRAPPER}} .dek-navigation-desktop a',
        ]);

        $this->end_controls_section();

        $this->start_controls_section('mobile_style', [
            'label' => __('Mobile Menu', 'dynamic-elementkit'),
            'tab' => \Elementor\Controls_Manager::TAB_STYLE,
        ]);

        $this->add_responsive_control('mobile_alignment', [
            'label' => __('Hamburger Alignment', 'dynamic-elementkit'),
            'type' => \Elementor\Controls_Manager::CHOOSE,
            'options' => [
                'flex-start' => ['title' => __('Left', 'dynamic-elementkit'), 'icon' => 'eicon-text-align-left'],
                'center' => ['title' => __('Center', 'dynamic-elementkit'), 'icon' => 'eicon-text-align-center'],
                'flex-end' => ['title' => __('Right', 'dynamic-elementkit'), 'icon' => 'eicon-text-align-right'],
            ],
            'default' => 'flex-end',
            'selectors' => ['{{WRAPPER}} .dek-navigation-mobile' => 'justify-content: {{VALUE}};'],
        ]);

        $this->add_control('toggle_color', [
            'label' => __('Hamburger Color', 'dynamic-elementkit'),
            'type' => \Elementor\Controls_Manager::COLOR,
            'default' => '#101828',
            'selectors' => ['{{WRAPPER}} .dek-navigation-toggle' => 'color: {{VALUE}};'],
        ]);

        $this->add_control('toggle_background', [
            'label' => __('Hamburger Background', 'dynamic-elementkit'),
            'type' => \Elementor\Controls_Manager::COLOR,
            'default' => '#ffffff',
            'selectors' => ['{{WRAPPER}} .dek-navigation-toggle' => 'background-color: {{VALUE}};'],
        ]);

        $this->add_responsive_control('toggle_size', [
            'label' => __('Hamburger Size', 'dynamic-elementkit'),
            'type' => \Elementor\Controls_Manager::SLIDER,
            'range' => ['px' => ['min' => 36, 'max' => 80]],
            'default' => ['size' => 48],
            'selectors' => ['{{WRAPPER}} .dek-navigation-toggle' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};'],
        ]);

        $this->add_responsive_control('drawer_width', [
            'label' => __('Drawer Width', 'dynamic-elementkit'),
            'type' => \Elementor\Controls_Manager::SLIDER,
            'range' => ['px' => ['min' => 240, 'max' => 600]],
            'default' => ['size' => 360],
            'selectors' => ['{{WRAPPER}} .dek-navigation-drawer' => 'width: min({{SIZE}}{{UNIT}}, calc(100vw - 20px));'],
        ]);

        $this->add_control('drawer_background', [
            'label' => __('Drawer Background', 'dynamic-elementkit'),
            'type' => \Elementor\Controls_Manager::COLOR,
            'default' => '#ffffff',
            'selectors' => ['{{WRAPPER}} .dek-navigation-drawer' => 'background-color: {{VALUE}};'],
        ]);

        $this->add_control('mobile_link_color', [
            'label' => __('Link Color', 'dynamic-elementkit'),
            'type' => \Elementor\Controls_Manager::COLOR,
            'default' => '#344054',
            'selectors' => ['{{WRAPPER}} .dek-navigation-drawer a' => 'color: {{VALUE}};'],
        ]);

        $this->add_control('mobile_link_active_color', [
            'label' => __('Hover and Active Color', 'dynamic-elementkit'),
            'type' => \Elementor\Controls_Manager::COLOR,
            'default' => '#2563eb',
            'selectors' => [
                '{{WRAPPER}} .dek-navigation-drawer a:hover' => 'color: {{VALUE}};',
                '{{WRAPPER}} .dek-navigation-drawer .current-menu-item > a' => 'color: {{VALUE}};',
            ],
        ]);

        $this->add_group_control(\Elementor\Group_Control_Typography::get_type(), [
            'name' => 'mobile_typography',
            'selector' => '{{WRAPPER}} .dek-navigation-drawer a',
        ]);

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        $menu_id = absint($settings['menu_id'] ?? 0);
        if (!$menu_id) {
            echo '<div class="dek-navigation-empty">' . esc_html__('Select a WordPress menu in the widget settings.', 'dynamic-elementkit') . '</div>';
            return;
        }

        $widget_id = sanitize_html_class($this->get_id());
        $drawer_id = 'dek-navigation-drawer-' . $widget_id;
        $position = in_array(($settings['drawer_position'] ?? 'right'), ['left', 'right'], true) ? $settings['drawer_position'] : 'right';
        $breakpoint = 'mobile' === ($settings['mobile_breakpoint'] ?? 'tablet') ? 'mobile' : 'tablet';
        $close_on_link = 'yes' === ($settings['close_on_link'] ?? 'yes') ? 'yes' : 'no';
        ?>
        <div class="dek-navigation dek-navigation-breakpoint-<?php echo esc_attr($breakpoint); ?> dek-navigation-drawer-<?php echo esc_attr($position); ?>" data-close-on-link="<?php echo esc_attr($close_on_link); ?>">
            <?php
            wp_nav_menu([
                'menu' => $menu_id,
                'container' => 'nav',
                'container_class' => 'dek-navigation-desktop',
                'container_aria_label' => __('Primary navigation', 'dynamic-elementkit'),
                'menu_class' => 'dek-navigation-list',
                'fallback_cb' => false,
                'depth' => 4,
            ]);
            ?>

            <div class="dek-navigation-mobile">
                <button type="button" class="dek-navigation-toggle" aria-expanded="false" aria-controls="<?php echo esc_attr($drawer_id); ?>" aria-label="<?php esc_attr_e('Open navigation menu', 'dynamic-elementkit'); ?>">
                    <span aria-hidden="true"><i></i><i></i><i></i></span>
                </button>
            </div>

            <button type="button" class="dek-navigation-overlay" tabindex="-1" aria-label="<?php esc_attr_e('Close navigation menu', 'dynamic-elementkit'); ?>"></button>
            <aside id="<?php echo esc_attr($drawer_id); ?>" class="dek-navigation-drawer" aria-hidden="true" aria-label="<?php esc_attr_e('Mobile navigation', 'dynamic-elementkit'); ?>">
                <div class="dek-navigation-drawer-header">
                    <strong><?php echo esc_html($settings['drawer_title'] ?? __('Menu', 'dynamic-elementkit')); ?></strong>
                    <button type="button" class="dek-navigation-close" aria-label="<?php esc_attr_e('Close navigation menu', 'dynamic-elementkit'); ?>">
                        <svg aria-hidden="true" viewBox="0 0 24 24" focusable="false"><path d="M6 6l12 12M18 6L6 18" /></svg>
                    </button>
                </div>
                <div class="dek-navigation-drawer-content">
                    <?php
                    wp_nav_menu([
                        'menu' => $menu_id,
                        'container' => 'nav',
                        'container_class' => 'dek-navigation-drawer-nav',
                        'container_aria_label' => __('Mobile navigation links', 'dynamic-elementkit'),
                        'menu_class' => 'dek-navigation-drawer-list',
                        'fallback_cb' => false,
                        'depth' => 4,
                    ]);
                    ?>
                </div>
            </aside>
        </div>
        <?php
    }

    private function get_menu_options() {
        $options = ['' => __('Select a menu', 'dynamic-elementkit')];
        foreach (wp_get_nav_menus() as $menu) {
            $options[$menu->term_id] = $menu->name;
        }
        return $options;
    }
}

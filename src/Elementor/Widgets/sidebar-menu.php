<?php
defined('ABSPATH') || exit;

class DEK_Sidebar_Menu_Widget extends \Elementor\Widget_Base {

    public function get_name() {
        return 'wpb-sidebar-menu';
    }

    public function get_title() {
        return __('Sidebar Menu', 'dynamic-elementkit');
    }

    public function get_icon() {
        return 'eicon-menu-bar';
    }

    public function get_categories() {
        return ['wpb-woo-page-builder'];
    }

    public function get_keywords() {
        return ['menu', 'sidebar', 'drawer', 'hamburger', 'navigation', 'off canvas'];
    }

    public function get_style_depends() {
        return ['wpb-sidebar-menu'];
    }

    public function get_script_depends() {
        return ['wpb-sidebar-menu'];
    }

    protected function register_controls() {
        $this->start_controls_section('menu_section', [
            'label' => __('Menu', 'dynamic-elementkit'),
            'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
        ]);

        $this->add_control('menu_id', [
            'label' => __('Select WordPress Menu', 'dynamic-elementkit'),
            'type' => \Elementor\Controls_Manager::SELECT,
            'options' => $this->get_menu_options(),
            'description' => __('Create and manage menus under Appearance → Menus.', 'dynamic-elementkit'),
        ]);

        $this->add_control('panel_title', [
            'label' => __('Panel Title', 'dynamic-elementkit'),
            'type' => \Elementor\Controls_Manager::TEXT,
            'default' => __('মেনু', 'dynamic-elementkit'),
        ]);

        $this->add_control('button_label', [
            'label' => __('Button Label', 'dynamic-elementkit'),
            'type' => \Elementor\Controls_Manager::TEXT,
            'default' => __('মেনু', 'dynamic-elementkit'),
        ]);

        $this->add_control('show_button_label', [
            'label' => __('Show Button Label', 'dynamic-elementkit'),
            'type' => \Elementor\Controls_Manager::SWITCHER,
            'label_on' => __('Show', 'dynamic-elementkit'),
            'label_off' => __('Hide', 'dynamic-elementkit'),
            'return_value' => 'yes',
            'default' => 'no',
        ]);

        $this->add_control('panel_position', [
            'label' => __('Panel Position', 'dynamic-elementkit'),
            'type' => \Elementor\Controls_Manager::CHOOSE,
            'default' => 'left',
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

        $this->start_controls_section('trigger_style', [
            'label' => __('Menu Button', 'dynamic-elementkit'),
            'tab' => \Elementor\Controls_Manager::TAB_STYLE,
        ]);

        $this->add_control('trigger_color', [
            'label' => __('Icon Color', 'dynamic-elementkit'),
            'type' => \Elementor\Controls_Manager::COLOR,
            'default' => '#101828',
            'selectors' => ['{{WRAPPER}} .wpb-sidebar-menu-trigger' => 'color: {{VALUE}};'],
        ]);

        $this->add_control('trigger_size', [
            'label' => __('Button Size', 'dynamic-elementkit'),
            'type' => \Elementor\Controls_Manager::SLIDER,
            'range' => ['px' => ['min' => 36, 'max' => 80]],
            'default' => ['size' => 48],
            'selectors' => ['{{WRAPPER}} .wpb-sidebar-menu-trigger' => 'min-width: {{SIZE}}{{UNIT}}; min-height: {{SIZE}}{{UNIT}};'],
        ]);

        $this->end_controls_section();

        $this->start_controls_section('panel_style', [
            'label' => __('Sidebar Panel', 'dynamic-elementkit'),
            'tab' => \Elementor\Controls_Manager::TAB_STYLE,
        ]);

        $this->add_control('panel_width', [
            'label' => __('Panel Width', 'dynamic-elementkit'),
            'type' => \Elementor\Controls_Manager::SLIDER,
            'range' => ['px' => ['min' => 260, 'max' => 600]],
            'default' => ['size' => 380],
            'selectors' => ['{{WRAPPER}} .wpb-sidebar-menu-panel' => 'width: min({{SIZE}}{{UNIT}}, 100vw);'],
        ]);

        $this->add_control('panel_background', [
            'label' => __('Panel Background', 'dynamic-elementkit'),
            'type' => \Elementor\Controls_Manager::COLOR,
            'default' => '#ffffff',
            'selectors' => ['{{WRAPPER}} .wpb-sidebar-menu-panel' => 'background-color: {{VALUE}};'],
        ]);

        $this->add_control('menu_color', [
            'label' => __('Menu Text Color', 'dynamic-elementkit'),
            'type' => \Elementor\Controls_Manager::COLOR,
            'default' => '#344054',
            'selectors' => ['{{WRAPPER}} .wpb-sidebar-menu-nav a' => 'color: {{VALUE}};'],
        ]);

        $this->add_control('menu_active_color', [
            'label' => __('Active Color', 'dynamic-elementkit'),
            'type' => \Elementor\Controls_Manager::COLOR,
            'default' => '#2563eb',
            'selectors' => [
                '{{WRAPPER}} .wpb-sidebar-menu-nav a:hover' => 'color: {{VALUE}};',
                '{{WRAPPER}} .wpb-sidebar-menu-nav .current-menu-item > a' => 'color: {{VALUE}};',
            ],
        ]);

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        $menu_id = !empty($settings['menu_id']) ? absint($settings['menu_id']) : 0;
        $position = in_array(($settings['panel_position'] ?? ''), ['left', 'right'], true) ? $settings['panel_position'] : 'left';
        $panel_id = 'wpb-sidebar-menu-panel-' . $this->get_id();
        $show_label = ($settings['show_button_label'] ?? 'no') === 'yes';

        if (!$menu_id) {
            echo '<div class="wpb-sidebar-menu-empty">' . esc_html__('Appearance → Menus থেকে একটি মেনু নির্বাচন করুন।', 'dynamic-elementkit') . '</div>';
            return;
        }
        ?>
        <div class="wpb-sidebar-menu wpb-sidebar-menu-<?php echo esc_attr($position); ?>"
             data-close-on-link="<?php echo esc_attr(($settings['close_on_link'] ?? 'yes') === 'yes' ? 'yes' : 'no'); ?>">
            <button type="button" class="wpb-sidebar-menu-trigger" aria-expanded="false" aria-controls="<?php echo esc_attr($panel_id); ?>" aria-label="<?php esc_attr_e('মেনু খুলুন', 'dynamic-elementkit'); ?>">
                <span class="wpb-sidebar-menu-bars" aria-hidden="true"><i></i><i></i><i></i></span>
                <?php if ($show_label): ?><span class="wpb-sidebar-menu-trigger-label"><?php echo esc_html($settings['button_label'] ?? __('মেনু', 'dynamic-elementkit')); ?></span><?php endif; ?>
            </button>
            <div class="wpb-sidebar-menu-overlay" aria-hidden="true"></div>
            <aside id="<?php echo esc_attr($panel_id); ?>" class="wpb-sidebar-menu-panel" aria-hidden="true" aria-label="<?php esc_attr_e('সাইট মেনু', 'dynamic-elementkit'); ?>">
                <header class="wpb-sidebar-menu-header">
                    <strong><?php echo esc_html($settings['panel_title'] ?? __('মেনু', 'dynamic-elementkit')); ?></strong>
                    <button type="button" class="wpb-sidebar-menu-close" aria-label="<?php esc_attr_e('মেনু বন্ধ করুন', 'dynamic-elementkit'); ?>">&times;</button>
                </header>
                <div class="wpb-sidebar-menu-content">
                    <?php
                    wp_nav_menu([
                        'menu' => $menu_id,
                        'container' => 'nav',
                        'container_class' => 'wpb-sidebar-menu-nav',
                        'menu_class' => 'wpb-sidebar-menu-list',
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

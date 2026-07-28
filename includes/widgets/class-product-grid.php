<?php
if (!defined('ABSPATH')) exit;

class WPB_Product_Grid_Widget extends \Elementor\Widget_Base {

    public function get_name() {
        return 'wpb-product-grid';
    }

    public function get_title() {
        return __('Product Grid', 'woocommerce-page-builder');
    }

    public function get_icon() {
        return 'eicon-products';
    }

    public function get_categories() {
        return ['wpb-woo-page-builder'];
    }

    public function get_keywords() {
        return ['product', 'grid', 'shop', 'woocommerce', 'archive'];
    }

    public function get_custom_help_url() {
        return 'https://example.com/';
    }

    protected function register_controls() {
        $this->start_controls_section(
            'query_section',
            [
                'label' => __('Query', 'woocommerce-page-builder'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'query_source',
            [
                'label' => __('Query Source', 'woocommerce-page-builder'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'all',
                'options' => [
                    'all' => __('All Products', 'woocommerce-page-builder'),
                    'category' => __('By Categories', 'woocommerce-page-builder'),
                    'tag' => __('By Tags', 'woocommerce-page-builder'),
                    'manual' => __('Manual Selection', 'woocommerce-page-builder'),
                ],
            ]
        );

        $this->add_control(
            'product_categories',
            [
                'label' => __('Categories', 'woocommerce-page-builder'),
                'type' => \Elementor\Controls_Manager::SELECT2,
                'options' => $this->get_terms('product_cat'),
                'multiple' => true,
                'condition' => [
                    'query_source' => 'category',
                ],
            ]
        );

        $this->add_control(
            'product_tags',
            [
                'label' => __('Tags', 'woocommerce-page-builder'),
                'type' => \Elementor\Controls_Manager::SELECT2,
                'options' => $this->get_terms('product_tag'),
                'multiple' => true,
                'condition' => [
                    'query_source' => 'tag',
                ],
            ]
        );

        $this->add_control(
            'product_ids',
            [
                'label' => __('Products', 'woocommerce-page-builder'),
                'type' => \Elementor\Controls_Manager::SELECT2,
                'options' => $this->get_products(),
                'multiple' => true,
                'condition' => [
                    'query_source' => 'manual',
                ],
            ]
        );

        $this->add_control(
            'posts_per_page',
            [
                'label' => __('Products Per Page', 'woocommerce-page-builder'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 12,
                'min' => 1,
                'max' => 100,
            ]
        );

        $this->add_control(
            'order_by',
            [
                'label' => __('Order By', 'woocommerce-page-builder'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'date',
                'options' => [
                    'date' => __('Date', 'woocommerce-page-builder'),
                    'title' => __('Title', 'woocommerce-page-builder'),
                    'price' => __('Price', 'woocommerce-page-builder'),
                    'popularity' => __('Popularity', 'woocommerce-page-builder'),
                    'rating' => __('Rating', 'woocommerce-page-builder'),
                ],
            ]
        );

        $this->add_control(
            'order',
            [
                'label' => __('Order', 'woocommerce-page-builder'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'desc',
                'options' => [
                    'asc' => __('ASC', 'woocommerce-page-builder'),
                    'desc' => __('DESC', 'woocommerce-page-builder'),
                ],
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'layout_section',
            [
                'label' => __('Layout', 'woocommerce-page-builder'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'columns',
            [
                'label' => __('Columns', 'woocommerce-page-builder'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 4,
                'options' => [
                    1 => __('1', 'woocommerce-page-builder'),
                    2 => __('2', 'woocommerce-page-builder'),
                    3 => __('3', 'woocommerce-page-builder'),
                    4 => __('4', 'woocommerce-page-builder'),
                    5 => __('5', 'woocommerce-page-builder'),
                    6 => __('6', 'woocommerce-page-builder'),
                ],
            ]
        );

        $this->add_control(
            'columns_tablet',
            [
                'label' => __('Columns (Tablet)', 'woocommerce-page-builder'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 2,
                'options' => [
                    1 => __('1', 'woocommerce-page-builder'),
                    2 => __('2', 'woocommerce-page-builder'),
                    3 => __('3', 'woocommerce-page-builder'),
                    4 => __('4', 'woocommerce-page-builder'),
                    5 => __('5', 'woocommerce-page-builder'),
                    6 => __('6', 'woocommerce-page-builder'),
                ],
                'separator' => 'after',
            ]
        );

        $this->add_control(
            'columns_mobile',
            [
                'label' => __('Columns (Mobile)', 'woocommerce-page-builder'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 1,
                'options' => [
                    1 => __('1', 'woocommerce-page-builder'),
                    2 => __('2', 'woocommerce-page-builder'),
                    3 => __('3', 'woocommerce-page-builder'),
                    4 => __('4', 'woocommerce-page-builder'),
                    5 => __('5', 'woocommerce-page-builder'),
                    6 => __('6', 'woocommerce-page-builder'),
                ],
            ]
        );

        $this->add_control(
            'show_title',
            [
                'label' => __('Show Title', 'woocommerce-page-builder'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'woocommerce-page-builder'),
                'label_off' => __('No', 'woocommerce-page-builder'),
                'return' => false,
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'show_price',
            [
                'label' => __('Show Price', 'woocommerce-page-builder'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'woocommerce-page-builder'),
                'label_off' => __('No', 'woocommerce-page-builder'),
                'return' => false,
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'show_add_to_cart',
            [
                'label' => __('Show Add To Cart', 'woocommerce-page-builder'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'woocommerce-page-builder'),
                'label_off' => __('No', 'woocommerce-page-builder'),
                'return' => false,
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'ajax_add_to_cart',
            [
                'label' => __('AJAX Add To Cart', 'woocommerce-page-builder'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'woocommerce-page-builder'),
                'label_off' => __('No', 'woocommerce-page-builder'),
                'return' => false,
                'default' => 'yes',
                'condition' => [
                    'show_add_to_cart' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'show_badge',
            [
                'label' => __('Show Badge', 'woocommerce-page-builder'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'woocommerce-page-builder'),
                'label_off' => __('No', 'woocommerce-page-builder'),
                'return' => false,
                'default' => 'no',
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'badge_text',
            [
                'label' => __('Badge Text', 'woocommerce-page-builder'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => 'Sale',
                'condition' => [
                    'show_badge' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'auto_sale_badge',
            [
                'label' => __('Auto Sale Badge', 'woocommerce-page-builder'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'woocommerce-page-builder'),
                'label_off' => __('No', 'woocommerce-page-builder'),
                'return' => false,
                'default' => 'no',
                'description' => __('Automatically show "Sale" badge on on-sale products.', 'woocommerce-page-builder'),
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'show_discount_percentage',
            [
                'label' => __('Show Discount Percentage', 'woocommerce-page-builder'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'woocommerce-page-builder'),
                'label_off' => __('No', 'woocommerce-page-builder'),
                'return' => false,
                'default' => 'no',
                'description' => __('Automatically show discount percentage on on-sale products.', 'woocommerce-page-builder'),
            ]
        );

        $this->add_control(
            'button_full_width',
            [
                'label' => __('Full Width Button', 'woocommerce-page-builder'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'woocommerce-page-builder'),
                'label_off' => __('No', 'woocommerce-page-builder'),
                'return' => false,
                'default' => 'no',
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'button_text',
            [
                'label' => __('Button Text', 'woocommerce-page-builder'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __('Add to Cart', 'woocommerce-page-builder'),
                'condition' => [
                    'show_add_to_cart' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'added_button_text',
            [
                'label' => __('Added Button Text', 'woocommerce-page-builder'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __('Added!', 'woocommerce-page-builder'),
                'description' => __('Text shown on the button after product is added to cart.', 'woocommerce-page-builder'),
                'condition' => [
                    'show_add_to_cart' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'variable_button_text',
            [
                'label' => __('Variable Product Button Text', 'woocommerce-page-builder'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __('Select options', 'woocommerce-page-builder'),
                'condition' => [
                    'show_add_to_cart' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'connect_id',
            [
                'label' => __('Connect ID', 'woocommerce-page-builder'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => '',
                'description' => __('Enter a unique Connect ID. Use the same Connect ID in a Category Filter widget to link them.', 'woocommerce-page-builder'),
                'separator' => 'before',
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'style_section',
            [
                'label' => __('Style', 'woocommerce-page-builder'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'grid_gap',
            [
                'label' => __('Grid Gap', 'woocommerce-page-builder'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'default' => [
                    'size' => 20,
                ],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 50,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .wpb-products' => 'gap: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'product_bg_color',
            [
                'label' => __('Product Background', 'woocommerce-page-builder'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .wpb-product' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'product_border_radius',
            [
                'label' => __('Product Border Radius', 'woocommerce-page-builder'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .wpb-product' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'title_typography',
                'label' => __('Title Typography', 'woocommerce-page-builder'),
                'selector' => '{{WRAPPER}} .wpb-product-title',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'price_typography',
                'label' => __('Price Typography', 'woocommerce-page-builder'),
                'selector' => '{{WRAPPER}} .wpb-product-price',
            ]
        );

        $this->add_control(
            'button_text_color',
            [
                'label' => __('Button Text Color', 'woocommerce-page-builder'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .wpb-add-to-cart' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'button_bg_color',
            [
                'label' => __('Button Background', 'woocommerce-page-builder'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .wpb-add-to-cart' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'button_border_radius',
            [
                'label' => __('Button Border Radius', 'woocommerce-page-builder'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .wpb-add-to-cart' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        $connect_id = !empty($settings['connect_id']) ? sanitize_text_field($settings['connect_id']) : '';
        $css_id = !empty($settings['_element_id']) ? sanitize_text_field($settings['_element_id']) : '';
        $grid_id = $connect_id ? $connect_id : ($css_id ? $css_id : $this->get_id());
        $query_args = $this->get_query_args($settings);
        $products = new WP_Query($query_args);

        if (!$products->have_posts()) {
            echo '<p class="wpb-no-products">' . esc_html__('No products found.', 'woocommerce-page-builder') . '</p>';
            return;
        }

        $desktop_columns = isset($settings['columns']) ? (int) $settings['columns'] : 4;
        $tablet_columns = isset($settings['columns_tablet']) ? (int) $settings['columns_tablet'] : max(2, (int) ($desktop_columns / 2));
        $mobile_columns = isset($settings['columns_mobile']) ? (int) $settings['columns_mobile'] : 1;
        ?>
<div class="wpb-product-grid"
              data-ajax="<?php echo esc_attr($settings['ajax_add_to_cart'] === 'yes' ? '1' : '0'); ?>"
              data-connect-id="<?php echo esc_attr($connect_id); ?>"
              data-added-button-text="<?php echo esc_attr($settings['added_button_text'] ?? __('Added!', 'woocommerce-page-builder')); ?>">
            <div class="wpb-products" id="wpb-grid-<?php echo esc_attr($grid_id); ?>" style="--wpb-cols-desktop: <?php echo esc_attr($desktop_columns); ?>; --wpb-cols-tablet: <?php echo esc_attr($tablet_columns); ?>; --wpb-cols-mobile: <?php echo esc_attr($mobile_columns); ?>;">
                <?php while ($products->have_posts()): $products->the_post(); ?>
                    <?php global $product; $product = wc_get_product(get_the_ID()); ?>
                    <div class="wpb-product">
                        <div class="wpb-product-image">
                            <a href="<?php the_permalink(); ?>">
                                <?php echo $product ? $product->get_image() : ''; ?>
                            </a>
                            <?php wpb_product_watermark_overlay(); ?>
                        <?php if ($settings['show_badge'] === 'yes' && !empty($settings['badge_text'])): ?>
                            <div class="wpb-product-badge">
                                <?php echo esc_html($settings['badge_text']); ?>
                            </div>
                        <?php endif; ?>
                        <?php if ($settings['auto_sale_badge'] === 'yes' && $product && $product->is_on_sale()): ?>
                            <div class="wpb-product-badge wpb-sale-badge">
                                <?php esc_html_e('Sale', 'woocommerce-page-builder'); ?>
                            </div>
                        <?php endif; ?>
                        <?php if ($settings['show_discount_percentage'] === 'yes' && $product && $product->is_on_sale()): ?>
                            <?php
                            $regular_price = $product->get_regular_price();
                            $sale_price = $product->get_sale_price();
                            if ($regular_price && $sale_price && $regular_price > $sale_price):
                                $discount_percentage = round((($regular_price - $sale_price) / $regular_price) * 100);
                            ?>
                                <div class="wpb-product-badge wpb-discount-badge">
                                    -<?php echo esc_html($discount_percentage); ?>%
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                            </div>
                            <div class="wpb-product-details">
                                <div class="wpb-product-content-wrapper">
                                    <?php if ($settings['show_title'] === 'yes'): ?>
                                        <h3 class="wpb-product-title">
                                            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                        </h3>
                                    <?php endif; ?>
                                    <?php if ($settings['show_price'] === 'yes' && $product): ?>
                                        <span class="wpb-product-price">
                                            <?php
                                            if ($product->is_type('variable')) {
                                                $min_price = $product->get_variation_price('min', true);
                                                $max_price = $product->get_variation_price('max', true);
                                                if ($min_price !== $max_price) {
                                                    echo wp_kses_post('<span class="wpb-price-from">' . __('From:', 'woocommerce-page-builder') . '</span> ' . wc_price($min_price));
                                                } else {
                                                    echo wc_price($min_price);
                                                }
                                            } else {
                                                echo $product->get_price_html();
                                            }
                                            ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                 <?php if ($settings['show_add_to_cart'] === 'yes' && $product): ?>
                                     <div class="wpb-product-add-to-cart">
                                         <?php if ($product->is_type('variable')): ?>
                                             <a href="<?php the_permalink(); ?>" class="wpb-add-to-cart button wpb-variation-button <?php echo $settings['button_full_width'] === 'yes' ? 'wpb-full-width' : ''; ?>">
                                                 <span class="wpb-button-text"><?php echo esc_html($settings['variable_button_text']); ?></span>
                                                 <span class="wpb-button-spinner" aria-hidden="true"></span>
                                             </a>
                                         <?php else: ?>
                                             <button class="wpb-add-to-cart button <?php echo $settings['button_full_width'] === 'yes' ? 'wpb-full-width' : ''; ?>" data-product-id="<?php echo esc_attr($product->get_id()); ?>">
                                                 <span class="wpb-button-text"><?php echo esc_html($settings['button_text']); ?></span>
                                                 <span class="wpb-button-spinner" aria-hidden="true"></span>
                                             </button>
                                         <?php endif; ?>
                                     </div>
                                 <?php endif; ?>
                            </div>
                    </div>
                <?php endwhile; wp_reset_postdata(); ?>
            </div>
        </div>
        <?php
    }

    protected function get_query_args($settings) {
        $query_args = [
            'post_type' => 'product',
            'posts_per_page' => isset($settings['posts_per_page']) ? (int) $settings['posts_per_page'] : 12,
            'post_status' => 'publish',
            'orderby' => isset($settings['order_by']) ? sanitize_text_field($settings['order_by']) : 'date',
            'order' => isset($settings['order']) ? sanitize_text_field($settings['order']) : 'desc',
        ];

        if (is_product_category() || is_shop()) {
            $term = get_queried_object();
            if ($term && !is_wp_error($term) && isset($term->term_id)) {
                $query_args['tax_query'] = [
                    [
                        'taxonomy' => 'product_cat',
                        'field' => 'term_id',
                        'terms' => [$term->term_id],
                    ],
                ];
            }
        }

        $tax_query = [];

        if ($settings['query_source'] === 'category' && !empty($settings['product_categories'])) {
            $tax_query[] = [
                'taxonomy' => 'product_cat',
                'field' => 'term_id',
                'terms' => array_map('intval', $settings['product_categories']),
            ];
        } elseif ($settings['query_source'] === 'tag' && !empty($settings['product_tags'])) {
            $tax_query[] = [
                'taxonomy' => 'product_tag',
                'field' => 'term_id',
                'terms' => array_map('intval', $settings['product_tags']),
            ];
        }

        if ($settings['query_source'] === 'manual' && !empty($settings['product_ids'])) {
            $query_args['post__in'] = array_map('intval', $settings['product_ids']);
            $query_args['posts_per_page'] = count($query_args['post__in']);
        }

        if (!empty($tax_query)) {
            $query_args['tax_query'] = $tax_query;
        }

        return $query_args;
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

    private function get_products() {
        $products = get_posts([
            'post_type' => 'product',
            'posts_per_page' => -1,
            'post_status' => 'publish',
        ]);
        $options = [];
        foreach ($products as $product) {
            $options[$product->ID] = $product->post_title;
        }
        return $options;
    }
}

<?php
if (!defined('ABSPATH')) exit;

class WPB_Product_Media_Widget extends \Elementor\Widget_Base {

    public function get_name() {
        return 'wpb-product-media';
    }

    public function get_title() {
        return __('Product Media', 'woocommerce-page-builder');
    }

    public function get_icon() {
        return 'eicon-image';
    }

    public function get_categories() {
        return ['wpb-woo-page-builder'];
    }

    public function get_keywords() {
        return ['product', 'media', 'image', 'video', 'gallery', 'woocommerce'];
    }

    public function get_custom_help_url() {
        return 'https://example.com/';
    }

    public function get_style_depends() {
        return ['wpb-product-media'];
    }

    public function get_script_depends() {
        return ['wpb-product-media'];
    }

    protected function register_controls() {
        $this->start_controls_section(
            'product_section',
            [
                'label' => __('Product', 'woocommerce-page-builder'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'source',
            [
                'label' => __('Source', 'woocommerce-page-builder'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'current',
                'options' => [
                    'current' => __('Current Product', 'woocommerce-page-builder'),
                    'manual' => __('Manual Selection', 'woocommerce-page-builder'),
                ],
            ]
        );

        $this->add_control(
            'product_id',
            [
                'label' => __('Select Product', 'woocommerce-page-builder'),
                'type' => \Elementor\Controls_Manager::SELECT2,
                'options' => $this->get_products(),
                'condition' => [
                    'source' => 'manual',
                ],
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'video_section',
            [
                'label' => __('Video', 'woocommerce-page-builder'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'video_url',
            [
                'label' => __('Video URL', 'woocommerce-page-builder'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => '',
                'description' => __('Enter YouTube, Vimeo, or direct MP4/WebM URL.', 'woocommerce-page-builder'),
            ]
        );

        $this->add_control(
            'video_autoplay',
            [
                'label' => __('Autoplay', 'woocommerce-page-builder'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'woocommerce-page-builder'),
                'label_off' => __('No', 'woocommerce-page-builder'),
                'return' => false,
                'default' => 'no',
            ]
        );

        $this->add_control(
            'video_muted',
            [
                'label' => __('Muted', 'woocommerce-page-builder'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'woocommerce-page-builder'),
                'label_off' => __('No', 'woocommerce-page-builder'),
                'return' => false,
                'default' => 'yes',
                'condition' => [
                    'video_autoplay' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'video_loop',
            [
                'label' => __('Loop', 'woocommerce-page-builder'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'woocommerce-page-builder'),
                'label_off' => __('No', 'woocommerce-page-builder'),
                'return' => false,
                'default' => 'no',
                'condition' => [
                    'video_autoplay' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'video_show_controls',
            [
                'label' => __('Show Controls', 'woocommerce-page-builder'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'woocommerce-page-builder'),
                'label_off' => __('No', 'woocommerce-page-builder'),
                'return' => false,
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'video_placeholder',
            [
                'label' => __('Placeholder Image', 'woocommerce-page-builder'),
                'type' => \Elementor\Controls_Manager::MEDIA,
                'default' => [
                    'url' => '',
                ],
                'description' => __('Show this image before the video plays.', 'woocommerce-page-builder'),
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'image_section',
            [
                'label' => __('Image', 'woocommerce-page-builder'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'image_size',
            [
                'label' => __('Image Size', 'woocommerce-page-builder'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'woocommerce_single',
                'options' => [
                    'woocommerce_thumbnail' => __('Thumbnail', 'woocommerce-page-builder'),
                    'woocommerce_single' => __('Single', 'woocommerce-page-builder'),
                    'medium' => __('Medium', 'woocommerce-page-builder'),
                    'large' => __('Large', 'woocommerce-page-builder'),
                    'full' => __('Full', 'woocommerce-page-builder'),
                ],
            ]
        );

        $this->add_control(
            'image_custom_size',
            [
                'label' => __('Custom Image Size', 'woocommerce-page-builder'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => '',
                'description' => __('Enter a custom image size name if your theme supports it.', 'woocommerce-page-builder'),
                'condition' => [
                    'image_size' => 'full',
                ],
            ]
        );

        $this->add_control(
            'show_gallery',
            [
                'label' => __('Show Gallery', 'woocommerce-page-builder'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'woocommerce-page-builder'),
                'label_off' => __('No', 'woocommerce-page-builder'),
                'return' => false,
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'gallery_columns',
            [
                'label' => __('Gallery Columns', 'woocommerce-page-builder'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 4,
                'options' => [
                    2 => __('2', 'woocommerce-page-builder'),
                    3 => __('3', 'woocommerce-page-builder'),
                    4 => __('4', 'woocommerce-page-builder'),
                    5 => __('5', 'woocommerce-page-builder'),
                    6 => __('6', 'woocommerce-page-builder'),
                ],
                'condition' => [
                    'show_gallery' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'gallery_click_action',
            [
                'label' => __('Gallery Click Action', 'woocommerce-page-builder'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'lightbox',
                'options' => [
                    'lightbox' => __('Lightbox', 'woocommerce-page-builder'),
                    'none' => __('None', 'woocommerce-page-builder'),
                ],
                'condition' => [
                    'show_gallery' => 'yes',
                ],
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
            'main_image_border_radius',
            [
                'label' => __('Main Image Border Radius', 'woocommerce-page-builder'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .wpb-product-media-main img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'gallery_gap',
            [
                'label' => __('Gallery Gap', 'woocommerce-page-builder'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'default' => [
                    'size' => 8,
                ],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 30,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .wpb-product-media-gallery' => 'gap: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'gallery_item_border_radius',
            [
                'label' => __('Gallery Item Border Radius', 'woocommerce-page-builder'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .wpb-product-media-gallery img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        $product = $this->get_product($settings);

        if (!$product) {
            echo '<p class="wpb-no-product">' . esc_html__('No product selected.', 'woocommerce-page-builder') . '</p>';
            return;
        }

        wp_enqueue_style('photoswipe');
        wp_enqueue_style('woocommerce-general');
        wp_enqueue_style('wpb-product-media', WPB_PLUGIN_URL . 'assets/css/product-media.css', [], WPB_VERSION);
        wp_enqueue_script('wpb-product-media', WPB_PLUGIN_URL . 'assets/js/product-media.js', [], WPB_VERSION, true);

        $main_image_id = $product->get_image_id();
        $gallery_ids = $product->get_gallery_image_ids();
        $video_url = !empty($settings['video_url']) ? esc_url_raw($settings['video_url']) : '';
        $placeholder_id = !empty($settings['video_placeholder']['id']) ? (int) $settings['video_placeholder']['id'] : 0;
        $placeholder_url = !empty($settings['video_placeholder']['url']) ? esc_url_raw($settings['video_placeholder']['url']) : '';

        $image_size = !empty($settings['image_custom_size']) ? sanitize_text_field($settings['image_custom_size']) : sanitize_text_field($settings['image_size']);

        $autoplay = $settings['video_autoplay'] === 'yes';
        $muted = $settings['video_muted'] === 'yes';
        $loop = $settings['video_loop'] === 'yes';
        $show_controls = $settings['video_show_controls'] === 'yes';

        $wrapper_classes = ['wpb-product-media'];
        if (!empty($gallery_ids) && $settings['show_gallery'] === 'yes') {
            $wrapper_classes[] = 'wpb-has-gallery';
        }
        ?>
        <div class="<?php echo esc_attr(implode(' ', $wrapper_classes)); ?>">
            <div class="wpb-product-media__viewport">
                <div class="wpb-product-media__container">
                    <?php if ($video_url): ?>
                        <div class="wpb-product-media__slide">
                            <?php echo $this->render_video($video_url, $autoplay, $muted, $loop, $show_controls, $placeholder_url, $placeholder_id, $image_size, $main_image_id); ?>
                        </div>
                    <?php elseif ($main_image_id): ?>
                        <div class="wpb-product-media__slide">
                            <?php echo wp_get_attachment_image($main_image_id, $image_size, false, ['class' => 'wpb-media-image']); ?>
                        </div>
                    <?php else: ?>
                        <div class="wpb-product-media__slide">
                            <img src="<?php echo esc_url(wc_placeholder_img_src()); ?>" alt="<?php echo esc_attr($product->get_name()); ?>" class="wpb-media-image" />
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($gallery_ids)): ?>
                        <?php foreach ($gallery_ids as $gallery_id): ?>
                            <div class="wpb-product-media__slide">
                                <?php echo wp_get_attachment_image($gallery_id, $image_size, false, ['class' => 'wpb-media-image']); ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            <?php if (!empty($gallery_ids) && $settings['show_gallery'] === 'yes'): ?>
                <div class="wpb-product-media__thumbs">
                    <div class="wpb-product-media__thumbs-list" style="display:flex;gap:8px;overflow-x:auto;padding-bottom:4px;scroll-behavior:smooth;-webkit-overflow-scrolling:touch;scrollbar-width:none;">
                        <?php if ($main_image_id): ?>
                            <button type="button" class="wpb-product-media__thumb wpb-product-media__thumb--active" data-index="0" style="flex:0 0 calc(25% - 6px);height:80px;padding:0;border:2px solid gray;border-radius:6px;background:#f8f9fa;cursor:pointer;overflow:hidden;">
                                <?php echo wp_get_attachment_image($main_image_id, 'thumbnail', false, ['class' => 'wpb-thumb-image']); ?>
                            </button>
                        <?php endif; ?>
                        <?php foreach ($gallery_ids as $index => $gallery_id): ?>
                            <button type="button" class="wpb-product-media__thumb" data-index="<?php echo esc_attr($index + 1); ?>" style="flex:0 0 calc(25% - 6px);height:80px;padding:0;border:2px solid gray;border-radius:6px;background:#f8f9fa;cursor:pointer;overflow:hidden;">
                                <?php echo wp_get_attachment_image($gallery_id, 'thumbnail', false, ['class' => 'wpb-thumb-image']); ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }

    protected function content_template() {
        ?>
        <div class="wpb-product-media">
            <div class="wpb-product-media__viewport">
                <div class="wpb-product-media__container">
                    <div class="wpb-product-media__slide">
                        <img src="<?php echo esc_url(wc_placeholder_img_src()); ?>" alt="<?php esc_attr_e('Product Image', 'woocommerce-page-builder'); ?>" class="wpb-media-image" />
                    </div>
                    <div class="wpb-product-media__slide">
                        <img src="<?php echo esc_url(wc_placeholder_img_src()); ?>" alt="<?php esc_attr_e('Product Image', 'woocommerce-page-builder'); ?>" class="wpb-media-image" />
                    </div>
                </div>
            </div>
            <div class="wpb-product-media__thumbs">
                <div class="wpb-product-media__thumbs-list">
                    <button type="button" class="wpb-product-media__thumb wpb-product-media__thumb--active" data-index="0">1</button>
                    <button type="button" class="wpb-product-media__thumb" data-index="1">2</button>
                </div>
            </div>
        </div>
        <?php
    }

    private function get_product($settings) {
        if ($settings['source'] === 'manual' && !empty($settings['product_id'])) {
            $product_id = (int) $settings['product_id'];
            return wc_get_product($product_id);
        }

        global $product;
        if ($product instanceof WC_Product) {
            return $product;
        }

        if (is_product()) {
            return wc_get_product(get_the_ID());
        }

        return null;
    }

    private function render_video($video_url, $autoplay, $muted, $loop, $show_controls, $placeholder_url, $placeholder_id, $image_size, $main_image_id) {
        $video_host = $this->get_video_host($video_url);
        $output = '<div class="wpb-product-media-video">';

        if ($placeholder_url || $placeholder_id) {
            $placeholder_img = $placeholder_id ? wp_get_attachment_image($placeholder_id, $image_size, false, ['class' => 'wpb-video-placeholder']) : '<img src="' . esc_url($placeholder_url) . '" alt="" class="wpb-video-placeholder" />';
            $output .= $placeholder_img;
        } elseif ($main_image_id) {
            $output .= wp_get_attachment_image($main_image_id, $image_size, false, ['class' => 'wpb-video-placeholder']);
        } else {
            $output .= '<img src="' . esc_url(wc_placeholder_img_src()) . '" alt="" class="wpb-video-placeholder" />';
        }

        if ($video_host === 'youtube') {
            $video_id = $this->get_youtube_id($video_url);
            $autoplay_param = $autoplay ? '1' : '0';
            $muted_param = $muted ? '1' : '0';
            $controls_param = $show_controls ? '1' : '0';
            $loop_param = $loop ? '1' : '0';
            $playlist_param = $loop ? '&playlist=' . esc_attr($video_id) : '';
            $src = 'https://www.youtube.com/embed/' . esc_attr($video_id) . '?autoplay=' . $autoplay_param . '&mute=' . $muted_param . '&controls=' . $controls_param . '&loop=' . $loop_param . $playlist_param . '&rel=0';
            $output .= '<iframe class="wpb-video-iframe" src="' . esc_url($src) . '" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>';
        } elseif ($video_host === 'vimeo') {
            $video_id = $this->get_vimeo_id($video_url);
            $autoplay_param = $autoplay ? '1' : '0';
            $muted_param = $muted ? '1' : '0';
            $controls_param = $show_controls ? '1' : '0';
            $loop_param = $loop ? '1' : '0';
            $src = 'https://player.vimeo.com/video/' . esc_attr($video_id) . '?autoplay=' . $autoplay_param . '&muted=' . $muted_param . '&controls=' . $controls_param . '&loop=' . $loop_param . '&title=0&byline=0&portrait=0';
            $output .= '<iframe class="wpb-video-iframe" src="' . esc_url($src) . '" frameborder="0" allow="autoplay; fullscreen" allowfullscreen></iframe>';
        } else {
            $output .= '<video class="wpb-video-native" src="' . esc_url($video_url) . '" ' . ($autoplay ? 'autoplay' : '') . ' ' . ($muted ? 'muted' : '') . ' ' . ($loop ? 'loop' : '') . ' ' . ($show_controls ? 'controls' : '') . ' playsinline></video>';
        }

        $output .= '</div>';
        return $output;
    }

    private function get_video_host($url) {
        $url = strtolower($url);
        if (strpos($url, 'youtube.com') !== false || strpos($url, 'youtu.be') !== false) {
            return 'youtube';
        }
        if (strpos($url, 'vimeo.com') !== false) {
            return 'vimeo';
        }
        return 'native';
    }

    private function get_youtube_id($url) {
        if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([a-zA-Z0-9_-]{11})/', $url, $matches)) {
            return $matches[1];
        }
        return '';
    }

    private function get_vimeo_id($url) {
        if (preg_match('/(?:vimeo\.com\/)(\d+)/', $url, $matches)) {
            return $matches[1];
        }
        return '';
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

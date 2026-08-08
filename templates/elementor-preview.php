<?php

defined('ABSPATH') || exit;

$template_id = isset($_GET['elementor-preview']) ? absint($_GET['elementor-preview']) : 0;

?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>
<body <?php body_class(['elementor-page', 'elementor-page-' . $template_id, 'dek-elementor-preview']); ?>>
<?php wp_body_open(); ?>

<main id="dek-elementor-preview" data-template-id="<?php echo esc_attr($template_id); ?>">
    <?php
    while (have_posts()) {
        the_post();
        the_content();
    }
    ?>
</main>

<?php wp_footer(); ?>
</body>
</html>

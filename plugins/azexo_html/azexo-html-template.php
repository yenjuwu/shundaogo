<!DOCTYPE html>
<html <?php language_attributes(); ?>>
    <head>
        <meta charset="<?php bloginfo('charset'); ?>">
        <?php wp_head(); ?>
    </head>
    <body <?php body_class(); ?>>  
        <?php while (have_posts()) : the_post(); ?>
            <div <?php post_class(); ?>>
                <?php the_content(); ?>
            </div><!-- #post -->
        <?php endwhile; ?>
        <?php wp_footer(); ?>
    </body>
</html>
<?php
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
    <!--<![endif]-->
    <head>
        <?php $options = get_option(AZEXO_FRAMEWORK); ?>
        <meta charset="<?php bloginfo('charset'); ?>">
        <meta name="viewport" content="width=device-width">
        <link rel="profile" href="http://gmpg.org/xfn/11">
        <link rel="pingback" href="<?php esc_url(bloginfo('pingback_url')); ?>">        
        <?php
        if (!function_exists('has_site_icon') || !has_site_icon()) {
            if (isset($options['favicon']['url']) && !empty($options['favicon']['url'])) {
                print '<link rel="shortcut icon" href="' . esc_url($options['favicon']['url']) . '" />';
            }
        }
        ?>
        <?php wp_head(); ?>
    </head>

    <body <?php body_class(); ?>>        
        <div id="preloader"><div id="status"></div></div>
        <div id="page" class="hfeed site">
            <header id="masthead" class="site-header clearfix">
                <?php
                get_sidebar('header');
                ?>                
                <div class="header-main clearfix">
                    <div class="header-parts <?php print ((isset($options['header_parts_fullwidth']) && $options['header_parts_fullwidth']) ? '' : 'container'); ?>">
                        <?php
                        if (isset($options['header'])) {
                            foreach ((array) $options['header'] as $part) {

                                $template_part = azexo_is_template_part_exists('template-parts/header', $part);
                                if (!empty($template_part)) {
                                    get_template_part('template-parts/header', $part);
                                } else {
                                    switch ($part) {
                                        case 'logo':
                                            ?>
                                            <a class="site-title" href="<?php print esc_url(home_url('/')); ?>" rel="home">
                                                <?php if (isset($options['logo']['url']) && !empty($options['logo']['url'])): ?>
                                                    <img src="<?php print esc_url($options['logo']['url']); ?>" alt="logo">
                                                <?php else: ?>
                                                    <span class="title"><?php print esc_html(get_bloginfo('name')); ?></span>
                                                <?php endif; ?>
                                            </a>
                                            <?php
                                            break;
                                        case 'search':
                                            azexo_get_search_form();
                                            break;
                                        case 'mobile_menu_button':
                                            ?>
                                            <div class="mobile-menu-button"><span><i class="fa fa-bars"></i></span></div>                    
                                            <?php
                                            break;
                                        case 'mobile_menu':
                                            ?><nav class="site-navigation mobile-menu"><?php
                                                        if (has_nav_menu('primary')) {
                                                            wp_nav_menu(array(
                                                                'theme_location' => 'primary',
                                                                'menu_class' => 'nav-menu',
                                                                'menu_id' => 'primary-menu-mobile',
                                                                'walker' => new AZEXO_Walker_Nav_Menu(),
                                                            ));
                                                        }
                                                        ?></nav><?php
                                            break;
                                        case 'primary_menu':
                                            ?><nav class="site-navigation primary-navigation"><?php
                                                if (has_nav_menu('primary')) {
                                                    wp_nav_menu(array(
                                                        'theme_location' => 'primary',
                                                        'menu_class' => 'nav-menu',
                                                        'menu_id' => 'primary-menu',
                                                        'walker' => new AZEXO_Walker_Nav_Menu(),
                                                    ));
                                                }
                                                ?></nav><?php
                                            break;
                                        case 'secondary_menu':
                                            ?><nav class="secondary-navigation"><?php
                                                if (has_nav_menu('secondary')) {
                                                    wp_nav_menu(array(
                                                        'theme_location' => 'secondary',
                                                        'menu_class' => 'nav-menu',
                                                        'menu_id' => 'secondary-menu',
                                                        'walker' => new AZEXO_Walker_Nav_Menu(),
                                                    ));
                                                }
                                                ?></nav><?php
                                            break;
                                        default:
                                            break;
                                    }
                                }
                            }
                        }
                        ?>                        
                    </div>
                </div>
                <?php
                global $post;
                get_sidebar('middle');
                //echo azexo_build_vendor_info($post);
                ?> 
           
            </header><!-- #masthead -->
            <div id="main" class="site-main">

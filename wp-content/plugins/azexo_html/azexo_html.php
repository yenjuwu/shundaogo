<?php
/*
  Plugin Name: AZEXO HTML customizer
  Plugin URI: http://azexo.com
  Description: AZEXO HTML customizer
  Author: azexo
  Author URI: http://azexo.com
  Version: 1.23
  Text Domain: azh
 */

define('AZH_VERSION', '1.22');
define('AZH_URL', plugins_url('', __FILE__));
define('AZH_DIR', trailingslashit(dirname(__FILE__)) . '/');

global $azh_shortcodes;
$azh_shortcodes = array();

include_once(AZH_DIR . 'icons.php' );
if (is_admin()) {
    include_once(AZH_DIR . 'envato/updater.php' );
    include_once(AZH_DIR . 'settings.php' );
    include_once(AZH_DIR . 'customizer.php' );
}

add_action('plugins_loaded', 'azh_plugins_loaded');

function azh_plugins_loaded() {
    load_plugin_textdomain('azh', FALSE, basename(dirname(__FILE__)) . '/languages/');
    add_action('add_meta_boxes', 'azh_add_meta_boxes', 10, 2);
    $settings = get_option('azh-settings');
    $icon_types = array('fontawesome', 'openiconic', 'typicons', 'entypo', 'linecons', 'monosocial');
    global $azh_icons, $azh_icons_index;
    $azh_icons = array();
    $azh_icons_index = array();
    foreach ($icon_types as $icon_type) {
        $azh_icons[$icon_type] = array();
        $arr1 = apply_filters('azh_icon-type-' . $icon_type, array());
        foreach ($arr1 as $arr2) {
            if (is_array($arr2)) {
                if (count($arr2) == 1) {
                    reset($arr2);
                    $azh_icons[$icon_type][key($arr2)] = current($arr2);
                    $azh_icons_index[key($arr2)] = $icon_type;
                } else {
                    foreach ($arr2 as $arr3) {
                        if (count($arr3) == 1) {
                            reset($arr3);
                            $azh_icons[$icon_type][key($arr3)] = current($arr3);
                            $azh_icons_index[key($arr3)] = $icon_type;
                        }
                    }
                }
            }
        }
    }
    $custom_icons = explode("\n", trim($settings['custom-icons-classes']));
    if (count($custom_icons) <= 1) {
        $custom_icons = explode(" ", trim($settings['custom-icons-classes']));
    }
    $azh_icons['custom'] = array_combine($custom_icons, $custom_icons);
    $azh_icons_index = array_merge($azh_icons_index, array_combine($custom_icons, array_fill(0, count($custom_icons), 'custom')));


    $azh_widgets = get_option('azh_widgets');
    if (is_array($azh_widgets) || empty($azh_widgets)) {
        update_post_cache($azh_widgets);
    } else {
        $azh_widgets = get_posts(array(
            'post_type' => 'azh_widget',
            'posts_per_page' => '-1',
        ));
        update_option('azh_widgets', $azh_widgets);
        update_post_cache($azh_widgets);
    }
}

add_action('save_post', 'azh_save_post', 10, 3);

function azh_save_post($post_ID, $post, $update) {
    if ($post->post_type == 'azh_widget') {
        $azh_widgets = get_option('azh_widgets');
        if (!is_array($azh_widgets)) {
            $azh_widgets = array();
        }
        $azh_widgets[$post_ID] = $post;
        update_option('azh_widgets', $azh_widgets);
        update_post_cache($azh_widgets);
    }
}

function azh_editor_scripts() {
    wp_enqueue_style('azh_admin', plugins_url('css/admin.css', __FILE__));
    wp_enqueue_script('azh_admin', plugins_url('js/admin.js', __FILE__), array('azexo_html'), false, true);

    wp_enqueue_script('jquery-ui-sortable');
    wp_enqueue_style('azexo_html', plugins_url('css/azexo_html.css', __FILE__));
    wp_enqueue_script('azexo_html', plugins_url('js/azexo_html.js', __FILE__), array('underscore', 'azh_htmlparser', 'jquery-ui-dialog', 'jquery-ui-tabs', 'jquery-ui-sortable', 'jquery-ui-autocomplete'), false, true);
    wp_enqueue_style('jquery-ui', '//ajax.googleapis.com/ajax/libs/jqueryui/1.8.21/themes/smoothness/jquery-ui.css', false, false, false);
    wp_enqueue_script('azh_htmlparser', plugins_url('js/htmlparser.js', __FILE__), false, true);
    wp_enqueue_script('azh_ace', plugins_url('js/ace/ace.js', __FILE__), false, true);

    $empty_html = esc_html__('Please switch to HTML and input content', 'azh');
    $dirs = apply_filters('azh_directory', array_combine(array(get_template_directory() . '/azh'), array(get_template_directory_uri() . '/azh')));
    if (is_array($dirs)) {
        foreach ($dirs as $dir => $uri) {
            if (is_dir($dir)) {
                $empty_html = esc_html__('Please add new sections from "AZEXO HTML" metabox in right sidebar', 'azh');
                break;
            }
        }
    }
    global $azh_shortcodes;
    $settings = get_option('azh-settings');
    $patterns = $settings['patterns'];
    $patterns = preg_replace("/\r\n/", "\n", $patterns);
    $properies = explode("\n\n", $patterns);
    $patterns = array();
    foreach ($properies as $propery) {
        $propery = explode("\n", $propery);
        $patterns[$propery[0]] = array();
        for ($i = 1; $i < count($propery); $i++) {
            $patterns[$propery[0]][] = $propery[$i];
        }
    }
    wp_localize_script('azh_admin', 'azh', array(
        'options' => apply_filters('azh_options', $patterns),
        'empty_html' => $empty_html,
        'icons' => apply_filters('azh_icons', array()),
        'shortcodes' => $azh_shortcodes,
        'edit_text' => esc_html__('Edit', 'azh'),
        'clear' => esc_html__('Clear', 'azh'),
        'clone' => esc_html__('Clone', 'azh'),
        'copy' => esc_html__('Copy', 'azh'),
        'paste' => esc_html__('Paste', 'azh'),
        'move' => esc_html__('Move', 'azh'),
        'done' => esc_html__('Done', 'azh'),
        'add' => esc_html__('Add', 'azh'),
        'remove' => esc_html__('Remove', 'azh'),
        'set' => esc_html__('Set', 'azh'),
        'title' => esc_html__('Title', 'azh'),
        'url' => esc_html__('URL', 'azh'),
        'remove' => esc_html__('Remove', 'azh'),
        'select_url' => esc_html__('Select URL', 'azh'),
        'switch_to_html' => esc_html__('Switch to html', 'azh'),
        'switch_to_customizer' => esc_html__('Switch to customizer', 'azh'),
        'control_description' => esc_html__('Control description', 'azh'),
        'description' => esc_html__('Description', 'azh'),
    ));
}

function azh_add_meta_boxes($post_type, $post) {
    $dirs = apply_filters('azh_directory', array_combine(array(get_template_directory() . '/azh'), array(get_template_directory_uri() . '/azh')));
    if (is_array($dirs)) {
        foreach ($dirs as $dir => $uri) {
            if (is_dir($dir) && in_array($post_type, array('page', 'azh_widget'))) {
                add_meta_box('azh', __('AZEXO HTML', 'azh'), 'azh_meta_box', $post_type, 'side', 'default');
                break;
            }
        }
    }
    if (apply_filters('azh_load', in_array($post_type, array('page', 'azh_widget')), $post_type, $post)) {
        azh_editor_scripts();
    }
}

function azh_meta_box($post = NULL, $metabox = NULL, $post_type = 'page') {
    if (!is_null($post)) {
        $post_type = get_post_type($post);
    }
    $elements = array();
    $elements_uri = array();
    $elements_categories = array();
    $sections = array();
    $sections_uri = array();
    $sections_categories = array();
    $dirs = apply_filters('azh_directory', array_combine(array(get_template_directory() . '/azh'), array(get_template_directory_uri() . '/azh')));
    if (is_array($dirs)) {
        foreach ($dirs as $dir => $uri) {
            if (is_dir($dir)) {
                $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir), RecursiveIteratorIterator::SELF_FIRST);
                foreach ($iterator as $fileInfo) {
                    if ($fileInfo->isFile() && $fileInfo->getExtension() == 'html') {
                        $sections[$fileInfo->getPathname()] = $fileInfo->getFilename();
                        $sections_uri[$fileInfo->getPathname()] = $uri;
                        $sections_categories[trim(str_replace($dir, '', $fileInfo->getPath()), '/')] = true;
                    }
                    if ($fileInfo->isFile() && $fileInfo->getExtension() == 'htm') {
                        $elements[$fileInfo->getPathname()] = $fileInfo->getFilename();
                        $elements_uri[$fileInfo->getPathname()] = $uri;
                        $elements_categories[trim(str_replace($dir, '', $fileInfo->getPath()), '/')] = true;
                    }
                }
            }
        }
    }
    ?>
    <?php if ($post_type != 'azh_widget'): ?>
        <div class="azh-structure" style="max-height: 600px;"></div>
        <a href="#" class="azh-add-section" data-open="<?php esc_html_e('Add section', 'azh') ?>" data-close="<?php esc_html_e('Close library', 'azh') ?>"><?php esc_html_e('Add section', 'azh') ?></a>
    <?php endif; ?>
    <div class="azh-library" style="display: none;">
        <?php if ($post_type != 'azh_widget'): ?>
            <select class="azh-categories">
                <option value=""><?php esc_html_e('Show All', 'azh') ?></option>
                <?php
                foreach ($sections_categories as $category => $flag) {
                    ?>
                    <option value="<?php print esc_attr($category) ?>"><?php print esc_html($category) ?></option>
                    <?php
                }
                ?>
            </select>
            <div class="azh-sections">
                <?php
                foreach ($sections as $path => $name) {
                    $preview = str_replace('.html', '.jpg', $path);
                    $url = str_replace($dir, $sections_uri[$path], $path);
                    if (file_exists($preview)) {
                        $preview = str_replace($dir, $sections_uri[$path], $preview);
                        ?><div class="azh-section" data-url="<?php print esc_attr($url); ?>" data-path="<?php print esc_attr(ltrim(str_replace($dir, '', $path), '/')) ?>" data-dir-uri="<?php print esc_attr($sections_uri[$path]) ?>"  style="background-image: url('<?php print esc_attr($preview); ?>');"></div><?php
                    } else {
                        ?><div class="azh-section no-image" data-url="<?php print esc_attr($url); ?>" data-path="<?php print esc_attr(ltrim(str_replace($dir, '', $path), '/')) ?>" data-dir-uri="<?php print esc_attr($sections_uri[$path]) ?>"><?php print esc_html($name) ?></div><?php
                    }
                }
                ?>        
            </div>
        <?php endif; ?>
        <div class="azh-elements" style="display: none;">            
            <select class="azh-categories">
                <option value=""><?php esc_html_e('Show All', 'azh') ?></option>
                <?php
                foreach ($elements_categories as $category => $flag) {
                    ?>
                    <option value="<?php print esc_attr($category) ?>"><?php print esc_html($category) ?></option>
                    <?php
                }
                ?>
            </select>
            <?php
            foreach ($elements as $path => $name) {
                $preview = str_replace('.htm', '.jpg', $path);
                $url = str_replace($dir, $elements_uri[$path], $path);
                if (file_exists($preview)) {
                    $preview = str_replace($dir, $elements_uri[$path], $preview);
                    ?><div class="azh-element" data-url="<?php print esc_attr($url); ?>" data-path="<?php print esc_attr(ltrim(str_replace($dir, '', $path), '/')) ?>"  data-dir-uri="<?php print esc_attr($elements_uri[$path]) ?>" style="background-image: url('<?php print esc_attr($preview); ?>');"></div><?php
                } else {
                    ?><div class="azh-element no-image" data-url="<?php print esc_attr($url); ?>" data-path="<?php print esc_attr(ltrim(str_replace($dir, '', $path), '/')) ?>" data-dir-uri="<?php print esc_attr($elements_uri[$path]) ?>"><h4><?php print esc_html($name) ?></h4></div><?php
                }
            }
            ?>        
        </div>
    </div>
    <?php
}

add_action('admin_enqueue_scripts', 'azh_admin_scripts');

function azh_admin_scripts() {
    wp_enqueue_style('font-awesome', plugins_url('css/font-awesome/css/font-awesome.min.css', __FILE__), array());
    wp_enqueue_style('az_typicons', plugins_url('css/typicons/src/font/typicons.min.css', __FILE__), array());
    wp_enqueue_style('az_openiconic', plugins_url('css/az-open-iconic/az_openiconic.min.css', __FILE__), array());
    wp_enqueue_style('az_linecons', plugins_url('css/az-linecons/az_linecons_icons.min.css', __FILE__), array());
    wp_enqueue_style('az_entypo', plugins_url('css/az-entypo/az_entypo.min.css', __FILE__), array());
    wp_enqueue_style('az_monosocialiconsfont', plugins_url('css/monosocialiconsfont/monosocialiconsfont.min.css', __FILE__), array());
    $settings = get_option('azh-settings');
    if (!empty($settings['custom-icons-css'])) {
        wp_enqueue_style('az_custom_icons', apply_filters('azh_uri', get_template_directory_uri() . '/azh') . '/' . $settings['custom-icons-css'], array());
    }
}

add_filter('azh_icons', 'azh_icons');

function azh_icons($icons) {
    global $azh_icons;
    return array_merge($azh_icons, $icons);
}

function azh_icon_font_enqueue($font) {
    switch ($font) {
        case 'fontawesome':
            wp_enqueue_style('font-awesome', plugins_url('css/font-awesome/css/font-awesome.min.css', __FILE__), array());
            break;
        case 'openiconic':
            wp_enqueue_style('az_openiconic', plugins_url('css/az-open-iconic/az_openiconic.min.css', __FILE__), array());
            break;
        case 'typicons':
            wp_enqueue_style('az_typicons', plugins_url('css/typicons/src/font/typicons.min.css', __FILE__), array());
            break;
        case 'entypo':
            wp_enqueue_style('az_entypo', plugins_url('css/az-entypo/az_entypo.min.css', __FILE__), array());
            break;
        case 'linecons':
            wp_enqueue_style('az_linecons', plugins_url('css/az-linecons/az_linecons_icons.min.css', __FILE__), array());
            break;
        case 'monosocial':
            wp_enqueue_style('az_monosocialiconsfont', plugins_url('css/monosocialiconsfont/monosocialiconsfont.min.css', __FILE__), array());
            break;
        case 'custom':
            $settings = get_option('azh-settings');
            if (!empty($settings['custom-icons-css'])) {
                wp_enqueue_style('az_custom_icons', apply_filters('azh_uri', get_template_directory_uri() . '/azh') . '/' . $settings['custom-icons-css'], array());
            }
            break;
        default:
            do_action('azh_icon_font_enqueue', $font);
    }
}

function azh_content_begin() {
    global $azh_widget_nested;
    if (!isset($azh_widget_nested)) {
        $azh_widget_nested = 0;
    }
    if (is_page() && get_post_meta(get_the_ID(), 'azh', true)) {
        remove_filter('the_content', 'wpautop');
    }
    if (get_post_type() == 'azh_widget') {
        $azh_widget_nested++;
        if ($azh_widget_nested == 1) {
            remove_filter('the_content', 'wptexturize');
            remove_filter('the_content', 'convert_smilies', 20);
            remove_filter('the_content', 'wpautop');
            remove_filter('the_content', 'shortcode_unautop');
            remove_filter('the_content', 'prepend_attachment');
            remove_filter('the_content', 'wp_make_content_images_responsive');

            remove_filter('the_content', 'do_shortcode', 11);
            add_filter('the_content', 'azh_do_shortcode', 11);
        }
    } else {
        
    }
}

function azh_content_end() {
    global $azh_widget_nested;
    if (is_page() && get_post_meta(get_the_ID(), 'azh', true)) {
        add_filter('the_content', 'wpautop');
    }
    if (get_post_type() == 'azh_widget') {
        $azh_widget_nested--;
        if ($azh_widget_nested == 0) {
            add_filter('the_content', 'wptexturize');
            add_filter('the_content', 'convert_smilies', 20);
            add_filter('the_content', 'wpautop');
            add_filter('the_content', 'shortcode_unautop');
            add_filter('the_content', 'prepend_attachment');
            add_filter('the_content', 'wp_make_content_images_responsive');

            remove_filter('the_content', 'azh_do_shortcode', 11);
            add_filter('the_content', 'do_shortcode', 11);
        } else {
            
        }
    }
}

add_filter('the_content', 'azh_the_content', 9);

function azh_the_content($content) {

    azh_content_begin();

    if (get_post_type() == 'azh_widget' || (is_page() && get_post_meta(get_the_ID(), 'azh', true))) {

        if (preg_match('/carousel-wrapper/', $content)) {
            wp_enqueue_script('owl.carousel');
            wp_enqueue_style('owl.carousel');
        }
        if (preg_match('/(image-popup|iframe-popup)/', $content)) {
            wp_enqueue_script('magnific-popup');
            wp_enqueue_style('magnific-popup');
        }
        if (preg_match('/data-sr/', $content)) {
            wp_enqueue_script('scrollReveal');
        }
        if (preg_match('/masonry/', $content)) {
            wp_enqueue_script('masonry');
        }
        if (preg_match('/azexo-tabs/', $content)) {
            wp_enqueue_script('jquery-ui-tabs');
        }
        if (preg_match('/azexo-accordion/', $content)) {
            wp_enqueue_script('jquery-ui-accordion');
        }
    }

    $replaces = array(
        'azh-uri' => apply_filters('azh_uri', get_template_directory_uri() . '/azh'),
    );
    $replaces = apply_filters('azh_replaces', $replaces);
    $content = preg_replace_callback('#{{([^}]+)}}#', function($m) use ($replaces) {
        if (isset($replaces[$m[1]])) { // If it exists in our array            
            return $replaces[$m[1]]; // Then replace it from our array
        } else {
            return $m[0]; // Otherwise return the whole match (basically we won't change it)
        }
    }, $content);

    $content = preg_replace_callback('#\[\[([^\]]+)\]\]#', function($m) {
        return '';
    }, $content);

    global $azh_icons, $azh_icons_index;
    foreach ($azh_icons as $icon_type => $icons) {
        $pattern = '/' . implode('|', array_keys($icons)) . '/';
        if (preg_match($pattern, $content, $matches)) {
            azh_icon_font_enqueue($azh_icons_index[$matches[0]]);
        }
    }
    return $content;
}

add_filter('the_content', 'azh_the_content_last', 100);

function azh_the_content_last($content) {
    azh_content_end();
    return $content;
}

add_action('widgets_init', 'azh_widgets_register_widgets');

function azh_widgets_register_widgets() {
    register_widget('AZH_Widget');
}

class AZH_Widget extends WP_Widget {

    function __construct() {
        parent::__construct('azh_widget', __('AZEXO - HTML Widget', 'azh'));
    }

    function widget($args, $instance) {

        $title = apply_filters('widget_title', empty($instance['title']) ? '' : $instance['title'], $instance, $this->id_base);

        print $args['before_widget'];
        if ($title) {
            print $args['before_title'] . $title . $args['after_title'];
        }

        if (!empty($instance['post'])) {
            $wpautop = false;
            if (has_filter('the_content', 'wpautop')) {
                remove_filter('the_content', 'wpautop');
                $wpautop = true;
            }

            if ($instance['post'] == NULL) {
                print apply_filters('the_content', get_the_content(''));
            } else {
                global $post, $wp_query;
                $original = $post;
                $post = get_post($instance['post']);
                setup_postdata($post);
                print apply_filters('the_content', get_the_content(''));
                $wp_query->post = $original;
                wp_reset_postdata();
            }

            if ($wpautop) {
                add_filter('the_content', 'wpautop');
            }
        }

        print $args['after_widget'];
    }

    function form($instance) {
        $defaults = array('post' => '', 'title' => '');
        $instance = wp_parse_args((array) $instance, $defaults);


        $azh_widgets = array();
        $loop = new WP_Query(array(
            'post_type' => 'azh_widget',
            'post_status' => 'publish',
            'showposts' => 100,
            'orderby' => 'title',
            'order' => 'ASC',
        ));
        if ($loop->have_posts()) {
            global $post, $wp_query;
            $original = $post;
            while ($loop->have_posts()) {
                $loop->the_post();
                $azh_widgets[] = $post;
            }
            $wp_query->post = $original;
            wp_reset_postdata();
        }
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'azh'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $instance['title']; ?>" />
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('post'); ?>"><?php _e('AZH Widget:', 'azh'); ?></label>
            <select class="widefat" id="<?php echo $this->get_field_id('post'); ?>" name="<?php echo $this->get_field_name('post'); ?>">
                <?php
                foreach ($azh_widgets as $azh_widget) :
                    ?>
                    <option value="<?php echo $azh_widget->ID ?>" <?php selected($azh_widget->ID, $instance['post']) ?>><?php echo $azh_widget->post_title; ?></option>
                <?php endforeach; ?>
            </select>
        </p>        
        <?php
    }

}

add_action('init', 'azh_widgets_register');

function azh_widgets_register() {
    register_post_type('azh_widget', array(
        'labels' => array(
            'name' => __('AZH Widget', 'azh'),
            'singular_name' => __('AZH Widget', 'azh'),
            'add_new' => _x('Add AZH Widget', 'azh'),
            'add_new_item' => _x('Add New AZH Widget', 'azh'),
            'edit_item' => _x('Edit AZH Widget', 'azh'),
            'new_item' => _x('New AZH Widget', 'azh'),
            'view_item' => _x('View AZH Widget', 'azh'),
            'search_items' => _x('Search AZH Widgets', 'azh'),
            'not_found' => _x('No AZH Widget found', 'azh'),
            'not_found_in_trash' => _x('No AZH Widget found in Trash', 'azh'),
            'parent_item_colon' => _x('Parent AZH Widget:', 'azh'),
            'menu_name' => _x('AZH Widgets', 'azh'),
        ),
        'query_var' => false,
        'rewrite' => true,
        'hierarchical' => true,
        'supports' => array('title', 'editor', 'revisions', 'thumbnail', 'custom-fields'),
        'show_ui' => true,
        'show_in_nav_menus' => true,
        'show_in_menu' => true,
        'public' => false,
        'exclude_from_search' => true,
        'publicly_queryable' => false,
            )
    );
    register_taxonomy('widget_type', array('azh_widget'), array(
        'label' => __('Widget type', 'azh'),
        'hierarchical' => true,
    ));
}

add_action('wp_enqueue_scripts', 'azh_scripts');

function azh_scripts() {
    $user = wp_get_current_user();
    if (in_array('administrator', (array) $user->roles)) {
        $edit_links = array();
        $azh_widgets_edit = array();
        global $wp_widget_factory;
        foreach ($wp_widget_factory->widgets as $name => $widget_obj) {
            if ($name == 'AZH_Widget') {
                $instances = $widget_obj->get_settings();
                foreach ($instances as $number => $instance) {
                    if (isset($instance['post']) && is_numeric($instance['post'])) {
                        $post = get_post($instance['post']);
                        if ($post) {
                            $azh_widgets_edit['#' . $widget_obj->id_base . '-' . $number] = get_edit_post_link($post);
                        }
                    }
                }
            }
        }
        $edit_links['azh_widgets'] = array(
            'links' => $azh_widgets_edit,
            'text' => esc_html__('Edit AZH Widget', 'vc_widgets'),
            'target' => '_blank',
        );
        if (is_page() && get_post_meta(get_the_ID(), 'azh', true)) {
            $sections_edit = array();
            $post = get_post();
            preg_match_all('/ data-section=[\'"]([^\'"]+)[\'"]/i', $post->post_content, $matches);
            if (is_array($matches)) {
                $post_type_object = get_post_type_object('page');
                foreach ($matches[1] as $match) {
                    $sections_edit['[data-section="' . $match . '"]'] = esc_url(admin_url(sprintf($post_type_object->_edit_link . '&action=edit&section=' . $match, get_the_ID())));
                }
            }
            $edit_links['sections'] = array(
                'links' => $sections_edit,
                'text' => esc_html__('Edit section', 'vc_widgets'),
                'target' => '_self',
            );
        }
        wp_enqueue_script('azh_admin_frontend', plugins_url('js/admin-frontend.js', __FILE__), array('jquery', 'underscore'), false, true);
        wp_localize_script('azh_admin_frontend', 'azh', array(
            'edit_links' => $edit_links,
        ));
    }
    wp_enqueue_script('azh_frontend', plugins_url('js/frontend.js', __FILE__), array('jquery'), false, true);
}

add_filter('post_type_link', 'azh_post_link', 10, 3);

function azh_post_link($permalink, $post, $leavename) {
    if (in_array($post->post_type, array('azh_widget'))) {
        $external_url = get_post_meta($post->ID, 'external_url', true);
        if (!empty($external_url)) {
            return $external_url;
        }
    }
    return $permalink;
}

function azh_group_label_order($a, $b) {
    if ($a['group'] < $b['group']) {
        return -1;
    } else {
        if ($a['group'] > $b['group']) {
            return 1;
        } else {
            if ($a['label'] < $b['label']) {
                return -1;
            } else {
                if ($a['label'] > $b['label']) {
                    return 1;
                } else {
                    return 0;
                }
            }
        }
    }
}

function azh_get_terms_labels() {
    $include = array_filter(explode(',', sanitize_text_field($_POST['values'])));
    if (empty($include)) {
        $include = array(0);
    }
    $data = array();
    $terms = get_terms(array(
        'hide_empty' => false,
        'include' => $include,
    ));
    if (is_array($terms) && !empty($terms)) {
        foreach ($terms as $term) {
            if (is_object($term)) {
                $data[$term->term_id] = $term->name;
            }
        }
    }
    print json_encode($data);
}

function azh_get_posts_labels() {
    $include = array_filter(explode(',', sanitize_text_field($_POST['values'])));
    if (empty($include)) {
        $include = array(0);
    }
    $data = array();
    $posts = get_posts(array(
        'post_type' => 'any',
        'include' => $include,
    ));
    if (is_array($posts) && !empty($posts)) {
        foreach ($posts as $post) {
            if (is_object($post)) {
                $data[$post->ID] = $post->post_title;
            }
        }
    }
    print json_encode($data);
}

add_action('wp_ajax_azh_autocomplete_labels', 'wp_ajax_azh_autocomplete_labels');

function wp_ajax_azh_autocomplete_labels() {
    if (isset($_POST['shortcode']) && isset($_POST['param_name']) && isset($_POST['values'])) {
        do_action('azh_autocomplete_' . sanitize_text_field($_POST['shortcode']) . '_' . sanitize_text_field($_POST['param_name']) . '_labels');
    }
    wp_die();
}

function azh_search_terms() {
    $data = array();
    $taxonomies_types = get_taxonomies(array('public' => true), 'objects');
    $exclude = array();
    if (isset($_POST['exclude'])) {
        $exclude = array_filter(explode(',', sanitize_text_field($_POST['exclude'])));
    }
    $terms = get_terms(array_keys($taxonomies_types), array(
        'hide_empty' => false,
        'exclude' => $exclude,
        'search' => sanitize_text_field($_POST['search']),
    ));
    if (is_array($terms) && !empty($terms)) {
        foreach ($terms as $term) {
            if (is_object($term)) {
                $data[] = array(
                    'label' => $term->name,
                    'value' => $term->term_id,
                    'group' => isset($taxonomies_types[$term->taxonomy], $taxonomies_types[$term->taxonomy]->labels, $taxonomies_types[$term->taxonomy]->labels->name) ? $taxonomies_types[$term->taxonomy]->labels->name : __('Taxonomies', 'azh'),
                );
            }
        }
    }
    usort($data, 'azh_group_label_order');
    print json_encode($data);
}

function azh_search_posts() {
    $data = array();
    $post_types = get_post_types(array('public' => true), 'objects');
    $exclude = array();
    if (isset($_POST['exclude'])) {
        $exclude = array_filter(explode(',', sanitize_text_field($_POST['exclude'])));
    }

    $posts = get_posts(array(
        'post_type' => array_keys($post_types),
        'exclude' => $exclude,
        's' => sanitize_text_field($_POST['search']),
    ));
    if (is_array($posts) && !empty($posts)) {
        foreach ($posts as $post) {
            if (is_object($post)) {
                $data[] = array(
                    'label' => $post->post_title,
                    'value' => $post->ID,
                    'group' => isset($post_types[$post->post_type], $post_types[$post->post_type]->labels, $post_types[$post->post_type]->labels->name) ? $post_types[$post->post_type]->labels->name : __('Posts', 'azh'),
                );
            }
        }
    }
    usort($data, 'azh_group_label_order');
    print json_encode($data);
}

add_action('wp_ajax_azh_autocomplete', 'wp_ajax_azh_autocomplete');

function wp_ajax_azh_autocomplete() {
    if (isset($_POST['shortcode']) && isset($_POST['param_name']) && isset($_POST['search'])) {

        do_action('azh_autocomplete_' . sanitize_text_field($_POST['shortcode']) . '_' . sanitize_text_field($_POST['param_name']));
    }
    wp_die();
}

function azh_get_attributes($tag, $atts) {
    global $azh_shortcodes;
    if (isset($azh_shortcodes)) {
        if ($tag && isset($azh_shortcodes[$tag])) {
            $settings = $azh_shortcodes[$tag];
            if (isset($settings['params']) && !empty($settings['params'])) {
                foreach ($settings['params'] as $param) {
                    if (!isset($atts[$param['param_name']]) && isset($param['value'])) {
                        $atts[$param['param_name']] = $param['value'];
                        if (is_array($atts[$param['param_name']])) {
                            $atts[$param['param_name']] = current($atts[$param['param_name']]);
                        }
                    }
                }
            }
        }
    }
    return $atts;
}

function azh_shortcode($atts, $content = null, $tag = null) {
    global $azh_shortcodes;
    if (isset($azh_shortcodes)) {
        if ($tag && isset($azh_shortcodes[$tag])) {
            $atts = azh_get_attributes($tag, $atts);
            if (isset($azh_shortcodes[$tag]['html_template']) && file_exists($azh_shortcodes[$tag]['html_template'])) {
                ob_start();
                include($azh_shortcodes[$tag]['html_template']);
                return ob_get_clean();
            } else {
                $located = locate_template('vc_templates' . '/' . $tag . '.php');
                if ($located) {
                    ob_start();
                    include($located);
                    return ob_get_clean();
                }
            }
        }
    }
}

function azh_add_element($settings, $func = false) {
    global $azh_shortcodes;
    if (isset($settings['base'])) {
        $azh_shortcodes[$settings['base']] = $settings;
        if (!shortcode_exists($settings['base'])) {
            if ($func) {
                add_shortcode($settings['base'], $func);
            } else {
                add_shortcode($settings['base'], 'azh_shortcode');
            }
        }
    }
}

function azexo_get_dir_files($src) {
    $files = array();
    $dir = opendir($src);
    if (is_resource($dir))
        while (false !== ( $file = readdir($dir))) {
            if (( $file != '.' ) && ( $file != '..' )) {
                $files[$file] = realpath($src . DIRECTORY_SEPARATOR . $file);
            }
        }
    closedir($dir);
    return $files;
}

function azexo_get_skins() {
    $skins = array();
    $files = azexo_get_dir_files(get_template_directory() . '/less');
    foreach ($files as $name => $path) {
        if (is_dir($path)) {
            $skin_files = azexo_get_dir_files($path);
            if (isset($skin_files['skin.less'])) {
                $skins[] = $name;
            }
        }
    }
    return $skins;
}

add_action('wp_ajax_azh_copy', 'wp_ajax_azh_copy');

function wp_ajax_azh_copy() {
    if (isset($_POST['code'])) {
        update_option('azh_clipboard', stripslashes($_POST['code']));
    }
    wp_die();
}

add_action('wp_ajax_azh_paste', 'wp_ajax_azh_paste');

function wp_ajax_azh_paste() {
    print get_option('azh_clipboard');
    wp_die();
}

azh_add_element(array(
    "name" => esc_html__('Text', 'azh'),
    "base" => "azh_text",
    "show_settings_on_create" => true,
    'params' => array(
        array(
            'type' => 'textarea_html',
            'heading' => esc_html__('Content', 'azh'),
            'holder' => 'div',
            'param_name' => 'content',
            'value' => wp_kses(__('<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut elit tellus, luctus nec ullamcorper mattis, pulvinar dapibus leo.</p>', 'azh'), array('p'))
        ),
    ),
        ), 'azh_text');

function azh_text($atts, $content = null, $tag = null) {
    return do_shortcode(shortcode_unautop($content));
}

add_action('wp_ajax_azh_get_wp_editor', 'azh_get_wp_editor');

function azh_get_wp_editor() {
    ob_start();
    wp_editor('', $_POST['id'], array(
        'dfw' => false,
        'media_buttons' => true,
        'tabfocus_elements' => 'insert-media-button',
        'editor_height' => 360,
        'wpautop' => false,
        'drag_drop_upload' => true,
    ));
    $editor = ob_get_contents();
    ob_end_clean();
    print $editor;
    die();
}

add_action('admin_bar_menu', 'azh_admin_bar_menu', 999);

function azh_admin_bar_menu($wp_admin_bar) {
    $args = array(
        'id' => 'edit-links',
        'title' => esc_html__('Edit links', 'azh'),
        'href' => '#',
        'meta' => array(
            'class' => 'active',
        ),
    );
    $wp_admin_bar->add_node($args);
}

add_filter('theme_page_templates', 'azh_theme_page_templates', 10, 4);

function azh_theme_page_templates($post_templates, $theme, $post, $post_type) {
    $post_templates['azexo-html-template.php'] = esc_html__('AZEXO HTML', 'azh');
    return $post_templates;
}

add_filter('template_include', 'azh_template_include');

function azh_template_include($template) {
    if (is_page()) {
        $template_slug = get_page_template_slug();
        if ($template_slug == 'azexo-html-template.php') {
            $template = locate_template('azexo-html-template.php');
            if (!$template) {
                $template = plugin_dir_path(__FILE__) . 'azexo-html-template.php';
            }
            return $template;
        }
    }
    return $template;
}

function azh_do_shortcode_matches($content, $matches) {
    if (!empty($matches)) {
        $shift = 0;
        for ($i = 0; $i < count($matches[0]); $i++) {
            $m = array($matches[0][$i][0], $matches[1][$i][0], $matches[2][$i][0], $matches[3][$i][0], $matches[4][$i][0], $matches[5][$i][0], $matches[6][$i][0]);
            $replacement = do_shortcode_tag($m);
            $content = substr_replace($content, $replacement, $matches[0][$i][1] + $shift, strlen($m[0]));
            $shift = $shift + strlen($replacement) - strlen($m[0]);
        }
    }
    return $content;
}

function azh_do_shortcode($content, $ignore_html = false) {
    global $shortcode_tags;

    static $cache = array();

    if (false === strpos($content, '[')) {
        return $content;
    }

    if (empty($shortcode_tags) || !is_array($shortcode_tags)) {
        return $content;
    }

    $md5 = md5($content);

    if (isset($cache[$md5])) {
        $content = azh_do_shortcode_matches($content, $cache[$md5]);
    } else {
        // Find all registered tag names in $content.
        preg_match_all('@\[([^<>&/\[\]\x00-\x20=]++)@', $content, $matches);
        $tagnames = array_intersect(array_keys($shortcode_tags), $matches[1]);

        if (empty($tagnames)) {
            return $content;
        }

        $content = azh_do_shortcodes_in_html_tags($content, $ignore_html, $tagnames);

        $pattern = get_shortcode_regex($tagnames);
        $matches = array();
        preg_match_all("/$pattern/", $content, $matches, PREG_OFFSET_CAPTURE);
        $cache[$md5] = $matches;
        $content = azh_do_shortcode_matches($content, $matches);
    }

    // Always restore square braces so we don't break things like <!--[if IE ]>
    $content = unescape_invalid_shortcodes($content);

    return $content;
}

function azh_do_shortcodes_in_html_tags($content, $ignore_html, $tagnames) {
    static $cache_split = array();
    static $cache_attributes = array();
    static $cache_tag = array();
    static $cache_attr1 = array();
    static $cache_attr2 = array();

    // Normalize entities in unfiltered HTML before adding placeholders.
    $trans = array('&#91;' => '&#091;', '&#93;' => '&#093;');
    $content = strtr($content, $trans);
    $trans = array('[' => '&#91;', ']' => '&#93;');

    $pattern = get_shortcode_regex($tagnames);
    if (isset($cache_split[md5($content)])) {
        $textarr = $cache_split[md5($content)];
    } else {
        $textarr = wp_html_split($content);
    }

    foreach ($textarr as &$element) {
        if ('' == $element || '<' !== $element[0]) {
            continue;
        }

        $noopen = false === strpos($element, '[');
        $noclose = false === strpos($element, ']');
        if ($noopen || $noclose) {
            // This element does not contain shortcodes.
            if ($noopen xor $noclose) {
                // Need to encode stray [ or ] chars.
                $element = strtr($element, $trans);
            }
            continue;
        }

        if ($ignore_html || '<!--' === substr($element, 0, 4) || '<![CDATA[' === substr($element, 0, 9)) {
            // Encode all [ and ] chars.
            $element = strtr($element, $trans);
            continue;
        }

        if (isset($cache_attributes[md5($element)])) {
            $attributes = $cache_attributes[md5($element)];
        } else {
            $attributes = wp_kses_attr_parse($element);
        }

        if (false === $attributes) {
            // Some plugins are doing things like [name] <[email]>.
            if (1 === preg_match('%^<\s*\[\[?[^\[\]]+\]%', $element)) {
                if (isset($cache_tag[md5($element)])) {
                    $element = azh_do_shortcode_matches($element, $cache_tag[md5($element)]);
                } else {
                    $matches = array();
                    preg_match_all("/$pattern/", $content, $matches, PREG_OFFSET_CAPTURE);
                    $cache_tag[md5($element)] = $matches;
                    $element = azh_do_shortcode_matches($element, $matches);
                }
            }

            // Looks like we found some crazy unfiltered HTML.  Skipping it for sanity.
            $element = strtr($element, $trans);
            continue;
        }

        // Get element name
        $front = array_shift($attributes);
        $back = array_pop($attributes);
        $matches = array();
        preg_match('%[a-zA-Z0-9]+%', $front, $matches);
        $elname = $matches[0];

        // Look for shortcodes in each attribute separately.
        foreach ($attributes as &$attr) {
            $open = strpos($attr, '[');
            $close = strpos($attr, ']');
            if (false === $open || false === $close) {
                continue; // Go to next attribute.  Square braces will be escaped at end of loop.
            }
            $double = strpos($attr, '"');
            $single = strpos($attr, "'");
            if (( false === $single || $open < $single ) && ( false === $double || $open < $double )) {
                // $attr like '[shortcode]' or 'name = [shortcode]' implies unfiltered_html.
                // In this specific situation we assume KSES did not run because the input
                // was written by an administrator, so we should avoid changing the output
                // and we do not need to run KSES here.

                if (isset($cache_attr1[md5($attr)])) {
                    $attr = azh_do_shortcode_matches($attr, $cache_attr1[md5($attr)]);
                } else {
                    $matches = array();
                    preg_match_all("/$pattern/", $attr, $matches, PREG_OFFSET_CAPTURE);
                    $cache_attr1[md5($attr)] = $matches;
                    $attr = azh_do_shortcode_matches($attr, $matches);
                }
            } else {
                // $attr like 'name = "[shortcode]"' or "name = '[shortcode]'"
                // We do not know if $content was unfiltered. Assume KSES ran before shortcodes.
                if (isset($cache_attr2[md5($attr)])) {
                    $new_attr = azh_do_shortcode_matches($attr, $cache_attr2[md5($attr)]);
                } else {
                    $matches = array();
                    preg_match_all("/$pattern/", $attr, $matches, PREG_OFFSET_CAPTURE);
                    $cache_attr2[md5($attr)] = $matches;
                    $new_attr = azh_do_shortcode_matches($attr, $matches);
                }
                if (isset($cache_attr2[md5($attr)])) {
                    // Sanitize the shortcode output using KSES.
                    $new_attr = wp_kses_one_attr($new_attr, $elname);
                    if ('' !== trim($new_attr)) {
                        // The shortcode is safe to use now.
                        $attr = $new_attr;
                    }
                }
            }
        }
        $element = $front . implode('', $attributes) . $back;

        // Now encode any remaining [ or ] chars.
        $element = strtr($element, $trans);
    }

    $content = implode('', $textarr);

    return $content;
}

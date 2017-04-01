<?php

/*
  Field Name: Profile is open now
 */
?>
<?php

global $post;
date_default_timezone_set('America/New_York');
$hours = get_post_meta($post->ID, 'working-hours-' . date('N') . '-hours');
if(in_array(date('G'), $hours)) {
    print '<div class="is-open open-now">'.  esc_html__('Open', 'foodpicky').'</div>';
} else {
    print '<div class="is-open close-now">'.  esc_html__('Closed', 'foodpicky').'</div>';
}
?>
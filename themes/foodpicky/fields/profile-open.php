<?php

/*
  Field Name: Profile is open now
 */
?>
<?php

global $post;
$hours = get_post_meta($post->ID, 'working-hours-' . date('N') . '-hours');
if(in_array(date('G'), $hours)) {
    print '<div class="is-open open-now">'.  esc_html__('Open', 'foodpicky').'</div>';
} else {
    print '<div class="is-open close-now">'.  esc_html__('Closed', 'foodpicky').'</div>';
}
?>
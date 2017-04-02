<?php

/*
  Field Name: Profile is open now
 */
?>
<?php

global $post;

date_default_timezone_set('America/New_York');
$postId;
if($post->post_type =="product"){
       $author_id = $post->post_author;
       $get_vendor_filter = array('post_type'=>'azl_profile','author__in'=>array($author_id));
       $vendor = get_posts($get_vendor_filter);
       $postId =$vendor->ID;
}else{
    $postId = $post->ID;
}
$hours = get_post_meta($postId, 'working-hours-' . date('N') . '-hours');
if(in_array(date('G'), $hours)) {
    print '<div class="is-open open-now">'.  esc_html__('Open', 'foodpicky').'</div>';
} else {
    print '<div class="is-open close-now">'.  esc_html__('Closed', 'foodpicky').'</div>';
}
?>
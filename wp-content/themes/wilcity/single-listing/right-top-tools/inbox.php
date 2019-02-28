<?php
global $post;
if ( $post->post_author == get_current_user_id() ){
    return '';
}
?>
<div class="listing-detail_rightItem__2CjTS wilcity-single-tool-inbox">
    <wiloke-message-btn btn-name="<?php esc_html_e('Inbox', 'wilcity'); ?>"></wiloke-message-btn>
</div>
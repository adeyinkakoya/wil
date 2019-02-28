<?php
global $post;
?>
<div class="listing-detail_rightItem__2CjTS wilcity-single-tool-share">
    <a class="wil-btn wil-btn--border wil-btn--round wil-btn--sm " href="#" data-toggle-button="share" data-body-toggle="true"><i class="color-primary la la-share"></i> <?php esc_html_e('Share', 'wilcity'); ?>
    </a>
    <div class="listing-detail_shareContent__2nr-2" data-toggle-content="share">
        <wiloke-socials-sharing post-link="<?php echo esc_url(get_permalink($post->ID)); ?>" post-title="<?php echo get_the_title($post->ID); ?>" post-id="<?php echo esc_attr($post->ID); ?>"></wiloke-socials-sharing>
    </div>
</div>
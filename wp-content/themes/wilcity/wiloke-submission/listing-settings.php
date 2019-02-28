<?php
global $post;
if ( !\WilokeListingTools\Frontend\User::isPostAuthor($post, true) ) {
    return '';
}
?>
<div id="single-listing-settings" class="listing-settings listing-detail_body__287ZB wilcity-js-toggle-group hidden" data-tab-key="listing-settings">
    <div class="container">
        <div class="listing-detail_row__2UU6R clearfix">
			<?php do_action('wilcity/single-listing/before-listing-settings'); ?>
            <div class="wil-colSmall">
                <wiloke-single-sidebar></wiloke-single-sidebar>
            </div>
            <div class="wil-colLarge">
                <wiloke-message v-show="msg.length" :status="msgStatus" :icon="msgIcon" :msg="msg"></wiloke-message>
                <div class="content-box_module__333d9">
                    <wiloke-single-general></wiloke-single-general>
                    <wiloke-single-edit-navigation></wiloke-single-edit-navigation>
                    <wiloke-single-edit-sidebar></wiloke-single-edit-sidebar>
                </div>
            </div>
			<?php do_action('wilcity/single-listing/after-listing-settings'); ?>
        </div>
    </div>
</div>
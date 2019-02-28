<?php
global $post;
$favoriteIconClass = \WilokeListingTools\Models\UserModel::isMyFavorite($post->ID) ? 'la la-heart color-primary' : 'la la-heart-o';
?>
<div class="listing-detail_rightItem__2CjTS wilcity-single-tool-favorite">
	<a class="wil-btn wil-btn--border wil-btn--round wil-btn--sm wilcity-js-favorite" data-post-id="<?php echo esc_attr($post->ID); ?>" href="#"><i class="color-quaternary <?php echo esc_attr($favoriteIconClass); ?>"></i> <?php esc_html_e('Favorite', 'wilcity'); ?>
	</a>
</div>
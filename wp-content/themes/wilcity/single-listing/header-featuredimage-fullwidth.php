<?php
global $post, $wiloke;
$coverImg = \WilokeListingTools\Framework\Helpers\GetSettings::getCoverImage($post->ID, 'large');
?>
<header class="listing-detail_header__18Cfs">
	<div class="listing-detail_img__3DyYX pos-a-full bg-cover" style="background-image: url(<?php echo esc_url($coverImg); ?>);"><img src="<?php echo esc_url($coverImg); ?>" alt="<?php the_title(); ?>"></div>
	<?php if ( isset($wiloke->aThemeOptions['listing_overlay_color']['rgba']) && !empty($wiloke->aThemeOptions['listing_overlay_color']['rgba']) ) : ?>
		<div class="wil-overlay" style="background-color: <?php echo esc_attr($wiloke->aThemeOptions['listing_overlay_color']['rgba']); ?>"></div>
	<?php else: ?>
		<div class="wil-overlay"></div>
	<?php endif; ?>
</header>
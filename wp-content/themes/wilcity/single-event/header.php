<?php
global $post;
use WilokeListingTools\Controllers\EventController;
use WilokeListingTools\Framework\Helpers\GetSettings;
use WilokeListingTools\Models\UserModel;
use WilokeListingTools\Models\FavoriteStatistic;
use WilokeListingTools\Frontend\User as WilokeUser;
use WilokeListingTools\Framework\Helpers\HTML;

$interestedClass = UserModel::isMyFavorite($post->ID) ? 'la la-star color-primary' : 'la la-star-o';
$totalInterested = FavoriteStatistic::countFavorites($post->ID);

?>
<header class="event-detail-content_header__VdI5m">
	<?php if ( has_post_thumbnail($post->ID) ) : ?>
	<div class="event-detail-content_img__2hZQO">
		<?php the_post_thumbnail($post->ID, 'large'); ?>
	</div>
	<?php endif; ?>
	<div class="event-detail-content_firstItem__3vz2x">
		<h1 class="event-detail-content_title__asKJI"><?php the_title(); ?></h1>
		<div class="event-detail-content_meta__1dBc1 wilcity-hosted-by">
			<span><?php esc_html_e('Hosted By', 'wilcity'); ?>
                <?php
                $hostedBy = GetSettings::getPostMeta($post->ID, 'hosted_by');
                if ( !empty($hostedBy) ):
                    $hostedByProfileURL = GetSettings::getPostMeta($post->ID, 'hosted_by_profile_url');
	                $hostedByProfileURL = empty($hostedByProfileURL) ? '#' : $hostedByProfileURL;
                ?>
                    <a href="<?php echo esc_url($hostedByProfileURL); ?>" class="color-dark-2"><?php echo esc_html($hostedBy); ?></a>
                <?php else: ?>
                <a href="<?php echo esc_url(WilokeUser::url($post->post_author)); ?>" class="color-dark-2"><?php echo esc_html(WilokeUser::getField('display_name', $post->post_author)); ?></a>
                <?php endif; ?>
            </span>
            <?php if ( !empty($totalInterested) ) : ?>
            <span><?php echo HTML::reStyleText($totalInterested) . ' ' . ( $totalInterested > 1 ? esc_html__('people interested', 'wilcity') : esc_html__('person interested', 'wilcity') ); ?></span>
            <?php endif; ?>
		</div>
		<a class="wil-btn wil-btn--border wil-btn--round wil-btn--sm is-event wilcity-js-favorite" href="#" data-post-id="<?php echo esc_attr($post->ID); ?>"><i class="<?php echo esc_attr($interestedClass); ?>"></i> <?php esc_html_e('Interested', 'wilcity'); ?>
		</a>
	</div>

    <?php do_action('wilcity/single-event/calendar', $post);  ?>
    <?php do_action('wilcity/single-event/meta-data', $post);  ?>

</header>
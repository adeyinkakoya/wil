<?php
use WilokeListingTools\Frontend\User;
use WilokeListingTools\Controllers\ReviewController;
use WilokeListingTools\Models\ReviewMetaModel;
use WilokeListingTools\Models\ReviewModel;

global $post, $wiloke, $wilcityoReview, $wilcityaUserInfo, $wilcityReviewConfiguration, $wilcityParentPost, $wilcityArgs;
$wilcityParentPost = $post;

$oSomeReviews = ReviewModel::getReviews($post->ID, array(
	'postsPerPage' => $wilcityArgs['maximumItemsOnHome']
));

if ( $oSomeReviews ) {
	$aGeneralReviewData = ReviewMetaModel::getGeneralReviewData($post->ID);
//	$aGeneralReviewData = \WilokeListingTools\Framework\Helpers\General::unSlashDeep($aGeneralReviewData);
	$reviewsTotal       = $aGeneralReviewData['total'];
	$wilcityaUserInfo['avatar']   = User::getAvatar();
	$wilcityaUserInfo['position'] = User::getPosition();
	$wilcityaUserInfo['displayName'] = User::getField('display_name');

	$wilcityReviewConfiguration['enableReviewDiscussion'] = ReviewController::isEnabledDiscussion($post->post_type);
	$isUserReviewed = ReviewModel::isUserReviewed($post->ID) ? 'yes' : 'no';
	?>
	<wiloke-review-statistic v-on:on-open-review-popup="onOpenReviewPopup" is-use-reviewed="<?php echo esc_attr($isUserReviewed); ?>" total-reviews="<?php echo esc_attr($reviewsTotal); ?>" post-title="<?php echo esc_attr($post->post_title); ?>"></wiloke-review-statistic>
	<?php
	while ($oSomeReviews->have_posts()){
	    $oSomeReviews->the_post();
		$wilcityoReview = $oSomeReviews->post;
		get_template_part('reviews/item');
	}
	wp_reset_postdata();
}
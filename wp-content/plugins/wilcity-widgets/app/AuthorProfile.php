<?php
namespace WilcityWidgets\App;

use WilokeListingTools\Controllers\FollowController;
use WilokeListingTools\Framework\Helpers\HTML;
use WilokeListingTools\Frontend\User;

class AuthorProfile extends \WP_Widget {
	public $aDef = array('title'=>'');

	public function __construct() {
		parent::__construct( 'wilcity_author_profile', WILCITY_WIDGET . ' (Single) Author Profile');
	}


	public function form( $aInstance ) {
		$aInstance = wp_parse_args($aInstance, $this->aDef);
		?>
		<div class="widget-group">
			<label for="<?php echo $this->get_field_id('title'); ?>">Title</label>
			<input type="text" class="widefat" name="<?php echo $this->get_field_name('title'); ?>" id="<?php echo $this->get_field_id('title'); ?>" value="<?php echo esc_attr($aInstance['title']); ?>">
		</div>
		<?php
	}

	public function widget( $aAtts, $aInstance ) {
		if ( !is_single() ){
			return '';
		}

		global $post, $wiloke;

		echo $aAtts['before_widget'];
            if ( !empty($aInstance['title']) ){
	            echo $aAtts['before_title']; ?><i class="la la-users"></i> <span><?php echo esc_html($aInstance['title']); ?></span><?php echo $aAtts['after_title'];
            }

            $avatar = User::getAvatar($post->post_author);
            $displayName = User::getField('display_name', $post->post_author);
            $position = User::getPosition($post->post_author);
            $authorPostsUrl = get_author_posts_url($post->post_author);
		?>
			<div class="content-box_body__3tSRB pb-0">
				<div class="author-listing_module__3K7-I">
					<div class="utility-box-1_module__MYXpX utility-box-1_md__VsXoU utility-box-1_boxLeft__3iS6b clearfix  mb-20 mb-sm-15">
						<div class="utility-box-1_avatar__DB9c_ rounded-circle">
                            <a class="clearfix" href="<?php echo esc_url($authorPostsUrl); ?>">
                                <img style="display: block !important;" src="<?php echo esc_url($avatar); ?>" alt="<?php echo esc_attr($displayName); ?>">
                            </a>
						</div>
						<div class="utility-box-1_body__8qd9j">
							<div class="utility-box-1_group__2ZPA2">
                                <h3 class="utility-box-1_title__1I925"><a href="<?php echo esc_url($authorPostsUrl); ?>"><?php echo esc_html($displayName); ?></a></h3>
							</div>
							<?php if ( !empty($position) ) : ?>
                                <div class="utility-box-1_description__2VDJ6"><a href="<?php echo esc_url($authorPostsUrl); ?>"><?php echo esc_html($position); ?></a></div>
							<?php endif; ?>
						</div>
					</div>
					<?php if ( FollowController::toggleFollow() ) :
						$followings = FollowController::countFollowings($post->post_author);
						$followers = FollowController::countFollowers($post->post_author);
					?>
					<div id="wilcity-follower-number-<?php echo esc_attr($post->post_author); ?>" class="author-listing_follow__3RxQ6">
						<div class="follow_module__17lY_">
							<div class="follow_item__3GAob">
								<div class="follow_content__2R1YP">
									<span class="color-primary wilcity-print-number"><?php echo HTML::reStyleText($followers); ?></span> <?php echo \WilokeHelpers::ngettext(esc_html__('Follower', 'wilcity-widgets'), esc_html__('Followers', 'wilcity-widgets'), esc_html__('Followers', 'wilcity-widgets'), $followers); ?>
								</div>
							</div>
							<div class="follow_item__3GAob">
								<div class="follow_content__2R1YP">
									<span class="color-primary"><?php echo HTML::reStyleText($followings); ?></span> <?php esc_html_e('Following', 'wilcity-widgets'); ?>
								</div>
							</div>
						    <?php if ( get_current_user_id() != $post->post_author ) : ?>
                                <div class="follow_item__3GAob">
                                    <div class="follow_content__2R1YP">
                                        <a class='wilcity-toggle-follow color-primary fs-12 font-secondary font-bold' data-textonly="true" data-authorid="<?php echo esc_attr($post->post_author); ?>" data-current-status="<?php echo FollowController::isIamFollowing($post->post_author) ? 'followingtext' : 'followtext'; ?>" data-followtext="<?php esc_html_e('Follow', 'wilcity-widgets'); ?>" data-followingtext="<?php esc_html_e('Following', 'wilcity-widgets'); ?>" href='<?php echo esc_url(get_author_posts_url($post->ID)); ?>'><i class='la la-refresh'></i> <?php echo FollowController::isIamFollowing($post->ID) ?  esc_html__('Following', 'wilcity-widgets') : esc_html__('Follow', 'wilcity-widgets'); ?></a>
                                    </div>
                                </div>
                            <?php endif; ?>
						</div>
					</div>
					<?php endif; ?>
					
				</div>
			</div>
		<?php
        echo $aAtts['after_widget'];
    }

	public function update( $aNewInstance, $aOldInstance ) {
		$aInstance = $aOldInstance;
		foreach ($aNewInstance as $key => $val){
			$aInstance[$key] = strip_tags($val);
		}
		return $aInstance;
	}
}
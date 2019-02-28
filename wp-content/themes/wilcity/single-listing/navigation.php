<?php
use WilokeListingTools\Frontend\SingleListing;
use WilokeListingTools\Framework\Helpers\GetSettings;
use WilokeListingTools\Frontend\User as WilokeUser;
use \WilokeListingTools\Framework\Helpers\General;

global $post;
$aTabs = SingleListing::getNavOrder();
$aTabs = General::unSlashDeep($aTabs);
$aTabs = apply_filters('wilcity/single-listing/tabs', $aTabs, $post);

?>
<div class="detail-navtop_module__zo_OS js-detail-navtop">
	<div class="container">
		<nav class="detail-navtop_nav__1j1Ti">
			<!-- list_module__1eis9 list-none -->
			<ul class="list_module__1eis9 list-none list_horizontal__7fIr5" style="min-height: 70px;">
				<li class="list_item__3YghP active" data-tab-key="home">
                    <wiloke-switch-tab-btn tab-key="home" v-on:on-switch-tab="switchTab" wrapper-class="list_link__2rDA1 text-ellipsis color-primary--hover">
                        <template slot="insideTab">
                            <span class="list_icon__2YpTp"><i class="la la-home"></i></span>
                            <span class="list_text__35R07"><?php esc_html_e('Home', 'wilcity'); ?></span>
                        </template>
                    </wiloke-switch-tab-btn>
				</li>
				<?php
				foreach ($aTabs as $aTab) :
					if ( $aTab['status'] == 'no' ){
						continue;
					}
					?>
                    <li class="list_item__3YghP" data-tab-key="<?php echo esc_attr($aTab['key']); ?>">
                        <wiloke-switch-tab-btn tab-key="<?php echo esc_attr($aTab['key']); ?>" v-on:on-switch-tab="switchTab">
                            <template slot="insideTab">
                                <span class="list_icon__2YpTp"><i class="<?php echo esc_html($aTab['icon']); ?>"></i></span>
                                <span class="list_text__35R07"><?php echo esc_html($aTab['name']); ?></span>
                            </template>
                        </wiloke-switch-tab-btn>
					</li>
				<?php endforeach; ?>

                <?php if ( WilokeUser::isPostAuthor($post, true) ) : ?>
                <li class="list_item__3YghP listing-settings-tab" data-tab-key="listing-settings">
                    <wiloke-switch-tab-btn wrapper-class="list_link__2rDA1 text-ellipsis color-primary--hover" tab-key="listing-settings" v-on:on-switch-tab="switchTab">
                        <template slot="insideTab">
                            <span class="list_icon__2YpTp"><i class="la la-cog"></i></span>
                            <span class="list_text__35R07"><?php esc_html_e('Settings', 'wilcity'); ?></span>
                        </template>
                    </wiloke-switch-tab-btn>
				</li>
                <?php endif; ?>
			</ul><!-- End /  list_module__1eis9 list-none -->
		</nav>

        <?php if ( $buttonLink = GetSettings::getPostMeta($post->ID, 'button_link') ) : ?>
		<div class="detail-navtop_right__KPAlw">
			<a class="wil-btn wil-btn--primary2 wil-btn--round wil-btn--md wil-btn--block" target="_blank" href="<?php echo esc_url($buttonLink); ?>"><i class="<?php echo esc_attr(GetSettings::getPostMeta($post->ID, 'button_icon')) ?>"></i> <?php echo esc_html(GetSettings::getPostMeta($post->ID, 'button_name')); ?>
			</a>
		</div>
        <?php endif; ?>
	</div>
</div><!-- End / detail-navtop_module__zo_OS -->

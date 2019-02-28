<?php
use WilokeListingTools\Framework\Helpers\GetSettings;
use WilokeListingTools\Framework\Helpers\Time as WilokeTime;
use WilokeListingTools\Models\UserModel;

function wilcity_render_event_item($post, $aAtts){
	$aAtts['img_size'] = \WILCITY_SC\SCHelpers::parseImgSize($aAtts['img_size']);
    $imgUrl = get_the_post_thumbnail_url($post->ID, $aAtts['img_size']);

    $aEventCalendarSettings = GetSettings::getEventSettings($post->ID);
    $address = GetSettings::getAddress($post->ID);
    $classes = $aAtts['maximum_posts_on_lg_screen'] . ' js-grid-item ' .  $aAtts['maximum_posts_on_md_screen'] . ' ' . $aAtts['maximum_posts_on_sm_screen'];
    ?>
    <div class="<?php echo esc_attr($classes); ?>">
        <!-- event_module__2zicF wil-shadow -->
        <div class="event_module__2zicF wil-shadow mb-30 mb-sm-20 js-event">
            <header class="event_header__u3oXZ">
                <a href="<?php echo get_permalink($post); ?>">
                    <div class="event_img__1mVnG pos-a-full bg-cover" style="background-image: url(<?php echo esc_url($imgUrl); ?>);">
                        <?php echo get_the_post_thumbnail($post->ID, $aAtts['img_size']); ?>
                    </div>
                </a>
            </header>
            <div class="js-grid-item-body event_body__BfZIC">
                <div class="event_calendar__2x4Hv">
                    <span class="event_month__S8D_o color-primary"><?php echo date_i18n('M', strtotime($aEventCalendarSettings['startsOn'])); ?></span>
                    <span class="event_date__2Z7TH"><?php echo date_i18n('d', strtotime($aEventCalendarSettings['startsOn'])); ?></span>
                </div>
                <div class="event_content__2fB-4">
                    <h2 class="event_title__3C2PA"><a href="<?php echo get_permalink($post); ?>"><?php echo get_the_title($post->ID); ?></a></h2>
                    <ul class="event_meta__CFFPg list-none">
                        <li class="event_metaList__1bEBH text-ellipsis">
                            <span><?php echo date_i18n('l', strtotime($aEventCalendarSettings['startsOn'])); ?> <?php echo WilokeTime::renderTimeFormat(strtotime($aEventCalendarSettings['startsOn']), $post->ID); ?></span>
                        </li>
                        <?php
                        if ( !empty($address) ) :
                            $mapUrl = GetSettings::getAddress($post->ID, true);
                        ?>
                        <li class="event_metaList__1bEBH text-ellipsis">
                            <a href="<?php echo esc_url($mapUrl); ?>" target="_blank"><span><?php echo esc_html($address); ?></span></a>
                        </li>
                        <?php endif; ?>
                        <?php \WILCITY_SC\SCHelpers::renderInterested($post); ?>
                    </ul>
                </div>
            </div>
            <footer class="js-grid-item-footer event_footer__1TsCF">
                <span class="event_by__23HUz">
                    <?php esc_html_e('Hosted By', 'wilcity-shortcodes'); ?> <a href="<?php echo esc_url(GetSettings::getEventHostedByUrl($post)); ?>" class="color-dark-2"><?php echo GetSettings::getEventHostedByName($post); ?></a>
                </span>
                <div class="event_right__drLk5 pos-a-center-right">
                    <?php $favoriteIconClass = UserModel::isMyFavorite($post->ID) ? 'la la-star color-primary' : 'la la-star-o'; ?>
                    <span class="wilcity-js-favorite event_interested__2RxI- is-event" data-tooltip="<?php esc_html_e('Interested', 'wilcity-shortcodes'); ?>" data-post-id="<?php echo esc_attr($post->ID); ?>" data-tooltip-placement="top"><i class="<?php echo esc_attr($favoriteIconClass); ?>"></i></span>
                </div>
            </footer>
        </div><!-- End / event_module__2zicF wil-shadow -->
    </div>
    <?php
}
<?php
use WilokeListingTools\Framework\Helpers\GetSettings;

add_shortcode('wilcity_sidebar_business_info', 'wicitySidebarBusinessInfo');

function wilcitySidebarBusinessInfoAreSocialsEmpty($aSocialNetworks){
	if ( empty($aSocialNetworks) ){
	    return true;
    }
	foreach ($aSocialNetworks as $icon => $link){
		if ( !empty($link) ){
			return false;
		}
    }
    return true;
}

function wicitySidebarBusinessInfo($aArgs){
    global $post;
	$aAtts = \WILCITY_SC\SCHelpers::decodeAtts($aArgs['atts']);
	$aAtts = wp_parse_args(
		$aAtts,
		array(
			'name'          => esc_html__('Business Info', WILOKE_LISTING_DOMAIN),
			'icon'          => 'la la-qq',
			'desc'          => '',
			'currencyIcon'  => 'la la-dollar'
		)
	);

	if ( isset($aAtts['isMobile']) ){
		return apply_filters('wilcity/mobile/sidebar/business_info', $post, $aAtts);
	}

	$wrapperClass = 'mt-20 mt-sm-15';
	$address = GetSettings::getAddress($post->ID, false);
	$email = !GetSettings::isPlanAvailableInListing($post->ID, 'toggle_email') ? '' : GetSettings::getPostMeta($post->ID, 'email');
	$phone = !GetSettings::isPlanAvailableInListing($post->ID, 'toggle_phone') ? '' : GetSettings::getPostMeta($post->ID, 'phone');
	$website = !GetSettings::isPlanAvailableInListing($post->ID, 'toggle_website') ? '' : GetSettings::getPostMeta($post->ID, 'website');
	$aSocialNetworks = !GetSettings::isPlanAvailableInListing($post->ID, 'toggle_social_networks') ? '' : GetSettings::getPostMeta($post->ID, 'social_networks');

	if ( !empty($address) || !empty($phone) || !empty($email) || !empty($website) || !wilcitySidebarBusinessInfoAreSocialsEmpty($aSocialNetworks)  ) :
	ob_start();
	?>
	<div class="content-box_module__333d9">
		<?php wilcityRenderSidebarHeader($aAtts['name'], $aAtts['icon']); ?>
		<div class="content-box_body__3tSRB">
			<?php
            if  ( !empty($address)  ) {
                \WILCITY_SC\SCHelpers::renderIconAndLink( $address, 'la la-map-marker', $address, array(
                    'wrapperClass'     => $wrapperClass . ' text-pre',
                    'isGoogle'         => true,
                    'iconWrapperClass' => 'rounded-circle'
                ));
            }

            if ( !empty($email) ) {
	            \WILCITY_SC\SCHelpers::renderIconAndLink($email, 'la la-envelope', $email, array(
                    'wrapperClass'     => $wrapperClass,
                    'isEmail' => true,
                    'iconWrapperClass' => 'rounded-circle'
	            ));
            }

			if ( !empty($phone) ) {
				\WILCITY_SC\SCHelpers::renderIconAndLink($phone, 'la la-phone', $phone, array(
					'wrapperClass'     => $wrapperClass,
					'isPhone' => true,
					'iconWrapperClass' => 'rounded-circle'
				));
			}
            
			if ( $website ) {
				\WILCITY_SC\SCHelpers::renderIconAndLink($website, 'la la-globe', $website, array(
					'wrapperClass'     => $wrapperClass,
					'iconWrapperClass' => 'rounded-circle'
				));
			}

            if ( !empty($aSocialNetworks) ) :
            ?>
            <div class="icon-box-1_module__uyg5F mt-20 mt-sm-15">
                <div class="social-icon_module__HOrwr social-icon_style-2__17BFy">
                    <?php
                    foreach ($aSocialNetworks as $icon => $link) :
                        if ( empty($link) ){
                            continue;
                        }

	                    if ( $icon == 'whatsapp'  ){
		                    $link = \WilokeListingTools\Framework\Helpers\General::renderWhatsApp($link);
	                    }
                    ?>
                    <a class="social-icon_item__3SLnb" href="<?php echo esc_url($link); ?>" target="_blank"><i class="fa fa-<?php echo esc_attr($icon); ?>"></i></a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
            <wiloke-message-btn btn-name="<?php esc_html_e('Inbox', 'wilcity'); ?>" wrapper-class="wilcity-inbox-btn wil-btn wil-btn--block mt-20 wil-btn--border wil-btn--round"></wiloke-message-btn>
		</div>
	</div>
	<?php
    $content = ob_get_contents();
    ob_end_clean();
    else:
    $content = '';
    endif;
    return $content;
}
<?php
use WilokeListingTools\Framework\Helpers\GetSettings;
use WilokeListingTools\Framework\Helpers\General;

function wilcity_sc_render_hero_search_form($aAtts){
	global $wiloke;
	$oFirstItem = reset($aAtts['items']);
	$oFirstItem = is_array($oFirstItem) ? (object)$oFirstItem : $oFirstItem;
	?>
    <div id="wilcity-hero-search-form" class="tab_module__3fEXT wil-tab" default-type="<?php echo esc_attr($oFirstItem->post_type); ?>">
        <ul class="tab_nav__3YJph wil-tab__nav" role="tablist">
            <?php
            $order=0;
            foreach ($aAtts['items'] as $oTab) :
	            $oTab = is_array($oTab) ? (object)$oTab : $oTab;
            ?>
                <li>
                    <wiloke-switch-post-type-btn v-on:on-switch-post-type="switchPostType" is-default-active="<?php echo empty($order) ? 'yes' : 'no'; ?>" icon-class="<?php echo esc_attr(\WilokeListingTools\Framework\Helpers\GetSettings::getPostTypeField('icon', $oTab->post_type)); ?> color-primary" post-type="<?php echo esc_attr($oTab->post_type); ?>" tab-name="<?php echo esc_html($oTab->name); ?>"></wiloke-switch-post-type-btn>
                </li>
            <?php $order++; endforeach; ?>
        </ul>
        <div class="tab_content__ndczY wil-tab__content">
            <?php
            $order = 0;
            foreach ($aAtts['items'] as $oTab) :
	            $oTab = is_array($oTab) ? (object)$oTab : $oTab;
            ?>
            <div class="wil-tab__panel <?php echo $order == 0 ? 'active' : ''; ?>" id="hero-search-<?php echo esc_attr($oTab->post_type); ?>-tab">
                <form class="hero_form__1ewus" action="<?php echo esc_url(home_url('/')); ?>">
                    <div class="row">
                        <wiloke-hero-search-form default-post-type="<?php echo esc_attr($oFirstItem->post_type); ?>" post-type="<?php echo esc_attr($oTab->post_type); ?>" search-url="<?php echo esc_url(get_permalink($wiloke->aThemeOptions['search_page'])); ?>"></wiloke-hero-search-form>
                    </div>
                </form>
            </div>
            <?php $order++; endforeach; ?>
        </div>
    </div>
<?php
}
<?php

namespace WilokeListingTools\Controllers;


use WilokeListingTools\Framework\Routing\Controller;

class TermsAndPolicyController extends Controller {
	public function __construct() {
		add_action('wilcity/agree-to-terms-and-policy', array($this, 'printAgreeToTerms'));
	}

	public function printAgreeToTerms(){
		global $wiloke;
		if ( isset($wiloke->aThemeOptions['toggle_privacy_policy']) && $wiloke->aThemeOptions['toggle_privacy_policy'] == 'enable' ){
			?>
			<div class="checkbox_module__1K5IS mb-20">
				<label class="checkbox_label__3cO9k">
					<input class="checkbox_inputcheck__1_X9Z" type="checkbox" v-model="agreeToPrivacyPolicy" true-value="yes" false-value="no">
					<span class="checkbox_icon__28tFk bg-color-primary--checked-after bd-color-primary--checked"><i class="la la-check"></i><span class="checkbox-iconBg"></span></span>
				</label>
				<span class="checkbox_text__3Go1u text-ellipsis"><?php echo \Wiloke::ksesHTML($wiloke->aThemeOptions['privacy_policy_desc']); ?><span class="checkbox-border"></span></span>
			</div>
			<?php
		}else{
			?>
			<input type="hidden" v-model="agreeToPrivacyPolicy" value="yes">
			<?php
		}

		if ( isset($wiloke->aThemeOptions['toggle_terms_and_conditionals']) && $wiloke->aThemeOptions['toggle_terms_and_conditionals'] == 'enable' ){
			?>
			<div class="checkbox_module__1K5IS mb-20">
				<label class="checkbox_label__3cO9k">
					<input class="checkbox_inputcheck__1_X9Z" type="checkbox" v-model="agreeToTerms" true-value="yes" false-value="no">
					<span class="checkbox_icon__28tFk bg-color-primary--checked-after bd-color-primary--checked"><i class="la la-check"></i><span class="checkbox-iconBg"></span></span>
				</label>
				<span class="checkbox_text__3Go1u text-ellipsis"><?php echo \Wiloke::ksesHTML($wiloke->aThemeOptions['terms_and_conditionals_desc']); ?><span class="checkbox-border"></span></span>
			</div>
			<?php
		}else{
			?>
			<input type="hidden" v-model="agreeToTerms" value="yes">
			<?php
		}
	}
}
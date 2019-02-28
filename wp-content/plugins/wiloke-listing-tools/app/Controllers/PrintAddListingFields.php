<?php

namespace WilokeListingTools\Controllers;


trait PrintAddListingFields {
	public function printAddListingFields($post){
		?>
		<form v-cloak id="wilcity-addlisting-form" type="POST" action="<?php echo esc_url(admin_url('admin-ajax.php?action=wilcity_handle_review')); ?>">

			<div v-for="oSection in oUsedSections" :id="sectionID(oSection.key)" class="content-box_module__333d9 content-box_lg__3v3a-">
				<header class="content-box_header__xPnGx clearfix">
					<div class="wil-float-left">
						<h4 class="content-box_title__1gBHS"><i :class="oSection.icon"></i>
							<span>{{oSection.heading}}</span>
						</h4>
					</div>
				</header>
				<div class="content-box_body__3tSRB">
					<div v-for="(oField, fieldKey) in oSection.fields" v-if="oField.isEnable!='no'" :class="{'group-required': (oSection.fields[fieldKey].isRequired && oSection.fields[fieldKey].isRequired == 'yes') || (oField.key && oSection.fields[oField.key] && oSection.fields[oField.key].isRequired && oSection.fields[oField.key].isRequired=='yes')}">
                        <wiloke-group v-if="oSection.type=='group'" :a-fields="oField"></wiloke-group>
                        <wiloke-input v-else-if="oField.type=='text'" :settings="oField"></wiloke-input>
                        <wiloke-checkbox-two v-else-if="oField.type=='checkbox2'" :settings="oField"></wiloke-checkbox-two>
                        <wiloke-radio v-else-if="oField.type=='radio'" :settings="oField"></wiloke-radio>
                        <wiloke-textarea v-else-if="oField.type=='textarea'" :settings="oField"></wiloke-textarea>
                        <wiloke-upload-img v-else-if="oField.type=='single_image'" :settings="{value: oField.value, isRequired:oField.isRequired, labelName: oField.label,isLinkTo:oField.isLinkTo, isMultiple: false, paramName:fieldKey, oPlanSettings: oPlanSettings}" :field="oField"></wiloke-upload-img>
                        <wiloke-upload-img v-else-if="oField.type=='gallery'" :settings="{value: oField.value, isRequired:oField.isRequired, labelName: oField.label, isMultiple: true, paramName:fieldKey, oPlanSettings: oPlanSettings}" :field="oField"></wiloke-upload-img>
                        <wiloke-select-two v-else-if="oField.type=='select2' || oField.type=='select'" :settings="oField" :target="fieldKey"></wiloke-select-two>
                        <wiloke-email v-else-if="oField.type=='email'" :settings="oField" :target="fieldKey"></wiloke-email>
                        <wiloke-url v-else-if="oField.type=='url'" :settings="oField" :target="fieldKey"></wiloke-url>
                        <wiloke-color-picker v-else-if="oField.type=='colorpicker'" :settings="oField" :target="fieldKey"></wiloke-color-picker>
                        <wiloke-social-networks v-else-if="oField.type=='social_networks'" :settings="oField" :target="fieldKey"></wiloke-social-networks>
                        <wiloke-video v-else-if="oField.type=='video'" :settings="oField"></wiloke-video>
                        <wiloke-price-range v-else-if="oField.type=='price_range'" :settings="oField"></wiloke-price-range>
                        <wiloke-business-hours v-else-if="oField.type=='business_hours'" :settings="oField"></wiloke-business-hours>
                        <wiloke-map v-else-if="oField.type=='map'" :settings="oField" :is-showing-map="true"></wiloke-map>
                        <wiloke-date-time v-else-if="oField.type=='date_time'" :settings="oField"></wiloke-date-time>
                        <wiloke-tags v-else-if="oField.type=='listing_tag'" :settings="oField"></wiloke-tags>
                        <wiloke-category v-else-if="oField.type=='category'" :settings="oField" target="listing_cat"></wiloke-category>
                        <wiloke-event-calendar v-else-if="oField.type=='event_calendar'" :settings="oField"></wiloke-event-calendar>
					</div>
				</div>
			</div>
            <ul v-cloak v-show="aErrors.length" class="list-none mt-20 mb-20" style="padding: 0;">
                <li v-for="errorMsg in aErrors" style="color: #d61313;" class="alert_content__1ntU3" v-html="errorMsg"></li>
            </ul>

			<button type="submit" class="wil-btn wil-btn--primary wil-btn--round wil-btn--lg wil-btn--block" :class="submitBtnClass" @click.prevent="handlePreview"><?php echo !\WilokeThemeOptions::isEnable('addlisting_skip_preview_step') ? esc_html__('Save &amp; Preview', 'wiloke-listing-tools') : esc_html__('Submit', 'wiloke-listing-tools'); ?> <div class="pill-loading_module__3LZ6v" v-show="isSubmitting"><div :class="pillLoadingClass"></div></div></button>
		</form>
		<?php
	}
}
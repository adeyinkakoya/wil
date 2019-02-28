import Tabs from '../dev/form-fields/Tabs.vue'
import Tab from '../dev/form-fields/Tab.vue'
import WilokeInput from '../dev/form-fields/Input.vue'
import WilokeIcon from '../dev/form-fields/Icon.vue'
import WilokeCheckbox from '../dev/form-fields/Checkbox.vue'
import WilokeHidden from '../dev/form-fields/Hidden.vue'
import WilokeSelect from '../dev/form-fields/Select.vue'
import WilokeTextarea from '../dev/form-fields/Textarea.vue'

Vue.config.devtools = true;

const config = {
	errorBagName: 'errors', // change if property conflicts
	fieldsBagName: 'fields',
	delay: 0,
	locale: 'en',
	dictionary: null,
	strict: true,
	classes: false,
	classNames: {
		touched: 'touched', // the control has been blurred
		untouched: 'untouched', // the control hasn't been blurred
		valid: 'valid', // model is valid
		invalid: 'invalid', // model is invalid
		pristine: 'pristine', // control has not been interacted with
		dirty: 'dirty' // control has been interacted with
	},
	events: 'input|blur',
	inject: true,
	validity: false,
	aria: true,
	i18n: null, // the vue-i18n plugin instance,
	i18nRootKey: 'validations' // the nested key under which the validation messsages will be located
};

new Vue({
	el: "#wiloke-review-settings",
	data: {
		usedSections: WILOKE_LISTING_TOOLS.addListing.usedSections,
		oAllSections: WILOKE_LISTING_TOOLS.addListing.allSections,
		oAvailableSections: WILOKE_LISTING_TOOLS.addListing.availableSections,
		oReviewSettings: WILOKE_LISTING_TOOLS.reviewSettings,
		oClaimFields: WILOKE_LISTING_TOOLS.claimfields,
		savingXHR: null,
		errorMsg:'',
		successMsg:'',
		aCustomFields: [
			'text', 'textarea', 'select', 'gallery', 'image'
		]
	},
	components:{
		Tabs,
		Tab,
		WilokeIcon,
		WilokeInput,
		WilokeCheckbox,
		WilokeHidden,
		WilokeSelect,
		WilokeTextarea
	},
	methods: {
		sectionName(oSection){
			if ( oSection.isCustomSection && oSection.isCustomSection === 'yes' ){
				return '( '+ oSection.type + ' - Custom Section' + ' )';
			}
			return '( ' + oSection.type + ' )';
		},
		isFieldEnabled(oFieldSettings, oSubField){
			if ( !oFieldSettings.dependencyOnToggle ){
				return true;
			}

			return (this.oUsedSections.fields.isEnabled === 'yes') || (oSubField.key === 'isEnabled');
		},
		uCaseFirst: function (customField) {
			let lengthOfText    = customField.length,
				firstCharacter  = customField.substr(0, 1),
				restCharacter   = customField.substr(1, lengthOfText);

			return firstCharacter.toUpperCase() + restCharacter;
		},
		generateKeyFromSectionName: function(blockName){
			blockName = blockName.toLowerCase();
			blockName = blockName.replace(/,|\.,\?/gi, function () {
				return '';
			});

			return blockName.split(' ').join('_').trim(' ');
		},
		changedSectionName: function(event){
			let sectionID = '';
			if ( jQuery(event.currentTarget).hasClass('isCustomSection') ){
				sectionID = jQuery(event.currentTarget).data('sectionid');
				this.usedSections[sectionID]['key'] = this.generateKeyFromSectionName(jQuery(event.currentTarget).val());
			}
		},
		expandBlockSettings: function (event) {
			jQuery(event.currentTarget).siblings('.dragArea__form-content').toggleClass('hidden');
		},
		addCustomSingleTab: function (event) {
			this.singleTabs.push({
				name: 'Custom Tab',
				isCustomTab: true,
				toggle: true,
				content: ''
			});
		},
		removeCustomTab: function (event) {
			let order = jQuery(event.currentTarget).data('order');
			this.singleTabs.splice(order, 1);
		},
		removeSection(sectionIndex, oSection){
			if ( oSection.isCustomSection && oSection.isCustomSection === 'yes' ){
				let askBeforeDeleting = confirm('You want to delete this block?');
				if ( !askBeforeDeleting ){
					return false;
				}
				this.usedSections.splice(sectionIndex, 1);
			}else{
				let oUsedSection = this.usedSections[sectionIndex];
				this.usedSections.splice(sectionIndex, 1);
				this.oAvailableSections.push(oUsedSection);
			}
		},
		saveSingleListingSettings: function (event) {
			if ( this.savingXHR !== null && this.savingXHR.status !== 200 ){
				this.savingXHR.abort();
			}

			let aSingleListingSettings = this.singleTabs, $form = jQuery('#wiloke-design-single-listing-form');

			$form.addClass('loading');

			this.savingXHR = jQuery.ajax({
				type: 'POST',
				url: ajaxurl,
				data: {
					results: aSingleListingSettings,
					action: 'wiloke_save_design_single_listing'
				},
				success:(response=>{
					if ( response.success ){
						this.successMsg = response.data.msg;
						setTimeout((()=>{
							this.successMsg = '';
						}), 10000);
					}else{
						this.errorMsg = response.data.msg;
						setTimeout((()=>{
							this.errorMsgs = '';
						}), 10000);
					}

					$form.removeClass('loading');
				})
			});

			return true;
		},
		addReviewCategory: function(event){
			this.oReviewSettings.details.push({
				name: 'Category name',
				key: '',
				isEditable: 'enable'
			});
		},
		removeReviewCategory: function (index) {
			this.oReviewSettings.details.splice(index, 1);
		},
		saveReviewSettings: function (event) {
			let $form = jQuery(event.currentTarget).closest('form');
			$form.addClass('loading');

			jQuery.ajax({
				type: 'POST',
				url: ajaxurl,
				data: {
					action: 'save_review_settings',
					value: this.oReviewSettings,
					postType: WILOKE_LISTING_TOOLS.postType
				},
				success(response) {
					if ( response.success ){
						this.reviewSuccess = response.data.msg;
						setTimeout(()=>{
							this.reviewSuccess = '';
						}, 50000);
					}else{
						this.reviewError = response.data.msg;
						setTimeout(()=>{
							this.reviewError = '';
						}, 50000);
					}

					$form.removeClass('loading');
				}
			})
		},
		changeClaimFieldType(oField, index){
			if ( oField.type === 'claimPackage' ){
				this.oClaimFields[index].key = 'claimPackage';
			}
		},
		addClaimField(){
			let oDate = new Date();
			this.oClaimFields.push({
				name: 'Label',
				key: 'label_'+oDate.getTime(),
				isRequired: 'no',
				type: 'text'
			});
			jQuery('#wilcity-claim-fields-wrapper').find('.ui.checkbox:last').checkbox();
		},
		removeClaimField(index){
			this.oClaimFields.splice(index, 1);
		},
		saveClaimFieldsSettings(){
			let $form = jQuery('#wiloke-design-claim-fields-form');

			$form.addClass('loading');

			jQuery.ajax({
				type: 'POST',
				data: {
					action: 'wiloke_save_claim_fields_setting',
					postType: WILOKE_LISTING_TOOLS.postType,
					data: this.oClaimFields
				},
				url: ajaxurl,
				success(response){
					if ( response.success ){
						this.successMsg = response.data.msg;
						setTimeout((()=>{
							this.successMsg = '';
						}), 4000);
					}else{
						this.errorMsg = response.data.msg;
						setTimeout((()=>{
							this.errorMsg = '';
						}), 4000);
					}

					$form.removeClass('loading');
				}
			})
		}
	},
	computed: {
		showingCustomFields: function () {
			let aUsedCustomFields = [];
			if ( this.usedBlock.length ){
				this.usedBlock.forEach(oField=>{
					if ( oField.isCustomField ){
						aUsedCustomFields.push(oField);
					}
				});
			}

			if ( !aUsedCustomFields.length ){
				return false;
			}else{
				return aUsedCustomFields;
			}
		}
	},
	mounted(){

	}
});

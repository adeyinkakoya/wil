import Tabs from '../dev/form-fields/Tabs.vue'
import Tab from '../dev/form-fields/Tab.vue'
import WilokeInput from '../dev/form-fields/Input.vue'
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
	el: "#wiloke-claim-settings",
	data: {
		oClaimFields: WILOKE_CLAIM_SETTINGS.aFields,
		savingXHR: null,
		aCustomFields: [
			'text', 'textarea', 'select', 'gallery', 'image'
		],
		errorMsg: '',
		successMsg: ''
	},
	components:{
		Tabs,
		Tab,
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
			let $form = jQuery('#wiloke-claim-settings');

			$form.addClass('loading');

			jQuery.ajax({
				type: 'POST',
				data: {
					action: 'wiloke_save_claim_fields_setting',
					data: this.oClaimFields
				},
				url: ajaxurl,
				success: (response)=>{
					if ( response.success ){
						this.successMsg = response.data.msg;

						setTimeout((()=>{
							this.successMsg = '';
						}), 10000);
					}else{
						this.errorMsg = response.data.msg;
						setTimeout((()=>{
							this.errorMsg = '';
						}), 10000);
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

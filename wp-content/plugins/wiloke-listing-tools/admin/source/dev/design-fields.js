import Tabs from '../dev/form-fields/Tabs.vue'
import Tab from '../dev/form-fields/Tab.vue'
import WilokeInput from '../dev/form-fields/Input.vue'
import WilokeIcon from '../dev/form-fields/Icon.vue'
import WilokeCheckbox from '../dev/form-fields/Checkbox.vue'
import WilokeHidden from '../dev/form-fields/Hidden.vue'
import WilokeSelect from '../dev/form-fields/Select.vue'
import WilokeTextarea from '../dev/form-fields/Textarea.vue'
import WilokeGroup from '../dev/new-form-fields/Group.vue'
import WilokeNewInput from '../dev/new-form-fields/Input.vue'

Vue.config.devtools = true;

if ( document.getElementById('wiloke-design-fields') !== null ){
	new Vue({
		el: "#wiloke-design-fields",
		data: {
			usedSections: WILOKE_LISTING_TOOLS.addListing.usedSections,
			oAllSections: WILOKE_LISTING_TOOLS.addListing.allSections,
			oAvailableSections: WILOKE_LISTING_TOOLS.addListing.availableSections,
			savingXHR: null,
			errorMsg:'',
			successMsg:'',
			aCustomFields: [
				'text', 'textarea', 'select', 'gallery', 'image'
			],
			reviewError: '',
			reviewSuccess: '',
			postType: WILOKE_LISTING_TOOLS.postType,
			ajaxAction: WILOKE_LISTING_TOOLS.addListing.ajaxAction
		},
		components:{
			Tabs,
			Tab,
			'wiloke-icon': WilokeIcon,
			'wiloke-text': WilokeInput,
			'wiloke-checkbox': WilokeCheckbox,
			'wiloke-hidden':WilokeHidden,
			'wiloke-select': WilokeSelect,
			'wiloke-textarea': WilokeTextarea,
			WilokeGroup,
			WilokeNewInput
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
			addedNewSectionInUsedArea(el){
				if ( typeof el.removed !== 'undefined' && el.removed.element.isNotDeleteAble !== 'undefined' && el.removed.element.isNotDeleteAble ){
					this.usedSections.splice(el.removed.oldIndex, 0, el.removed.element);
					return false;
				}

				if ( typeof el.added === 'undefined' || el.added.element.isCustomSection !== 'yes'){
					return false;
				}
				this.oAvailableSections.push(el.added.element);
			},
			addedNewSectionInAvailableArea(el){
				if ( typeof el.added !== 'undefined' && el.added.element.isNotDeleteAble !== 'undefined' && el.added.element.isNotDeleteAble ){
					this.oAvailableSections.splice(el.added.newIndex, 1);
					return false;
				}

				if ( typeof el.added === 'undefined' || el.added.element.isCustomSection !== 'no'){
					return false;
				}

				this.oAvailableSections.splice(el.added.newIndex, 1);
			},
			saveValue(event){
				if ( this.savingXHR !== null && this.savingXHR.status !== 200 ){
					this.$form.addClass('loading');
					this.savingXHR.abort();
				}

				let $form = jQuery(event.currentTarget), aUsedBlock = this.usedSections;

				$form.addClass('loading');
				this.savingXHR = jQuery.ajax({
					type: 'POST',
					url: ajaxurl,
					data: {
						results: aUsedBlock,
						action: this.ajaxAction,
						postType: this.postType
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
								this.errorMsg = '';
							}), 10000);
						}

						$form.removeClass('loading');
					})
				});
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
				if ( oSection.isNotDeleteAble ){
					alert('This section is required by the theme!');
					return false;
				}
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
			resetDefault(){
				let iWantToReset = confirm('Do you want to reset to the default settings?');
				if ( !iWantToReset ){
					return false;
				}
				let action = 'wilcity_reset_addlisting_settings';
				if ( this.postType === 'event' ){
					action = 'wilcity_reset_event_settings';
				}

				jQuery.ajax({
					url: ajaxurl,
					type: 'POST',
					data: {
						action: action,
						postType: this.postType,
						isReset: 'yes'
					},
					success: ((response)=>{
						if ( response.success ){
							this.successMsg = response.data.msg;
							setTimeout(()=>{
								this.successMsg = '';
							}, 1000);
						}else{
							this.errorMsg = response.data.msg;
							setTimeout(()=>{
								this.errorMsg = '';
							}, 1000);
						}
					})
				})
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
								this.errorMsg = '';
							}), 10000);
						}

						$form.removeClass('loading');
					})
				});

				return true;
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
		}
	});
}
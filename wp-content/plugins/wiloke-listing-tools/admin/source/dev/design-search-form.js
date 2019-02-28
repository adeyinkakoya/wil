Vue.config.devtools = true;

if ( document.getElementById('wiloke-design-search-form') !== null ){
	new Vue({
		el: "#wiloke-design-search-form",
		data: {
			oUsedFields: Object.values(WILOKE_LISTING_TOOLS.aSearchForm.aUsedFields),
			oAllFields: WILOKE_LISTING_TOOLS.aSearchForm.aAllFields,
			oAvailableFields: (WILOKE_LISTING_TOOLS.aSearchForm.aAvailableFields.length) ? WILOKE_LISTING_TOOLS.aSearchForm.aAvailableFields : [],
			savingXHR: null,
			errorMsg: '',
			successMsg: '',
			postType: WILOKE_LISTING_TOOLS.postType,
			ajaxAction: WILOKE_LISTING_TOOLS.aSearchForm.ajaxAction
		},
		methods: {
			resetDefaults(){
				let iWantToReset = confirm('Do you want to reset the settings?');
				if ( !iWantToReset ){
					return false;
				}
				let $form = jQuery('#wiloke-design-search-form-form');
				$form.addClass('loading');

				jQuery.ajax({
					type: 'POST',
					url: ajaxurl,
					data: {
						action: 'wilcity_reset_to_default_search_form',
						postType: this.postType
					},
					success: (response=>{
						$form.addClass('loading');

						if ( response.success ){
							this.successMsg = response.data.msg;
							setTimeout((()=>{
								this.successMsg = '';
								location.reload();
							}), 40000);
						}else{
							this.errorMsg = response.data.msg;
							setTimeout((()=>{
								this.errorMsg = '';
								location.reload();
							}), 40000);
						}

						$form.removeClass('loading');
					})
				})
			},
			dragFormClass(oSection){
				return {
					'dragArea__form ui form field-wrapper segment': 1===1,
					'red': oSection.isTemporaryDisable === 'yes'
				}
			},
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

				return (this.aUsedSections.fields.isEnabled === 'yes') || (oSubField.key === 'isEnabled');
			},
			uCaseFirst: function (customField) {
				let lengthOfText    = customField.length,
					firstCharacter  = customField.substr(0, 1),
					restCharacter   = customField.substr(1, lengthOfText);

				return firstCharacter.toUpperCase() + restCharacter;
			},
			addedNewSectionInUsedArea(el){
				if ( typeof el.removed !== 'undefined' && el.removed.element.isNotDeleteAble !== 'undefined' && el.removed.element.isNotDeleteAble ){
					this.aUsedSections.splice(el.removed.oldIndex, 0, el.removed.element);
					return false;
				}

				if ( typeof el.added === 'undefined' || el.added.element.isCustomSection !== 'yes'){
					return false;
				}
				this.oAvailableSections.push(el.added.element);
				this.aUsedSections.splice(el.added.element.newIndex, 1, JSON.parse(JSON.stringify(el.added.element)));
			},
			addedNewSectionInAvailableArea(el){
				if ( typeof el.added !== 'undefined' && el.added.element.isNotDeleteAble !== 'undefined' && el.added.element.isNotDeleteAble ){
					this.oAvailableSections.splice(el.added.newIndex, 1);
					this.savedMessage = 'We can not delete a required section';
					setTimeout(()=>{
						this.savedMessage = '';
					}, 10000);
					return false;
				}

				if ( typeof el.added === 'undefined' || el.added.element.isCustomSection !== 'no'){
					return false;
				}

				this.oAvailableSections.splice(el.added.newIndex, 1);
			},
			saveChanges(event){
				if ( this.savingXHR !== null && this.savingXHR.status !== 200 ){
					this.$form.addClass('loading');
					this.savingXHR.abort();
				}

				let $form = jQuery(event.currentTarget), $printMsg = jQuery('#print-msg');

				$form.addClass('loading');
				this.savingXHR = jQuery.ajax({
					type: 'POST',
					url: ajaxurl,
					data: {
						data: this.oUsedFields,
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
							$printMsg.addClass('hidden');
							alert(response.data.msg);
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
					this.aUsedSections[sectionID]['key'] = this.generateKeyFromSectionName(jQuery(event.currentTarget).val());
				}
			},
			expandBlockSettings: function (event) {
				jQuery(event.currentTarget).siblings('.dragArea__form-content').toggleClass('hidden');
			},
			addMoreSection: function (){

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
					this.oUsedFields.splice(sectionIndex, 1);
				}else{
					let oUsedField = this.oUsedFields[sectionIndex];
					this.oUsedFields.splice(sectionIndex, 1);
					this.oAvailableFields.push(oUsedField);
				}
			}
		},
		mounted(){

		}
	});
}
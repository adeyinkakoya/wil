import WilokeColorPicker from './form-fields/ColorPicker.vue'

if ( document.getElementById('wiloke-design-highlight-boxes') !== null ){
	new Vue({
		el: "#wiloke-design-highlight-boxes",
		data: {
			aBoxes: WILOKE_LISTING_TOOLS.aBoxes,
			savingXHR: null,
			aIsUsingPopupOnFrontend: ['wilcity-message-popup', 'wilcity-claim-popup', 'event', 'wilcity-add-photos-videos-popup'],
			successMsg: '',
			errorMsg: '',
			postType: WILOKE_LISTING_TOOLS.postType,
			ajaxAction: WILOKE_LISTING_TOOLS.aBoxes.ajaxAction
		},
		components: {
			WilokeColorPicker
		},
		methods: {
			removeItem(index, oSections){
				if ( typeof oSections.aItems[index].isCustomSection === 'undefined' ){
					alert('You can not delete the default sections, but you can disable it');
					return false;
				}

				let iWantToDeleteIt = confirm('Do you want to delete this section?');
				if ( !iWantToDeleteIt ){
					return false;
				}

				this.aBoxes.aItems.splice(index, 1);
			},
			addNewItem(){
				let oDate = new Date();

				this.aBoxes.aItems.push({
					name: 'Custom Section',
					key:  oDate.getTime(),
					icon: 'la la-image',
					bgColor: '',
					type: 'custom-link',
					isCustomSection: 'yes',
					linkTargetType: 'self',
					linkTo: ''
				});
			},
			nameChanged: function(index){
				if ( this.aIsUsingPopupOnFrontend.indexOf(this.aBoxes.aItems[index].type) === -1 ){
					let nameToLower = this.aBoxes.aItems[index].name.toLowerCase();
					nameToLower = nameToLower.replace(/,|\.,\?/gi, function () {
						return '';
					});

					nameToLower = nameToLower.split(' ').join('_').trim(' ');
					this.aBoxes.aItems[index].key = nameToLower;
				}
			},
			changeBoxType(index){
				let order = this.aIsUsingPopupOnFrontend.indexOf(this.aBoxes.aItems[index].type);
				if (  order !== -1 ){
					this.aBoxes.aItems[index].key = this.aIsUsingPopupOnFrontend[order];
					this.aBoxes.aItems[index].isPopup = 'yes';
				}else{
					let oDate = new Date();
					this.aBoxes.aItems[index].key = oDate.getTime();
					this.aBoxes.aItems[index].isPopup = 'no';
				}
			},
			saveChanges(event){
				if ( this.savingXHR !== null && this.savingXHR.status !== 200 ){
					this.$form.addClass('loading');
					this.savingXHR.abort();
				}

				let $form = jQuery(event.currentTarget).closest('form');
				jQuery('#wiloke-design-highlight-boxes').addClass('loading');

				this.savingXHR = jQuery.ajax({
					type: 'POST',
					url: ajaxurl,
					data: {
						data: this.aBoxes,
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
			}
		}
	});
}
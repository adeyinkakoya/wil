Vue.config.devtools = true;

if ( document.getElementById('wiloke-design-single-nav') !== null ){
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
		el: "#wiloke-design-single-nav",
		data: {
			aSingleNav: Object.values(WILOKE_LISTING_TOOLS.aSingleNavigation.aSections),
			savingXHR: null,
			savedMessage:'',
			aCustomFields: [
				'text', 'textarea', 'select', 'gallery', 'image'
			],
			successMsg: '',
			errorMsg: '',
			postType: WILOKE_LISTING_TOOLS.postType,
			ajaxAction: WILOKE_LISTING_TOOLS.aSingleNavigation.ajaxAction
		},
		methods: {
			checkMove(evt){
				return (typeof evt.draggedContext.element.isDraggable === 'undefined' || evt.draggedContext.element.isDraggable === 'yes');
			},
			reset(){
				let iWantToReset = confirm('Do you want to reset this setting?');
				if ( !iWantToReset ){
					return false;
				}

				let $form = jQuery(event.currentTarget).closest('form');
				$form.addClass('loading');

				jQuery.ajax({
					type: 'POST',
					url: ajaxurl,
					data: {
						action: this.ajaxAction,
						postType: this.postType,
						isReset: 'yes'
					},
					success:(response=>{
						if ( response.success ){
							this.successMsg = response.data.msg;
							setTimeout((()=>{
								this.successMsg = '';
								location.reload();
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
			removeItem(index, oSections){
				if ( typeof oSections[index].isCustomSection === 'undefined' ){
					alert('You can not delete the default sections, but you can disable it');
					return false;
				}

				let iWantToDeleteIt = confirm('Do you want to delete this section?');
				if ( !iWantToDeleteIt ){
					return false;
				}

				this.aSingleNav.splice(index, 1);
			},
			addNewSection(){
				let oDate = new Date();
				this.aSingleNav.push({
					name: 'Custom Section',
					key:  oDate.getTime(),
					icon: 'la la-image',
					isCustomSection: 'yes',
					isShowOnHome: 'yes',
					status: 'yes',
					content: ''
				});
			},
			addNewSectionToTop(){
				let oDate = new Date();
				this.aSingleNav.unshift({
					name: 'Custom Section',
					key:  oDate.getTime(),
					icon: 'la la-image',
					isCustomSection: 'yes',
					isShowOnHome: 'yes',
					status: 'yes',
					content: ''
				});
			},
			saveChanges(event){
				if ( this.savingXHR !== null && this.savingXHR.status !== 200 ){
					this.$form.addClass('loading');
					this.savingXHR.abort();
				}

				let $form = jQuery(event.currentTarget).closest('form');
				jQuery('#wiloke-design-single-nav').addClass('loading');

				this.savingXHR = jQuery.ajax({
					type: 'POST',
					url: ajaxurl,
					data: {
						data: this.aSingleNav,
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
		},
		mounted(){
		}
	});
}
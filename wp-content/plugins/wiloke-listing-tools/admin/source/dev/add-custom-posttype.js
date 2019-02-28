import WilokeColorPicker from './form-fields/ColorPicker.vue'

if ( document.getElementById('wiloke-add-custom-posttypes') !== null ){
	new Vue({
		el: "#wiloke-add-custom-posttypes",
		data: {
			errorMessage: '',
			successMessage: '',
			savingXHR: null,
			oFields: WILOKE_CUSTOM_POSTTYPES
		},
		components: {
			WilokeColorPicker
		},
		methods: {
			testFat(){

			},
			saveAddCustomPostTypes(event){
				if ( this.savingXHR !== null && this.savingXHR.status !== 200 ){
					this.savingXHR.abort();
				}
				jQuery(this.$el).addClass('loading');

				this.savingXHR = jQuery.ajax({
					type: 'POST',
					url: ajaxurl,
					data: {
						action: 'wiloke_save_custom_posttypes',
						data: this.oFields
					},
					success: (response=>{
						if ( response.success ){
							this.successMessage = response.data.msg;
						}else{
							this.errorMessage = response.data.msg;
							setTimeout(()=>{
								this.errorMessage = '';
							}, 3000);
						}

						jQuery(this.$el).removeClass('loading');
					})
				})
			},
			removePostType(index){
				let iWantToDeleteIt = confirm('Do you want to delete this post type?');
				if( !iWantToDeleteIt ){
					return false;
				}
				this.oFields.splice(index, 1);
			},
			addCustomPostType(){
				this.oFields.push({
					key: '',
					slug: '',
					name: ''
				});

				jQuery(this.$el).find('.wilcity-color-picker:last').spectrum({
					showAlpha: true
				});
			},
			test(){

			}
		},
		mounted(){

		}
	})
}
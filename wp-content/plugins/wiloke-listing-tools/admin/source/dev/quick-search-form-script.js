import SelectFromPostTypes from './form-fields/SelectFromPostTypes.vue'

new Vue({
	el: "#wiloke-quick-search-form-settings",
	data: {
		aFields: WILOKE_QUICK_SEARCH_FORM.aFields,
		isLoading: false,
		errorMsg: '',
		page: 1,
		successMsg: ''
	},
	computed: {
		formClass(){
			return this.isLoading ? 'form ui loading' : 'form ui';
		}
	},
	methods: {
		saveChanges(){
			this.isLoading = true;
			this.errorMsg = this.successMsg = '';

			jQuery.ajax({
				type: 'POST',
				url: ajaxurl,
				data:{
					action: 'wiloke_save_quick_search_form',
					aFields: this.aFields,
					page: this.page
				},
				success: ((response)=>{
					this.errMsg = '';
					this.successMsg = '';

					if ( !response.success ){
						this.errMsg = response.data.msg;
						this.isLoading = false;
					}else{
						if ( typeof response.data.continue !== 'undefined' ){
							this.page++;
							this.saveChanges();
						}else{
							this.successMsg = response.data.msg;
							this.isLoading = false;
							this.page = 1;
						}
					}
				})
			})
		}
	}
});
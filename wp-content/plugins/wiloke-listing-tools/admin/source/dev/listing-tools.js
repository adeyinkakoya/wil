if ( document.getElementById('wiloke-listing-tools-general') !== null ){
	new Vue({
		el: "#wiloke-listing-tools-general",
		data: {
			savingXHR:null,
			aResponse:[],
			isLoading: false
		},
		computed: {
			additionalClass(){
				return this.isLoading ? 'loading' : '';
			}
		},
		methods: {
			printStyle(oItem){
				if ( oItem.status === 'error' ){
					return 'color: red !important';
				}

				return '';
			},
			installPages(){
				if (this.savingXHR !== null && this.savingXHR.status !== 200) {
					this.isLoading = false;
					this.savingXHR.abort();
				}

				this.isLoading = true;
				this.savingXHR = jQuery.ajax({
					type: 'POST',
					url: ajaxurl,
					data: {
						action: 'wiloke_install_submission_pages'
					},
					success: (response => {
						this.aResponse = response.data;
						this.isLoading = false;
					})
				});
			}
		}
	});
}
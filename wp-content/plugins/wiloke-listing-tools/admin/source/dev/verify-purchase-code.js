if ( document.getElementById('wilcity-verify-purchase-code-form') !== null ){
	new Vue({
		el: "#wilcity-verify-purchase-code-form",
		data: {
			savingXHR:null,
			aResponse:[],
			isLoading: false,
			isRevoking: false,
			purchasedCode: null
		},
		computed: {
			formClass(){
				return this.isLoading ? 'loading form ui' : 'form ui';
			},
			revokeBtnClass(){
				return this.isRevoking ? 'ui red button loading' : 'ui red button';
			}
		},
		methods: {
			verifyPurchaseCode(){
				if (this.savingXHR !== null && this.savingXHR.status !== 200) {
					this.isLoading = false;
					this.savingXHR.abort();
				}

				this.isLoading = true;
				this.savingXHR = jQuery.ajax({
					type: 'POST',
					url: ajaxurl,
					data: {
						action: 'wiloke_verify_purchase_code',
						purchasedCode: this.purchasedCode
					},
					success: (response => {
						if ( response.data.success ){
							location.reload();
						}else{
							alert(response.data.msg);
						}
						this.isLoading = false;
					})
				});
			},
			revokeLicense(){
				if (this.savingXHR !== null && this.savingXHR.status !== 200) {
					this.isRevoking = false;
					this.savingXHR.abort();
				}

				this.isRevoking = true;
				this.savingXHR = jQuery.ajax({
					type: 'POST',
					url: ajaxurl,
					data: {
						action: 'wiloke_revoke_purchase_code',
						purchasedCode: this.purchasedCode
					},
					success: (response => {
						if ( response.data.success ){
							location.reload();
						}else{
							alert(response.data.msg);
						}
						this.isRevoking = false;
					})
				});
			}
		}
	});
}
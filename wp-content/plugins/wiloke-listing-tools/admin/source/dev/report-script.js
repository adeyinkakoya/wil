new Vue({
	el: "#wiloke-report-settings",
	data: {
		aFields: WILOKE_REPORT.fields,
		toggle: WILOKE_REPORT.toggle,
		thankyou: WILOKE_REPORT.thankyou,
		description: WILOKE_REPORT.description,
		isLoading: false,
		errorMsg: '',
		successMsg: ''
	},
	methods: {
		addField(){
			let oDate = new Date();

			this.aFields.push({
				label: 'Field Name',
				key: 'field_key_'+oDate.getTime(),
				type: 'text',
				options: ''
			});
		},
		saveChanges(){
			this.isLoading = true;
			jQuery.ajax({
				type: 'POST',
				url: ajaxurl,
				data:{
					action: 'wiloke_save_report_settings',
					fields: this.aFields,
					toggle: this.toggle,
					thankyou: this.thankyou,
					description: this.description
				},
				success: ((response)=>{
					this.errMsg = '';
					this.successMsg = '';

					this.isLoading = false;
					if ( !response.success ){
						this.errMsg = 'Something went to error';
					}else{
						this.successMsg = 'The plans have been saved successfully';
					}
				})
			})
		},
		removeField(index){
			let iWantToRemove = confirm('Do you want to remove this field?');

			if ( !iWantToRemove ){
				return false;
			}

			this.aFields.splice(index, 1);
		}
	},
	mounted(){

	},
	computed: {
		formClass(){
			return {
				'ui form': 1===1,
				'loading': this.isLoading
			}
		},
		isPositionsExist(){
			return this.oAvailablePositions.length;
		}
	}
});
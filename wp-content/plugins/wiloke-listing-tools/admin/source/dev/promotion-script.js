import SelectFromPostTypes from './form-fields/SelectFromPostTypes.vue'

new Vue({
	el: "#wiloke-promotion-settings",
	data: {
		oPositions: WILOKE_PROMOTIONS.positions,
		aPlans: WILOKE_PROMOTIONS.plans,
		toggle: WILOKE_PROMOTIONS.toggle,
		oAvailablePositions: WILOKE_PROMOTIONS.availablePositions,
		isLoading: false,
		errorMsg: '',
		successMsg: ''
	},
	components: {
		SelectFromPostTypes
	},
	methods: {
		updateWooCommerceAssociate(values, oSettings){
			this.aPlans[oSettings.index].productAssociation = values;
		},
		saveChanges(event){
			this.isLoading = true;

			jQuery.ajax({
				type: 'POST',
				url: ajaxurl,
				data:{
					action: 'wiloke_save_promotion_settings',
					plans: this.aPlans,
					toggle: this.toggle
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
		removePlan(index){
			let iWantToRemove = confirm('Do you want to remove this plan?');

			if ( !iWantToRemove ){
				return false;
			}

			this.aPlans.splice(index, 1);
		},
		getAvailablePositions(){
			if ( this.aPlans.length ){
				for ( let index in this.aPlans ){
					let findIndex = this.oAvailablePositions.indexOf(this.aPlans[index].position);
					this.oAvailablePositions.splice(findIndex, 1);
				}
			}
		},
		addPlan(){
			this.aPlans.push({
				name: '',
				price: '',
				duration:'',
				position: ''
			});

			this.getAvailablePositions();
		},
		optionClass(position){
			if ( !this.oAvailablePositions.length ){
				return '';
			}

			if ( this.oAvailablePositions.indexOf(position) === -1 ){
				return 'selected';
			}else{
				return '';
			}
		}
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
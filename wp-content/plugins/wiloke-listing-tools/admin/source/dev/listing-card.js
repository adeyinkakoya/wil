import Tabs from '../dev/form-fields/Tabs.vue'
import Tab from '../dev/form-fields/Tab.vue'
import WilokeCheckbox from '../dev/form-fields/Checkbox.vue'
import WilokeHidden from '../dev/form-fields/Hidden.vue'

import WilokeTextarea from '../dev/form-fields/Textarea.vue'
import WilokeGroup from '../dev/new-form-fields/Group.vue'
import WilokeNewInput from '../dev/new-form-fields/Input.vue'
import WilokeNewIcon from '../dev/new-form-fields/Icon.vue'
import WilokeInputReadOnly from '../dev/new-form-fields/InputReadOnly.vue'
import WilokeNewTextarea from '../dev/new-form-fields/Textarea.vue'
import WilokeNewSelect from '../dev/new-form-fields/Select.vue'

Vue.config.devtools = true;

if ( document.getElementById('wiloke-listing-cards') !== null ){
	new Vue({
		el: "#wiloke-listing-cards",
		data: {
			aBodyAvailableFields: WILOKE_LISTING_TOOLS.aListingCard.aSettings.aBody.aAvailableFields,
			aBodyUsedFields: WILOKE_LISTING_TOOLS.aListingCard.aSettings.aBody.aUsedFields,
			aBodyTypeFields: WILOKE_LISTING_TOOLS.aListingCard.aSettings.aBody.aTypeOptions,
			aTypeKeyRelationship: WILOKE_LISTING_TOOLS.aListingCard.aSettings.aBody.aTypeKeyRelationship,
			savingXHR: null,
			errorMsg: '',
			successMsg: '',
			isLoading: false,
			aCustomFields: [
				'text', 'textarea', 'select', 'gallery', 'image'
			],
			postType: WILOKE_LISTING_TOOLS.postType,
			ajaxAction: WILOKE_LISTING_TOOLS.aListingCard.ajaxAction
		},
		components: {
			Tabs,
			Tab,
			'wiloke-checkbox': WilokeCheckbox,
			'wiloke-hidden': WilokeHidden,
			'wiloke-textarea': WilokeTextarea,
			WilokeGroup,
			WilokeNewInput,
			WilokeNewTextarea,
			WilokeNewIcon,
			WilokeInputReadOnly,
			WilokeNewSelect
		},
		computed: {
			wrapperClass(){
				if ( this.isLoading ){
					return 'form ui loading';
				}

				return 'form ui';
			}
		},
		methods: {
			removeItem(index, oBodyFields){
				this.aBodyUsedFields.splice(index, 1);
			},
			addItem(position){
				if ( position === 'top' ){
					this.aBodyUsedFields.unshift({
						icon:'',
						type:'',
						value:'',
						key:''
					});
				}else{
					this.aBodyUsedFields.push({
						icon:'',
						type:'',
						value:'',
						key:''
					});
				}
			},
			saveChanges(){
				this.isLoading = true;

				jQuery.ajax({
					type: 'POST',
					url: ajaxurl,
					data: {
						action: 'wilcity_save_listing_card',
						postType: this.postType,
						aBodyUsedFields: this.aBodyUsedFields
					},
					success: response => {
						this.errorMsg = '';
						this.successMsg = '';
						if ( response.success ){
							this.successMsg = response.data.msg;
						}else{
							this.errorMsg = response.data.msg;
						}
						this.isLoading = false;
					}
				})
			},
			changeType(type, index){
				if ( type !== 'custom_field' ){
					this.aBodyUsedFields[index]['key'] = type;
				}
			},
			resetSettings(){
				let iWantToReset = confirm('Do you want to reset to the default setting?');
				if ( !iWantToReset ){
					return false;
				}
				this.isLoading = true;
				jQuery.ajax({
					type: 'POST',
					url: ajaxurl,
					data: {
						action: 'wilcity_reset_listing_card',
						postType: this.postType
					},
					success: response => {
						this.errorMsg = '';
						this.successMsg = '';
						if ( response.success ){
							this.successMsg = response.data.msg;
							this.aBodyUsedFields = response.data.aData;
						}else{
							this.errorMsg = response.data.msg;
						}
						this.isLoading = false;
					}
				})
			}
		}
	});
}
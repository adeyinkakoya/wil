import Tabs from '../dev/form-fields/Tabs.vue'
import Tab from '../dev/form-fields/Tab.vue'
import WilokeInput from '../dev/form-fields/Input.vue'
import WilokeCheckbox from '../dev/form-fields/Checkbox.vue'
import WilokeHidden from '../dev/form-fields/Hidden.vue'
import WilokeSelect from '../dev/form-fields/Select.vue'
import WilokeTextarea from '../dev/form-fields/Textarea.vue'
import AjaxSearchField from '../dev/form-fields/AjaxSearchField.vue'

new Vue({
	el: "#wiloke-event-settings",
	data() {
		return {
			oEventGenerals: WILOKE_EVENT_GENERAL_SETTINGS,
			isLoading: false,
			errMsg: '',
			successMsg: ''
		}
	},
	components:{
		Tabs,
		Tab,
		WilokeInput,
		WilokeCheckbox,
		WilokeHidden,
		WilokeSelect,
		WilokeTextarea,
		AjaxSearchField
	},
	methods: {
		saveGeneralSettings(event){
			this.isLoading = true;

			jQuery.ajax({
				type: 'POST',
				url: ajaxurl,
				data:{
					action: 'wiloke_save_event_general_settings',
					settings: this.oEventGenerals
				},
				success: ((response)=>{
					this.errMsg = '';
					this.successMsg = '';

					this.isLoading = false;
					if ( !response.success ){
						this.errMsg = 'Something went to error';
					}else{
						this.successMsg = 'The event settings have been updated successfully';
					}
				})
			})
		}
	},
	computed: {
		formClass(){
			return {
				'ui form wiloke-form-has-icon': 1===1,
				'loading': this.isLoading
			}
		}
	}
});

new Vue({
	el: '#wiloke-design-single-event-content',
	data() {
		return {
			oContent: WILOKE_EVENT_CONTENT,
			isLoading: false,
			errorMsg: '',
			successMsg: ''
		};
	},
	components:{
		Tabs,
		Tab,
		WilokeInput,
		WilokeCheckbox,
		WilokeHidden,
		WilokeSelect,
		WilokeTextarea,
		AjaxSearchField
	},
	computed:{
		formClass(){
			return !this.isLoading ? 'ui form wiloke-form-has-icon' : 'ui form loading wiloke-form-has-icon';
		}
	},
	methods:{
		addNewSection(){
			this.oContent.push({
				key: '',
				icon: '',
				name: ''
			});
		},
		saveContent(){
			this.isLoading = true;
			jQuery.ajax({
				url: ajaxurl,
				type: 'POST',
				data: {
					action: 'wlt_save_event_content',
					fields: this.oContent,
					postType: 'event'
				},
				success: response => {
					if ( response.success ){
						this.successMsg = response.data.msg;
					}else{
						this.errorMsg = response.data.msg;
					}
					this.isLoading = false;
				}
			})
		},
		removeItem(index){
			this.oContent.splice(index, 1);
		}
	}
});
Vue.config.devtools = true;
import JsonEditor from 'vue-json-edit'
Vue.use(JsonEditor);
if ( document.getElementById('wiloke-schema-markup') !== null ){
	new Vue({
		el: "#wiloke-schema-markup",
		data(){
			return {
				oSchemaMarkup: this.oParsedSchemaMarkupValue(WILOKE_LISTING_TOOLS.aSchemaMarkup.aSettings),
				ajaxAction: WILOKE_LISTING_TOOLS.aSchemaMarkup.ajaxAction,
				errorMsg: '',
				successMsg: '',
				xhr: null,
				isLoading: false,
				postType: WILOKE_LISTING_TOOLS.postType
			}
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
			oParsedSchemaMarkupValue(rawVal){
				if ( typeof rawVal !== 'undefined' && rawVal !== null  ){
					return typeof rawVal === 'object' ? rawVal :  JSON.parse(rawVal);
				}
				return {};
			},
			saveChanges(){
				this.isLoading = true;
				if ( this.xhr !== null && this.xhr.status !== 200 ){
					this.xhr.abort();
				}
				this.errorMsg = '';
				this.successMsg = '';

				this.xhr = jQuery.ajax({
					type: 'POST',
					url: ajaxurl,
					data: {
						action: this.ajaxAction,
						data: this.oSchemaMarkup,
						postType: this.postType
					},
					success: response => {
						if ( response.success ){
							this.successMsg = response.data.msg;
						}else{
							this.errorMsg = response.data.msg;
						}
						this.isLoading = false;
					}
				});
			},
			resetSetting(){
				this.isLoading = true;
				this.xhr = jQuery.ajax({
					type: 'POST',
					url: ajaxurl,
					data: {
						action: this.ajaxAction + '_reset',
						postType: this.postType
					},
					success: response => {
						if ( response.success ){
							this.successMsg = response.data.msg;
							this.$set(this.$data, 'oSchemaMarkup', JSON.parse(response.data.settings));
						}else{
							this.errorMsg = response.data.msg;
						}
						this.isLoading = false;
					}
				});
			}
		}
	});
}
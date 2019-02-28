import Tabs from '../dev/new-form-fields/Tabs.vue'
import Tab from '../dev/new-form-fields/Tab.vue'
import WilokeInput from '../dev/new-form-fields/Input.vue'
import WilokeIcon from '../dev/new-form-fields/Icon.vue'
import WilokeAjaxSearchField from '../dev/new-form-fields/AjaxSearchField.vue'
import WilokeInputReadOnly from '../dev/new-form-fields/InputReadOnly.vue'
import WilokeSelect from '../dev/new-form-fields/Select.vue'

Vue.config.devtools = true;
// <component v-for="(oField, subIndex) in oMenuItem.aFields" v-model="oField.value" :is="oField.component" :label="oField.label ? oField.label : oField.value" :std="oField.value" :desc="oField.desc" :a-options="oField.aOptions" :action="oField.action"></component>
new Vue({
	el: "#wiloke-mobile-main-menu-settings",
	data: {
		aAvailableMenuItems: WILOKE_MAIN_MENU_AVAILABLE_MENU,
		aUsedMenuItems: WILOKE_MAIN_MOBILE_MENU,
		savingXHR: null,
		errorMsg: '',
		successMsg: '',
		isLoading: false,
		isRefusedNewItem: false,
		maximumItems: 5
	},
	components:{
		Tabs,
		Tab,
		WilokeInput,
		WilokeIcon,
		WilokeAjaxSearchField,
		WilokeSelect,
		WilokeInputReadOnly
	},
	computed:{
		formClass(){
			return {
				'ui form': 1===1,
				'loading': this.isLoading
			}
		}
	},
	methods: {
		wrapperClass(oGeneral){
			let cl = '';
			if ( typeof oGeneral.class !== 'undefined' ){
				cl += ' ' + oGeneral.class;
			}
			return cl;
		},
		expandBlockSettings: function (event) {
			jQuery(event.currentTarget).siblings('.dragArea__form-content').toggleClass('hidden');
		},
		sectionName(oSection){
			if ( oSection.isCustomSection && oSection.isCustomSection === 'yes' ){
				return '( '+ oSection.type + ' - Custom Section' + ' )';
			}
			return '( ' + oSection.type + ' )';
		},
		uCaseFirst: function (customField) {
			let lengthOfText    = customField.length,
				firstCharacter  = customField.substr(0, 1),
				restCharacter   = customField.substr(1, lengthOfText);

			return firstCharacter.toUpperCase() + restCharacter;
		},
		generateKeyFromSectionName: function(blockName){
			blockName = blockName.toLowerCase();
			blockName = blockName.replace(/,|\.,\?/gi, function () {
				return '';
			});

			return blockName.split(' ').join('_').trim(' ');
		},
		addedNewSectionInUsedArea(oItem){
			if ( typeof oItem.added !== 'undefined' ){
				if ( this.aUsedMenuItems.length > this.maximumItems ){
					this.isRefusedNewItem = true;
					this.aUsedMenuItems.splice(oItem.added.newIndex, 1);
					alert('You have exceeded the number of menu items on the main menu');
					return false;
				}
			}
		},
		removeASectionInAvailableArea(oItem){
			if ( typeof oItem.removed !== 'undefined' && typeof oItem.removed.element !== 'undefined' ){
				if ( typeof oItem.removed.element.oGeneral.canClone !== 'undefined' ){
					if ( oItem.removed.element.oGeneral.canClone === 'no' ){
						if ( this.isRefusedNewItem && this.aUsedMenuItems.length === this.maximumItems ){
							this.aAvailableMenuItems.push(oItem.removed.element);
							this.isRefusedNewItem = false;
						}
					}else{
						this.aAvailableMenuItems.push(oItem.removed.element);
					}
				}
			}
		},
		removeSection(index, oMenuItem){
			this.aUsedMenuItems.splice(index, 1);
			this.aAvailableMenuItems.push(oMenuItem);
		},
		saveChanges(){
			this.isLoading = true;
			jQuery.ajax({
				type: 'POST',
				data: {
					action: 'wilcity_save_main_mobile_menu_settings',
					data: this.aUsedMenuItems
				},
				url: ajaxurl,
				success: (response)=>{
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
					this.isLoading = false;
				}
			})
		}
	}
});

new Vue({
	el: '#wiloke-mobile-secondary-menu-settings',
	data: {
		aMenuItems: WILOKE_SECONDARY_MENU_ITEMS,
		aAvailableMenuItems: WILOKE_SECONDARY_AVAILABLE_MENU_ITEMS,
		aUsedMenuItems: WILCITY_SECONDARY_USED_MENU_ITEMS,
		savingXHR: null,
		errorMsg: '',
		successMsg: '',
		aDeleteAble: ['homeSlack', 'eventSlack'],
		isLoading: false
	},
	computed:{
		formClass(){
			return {
				'ui form': 1===1,
				'loading': this.isLoading
			}
		}
	},
	methods: {
		addedNewSectionInAvailableArea(el){
			if ( typeof el.removed !== 'undefined' ){
				if ( this.aDeleteAble.indexOf(el.removed.element) !== -1 ){
					//this.aAvailableMenuItems.splice(el.added.oldIndex, 1);
				}
			}else{

			}
		},
		addedNewSectionInUsedArea(el){
			if ( typeof el.added !== 'undefined' ){
				if ( typeof this.aMenuItems[el.added.element] !== 'undefined' ){
					//this.aUsedMenuItems.push(this.aMenuItems[el.added.element]);

				}
			}
			let screen = el.added.element.oGeneral.screen;
			let oRawMenuItem = this.aMenuItems[screen];
			this.aAvailableMenuItems.push(oRawMenuItem);
		},
		expandBlockSettings: function (event) {
			jQuery(event.currentTarget).siblings('.dragArea__form-content').toggleClass('hidden');
		},
		saveChanges(){
			this.isLoading = true;
			jQuery.ajax({
				type: 'POST',
				data: {
					action: 'wilcity_save_secondary_menu_settings',
					data: this.aUsedMenuItems
				},
				url: ajaxurl,
				success: response => {
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
					this.isLoading = false;
				}
			})
		},
		removeSection(index, oMenuItem){
			this.aUsedMenuItems.splice(index, 1);
			this.aAvailableMenuItems.push(oMenuItem);
		}
	},
	components:{
		Tabs,
		Tab,
		WilokeInput,
		WilokeIcon,
		WilokeAjaxSearchField,
		WilokeSelect,
		WilokeInputReadOnly
	},
	mounted(){
	}
});
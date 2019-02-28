(function () {
'use strict';

var WilokeMessage = {render: function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{directives:[{name:"show",rawName:"v-show",value:(!_vm.isHidden),expression:"!isHidden"}],class:_vm.wrapperClass},[_c('div',{directives:[{name:"show",rawName:"v-show",value:(_vm.icon!=''),expression:"icon!=''"}],staticClass:"alert_icon__1bDKL"},[_c('i',{class:_vm.icon})]),_vm._v(" "),_c('div',{staticClass:"alert_content__1ntU3",domProps:{"innerHTML":_vm._s(_vm.msg)}}),(_vm.hasRemove=='yes')?_c('a',{staticClass:"alert_close__3PtGd",attrs:{"href":"#"},on:{"click":function($event){$event.preventDefault();_vm.hideMsg($event);}}},[_c('i',{staticClass:"la la-times"})]):_vm._e()])},staticRenderFns: [],
    data: function data(){
        return {
            isHidden: false
        }
    },
    props: {
        msg: {
            type: String,
            default: ''
        },
        hasRemove: {
            type: String,
            default: 'no'
        },
        status: {
            type: String,
            default: ''
        },
        icon: {
            type: String,
            default: ''
        },
        additionalClass: {
            type: String,
            default: ''
        }
    },
    computed: {
        wrapperClass: function wrapperClass(){
            var cssClass = 'alert_module__Q4QZx';

            switch(this.status){
                case 'danger':
                    cssClass += ' alert_danger__2ajVf';
                    break;
                case 'success':
                    cssClass += ' alert_success__1nkos';
                    break;
                case 'warning':
                    cssClass += ' alert_warning__2IUiO';
                    break;
                case 'info':
                    cssClass += ' alert_info__2dwkg';
                    break;
                case 'dark':
                    cssClass += ' alert_dark__3ks';
                default:
                    cssClass += ' ';
                    break;
            }

            return cssClass + ' ' + this.additionalClass;
        }
    },
    methods: {
        hideMsg: function hideMsg(){
            event.preventDefault();
            this.isHidden = true;
        }
    }
};

var QuickSearchForm = {render: function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',[_c('div',{class:_vm.searchFormWrapper},[_c('div',{staticClass:"field_wrap__Gv92k"},[_c('input',{directives:[{name:"model",rawName:"v-model",value:(_vm.s),expression:"s"}],staticClass:"field_field__3U_Rt",attrs:{"type":"text"},domProps:{"value":(_vm.s)},on:{"focus":_vm.onFocusing,"keyup":_vm.searchByAjax,"blur":_vm.outFocus,"input":function($event){if($event.target.composing){ return; }_vm.s=$event.target.value;}}}),_vm._v(" "),_c('span',{staticClass:"field_label__2eCP7 text-ellipsis",domProps:{"innerHTML":_vm._s(_vm.oTranslation.whatAreULookingFor)}}),_c('span',{staticClass:"bg-color-primary"}),_vm._v(" "),_vm._m(0)])]),_vm._v(" "),_c('div',{class:_vm.resultsWrapperClass},[_c('div',{staticClass:"listBox_content__1G-fl pos-r"},[_c('div',{directives:[{name:"show",rawName:"v-show",value:(_vm.isSearching),expression:"isSearching"}],staticClass:"listBox_load__3bMA_"},[_vm._m(1)]),_vm._v(" "),_c('wiloke-message',{directives:[{name:"show",rawName:"v-show",value:(_vm.isNotFound),expression:"isNotFound"}],attrs:{"status":"danger","icon":"la la-frown-o","msg":_vm.oTranslation.notFound}}),_vm._v(" "),_c('ul',{directives:[{name:"show",rawName:"v-show",value:(!_vm.isSearching),expression:"!isSearching"}],staticClass:"listBox_list__3yLBa"},[_vm._l((_vm.oListings),function(oGroup){return _c('li',{directives:[{name:"show",rawName:"v-show",value:(Object.values(_vm.oListings).length),expression:"Object.values(oListings).length"}],staticClass:"listBox_type__1nqKw"},[(oGroup.groupIcon)?_c('span',{staticClass:"wilcity-group"},[_c('i',{class:oGroup.groupIcon,style:({'color': oGroup.groupIconColor})}),_vm._v(" "),_c('span',{domProps:{"innerHTML":_vm._s(oGroup.groupTitle)}})]):_c('span',{domProps:{"innerHTML":_vm._s(oGroup.groupTitle)}}),_vm._v(" "),_c('ul',_vm._l((oGroup.posts),function(oResult){return _c('li',[_c('div',{staticClass:"icon-box-1_module__uyg5F six-text-ellipsis mt-20 mb-20"},[_c('div',{staticClass:"icon-box-1_block1__bJ25J"},[_c('a',{attrs:{"href":oResult.postLink}},[(oResult.logo && oResult.logo.length)?_c('div',{staticClass:"icon-box-1_icon__3V5c0 bg-transparent"},[_c('img',{staticClass:"rounded-circle",attrs:{"src":oResult.logo,"alt":"oResult.postTitle"}})]):_vm._e(),_vm._v(" "),_c('div',{staticClass:"icon-box-1_text__3R39g",domProps:{"innerHTML":_vm._s(oResult.postTitle)}})])])])])}))])}),_vm._v(" "),_vm._l((_vm.aTerms),function(oTerm){return _c('li',{directives:[{name:"show",rawName:"v-show",value:(_vm.aTerms.length),expression:"aTerms.length"}],staticClass:"listBox_suggest__2ks_N"},[_c('div',{staticClass:"icon-box-1_module__uyg5F six-text-ellipsis mt-20 mb-20"},[_c('div',{staticClass:"icon-box-1_block1__bJ25J"},[_c('a',{attrs:{"href":oTerm.link}},[(oTerm.oIcon && oTerm.oIcon.type == 'icon')?_c('div',{staticClass:"icon-box-1_icon__3V5c0 rounded-circle",style:({'background-color': oTerm.oIcon.color})},[_c('i',{class:oTerm.oIcon.icon})]):(oTerm.aIcon && oTerm.oIcon.type=='image')?_c('div',{staticClass:"icon-box-1_icon__3V5c0 bg-transparent"},[_c('img',{staticClass:"rounded-circle",attrs:{"src":oTerm.oIcon.url,"alt":"oTerm.termName"}})]):_vm._e(),_vm._v(" "),_c('div',{staticClass:"icon-box-1_text__3R39g",domProps:{"innerHTML":_vm._s(oTerm.name)}})])])])])})],2)],1)])])},staticRenderFns: [function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{staticClass:"field_right__2qM90 pos-a-center-right"},[_c('span',{staticClass:"field_icon__1_sOi"},[_c('i',{staticClass:"la la-search"})])])},function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{staticClass:"pill-loading_module__3LZ6v"},[_c('div',{staticClass:"pill-loading_loader__3LOnT"})])}],
        data: function data(){
            return{
                aRawResults: {},
				oListings: {},
				aTerms: [],
				aTermsSuggestion: [],
				noTermSuggestion: false,
				s: '',
				ajaxSearch: null,
				handle: null,
				isNotFound: false,
				isFetched: false,
				isFocusing: false,
				isSearching: false,
				isFirstTimeLoaded: true,
				aPosts: [],
				isUsingAjax: false,
				oTemporaryCache: {},
				oTranslation: WILCITY_I18
            }
        },
        components:{
            WilokeMessage: WilokeMessage
        },
        computed:{
			resultsWrapperClass: function resultsWrapperClass(){
				return this.isFocusing ? 'listBox_module__RivDj wil-scroll-bar active' : 'listBox_module__RivDj wil-scroll-bar';
			},
			searchFormWrapper: function searchFormWrapper(){
			    return this.s.length ? 'field_module__1H6kT field_style2__2Znhe active' : 'field_module__1H6kT field_style2__2Znhe';
			}
		},
		methods: {
			outFocus: function outFocus(event){
				var this$1 = this;

				setTimeout(function () {
					this$1.isFocusing = false;
				}, 150);
			},
			onFocusing: function onFocusing(){
				this.isFocusing = true;
				this.searchByAjax();
			},
			setResults: function setResults(oResponse){
				if ( oResponse.type === 'term' ){
					this.aTerms = oResponse.aResults;
					this.oListings = [];
				}else{
					this.oListings = oResponse.aResults;
					this.aTerms = [];
				}

				if ( !Object.values(this.oListings).length && !this.aTerms.length ){
					this.isNotFound = true;
				}
			},
			fetchTermsSuggestion: function fetchTermsSuggestion(){
				var this$1 = this;

				this.isSearching = true;
				if ( this.noTermSuggestion ){
					this.isNotFound = true;
					this.aTermsSuggestion = [];
					return false;
				}

				jQuery.ajax({
					url: WILOKE_GLOBAL.ajaxurl,
					type: 'POST',
					data: {
						action: 'wilcity_fetch_terms_suggestions'
					},
					success: function (response) {
						if (!response.success) {
							this$1.noTermSuggestion = true;
							this$1.aTermsSuggestion = [];
						} else {
							this$1.aTermsSuggestion = response.data.aResults;
							this$1.aTerms = this$1.aTermsSuggestion;
						}
						this$1.isSearching = false;
					}
				});
			},
			abortSearch: function abortSearch(){
				if (this.ajaxSearch !== null && this.ajaxSearch.status !== 200) {
					this.ajaxSearch.abort();
				}
			},
			searchByAjax: function searchByAjax(){
				var this$1 = this;

				this.isSearching = true;
				if (!this.s.length) {
					if ( !this.aTermsSuggestion.length ){
						this.fetchTermsSuggestion();
					}else{
						this.aTerms = this.aTermsSuggestion;
					}

					this.oListings = {};
					this.isSearching = false;
					this.isNotFound = false;
					this.abortSearch();

					return true;
				}

				this.abortSearch();

				if (typeof this.oTemporaryCache[this.s] !== 'undefined') {
					this.aResults = this.oTemporaryCache[this.s];
					this.setResults(this.aResults);
					this.isSearching = false;
					return true;
				}

				this.ajaxSearch = jQuery.ajax({
					url: WILOKE_GLOBAL.ajaxurl,
					type: 'POST',
					data: {
						action: 'wilcity_search_by_ajax',
						s: this.s
					},
					success: function (response) {
						if (!response.success) {
							this$1.oListings = {};
							this$1.aTerms = [];
							this$1.isNotFound = true;
							console.log(this$1.isNotFound);
						} else {
							this$1.setResults(response.data);
						}
						this$1.oTemporaryCache[this$1.s] = response.data;
						this$1.isSearching = false;
					}
				});
			}
		}
    };

if ( document.getElementById('wilcity-quick-search-wrapper') ){
	new Vue({
		el: '#wilcity-quick-search-wrapper',
		components: {
			QuickSearchForm: QuickSearchForm
		}
	});
}

}());

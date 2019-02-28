(function () {
'use strict';

var Helpers = function Helpers () {};

Helpers.isNull = function isNull (val){
	return val === '' || val === null || (typeof val === 'object' && !Object.keys(val).length) || typeof val === 'undefined';
};

Helpers.count = function count (oTarget){
	if ( typeof oTarget === 'object' ){
		return Object.keys(oTarget).length;
	}

	return oTarget.length;
};

Helpers.validEmail = function validEmail (email) {
	var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
	return re.test(email);
};

Helpers.ucFirst = function ucFirst (text){
	return text.charAt(0).toUpperCase() + text.slice(1).toLowerCase();
};

Helpers.isMobile = function isMobile (type){
	switch (type){
		case 'Android':
			return navigator.userAgent.match(/Android/i);
			break;
		case 'BlackBerry':
			return navigator.userAgent.match(/BlackBerry/i);
			break;
		case 'IOS':
			return navigator.userAgent.match(/iPhone|iPad|iPod/i);
			break;
		case 'Opera':
			return navigator.userAgent.match(/Opera Mini/i);
			break;
		case 'Windows':
			return navigator.userAgent.match(/IEMobile/i);
			break;
		default:
			return Helpers.isMobile('Android') || Helpers.isMobile('BlackBerry') || Helpers.isMobile('IOS') || Helpers.isMobile('Opera') || Helpers.isMobile('Windows');
			break;
	}
};

Helpers.additionalHeightToScrollTop = function additionalHeightToScrollTop (){
	var additional = 0;
	if ( jQuery('header.js-header-sticky').length ){
		additional = 88;
	}

	var $adminBar = jQuery('#wpadminbar');
	if ( $adminBar.lenght ){
		additional +=  $adminBar.outerHeight();
	}
	return additional;
};

Helpers.getParamFromUrl = function getParamFromUrl (target){
	var getUrl = window.location.href;
	if ( getUrl.indexOf(target+'=') === -1 ){
		return false;
	}

	var aFStep = getUrl.split(target+'=');
	var aSStep = aFStep[1].split('&');
	return aSStep[0];
};

Helpers.buildRouter = function buildRouter (route, url){
	url = typeof url !== 'undefined' ? url : window.location.href;

	if ( url.indexOf('?') === -1 ){
		url += '#' + route;
	}else{
		var aParseUrl = url.split(',');
		url = aParseUrl[0] + '#' + route + '?' + aParseUrl[1];
	}
	return url;
};

Helpers.buildQuery = function buildQuery (args, param, url){
	url = typeof url !== 'undefined' ? url : window.location.href;
	param = encodeURIComponent(param);
	if ( url.indexOf('?') === -1 ){
		url = url + '?' + args +'='+param;
	}else{
		url = url + '&' + args +'='+param;
	}
	return url;
};

var WilokePagination = function WilokePagination($wrapper, totalPosts, postsPerPage, maxPages, currentPage){
	this.currentPage    = typeof currentPage !== 'undefined' ? currentPage : 1;
	this.totalPosts     = totalPosts;
	this.postsPerPage   = postsPerPage;
	this.maxPages       = maxPages;
	this.nextPage       = 0;
	this.prevPage       = 0;
	this.xhr = null;
	this.$wrapper  = $wrapper;
};
	
WilokePagination.prototype.resetPagination = function resetPagination (){
		var this$1 = this;

	this.$wrapper.on('resetPagination', function (event, currentPage, totalPosts){
		this$1.currentPage = currentPage;
		this$1.totalPosts = totalPosts;
		this$1.$wrapper.html(this$1.createPagination());
	});
};

WilokePagination.prototype.ajaxLoading = function ajaxLoading (){
		var this$1 = this;

	jQuery('body').on('click', '.pagination_pageItem__3SatM', function (event) {
		event.preventDefault();
		if ( this$1.xhr !== null && this$1.xhr.status !== 200 ){
			this$1.xhr.abort();
		}

		this$1.$wrapper.find('.pagination_pageItem__3SatM.current').removeClass('current');
		var $target = jQuery(event.currentTarget), gridID = this$1.$wrapper.data('gridid');
		jQuery('body').trigger('blockLoading', [gridID]);

		$target.addClass('current');

		this$1.currentPage = $target.find('a').data('page');

		var oArgs = {
			page: this$1.currentPage,
			action: this$1.$wrapper.data('action')
		};
		oArgs = Object.assign({}, oArgs, this$1.$wrapper.data());

		this$1.xhr = jQuery.ajax({
			type: 'POST',
			url: WILOKE_GLOBAL.ajaxurl,
			data: oArgs,
			success: function (response) {
				jQuery('body').trigger('paginationLoaded', [response, this$1.$wrapper.data('gridid')]);
				if ( !response.success ){
					this$1.$wrapper.addClass('hidden');
				}else{
					this$1.$wrapper.html(this$1.createPagination());
				}
			}
		});
	});
};

WilokePagination.prototype.createPagination = function createPagination (){
		var this$1 = this;

	var aPages = [], pagination='';
	this.totalPosts = parseInt(this.totalPosts);
	this.postsPerPage = parseInt(this.postsPerPage);

	if ( this.totalPosts === 0 || this.totalPosts <= this.postsPerPage ){
		return '';
	}

	this.currentPage = parseInt(this.currentPage, 10);
	this.nextPage = this.currentPage+1;
	this.prevPage = this.currentPage-1;
	this.maxPages = Math.ceil(this.totalPosts/this.postsPerPage);

	// If, in case, We have only one page, simply print nothing
	if ( this.maxPages <= 1 ){
		return false;
	}

	// If the total page is smaller than 8 or equal to 8, We print all
	if ( this.maxPages <= 8 ){
		for ( var i = 1; i <= this.maxPages; i++ ){
			aPages.push(i);
		}
	}else{
		if ( this.currentPage <= 3 ){
			// If the current page is smaller than 4, We print the first three pages and the last page
			aPages = [1, 2, 3, 4, 'x', this.maxPages];
		}else if(this.currentPage < 7){
			// if the current page is smaller than 7, We print the first seven pages and the last page
			aPages = [1, 2, 3, 4, 5, 6, 7, 'x', this.maxPages];
		}else{
			// And, in the last casfe, We print the first three pages and the pages range of [currentPage-3, currentPage]
			aPages = [1, 'x'];

			for ( var i$1 = 2;  i$1 >= 0; i$1--  ){
				aPages.push(this$1.currentPage-i$1);
			}

			var currentToLast = this.maxPages - this.currentPage;
			if ( currentToLast <= 8 ){
				for ( var j = this.currentPage+1; j <= this.maxPages ; j++ ){
					aPages.push(j);
				}
			}else{
				for ( var j$1 = 0; j$1 <= 2 ; j$1++ ){
					aPages.push(this$1.currentPage+1+j$1);
				}

				aPages.push('x');
				aPages.push(this.maxPages);
			}
		}
	}

	for ( var i$2 = 0, maximum = aPages.length; i$2 < maximum; i$2++ ){
		if ( this$1.currentPage === aPages[i$2] ){
			pagination += '<li class="pagination_pageItem__3SatM current"><a class="pagination_pageLink__2UQhK" data-page="'+aPages[i$2]+'">'+aPages[i$2]+'</a></li>';
		}else if(aPages[i$2] === 'x'){
			pagination += '<li class="pagination_pageItem__3SatM disable"><span class="pagination_pageLink__2UQhK"><i class="la la-ellipsis-h"></i></span></li>';
		}else{
			pagination += '<li class="pagination_pageItem__3SatM"><a href="#" class="pagination_pageLink__2UQhK"  data-page="'+aPages[i$2]+'">'+aPages[i$2]+'</a></li>';
		}
	}

	if ( pagination !== '' ){
		if ( this.currentPage !== 1 ){
			pagination = '<li class="pagination_pageItem__3SatM"><a href="#" class="pagination_pageLink__2UQhK" data-page="'+this.prevPage+'"><i class="la la-angle-left"></i></a></li>' + pagination;
		}

		if ( this.currentPage !== this.maxPages ){
			pagination += '<li class="pagination_pageItem__3SatM next"><a href="#" class="pagination_pageLink__2UQhK" data-page="'+this.nextPage+'"><i class="la la-angle-right"></i></a></li>';
		}
	}

	return pagination;
};

var Follow = function Follow(){
	this.$toggle = jQuery('.wilcity-toggle-follow');
	this.init();
};

Follow.prototype.init = function init (){
	if ( !this.$toggle.length ){
		return false;
	}

	this.$toggle.on('click', (function (event){
		event.preventDefault();

		if ( WILOKE_GLOBAL.isUserLoggedIn !== 'yes' ){
			document.getElementById('wilcity-login-btn').click();
			return false;
		}

		var $target = jQuery(event.currentTarget),
			authorID = $target.data('authorid'),
			$followersNumber = jQuery('#wilcity-follower-number-'+authorID);

		var status = $target.data('currentStatus');

		jQuery.ajax({
			type: 'POST',
			url: WILOKE_GLOBAL.ajaxurl,
			data: {
				action: 'wilcity_update_following',
				authorID: authorID
			},
			success: function (response) {
				if ( response.success ){
					if ( $followersNumber.length ){
						$followersNumber.find('.wilcity-print-number').html(response.data);
					}

					if ( $target.data('textonly') ){
						if ( status === 'followingtext' ){
							$target.html('<i class="la la-refresh"></i> ' + $target.data('followtext'));
							$target.data('currentStatus', 'followtext');
						}else{
							$target.html('<i class="la la-refresh"></i> ' + $target.data('followingtext'));
							$target.data('currentStatus', 'followingtext');
						}
					}else{
						$target.find('span').html(response.data);

						if ( status === 'followingtext' ){
							$target.html('<span>'+response.data+'</span> ' + $target.data('followtext'));
							$target.data('currentStatus', 'followtext');
						}else{
							$target.html('<span class="color-primary">'+response.data+'</span> ' + $target.data('followingtext'));
							$target.data('currentStatus', 'followingtext');
						}
					}

				}else{
					alert(response.data.msg);
				}
			}
		});

	}));
};

var WilokeGoogleMap = function WilokeGoogleMap(mapID){
	this.mapID = mapID;
	this.init();
};

WilokeGoogleMap.prototype.mapStyles = function mapStyles (theme){
	var oStyles =  {
		blurWater: [
			{
				"featureType": "administrative",
				"elementType": "labels.text.fill",
				"stylers": [
					{
						"color": "#444444"
					}
				]
			},
			{
				"featureType": "landscape",
				"elementType": "all",
				"stylers": [
					{
						"color": "#f2f2f2"
					}
				]
			},
			{
				"featureType": "poi",
				"elementType": "all",
				"stylers": [
					{
						"visibility": "off"
					}
				]
			},
			{
				"featureType": "road",
				"elementType": "all",
				"stylers": [
					{
						"saturation": -100
					},
					{
						"lightness": 45
					}
				]
			},
			{
				"featureType": "road.highway",
				"elementType": "all",
				"stylers": [
					{
						"visibility": "simplified"
					}
				]
			},
			{
				"featureType": "road.arterial",
				"elementType": "labels.icon",
				"stylers": [
					{
						"visibility": "off"
					}
				]
			},
			{
				"featureType": "transit",
				"elementType": "all",
				"stylers": [
					{
						"visibility": "off"
					}
				]
			},
			{
				"featureType": "water",
				"elementType": "all",
				"stylers": [
					{
						"color": "#46bcec"
					},
					{
						"visibility": "on"
					}
				]
			}
		]
	};

	return typeof oStyles[theme] !== 'undefined' ? oStyles[theme] : oStyles.blurWater;
};

WilokeGoogleMap.prototype.parseLatLng = function parseLatLng (latLng){
	var aParse = latLng.split(',');

	return {
		lat: parseInt(aParse[0], 10),
		lng: parseInt(aParse[1], 10)
	}
};

WilokeGoogleMap.prototype.setParams = function setParams (){
	var oDefault = {
		isSingle: true,
		icon: '',
		zoom: 3,
		zoomControl: true,
		scaleControl: true,
		disableDoubleClickZoom: false,
		streetViewControl: true,
		overviewMapControl: true,
		maxZoom: 21,
		minZoom: 1,
		styles: this.mapStyles('blurWater')
	};

	var oConfiguration = this.$map.dataset;
	this.aConfiguration = Object.assign({}, oDefault, oConfiguration);
};
	
WilokeGoogleMap.prototype.createMapMaker = function createMapMaker (oLatLng, icon){
	var oConfiguration = {
		position: oLatLng
	};

	if ( typeof icon !== 'undefined' && icon !== '' ){
		oConfiguration.icon = icon;
	}

	return new google.maps.Marker(oConfiguration);
};

WilokeGoogleMap.prototype.buildSingleMap = function buildSingleMap (){
	if ( typeof this.aConfiguration.latlng === 'undefined' ){
		alert('Latitude and Longitude are required');
		return false;
	}

	var oLatLng = this.parseLatLng(this.aConfiguration.latlng);
	this.aConfiguration.center = new google.maps.LatLng(oLatLng.lat, oLatLng.lng);

	var oInitMap = new google.maps.Map(this.$map, this.aConfiguration);
	var oMapMaker = this.createMapMaker(oLatLng, this.aConfiguration.icon);
	oMapMaker.setMap(oInitMap);
};

WilokeGoogleMap.prototype.init = function init (){
	this.$map = document.getElementById(this.mapID);
	if ( this.$map === 'undefined' ){
		return false;
	}

	this.setParams();

	if ( this.aConfiguration.isSingle ){
		this.buildSingleMap();
	}else{

	}
};

var defaultExport = function defaultExport($button){
	if ( $button.length ){
		this.$btn = $button;
		this.toggle();
	}
};

defaultExport.prototype.toggle = function toggle (){
	if ( this.$btn.hasClass('wil-btn--loading') ){
		this.$btn.find('.pill-loading_module__3LZ6v').addClass('hidden');
		this.$btn.removeClass('wil-btn--loading');
	}else{
		this.$btn.addClass('wil-btn--loading');
		if ( !this.$btn.find('.pill-loading_module__3LZ6v').length ){
			this.$btn.append('<div class="pill-loading_module__3LZ6v"><div class="pill-loading_loader__3LOnT"></div></div>');
		}else{
			this.$btn.find('.pill-loading_module__3LZ6v').removeClass('hidden');
		}
	}
};

var AddListingData = function AddListingData () {};

AddListingData.configuration = function configuration (){
	return {
		// planID: jQuery('#wilcity-plan-id').val(),
		couponCode: jQuery('#wilcity-valid-coupon-code').val()
	};
};

var WilokeStripe = function WilokeStripe($btn){
	if ( $btn.length ){
		this.$btn = $btn;
		this.text = null;
		this.xhr = null;
		this.oData = {
			token: '',
			email: ''
		};

		this.pay();
	}
};

WilokeStripe.prototype.ajaxRequest = function ajaxRequest (){
		var this$1 = this;

	var self = this;
	if ( self.xhr !== null && self.xhr.status !== 200 ){
		self.xhr.abort();
	}

	var oData = AddListingData.configuration();

	oData.action    = 'wiloke_submission_buy_plan_via_stripe';
	oData.token     = self.oData.token;
	oData.email     = self.oData.email;

	self.xhr = jQuery.ajax({
		type: 'POST',
		url: WILOKE_GLOBAL.ajaxurl,
		data: oData,
		success: function (response) {
			if ( !response.success ){
				jQuery('#wilcity-print-msg').trigger('printErrMsg', [response.data.msg]);
			}else{
				window.location.href = response.data.thankyou;
			}
			new defaultExport(this$1.$btn);
		}
	});
};

WilokeStripe.prototype.afterGetToken = function afterGetToken (token){
	this.oData.token    = token.id;
	this.oData.email    = token.email;
};

WilokeStripe.prototype.showPopup = function showPopup (){
		var this$1 = this;

	this.oData = {};
	this.oStripe.open({
		name: WILCITY_GLOBAL.oGeneral.brandName,
		zipCode: false,
		closed: (function (){
			new defaultExport(this$1.$btn);
		}),
		token:  (function (token){
			this$1.afterGetToken(token);
			this$1.tokenTriggered = true;
			this$1.ajaxRequest();
		})
	});
};

WilokeStripe.prototype.pay = function pay (){
		var this$1 = this;

	this.$btn.on('click', (function (event){
		var $target = jQuery(event.currentTarget);
		event.preventDefault();
		jQuery('#wilcity-print-msg').addClass('hidden');
		new defaultExport($target);
		if ( WILCITY_GLOBAL.oStripe.hasCustomerID != 'yes' ){
			this$1.oStripe = StripeCheckout.configure({
				key: WILCITY_GLOBAL.oStripe.publishableKey,
				locale: 'auto'
			});
			this$1.showPopup();
		}else{
			this$1.ajaxRequest();
		}
	}));
};

var WilokePayPal = function WilokePayPal($btn){
	if ( $btn.length ) {
		this.xhr = null;
		this.pay($btn);
	}
};

WilokePayPal.prototype.pay = function pay ($btn){
		var this$1 = this;

	$btn.on('click', (function (event){
		event.preventDefault();
		jQuery('#wilcity-print-msg').addClass('hidden');
		var oData = AddListingData.configuration();
		oData.action = 'wiloke_submission_pay_with_paypal';

		new defaultExport($btn);

		if ( this$1.xhr !== null && this$1.xhr.status !== 200 ){
			this$1.xhr.abort();
		}

		this$1.xhr = jQuery.ajax({
			type: 'POST',
			data: oData,
			url: WILOKE_GLOBAL.ajaxurl,
			success: function (response){
				if ( response.success ){
					if ( typeof response.data.redirectTo !== 'undefined' ){
						window.location.href = response.data.redirectTo;
					}
				}else{
					jQuery('#wilcity-print-msg').trigger('printErrMsg', [response.data.msg]);
				}

				new defaultExport($btn);
			}
		});
	}));
};

var WilokeDirectBankTransfer = function WilokeDirectBankTransfer($btn){
	if ( $btn.length ) {
		this.xhr = null;
		this.pay($btn);
	}
};

WilokeDirectBankTransfer.prototype.pay = function pay ($btn){
		var this$1 = this;

	$btn.on('click', (function (event){
		event.preventDefault();
		jQuery('#wilcity-print-msg').addClass('hidden');

		var oData = AddListingData.configuration();
		oData.action = 'wiloke_submission_pay_with_directbanktransfer';
		new defaultExport($btn);

		if ( this$1.xhr !== null && this$1.xhr.status !== 200 ){
			this$1.xhr.abort();
		}

		this$1.xhr = jQuery.ajax({
			type: 'POST',
			data: oData,
			url: WILOKE_GLOBAL.ajaxurl,
			success: function (response){
				if ( response.success ){
					if ( typeof response.data.redirectTo !== 'undefined' ){
						window.location.href = response.data.redirectTo;
					}
				}else{
					jQuery('#wilcity-print-msg').trigger('printErrMsg', [response.data.msg]);
				}
				new defaultExport($btn);
			}
		});
	}));
};

var defaultExport$1 = function defaultExport$$1(){
	jQuery('#wilcity-submit-coupon').on('click', (function (event){
		event.preventDefault();
		var $target = jQuery(event.currentTarget), couponCode = jQuery('#wilcity-coupon-code').val(), $msg = jQuery('#wilcity-coupon-msg');

		if ( couponCode === '' ){
			return false;
		}

		new defaultExport($target);

		jQuery.ajax({
			type: 'POST',
			url: WILOKE_GLOBAL.ajaxurl,
			data: {
				action: 'wiloke_submission_verify_coupon',
				code: couponCode
			},
			success: function success(response){
				jQuery('.column-sub').html(response.data.subTotal);
				jQuery('.column-discount').html(response.data.discount);
				jQuery('.column-total').html(response.data.total);

				if ( response.success ){
					jQuery('#wilcity-valid-coupon-code').val(couponCode);
				}else{
					jQuery('#wilcity-valid-coupon-code').val('');
				}
				$msg.html('<div class="mt-20"></div>'+response.data.msg);
				new defaultExport($target);
			}
		});
	}));
};

/**
 * Created by pirates on 2/7/18.
 */
var PayAndPublish = function PayAndPublish($btn){
	this.$btn = $btn;
	this.submit();
};

PayAndPublish.prototype.submit = function submit (){
	this.$btn.on('click', (function (event){
		event.preventDefault();

		$.ajax({
			type: 'POST',
			data: {
				action: 'wilcity_get_pay_and_publish_url'
			},
			url: WILOKE_GLOBAL.ajaxurl,
			success: function (response) {
				if ( response.success ){
					window.location.href = response.data.redirectTo;
				}else{
					alert(response.data.msg);
				}
			}
		});
	}));
};

var EditDiscussion = {render: function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{staticClass:"comment-review_comment__dJNqv mt-10"},[_c('div',{class:_vm.discussionWrapper},[_c('div',{staticClass:"field_wrap__Gv92k"},[_c('textarea',{directives:[{name:"model",rawName:"v-model",value:(_vm.postContent),expression:"postContent"}],staticClass:"field_field__3U_Rt",attrs:{"data-height-default":"22"},domProps:{"value":(_vm.postContent)},on:{"input":function($event){if($event.target.composing){ return; }_vm.postContent=$event.target.value;}}}),_vm._v(" "),_c('span',{staticClass:"field_label__2eCP7 text-ellipsis"}),_vm._v(" "),_c('span',{staticClass:"bg-color-primary"}),_vm._v(" "),_c('div',{class:_vm.submitDiscussionClass},[_c('span',{staticClass:"field_iconButton__2p3sr bg-color-primary",on:{"click":function($event){$event.preventDefault();_vm.submitDiscussion($event);}}},[_c('i',{staticClass:"la la-arrow-up"})])]),_vm._v(" "),_c('div',{staticClass:"mt-10 pos-a review-cancel"},[_c('a',{attrs:{"href":"#","title":_vm.oTranslations.cancel},on:{"click":function($event){$event.preventDefault();_vm.cancelEdit($event);}}},[_c('i',{staticClass:"la la-times-circle"})])])])])])},staticRenderFns: [],
    data: function data(){
        return{
            oTranslations: WILCITY_I18,
            postContent: this.discussion,
            wrapperClass: 'field_module__1H6kT field_style4__2DBqx field-autoHeight',
            submissionClass: 'field_rightButton__1GGWz js-field-rightButton'
        }
    },
    props: ['discussion', 'order'],
    computed: {
        discussionWrapper: function discussionWrapper(){
            return this.postContent.length ? this.wrapperClass + ' active' : this.wrapperClass;
        },
        submitDiscussionClass: function submitDiscussionClass(){
            return this.postContent.length ? this.submissionClass + ' active' : this.submissionClass;
        }
    },
    methods: {
        submitDiscussion: function submitDiscussion(){
            this.$emit('on-changed', this.order, this.postContent, this.postContent != this.discussion);
        },
        cancelEdit: function cancelEdit(){
            this.$emit('on-cancel', this.order);
        }
    }
};

var WilokeEmptyComponent = {render: function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div')},staticRenderFns: [],
};

var WilokeDiscussion = {render: function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',[_c('ul',{directives:[{name:"show",rawName:"v-show",value:(_vm.aDiscussions.length),expression:"aDiscussions.length"}],staticClass:"comment-review_commentlist__1LH_D list-none"},_vm._l((_vm.aDiscussions),function(oDiscussion,order){return _c('li',{class:_vm.discussionItemWrapper(oDiscussion),attrs:{"data-id":oDiscussion.ID}},[_c('div',{staticClass:"utility-box-1_module__MYXpX utility-box-1_xs__3Nipt utility-box-1_boxLeft__3iS6b clearfix"},[_c('div',{staticClass:"utility-box-1_avatar__DB9c_ rounded-circle wil-float-left",style:('background-image: url('+oDiscussion.avatar+');')},[_c('img',{attrs:{"src":oDiscussion.avatar,"alt":oDiscussion.displayName}})]),_vm._v(" "),_c('div',{staticClass:"utility-box-1_body__8qd9j"},[_c('div',{staticClass:"utility-box-1_group__2ZPA2"},[_c('h3',{staticClass:"utility-box-1_title__1I925"},[_vm._v(_vm._s(oDiscussion.displayName))]),_vm._v(" "),_c('div',{staticClass:"utility-box-1_content__3jEL7",domProps:{"innerHTML":_vm._s(_vm.printDiscussion(oDiscussion.postContent))}})]),_vm._v(" "),_c('div',{staticClass:"utility-box-1_description__2VDJ6"},[_vm._v(_vm._s(_vm.printDiscussionInfo(oDiscussion)))]),_vm._v(" "),_c(_vm.oEditComponents[oDiscussion.ID],{tag:"component",attrs:{"discussion":oDiscussion.postContent,"order":order},on:{"on-changed":_vm.onEditedDiscussion,"on-cancel":_vm.cancelEditDiscussion}})],1)]),_vm._v(" "),_c('div',{directives:[{name:"show",rawName:"v-show",value:((oDiscussion.isAuthor=='yes' || oDiscussion.isAdmin=='yes') && !oDiscussion.isTemporaryDisableEdit),expression:"(oDiscussion.isAuthor=='yes' || oDiscussion.isAdmin=='yes') && !oDiscussion.isTemporaryDisableEdit"}],staticClass:"dropdown_module__J_Zpj"},[_c('div',{class:_vm.dropdownControllerClass(order),on:{"click":function($event){$event.preventDefault();_vm.toggleDropdown(order);}}},[_c('span',{staticClass:"dropdown_dot__3I1Rn"}),_vm._v(" "),_c('span',{staticClass:"dropdown_dot__3I1Rn"}),_vm._v(" "),_c('span',{staticClass:"dropdown_dot__3I1Rn"})]),_vm._v(" "),_c('div',{class:_vm.dropdownWrapperClass(order),attrs:{"data-toggle-content":"true"}},[_c('ul',{staticClass:"list_module__1eis9 list-none list_small__3fRoS list_abs__OP7Og arrow--top-right"},[(oDiscussion.isAuthor=='yes')?_c('li',{staticClass:"list_item__3YghP"},[_c('a',{staticClass:"list_link__2rDA1 text-ellipsis color-primary--hover",attrs:{"href":"#"},on:{"click":function($event){$event.preventDefault();_vm.toggleEditForm(order, oDiscussion.ID);}}},[_vm._m(0,true),_c('span',{staticClass:"list_text__35R07"},[_vm._v(_vm._s(_vm.oTranslation.edit))])])]):_vm._e(),_vm._v(" "),(oDiscussion.isAuthor=='yes' || oDiscussion.isAdmin=='yes')?_c('li',{staticClass:"list_item__3YghP"},[_c('a',{staticClass:"list_link__2rDA1 text-ellipsis color-primary--hover",attrs:{"data-id":oDiscussion.ID,"data-order":order,"href":"#"},on:{"click":function($event){$event.preventDefault();_vm.deleteDiscussion($event);}}},[_vm._m(1,true),_c('span',{staticClass:"list_text__35R07"},[_vm._v(_vm._s(_vm.oTranslation.delete))])])]):_vm._e()])])])])})),_vm._v(" "),(!_vm.isFinished && _vm.maxDiscussions > _vm.aDiscussions.length)?_c('a',{staticStyle:{"display":"block","font-size":"13px","opacity":"0.8","padding":"5px 20px","background-color":"#fbfbfc"},attrs:{"href":"#"},on:{"click":function($event){$event.preventDefault();_vm.fetchMoreComments($event);}}},[_vm._v(_vm._s(_vm.oTranslation.viewMoreComments))]):_vm._e(),_vm._v(" "),(_vm.toggleReviewDiscussion == 'yes')?_c('div',{staticClass:"comment-review_form__20wWm"},[_c('div',{staticClass:"utility-box-1_module__MYXpX utility-box-1_xs__3Nipt d-inline-block mr-10 wil-float-left"},[_c('div',{staticClass:"utility-box-1_avatar__DB9c_ rounded-circle",style:('background-image: url('+_vm.oDiscussionAuthor.avatar+');')},[_c('img',{attrs:{"src":_vm.oDiscussionAuthor.avatar,"alt":_vm.oDiscussionAuthor.displayName}})])]),_vm._v(" "),_c('div',{staticClass:"comment-review_comment__dJNqv"},[_c('div',{class:_vm.discussionWrapper},[_c('div',{staticClass:"field_wrap__Gv92k"},[_c('textarea',{directives:[{name:"model",rawName:"v-model",value:(_vm.discussion),expression:"discussion"}],ref:"writenewdiscussion",class:_vm.writeDiscussionClass,attrs:{"data-height-default":"22"},domProps:{"value":(_vm.discussion)},on:{"input":function($event){if($event.target.composing){ return; }_vm.discussion=$event.target.value;}}}),_c('span',{staticClass:"field_label__2eCP7 text-ellipsis"},[_vm._v(_vm._s(_vm.oTranslation.typeAMessage))]),_c('span',{staticClass:"bg-color-primary"}),_c('div',{class:_vm.submitDiscussionClass},[_c('span',{staticClass:"field_iconButton__2p3sr bg-color-primary",on:{"click":function($event){$event.preventDefault();_vm.submitDiscussion(_vm.order);}}},[_c('i',{staticClass:"la la-arrow-up"})])])])])])]):_vm._e()])},staticRenderFns: [function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('span',{staticClass:"list_icon__2YpTp"},[_c('i',{staticClass:"la la-edit"})])},function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('span',{staticClass:"list_icon__2YpTp"},[_c('i',{staticClass:"la la-trash"})])}],
    data: function data(){
        return {
            aDiscussions: this.aDiscussionDefault(),
            isTemporaryDisableDiscussion: false,
            discussion: '',
            page: 1,
            isFinished: false,
            aControllersStatus: [],
            handleID: null,
            isDropping: false,
            prevEditing: '',
            editingOrder: null,
            oTranslation: WILCITY_I18,
            xhr: null,
            oDiscussionAuthor: typeof this.oUser == 'string' ? JSON.parse(this.oUser) : this.oUser,
            oEditComponents: []
        }
    },
    mounted: function mounted(){
        this.focusWriteDiscussionField();
        this.setupEditDiscussions();
    },
    components: {
        'edit-discussion': EditDiscussion,
        'empty-component': WilokeEmptyComponent
    },
    props: ['aOldDiscussions', 'parent-id', 'oUser', 'toggleReviewDiscussion', 'maxDiscussions'],
    computed:{
        renderGenerateRef: function renderGenerateRef(){
            return 'discussion-'+this.parentId;
        },
        submitDiscussionClass: function submitDiscussionClass(){
            return {
                active: this.discussion.length != '',
                'field_rightButton__1GGWz js-field-rightButton': 1==1
            }
        },
        discussionWrapper: function discussionWrapper(){
            var cl = 'field_module__1H6kT field_style4__2DBqx field-autoHeight';
            return this.discussion.length ? cl + ' active' : cl;
        },
        writeDiscussionClass: function writeDiscussionClass(){
            var cl = 'wilcity-write-a-new-comment-box field_field__3U_Rt';
            return !this.isTemporaryDisableDiscussion ? cl : cl + ' disable';
        }
    },
    methods: {
        discussionItemWrapper: function discussionItemWrapper(oItem){
            return 'comment-review_commentlistItem__2DILM wilcity-js-review-discussion-'+oItem.ID;
        },
        fetchMoreComments: function fetchMoreComments(){
            var this$1 = this;

            this.page = this.page + 1;
            jQuery.ajax({
                type: 'POST',
                url: WILOKE_GLOBAL.ajaxurl,
                data: {
                    action: 'wilcity_fetch_discussions',
                    parentID: this.parentId,
                    page: this.page
                },
                success: function (response) {
                    if ( !response.success ){
                        this$1.isFinished = true;
                    }else{
                        this$1.aDiscussions = this$1.aDiscussions.concat(response.data.discussions);
                    }
                }
            });
        },
        focusWriteDiscussionField: function focusWriteDiscussionField(){
            var this$1 = this;

            this.$parent.$on('focusingNewCommentBox', function (){
                this$1.$refs.writenewdiscussion.focus();
            });
        },
        aDiscussionDefault: function aDiscussionDefault(){
            if ( typeof this.aOldDiscussions !== 'undefined' && this.aOldDiscussions.length ){
                if ( typeof this.aOldDiscussions === 'object' ){
                    return this.aOldDiscussions;
                }else if ( typeof this.aOldDiscussions == 'string' ){
                    return JSON.parse(this.aOldDiscussions);
                }
            }
            return [];
        },
        processDeleteDiscussion: function processDeleteDiscussion(reviewID, order){
            this.aDiscussions.splice(order, 1);
            this.$parent.$emit('updateDiscussions', this.aDiscussions);
            jQuery.ajax({
                type: 'POST',
                url: WILOKE_GLOBAL.ajaxurl,
                data: {
                    reviewID: reviewID,
                    action: 'wilcity_delete_discussion'
                },
                success: function(response){

                }
            });
        },
        deleteDiscussion: function deleteDiscussion(event){
            var isDelete = confirm(this.oTranslation.confirmDeleteComment);

            if ( !isDelete ){
                return false;
            }

            var reviewID = jQuery(event.currentTarget).data('id'),
                order = jQuery(event.currentTarget).data('order');
            this.abortAjax(reviewID);

            this.processDeleteDiscussion(reviewID, order);
        },
        submitDiscussion: function submitDiscussion(order){
            var this$1 = this;

            if ( !this.discussion.length ){
                return false;
            }
            var oNewDiscussion = {
                content: this.discussion,
                parentID: this.parentId,
                action: 'wilcity_review_discussion'
            };

            this.discussion = '';
            this.isTemporaryDisableDiscussion = true;
            jQuery.ajax({
                type: 'POST',
                url: WILOKE_GLOBAL.ajaxurl,
                data: oNewDiscussion,
                success: function (response) {
                    if ( !response.success ){
                        alert(response.data.msg);
                    }else{
                        this$1.aDiscussions.push(response.data);
                    }
                    this$1.isTemporaryDisableDiscussion = false;

                    jQuery(this$1.$el).find('.wilcity-write-a-new-comment-box').attr('style', '');
                }
            });
        },
        submitEditDiscussion: function submitEditDiscussion(order, newContent){
            var this$1 = this;

            this.abortAjax(this.aDiscussions[order].ID);
            if ( newContent == '' ){
                var isDelete = confirm(this.oTranslation.confirmDeleteComment);

                if ( !isDelete ){
                    return false;
                }

                 this.processDeleteDiscussion(this.aDiscussions[order].ID, order);
            }else{
                this.$set(this.aDiscussions[order], 'isTemporaryDisableEdit', false);

                this.xhr = jQuery.ajax({
                    type: 'POST',
                    url: WILOKE_GLOBAL.ajaxurl,
                    data: {
                        action: 'wilcity_update_discussion',
                        discussionID: this.aDiscussions[order].ID,
                        content: newContent
                    },
                    success: function (response) {
                        if ( !response.success ){
                            alert(response.data.msg);
                        }else{
                            this$1.aDiscussions[order].postContent = newContent;
                        }
                    }
                });
            }
        },
        setupEditDiscussions: function setupEditDiscussions(){
            var this$1 = this;

            if ( !this.aDiscussions.length ){
                return [];
            }else{
                for ( var order in this$1.aDiscussions ){
                    this$1.oEditComponents[this$1.aDiscussions[order].ID] = '';
                }
            }
        },
        onEditedDiscussion: function onEditedDiscussion(order, newContent, isChanged){
            this.$set(this.oEditComponents, this.aDiscussions[order].ID, '');
            if ( !isChanged ){
                return false;
            }
            this.submitEditDiscussion(order, newContent);
        },
        cancelEditDiscussion: function cancelEditDiscussion(order){
            this.$set(this.oEditComponents, this.aDiscussions[order].ID, '');
        },
        toggleDropdown: function toggleDropdown(order){
            var status = typeof this.aControllersStatus[order] == 'undefined' ? true : !this.aControllersStatus[order];
            this.$set(this.aControllersStatus, order, status);
        },
        toggleEditForm: function toggleEditForm(order, ID){
            this.$set(this.aControllersStatus, order, !this.aControllersStatus[order]);

            this.oEditComponents[ID] = 'edit-discussion';
        },
        printDiscussionInfo: function printDiscussionInfo(oDiscussion){
            if ( oDiscussion.position ){
                return oDiscussion.position + ' . ' + oDiscussion.postDate;
            }else{
                return oDiscussion.postDate;
            }
        },
        dropdownControllerClass: function dropdownControllerClass(order){
            return this.aControllersStatus[order] ? 'dropdown_threeDots__3fa2o active' : 'dropdown_threeDots__3fa2o';
        },
        dropdownWrapperClass: function dropdownWrapperClass(order){
            return this.aControllersStatus[order] ? 'dropdown_itemsWrap__2fuze active' : 'dropdown_itemsWrap__2fuze';
        },
        abortAjax: function abortAjax(reviewID){
            if ( this.handleID == reviewID && this.xhr !== null && this.xhr.status !== 200 ){
                this.xhr.abort();
            }
        },
        printDiscussion: function printDiscussion(content){
            return content.replace('\n', '<br/>');
        },
        hideDropdownButtons: function hideDropdownButtons(order){
            this.isDropping = false;
            this.$set(this.aDiscussions[order], 'showButton', false);
        },
        unFocusedEditing: function unFocusedEditing(order){
            this.$set(this.aDiscussions[order], 'isEditing', false);
            this.aDiscussions[order].focused = false;
            this.editingOrder = null;
        }
    }
};

/**
 * vuex v3.0.1
 * (c) 2017 Evan You
 * @license MIT
 */
var applyMixin = function (Vue) {
  var version = Number(Vue.version.split('.')[0]);

  if (version >= 2) {
    Vue.mixin({ beforeCreate: vuexInit });
  } else {
    // override init and inject vuex init procedure
    // for 1.x backwards compatibility.
    var _init = Vue.prototype._init;
    Vue.prototype._init = function (options) {
      if ( options === void 0 ) { options = {}; }

      options.init = options.init
        ? [vuexInit].concat(options.init)
        : vuexInit;
      _init.call(this, options);
    };
  }

  /**
   * Vuex init hook, injected into each instances init hooks list.
   */

  function vuexInit () {
    var options = this.$options;
    // store injection
    if (options.store) {
      this.$store = typeof options.store === 'function'
        ? options.store()
        : options.store;
    } else if (options.parent && options.parent.$store) {
      this.$store = options.parent.$store;
    }
  }
};

var devtoolHook =
  typeof window !== 'undefined' &&
  window.__VUE_DEVTOOLS_GLOBAL_HOOK__;

function devtoolPlugin (store) {
  if (!devtoolHook) { return }

  store._devtoolHook = devtoolHook;

  devtoolHook.emit('vuex:init', store);

  devtoolHook.on('vuex:travel-to-state', function (targetState) {
    store.replaceState(targetState);
  });

  store.subscribe(function (mutation, state) {
    devtoolHook.emit('vuex:mutation', mutation, state);
  });
}

/**
 * Get the first item that pass the test
 * by second argument function
 *
 * @param {Array} list
 * @param {Function} f
 * @return {*}
 */
/**
 * Deep copy the given object considering circular structure.
 * This function caches all nested objects and its copies.
 * If it detects circular structure, use cached copy to avoid infinite loop.
 *
 * @param {*} obj
 * @param {Array<Object>} cache
 * @return {*}
 */


/**
 * forEach for object
 */
function forEachValue (obj, fn) {
  Object.keys(obj).forEach(function (key) { return fn(obj[key], key); });
}

function isObject (obj) {
  return obj !== null && typeof obj === 'object'
}

function isPromise (val) {
  return val && typeof val.then === 'function'
}

function assert (condition, msg) {
  if (!condition) { throw new Error(("[vuex] " + msg)) }
}

var Module = function Module (rawModule, runtime) {
  this.runtime = runtime;
  this._children = Object.create(null);
  this._rawModule = rawModule;
  var rawState = rawModule.state;
  this.state = (typeof rawState === 'function' ? rawState() : rawState) || {};
};

var prototypeAccessors$1 = { namespaced: { configurable: true } };

prototypeAccessors$1.namespaced.get = function () {
  return !!this._rawModule.namespaced
};

Module.prototype.addChild = function addChild (key, module) {
  this._children[key] = module;
};

Module.prototype.removeChild = function removeChild (key) {
  delete this._children[key];
};

Module.prototype.getChild = function getChild (key) {
  return this._children[key]
};

Module.prototype.update = function update (rawModule) {
  this._rawModule.namespaced = rawModule.namespaced;
  if (rawModule.actions) {
    this._rawModule.actions = rawModule.actions;
  }
  if (rawModule.mutations) {
    this._rawModule.mutations = rawModule.mutations;
  }
  if (rawModule.getters) {
    this._rawModule.getters = rawModule.getters;
  }
};

Module.prototype.forEachChild = function forEachChild (fn) {
  forEachValue(this._children, fn);
};

Module.prototype.forEachGetter = function forEachGetter (fn) {
  if (this._rawModule.getters) {
    forEachValue(this._rawModule.getters, fn);
  }
};

Module.prototype.forEachAction = function forEachAction (fn) {
  if (this._rawModule.actions) {
    forEachValue(this._rawModule.actions, fn);
  }
};

Module.prototype.forEachMutation = function forEachMutation (fn) {
  if (this._rawModule.mutations) {
    forEachValue(this._rawModule.mutations, fn);
  }
};

Object.defineProperties( Module.prototype, prototypeAccessors$1 );

var ModuleCollection = function ModuleCollection (rawRootModule) {
  // register root module (Vuex.Store options)
  this.register([], rawRootModule, false);
};

ModuleCollection.prototype.get = function get (path) {
  return path.reduce(function (module, key) {
    return module.getChild(key)
  }, this.root)
};

ModuleCollection.prototype.getNamespace = function getNamespace (path) {
  var module = this.root;
  return path.reduce(function (namespace, key) {
    module = module.getChild(key);
    return namespace + (module.namespaced ? key + '/' : '')
  }, '')
};

ModuleCollection.prototype.update = function update$1 (rawRootModule) {
  update([], this.root, rawRootModule);
};

ModuleCollection.prototype.register = function register (path, rawModule, runtime) {
    var this$1 = this;
    if ( runtime === void 0 ) { runtime = true; }

  if (process.env.NODE_ENV !== 'production') {
    assertRawModule(path, rawModule);
  }

  var newModule = new Module(rawModule, runtime);
  if (path.length === 0) {
    this.root = newModule;
  } else {
    var parent = this.get(path.slice(0, -1));
    parent.addChild(path[path.length - 1], newModule);
  }

  // register nested modules
  if (rawModule.modules) {
    forEachValue(rawModule.modules, function (rawChildModule, key) {
      this$1.register(path.concat(key), rawChildModule, runtime);
    });
  }
};

ModuleCollection.prototype.unregister = function unregister (path) {
  var parent = this.get(path.slice(0, -1));
  var key = path[path.length - 1];
  if (!parent.getChild(key).runtime) { return }

  parent.removeChild(key);
};

function update (path, targetModule, newModule) {
  if (process.env.NODE_ENV !== 'production') {
    assertRawModule(path, newModule);
  }

  // update target module
  targetModule.update(newModule);

  // update nested modules
  if (newModule.modules) {
    for (var key in newModule.modules) {
      if (!targetModule.getChild(key)) {
        if (process.env.NODE_ENV !== 'production') {
          console.warn(
            "[vuex] trying to add a new module '" + key + "' on hot reloading, " +
            'manual reload is needed'
          );
        }
        return
      }
      update(
        path.concat(key),
        targetModule.getChild(key),
        newModule.modules[key]
      );
    }
  }
}

var functionAssert = {
  assert: function (value) { return typeof value === 'function'; },
  expected: 'function'
};

var objectAssert = {
  assert: function (value) { return typeof value === 'function' ||
    (typeof value === 'object' && typeof value.handler === 'function'); },
  expected: 'function or object with "handler" function'
};

var assertTypes = {
  getters: functionAssert,
  mutations: functionAssert,
  actions: objectAssert
};

function assertRawModule (path, rawModule) {
  Object.keys(assertTypes).forEach(function (key) {
    if (!rawModule[key]) { return }

    var assertOptions = assertTypes[key];

    forEachValue(rawModule[key], function (value, type) {
      assert(
        assertOptions.assert(value),
        makeAssertionMessage(path, key, type, value, assertOptions.expected)
      );
    });
  });
}

function makeAssertionMessage (path, key, type, value, expected) {
  var buf = key + " should be " + expected + " but \"" + key + "." + type + "\"";
  if (path.length > 0) {
    buf += " in module \"" + (path.join('.')) + "\"";
  }
  buf += " is " + (JSON.stringify(value)) + ".";
  return buf
}

var Vue$1; // bind on install

var Store = function Store (options) {
  var this$1 = this;
  if ( options === void 0 ) { options = {}; }

  // Auto install if it is not done yet and `window` has `Vue`.
  // To allow users to avoid auto-installation in some cases,
  // this code should be placed here. See #731
  if (!Vue$1 && typeof window !== 'undefined' && window.Vue) {
    install(window.Vue);
  }

  if (process.env.NODE_ENV !== 'production') {
    assert(Vue$1, "must call Vue.use(Vuex) before creating a store instance.");
    assert(typeof Promise !== 'undefined', "vuex requires a Promise polyfill in this browser.");
    assert(this instanceof Store, "Store must be called with the new operator.");
  }

  var plugins = options.plugins; if ( plugins === void 0 ) { plugins = []; }
  var strict = options.strict; if ( strict === void 0 ) { strict = false; }

  var state = options.state; if ( state === void 0 ) { state = {}; }
  if (typeof state === 'function') {
    state = state() || {};
  }

  // store internal state
  this._committing = false;
  this._actions = Object.create(null);
  this._actionSubscribers = [];
  this._mutations = Object.create(null);
  this._wrappedGetters = Object.create(null);
  this._modules = new ModuleCollection(options);
  this._modulesNamespaceMap = Object.create(null);
  this._subscribers = [];
  this._watcherVM = new Vue$1();

  // bind commit and dispatch to self
  var store = this;
  var ref = this;
  var dispatch = ref.dispatch;
  var commit = ref.commit;
  this.dispatch = function boundDispatch (type, payload) {
    return dispatch.call(store, type, payload)
  };
  this.commit = function boundCommit (type, payload, options) {
    return commit.call(store, type, payload, options)
  };

  // strict mode
  this.strict = strict;

  // init root module.
  // this also recursively registers all sub-modules
  // and collects all module getters inside this._wrappedGetters
  installModule(this, state, [], this._modules.root);

  // initialize the store vm, which is responsible for the reactivity
  // (also registers _wrappedGetters as computed properties)
  resetStoreVM(this, state);

  // apply plugins
  plugins.forEach(function (plugin) { return plugin(this$1); });

  if (Vue$1.config.devtools) {
    devtoolPlugin(this);
  }
};

var prototypeAccessors = { state: { configurable: true } };

prototypeAccessors.state.get = function () {
  return this._vm._data.$$state
};

prototypeAccessors.state.set = function (v) {
  if (process.env.NODE_ENV !== 'production') {
    assert(false, "Use store.replaceState() to explicit replace store state.");
  }
};

Store.prototype.commit = function commit (_type, _payload, _options) {
    var this$1 = this;

  // check object-style commit
  var ref = unifyObjectStyle(_type, _payload, _options);
    var type = ref.type;
    var payload = ref.payload;
    var options = ref.options;

  var mutation = { type: type, payload: payload };
  var entry = this._mutations[type];
  if (!entry) {
    if (process.env.NODE_ENV !== 'production') {
      console.error(("[vuex] unknown mutation type: " + type));
    }
    return
  }
  this._withCommit(function () {
    entry.forEach(function commitIterator (handler) {
      handler(payload);
    });
  });
  this._subscribers.forEach(function (sub) { return sub(mutation, this$1.state); });

  if (
    process.env.NODE_ENV !== 'production' &&
    options && options.silent
  ) {
    console.warn(
      "[vuex] mutation type: " + type + ". Silent option has been removed. " +
      'Use the filter functionality in the vue-devtools'
    );
  }
};

Store.prototype.dispatch = function dispatch (_type, _payload) {
    var this$1 = this;

  // check object-style dispatch
  var ref = unifyObjectStyle(_type, _payload);
    var type = ref.type;
    var payload = ref.payload;

  var action = { type: type, payload: payload };
  var entry = this._actions[type];
  if (!entry) {
    if (process.env.NODE_ENV !== 'production') {
      console.error(("[vuex] unknown action type: " + type));
    }
    return
  }

  this._actionSubscribers.forEach(function (sub) { return sub(action, this$1.state); });

  return entry.length > 1
    ? Promise.all(entry.map(function (handler) { return handler(payload); }))
    : entry[0](payload)
};

Store.prototype.subscribe = function subscribe (fn) {
  return genericSubscribe(fn, this._subscribers)
};

Store.prototype.subscribeAction = function subscribeAction (fn) {
  return genericSubscribe(fn, this._actionSubscribers)
};

Store.prototype.watch = function watch (getter, cb, options) {
    var this$1 = this;

  if (process.env.NODE_ENV !== 'production') {
    assert(typeof getter === 'function', "store.watch only accepts a function.");
  }
  return this._watcherVM.$watch(function () { return getter(this$1.state, this$1.getters); }, cb, options)
};

Store.prototype.replaceState = function replaceState (state) {
    var this$1 = this;

  this._withCommit(function () {
    this$1._vm._data.$$state = state;
  });
};

Store.prototype.registerModule = function registerModule (path, rawModule, options) {
    if ( options === void 0 ) { options = {}; }

  if (typeof path === 'string') { path = [path]; }

  if (process.env.NODE_ENV !== 'production') {
    assert(Array.isArray(path), "module path must be a string or an Array.");
    assert(path.length > 0, 'cannot register the root module by using registerModule.');
  }

  this._modules.register(path, rawModule);
  installModule(this, this.state, path, this._modules.get(path), options.preserveState);
  // reset store to update getters...
  resetStoreVM(this, this.state);
};

Store.prototype.unregisterModule = function unregisterModule (path) {
    var this$1 = this;

  if (typeof path === 'string') { path = [path]; }

  if (process.env.NODE_ENV !== 'production') {
    assert(Array.isArray(path), "module path must be a string or an Array.");
  }

  this._modules.unregister(path);
  this._withCommit(function () {
    var parentState = getNestedState(this$1.state, path.slice(0, -1));
    Vue$1.delete(parentState, path[path.length - 1]);
  });
  resetStore(this);
};

Store.prototype.hotUpdate = function hotUpdate (newOptions) {
  this._modules.update(newOptions);
  resetStore(this, true);
};

Store.prototype._withCommit = function _withCommit (fn) {
  var committing = this._committing;
  this._committing = true;
  fn();
  this._committing = committing;
};

Object.defineProperties( Store.prototype, prototypeAccessors );

function genericSubscribe (fn, subs) {
  if (subs.indexOf(fn) < 0) {
    subs.push(fn);
  }
  return function () {
    var i = subs.indexOf(fn);
    if (i > -1) {
      subs.splice(i, 1);
    }
  }
}

function resetStore (store, hot) {
  store._actions = Object.create(null);
  store._mutations = Object.create(null);
  store._wrappedGetters = Object.create(null);
  store._modulesNamespaceMap = Object.create(null);
  var state = store.state;
  // init all modules
  installModule(store, state, [], store._modules.root, true);
  // reset vm
  resetStoreVM(store, state, hot);
}

function resetStoreVM (store, state, hot) {
  var oldVm = store._vm;

  // bind store public getters
  store.getters = {};
  var wrappedGetters = store._wrappedGetters;
  var computed = {};
  forEachValue(wrappedGetters, function (fn, key) {
    // use computed to leverage its lazy-caching mechanism
    computed[key] = function () { return fn(store); };
    Object.defineProperty(store.getters, key, {
      get: function () { return store._vm[key]; },
      enumerable: true // for local getters
    });
  });

  // use a Vue instance to store the state tree
  // suppress warnings just in case the user has added
  // some funky global mixins
  var silent = Vue$1.config.silent;
  Vue$1.config.silent = true;
  store._vm = new Vue$1({
    data: {
      $$state: state
    },
    computed: computed
  });
  Vue$1.config.silent = silent;

  // enable strict mode for new vm
  if (store.strict) {
    enableStrictMode(store);
  }

  if (oldVm) {
    if (hot) {
      // dispatch changes in all subscribed watchers
      // to force getter re-evaluation for hot reloading.
      store._withCommit(function () {
        oldVm._data.$$state = null;
      });
    }
    Vue$1.nextTick(function () { return oldVm.$destroy(); });
  }
}

function installModule (store, rootState, path, module, hot) {
  var isRoot = !path.length;
  var namespace = store._modules.getNamespace(path);

  // register in namespace map
  if (module.namespaced) {
    store._modulesNamespaceMap[namespace] = module;
  }

  // set state
  if (!isRoot && !hot) {
    var parentState = getNestedState(rootState, path.slice(0, -1));
    var moduleName = path[path.length - 1];
    store._withCommit(function () {
      Vue$1.set(parentState, moduleName, module.state);
    });
  }

  var local = module.context = makeLocalContext(store, namespace, path);

  module.forEachMutation(function (mutation, key) {
    var namespacedType = namespace + key;
    registerMutation(store, namespacedType, mutation, local);
  });

  module.forEachAction(function (action, key) {
    var type = action.root ? key : namespace + key;
    var handler = action.handler || action;
    registerAction(store, type, handler, local);
  });

  module.forEachGetter(function (getter, key) {
    var namespacedType = namespace + key;
    registerGetter(store, namespacedType, getter, local);
  });

  module.forEachChild(function (child, key) {
    installModule(store, rootState, path.concat(key), child, hot);
  });
}

/**
 * make localized dispatch, commit, getters and state
 * if there is no namespace, just use root ones
 */
function makeLocalContext (store, namespace, path) {
  var noNamespace = namespace === '';

  var local = {
    dispatch: noNamespace ? store.dispatch : function (_type, _payload, _options) {
      var args = unifyObjectStyle(_type, _payload, _options);
      var payload = args.payload;
      var options = args.options;
      var type = args.type;

      if (!options || !options.root) {
        type = namespace + type;
        if (process.env.NODE_ENV !== 'production' && !store._actions[type]) {
          console.error(("[vuex] unknown local action type: " + (args.type) + ", global type: " + type));
          return
        }
      }

      return store.dispatch(type, payload)
    },

    commit: noNamespace ? store.commit : function (_type, _payload, _options) {
      var args = unifyObjectStyle(_type, _payload, _options);
      var payload = args.payload;
      var options = args.options;
      var type = args.type;

      if (!options || !options.root) {
        type = namespace + type;
        if (process.env.NODE_ENV !== 'production' && !store._mutations[type]) {
          console.error(("[vuex] unknown local mutation type: " + (args.type) + ", global type: " + type));
          return
        }
      }

      store.commit(type, payload, options);
    }
  };

  // getters and state object must be gotten lazily
  // because they will be changed by vm update
  Object.defineProperties(local, {
    getters: {
      get: noNamespace
        ? function () { return store.getters; }
        : function () { return makeLocalGetters(store, namespace); }
    },
    state: {
      get: function () { return getNestedState(store.state, path); }
    }
  });

  return local
}

function makeLocalGetters (store, namespace) {
  var gettersProxy = {};

  var splitPos = namespace.length;
  Object.keys(store.getters).forEach(function (type) {
    // skip if the target getter is not match this namespace
    if (type.slice(0, splitPos) !== namespace) { return }

    // extract local getter type
    var localType = type.slice(splitPos);

    // Add a port to the getters proxy.
    // Define as getter property because
    // we do not want to evaluate the getters in this time.
    Object.defineProperty(gettersProxy, localType, {
      get: function () { return store.getters[type]; },
      enumerable: true
    });
  });

  return gettersProxy
}

function registerMutation (store, type, handler, local) {
  var entry = store._mutations[type] || (store._mutations[type] = []);
  entry.push(function wrappedMutationHandler (payload) {
    handler.call(store, local.state, payload);
  });
}

function registerAction (store, type, handler, local) {
  var entry = store._actions[type] || (store._actions[type] = []);
  entry.push(function wrappedActionHandler (payload, cb) {
    var res = handler.call(store, {
      dispatch: local.dispatch,
      commit: local.commit,
      getters: local.getters,
      state: local.state,
      rootGetters: store.getters,
      rootState: store.state
    }, payload, cb);
    if (!isPromise(res)) {
      res = Promise.resolve(res);
    }
    if (store._devtoolHook) {
      return res.catch(function (err) {
        store._devtoolHook.emit('vuex:error', err);
        throw err
      })
    } else {
      return res
    }
  });
}

function registerGetter (store, type, rawGetter, local) {
  if (store._wrappedGetters[type]) {
    if (process.env.NODE_ENV !== 'production') {
      console.error(("[vuex] duplicate getter key: " + type));
    }
    return
  }
  store._wrappedGetters[type] = function wrappedGetter (store) {
    return rawGetter(
      local.state, // local state
      local.getters, // local getters
      store.state, // root state
      store.getters // root getters
    )
  };
}

function enableStrictMode (store) {
  store._vm.$watch(function () { return this._data.$$state }, function () {
    if (process.env.NODE_ENV !== 'production') {
      assert(store._committing, "Do not mutate vuex store state outside mutation handlers.");
    }
  }, { deep: true, sync: true });
}

function getNestedState (state, path) {
  return path.length
    ? path.reduce(function (state, key) { return state[key]; }, state)
    : state
}

function unifyObjectStyle (type, payload, options) {
  if (isObject(type) && type.type) {
    options = payload;
    payload = type;
    type = type.type;
  }

  if (process.env.NODE_ENV !== 'production') {
    assert(typeof type === 'string', ("Expects string as the type, but found " + (typeof type) + "."));
  }

  return { type: type, payload: payload, options: options }
}

function install (_Vue) {
  if (Vue$1 && _Vue === Vue$1) {
    if (process.env.NODE_ENV !== 'production') {
      console.error(
        '[vuex] already installed. Vue.use(Vuex) should be called only once.'
      );
    }
    return
  }
  Vue$1 = _Vue;
  applyMixin(Vue$1);
}

var WilokeGallery = {render: function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{class:_vm.cssClass},[_c('div',{staticClass:"row",attrs:{"data-col-xs-gap":_vm.xsGap,"data-col-sm-gap":_vm.smGap}},_vm._l((_vm.aGallery),function(oItem,order){return _c('div',{class:_vm.itemCssClass(oItem, order)},[_c('div',{staticClass:"gallery-item_module__1wn6T"},[_c('a',{attrs:{"href":_vm.getImgLink(oItem)}},[_c('div',{staticClass:"imageCover_module__1VM4k"},[_c('div',{staticClass:"imageCover_img__3pxw7",style:({ 'background-image': 'url(' + _vm.getImgSize(oItem) + ')' })})]),_vm._v(" "),(_vm.isPrintNumberOfPhotos(order))?_c('div',{staticClass:"gallery-item_more__1nWfn pos-a-full"},[_c('div',{staticClass:"gallery-item_wrap__1olrT"},[_c('div',{staticClass:"gallery-item_number__vrRlG"},[_vm._v(_vm._s(_vm.maxPhotos))]),_vm._v(" "),_c('h4',{staticClass:"gallery-item_title__2yStU"},[_vm._v(_vm._s(_vm.photoText))])])]):_vm._e()])])])}))])},staticRenderFns: [],
    data: function data(){
        return{
            id: 'single-gallery',
            xsGap:5,
            fullGallery: {},
            aFullGallery: [],
            aUsedImages: [],
            smGap:10,
            oMagnificInstance: {},
            isLoading: false,
            maxPhotos: 0,
            photoText: WILCITY_I18.photos
        }
    },
    props: {
        size: {
            type: String,
            default: 'src'
        },
        itemClass: {
            type: String,
            default: ''
        },
        numberOfItems: {
            type: String,
            default: ''
        },
        rawGallery: {
            type: String,
            default: ''
        },
        aRealGallery: {
            type: Array,
            default: function (){
                return [];
            }
        },
        numberOfItems: {
            type: String,
            default: 3
        },
        additionalClass: {
            type: String,
            default: ''
        },
        galleryId: {
            type: String,
            default: ''
        },
        isShowOnHome: {
            type: String,
            default: 'no'
        },
    },
    computed: {
        cssClass: function cssClass(){
            return 'gallery_module__2AbLA '+ this.additionalClass;
        },
        aGallery: function aGallery(){
            if ( this.id === this.$store.state.oSingleGallery.id ){
                return this.$store.getters.getSingleGallery;
            }else{
                return this.aUsedImages;
            }
        }
    },
    methods: {
        itemCssClass: function itemCssClass(oItem, order){
            var itemClass = this.itemClass;
            if ( !this.itemClass ){
                itemClass = 'col-xs-6 col-sm-3';
            }

            if ( this.isShowOnHome == 'yes' ){
                if ( this.numberOfItems <= order ){
                    itemClass = ' hidden';
                }
            }

            return itemClass;
        },
        getImgLink: function getImgLink(oItem){
             if ( typeof oItem.link === 'undefined' ){
                return oItem.full;
            }

            return oItem.link;
        },
        getImgSize: function getImgSize(oItem){
            if ( typeof oItem[this.size] === 'undefined' ){
                return oItem['full'];
            }

            return oItem[this.size];
        },
        isPrintNumberOfPhotos: function isPrintNumberOfPhotos(order){
            if ( this.isShowOnHome == 'no' ){
                return false;
            }

            return (order + 1) == this.numberOfItems && this.numberOfItems < this.maxPhotos;
        },
        setup: function setup(){
            var this$1 = this;

            if ( !this.numberOfItems ){
                this.numberOfItems = 0;
            }
            var numberOfItems = this.numberOfItems;
            try {
                if ( this.aRealGallery.length ){
                    this.fullGallery = this.aRealGallery;
                }else if( !WilCityHelpers.isNull(this.rawGallery) ){
                    this.fullGallery = JSON.parse(this.rawGallery);
                }

                if ( this.fullGallery.length ){
                    if (this.fullGallery.length < this.numberOfItems){
                        numberOfItems = this.fullGallery.length;
                    }

                    for ( var order in this$1.fullGallery ){
                        if ( order < numberOfItems ){
                            this$1.aUsedImages.push(this$1.fullGallery[order]);
                        }

                        this$1.aFullGallery.push(this$1.fullGallery[order]);
                    }
                }

                this.maxPhotos = this.aFullGallery.length;
            }catch(err){
                console.log('Gallery is emptied');
            }
        },
        magnific: function magnific(){
            jQuery(this.$el).magnificPopup({
                delegate: 'a',
                gallery: {
                  enabled: true
                },
                type: 'image' // this is default type
            });
        },
        destroyMagnific: function destroyMagnific(){
            jQuery(this.$el).removeData('magnificPopup');
        }
    },
    beforeMount: function beforeMount(){
        this.setup();
    },
    mounted: function mounted(){
        this.magnific();
    }
};

var HeaderComponent = {render: function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('header',{staticClass:"content-box_header__xPnGx clearfix"},[_c('div',{staticClass:"wil-float-left"},[_c('h4',{staticClass:"content-box_title__1gBHS"},[_c('i',{class:_vm.settings.icon}),_c('span',[_vm._v(_vm._s(_vm.settings.name))])])])])},staticRenderFns: [],
    props: ['settings']
};

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

var WilokeReviewStatistic = {render: function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{staticClass:"add-review_module__2KOQC"},[_c('div',{staticClass:"add-review_wrap__K1JtF"},[_c('div',{staticClass:"add-review_left__ziIv1"},[_c('h3',{staticClass:"add-review_title__3ePFu",domProps:{"innerHTML":_vm._s(_vm.printTotalReviews)}})]),_vm._v(" "),_c('div',{directives:[{name:"show",rawName:"v-show",value:(_vm.isUseReviewed == 'no'),expression:"isUseReviewed == 'no'"}],staticClass:"add-review_right__31XA0"},[_c('a',{staticClass:"wil-btn wil-btn--primary wil-btn--sm wil-btn--round",attrs:{"href":"#"},on:{"click":function($event){$event.preventDefault();_vm.onOpenReviewPopup($event);}}},[_c('i',{staticClass:"la la-star-o"}),_vm._v(" "+_vm._s(_vm.oTranslation.addReview)+" ")])])])])},staticRenderFns: [],
    data: function data(){
        return {
            oTranslation: WILCITY_I18
        }
    },
    props: ['totalReviews', 'postTitle', 'isUseReviewed'],
    computed: {
        printTotalReviews: function printTotalReviews(){
            var totalReviews = 0;
            if ( this.totalReviews > 1000 ){
                totalReviews = Math.floor(this.totalReviews / 1000);
                if ( this.totalReviews > totalReviews ){
                    totalReviews += totalReviews + 'k' + Math.ceil((this.totalReviews - totalReviews)/100);
                }else{
                    totalReviews = totalReviews + 'k';
                }
            }else{
                totalReviews = this.totalReviews;
            }

            totalReviews = "<span class='color-primary'>"+totalReviews+"</span> " + (this.totalReviews > 1 ?  this.oTranslation.reviewsFor : this.oTranslation.reviewFor) + ' ' + this.postTitle;

            return totalReviews;
        }
    },
    methods: {
        onOpenReviewPopup: function onOpenReviewPopup(){
            this.$emit('on-open-review-popup');
        }
    }
};

var WilokeTotalReviews = {render: function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('header',{staticClass:"content-box_header__xPnGx clearfix"},[_c('div',{staticClass:"wil-float-left"},[_c('h4',{staticClass:"content-box_title__1gBHS"},[_c('i',{staticClass:"la la-star-o"}),_c('span',{domProps:{"innerHTML":_vm._s(_vm.printTotalReviews)}})])])])},staticRenderFns: [],
    data: function data(){
        return{
            oTranslation: WILCITY_I18
        }
    },
    props: ['totalReviews', 'postTitle'],
    computed:{
       printTotalReviews: function printTotalReviews(){
            var totalReviews = 0;
            if ( this.totalReviews > 1000 ){
                totalReviews = Math.floor(this.totalReviews / 1000);
                if ( this.totalReviews > totalReviews ){
                    totalReviews += totalReviews + 'k' + Math.ceil((this.totalReviews - totalReviews)/100);
                }else{
                    totalReviews = totalReviews + 'k';
                }
            }else{
                totalReviews = this.totalReviews;
            }

            return totalReviews = "<span class='color-primary'>"+totalReviews+"</span> " + (this.totalReviews > 1 ?  this.oTranslation.reviewsFor : this.oTranslation.reviewFor) + ' ' + this.postTitle;
        }
    }
};

var WilokeSocialsSharing = {render: function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return (_vm.aSocialNetworks)?_c('ul',{staticClass:"list_module__1eis9 list-none list_social__31Q0V list_medium__1aT2c list_abs__OP7Og arrow--top-right"},[_vm._l((_vm.aSocialNetworks),function(social){return _c('li',{staticClass:"list_item__3YghP"},[(_vm.getSocialLink(social))?_c('a',{staticClass:"list_link__2rDA1 text-ellipsis color-primary--hover",attrs:{"href":_vm.getSocialLink(social),"target":"_blank"},on:{"click":_vm.sharePost}},[_c('span',{staticClass:"list_icon__2YpTp"},[_c('i',{class:_vm.socialIcon(social)})]),_c('span',{staticClass:"list_text__35R07",domProps:{"textContent":_vm._s(_vm.socialName(social))}})]):_vm._e()])}),_vm._v(" "),_c('li',{staticClass:"list_item__3YghP"},[_c('a',{staticClass:"list_link__2rDA1 text-ellipsis color-primary--hover",attrs:{"href":_vm.email},on:{"click":_vm.sharePost}},[_vm._m(0),_c('span',{staticClass:"list_text__35R07"},[_vm._v("Email")])])]),_vm._v(" "),_c('li',{staticClass:"list_item__3YghP"},[_c('a',{staticClass:"list_link__2rDA1 text-ellipsis color-primary--hover",attrs:{"href":"#","data-shortlink":_vm.copylink},on:{"click":function($event){$event.preventDefault();_vm.copyLink(_vm.copylink);}}},[_vm._m(1),_c('span',{staticClass:"list_text__35R07"},[_vm._v(_vm._s(_vm.oTranslation.copyLink))])])])],2):_vm._e()},staticRenderFns: [function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('span',{staticClass:"list_icon__2YpTp"},[_c('i',{staticClass:"fa fa-envelope"})])},function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('span',{staticClass:"list_icon__2YpTp"},[_c('i',{staticClass:"fa fa-link"})])}],
    props: ['postLink', 'postTitle', 'postId'],
    data: function data(){
        return {
            oTranslation: WILCITY_I18,
            usedSocialNetworks: WILOKE_GLOBAL.aUsedSocialNetworks
        }
    },
    computed: {
        aSocialNetworks: function aSocialNetworks(){
            if ( typeof this.usedSocialNetworks == 'undefined' ){
                return false;
            }

            return Object.values(this.usedSocialNetworks);
        },
        email: function email(){
            return 'mailto:?Subject='+this.postTitle.replace(' ', '%20')+"&Body="+this.postLink;
        },
        copylink: function copylink(){
            return this.postLink;
        }
    },
    methods: {
        getSocialLink: function getSocialLink(social){
            var oSocialLinks = {
                facebook: '//facebook.com/sharer.php?u='+encodeURI(this.postLink)+"&t="+encodeURI(this.postTitle),
                twitter: '//twitter.com/intent/tweet?text='+encodeURI(this.postTitle)+'-'+encodeURI(this.postLink)+'&source=webclient',
                'google-plus': '//plus.google.com/share?url='+encodeURI(this.postLink)+'&title='+encodeURI(this.postTitle)+'&source=webclient',
                digg: '//www.digg.com/submit?url='+encodeURI(this.postLink)+'&title='+encodeURI(this.postTitle)+'&source=webclient',
                reddit: '//reddit.com/submit?url='+encodeURI(this.postLink)+'&title='+encodeURI(this.postTitle)+'&source=webclient',
                linkedin: '//www.linkedin.com/shareArticle?mini=true&url='+encodeURI(this.postLink)+'&title='+encodeURI(this.postTitle)+'&source=webclient',
                stumbleupon: '//www.stumbleupon.com/submit?url='+encodeURI(this.postLink)+'&title='+encodeURI(this.postTitle)+'&source=webclient',
                tumblr: '//www.tumblr.com/share/link?url='+encodeURI(this.postLink)+'&name='+encodeURI(this.postTitle)+'&source=webclient',
                pinterest: '//pinterest.com/pin/create/button/?url='+encodeURI(this.postLink)+'&description='+encodeURI(this.postLink)+'&source=webclient',
                vk: '//vk.com/share.php?url='+encodeURI(this.postLink)+'&description='+encodeURI(this.postTitle)+'&source=webclient'
            };

            if ( typeof oSocialLinks[social] !== 'undefined' ){
                return oSocialLinks[social];
            }

            return false;
        },
        socialIcon: function socialIcon(socialName){
            return 'fa fa-'+socialName;
        },
        socialName: function socialName(socialName$1){
            return socialName$1 == 'google-plus' ? 'Google+' : WilCityHelpers.ucFirst(socialName$1);
        },
        ajax: function ajax(){
            var this$1 = this;

            jQuery.ajax({
                type: 'POST',
                url: WILOKE_GLOBAL.ajaxurl,
                data: {
                    action: 'wilcity_count_shares',
                    postID: this.postId
                },
                success: (function (response){
                    if ( typeof response.data.countShared !== 'undefined' ){
                        jQuery('.wilcity-count-shared-'+this$1.postId).html(response.data.countShared + ' ' + response.data.text);
                    }
                })
            });
        },
        copyLink: function copyLink(copyLink$1){
            window.prompt(this.oTranslation.pressToCopy, copyLink$1);
            this.ajax();
        },
        sharePost: function sharePost(){
            this.ajax();
        }
    }
};

var ReviewItem = {render: function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{class:_vm.wrapperClass},[_c('div',{staticClass:"comment-review_header__1si3M"},[_c('div',{staticClass:"utility-box-1_module__MYXpX utility-box-1_boxLeft__3iS6b clearfix utility-box-1_sm__mopok review-author-avatar"},[_c('div',{staticClass:"utility-box-1_avatar__DB9c_ rounded-circle",style:('background-image: url('+_vm.oReview.avatar+');')},[_c('img',{attrs:{"src":_vm.oReview.avatar,"alt":_vm.oReview.displayName}})]),_vm._v(" "),_c('div',{staticClass:"utility-box-1_body__8qd9j"},[_c('div',{staticClass:"utility-box-1_group__2ZPA2"},[_c('h3',{staticClass:"utility-box-1_title__1I925"},[_vm._v(_vm._s(_vm.oReview.displayName))])]),_vm._v(" "),_c('div',{staticClass:"utility-box-1_description__2VDJ6"},[_vm._v(_vm._s(_vm.oReview.postDate))])])]),_vm._v(" "),_c('div',{staticClass:"comment-review_abs__9mb1G pos-a-center-right"},[_c('span',{class:_vm.stickyClass(_vm.oReview)},[_c('i',{staticClass:"la la-thumb-tack"})]),_vm._v(" "),(_vm.oReview.oRating)?_c('div',{staticClass:"rated-small_module__1vw2B rated-small_style-1__2lG7u ml-20"},[_c('div',{staticClass:"rated-small_wrap__2Eetz",attrs:{"data-rated":_vm.dataRated(_vm.oReview.oRating.average)}},[_c('div',{staticClass:"rated-small_overallRating__oFmKR"},[_vm._v(_vm._s(_vm.oReview.oRating.average))]),_vm._v(" "),_c('div',{staticClass:"rated-small_ratingWrap__3lzhB"},[_c('div',{staticClass:"rated-small_maxRating__2D9mI"},[_vm._v(_vm._s(_vm.oReview.oRating.mode))]),_vm._v(" "),_c('div',{staticClass:"rated-small_ratingOverview__2kCI_"},[_vm._v(_vm._s(_vm.oReview.oRating.quality))])])])]):_vm._e(),_vm._v(" "),_c('div',{staticClass:"dropdown_module__J_Zpj ml-20"},[_c('div',{staticClass:"dropdown_threeDots__3fa2o",on:{"click":function($event){$event.preventDefault();_vm.toggleToolbar($event);}}},[_c('span',{staticClass:"dropdown_dot__3I1Rn"}),_c('span',{staticClass:"dropdown_dot__3I1Rn"}),_c('span',{staticClass:"dropdown_dot__3I1Rn"})]),_vm._v(" "),_c('div',{class:_vm.wrapperToolbarClass},[_c('ul',{staticClass:"list_module__1eis9 list-none list_small__3fRoS list_abs__OP7Og arrow--top-right"},[_c('li',{staticClass:"list_item__3YghP"},[_c('a',{staticClass:"list_link__2rDA1 text-ellipsis color-primary--hover",attrs:{"href":"#"},on:{"click":function($event){$event.preventDefault();_vm.onOpenReportPopup(_vm.oReview.ID);}}},[_vm._m(0),_c('span',{staticClass:"list_text__35R07"},[_vm._v(_vm._s(_vm.oTranslation.reportReview))])])]),_vm._v(" "),((_vm.oReview.isParentAuthor=='yes' ||  _vm.canDoAnything=='yes') && _vm.oReview.isPintToTop != 'yes')?_c('li',{staticClass:"list_item__3YghP"},[_c('a',{staticClass:"list_link__2rDA1 text-ellipsis color-primary--hover",attrs:{"href":"#"},on:{"click":function($event){$event.preventDefault();_vm.putThisReviewToTop($event);}}},[_vm._m(1),_vm._v(" "),_c('span',{staticClass:"list_text__35R07"},[_vm._v(_vm._s(_vm.oTranslation.pinToTopOfReview))])])]):(_vm.oReview.isParentAuthor=='yes' || _vm.canDoAnything=='yes')?_c('li',{staticClass:"list_item__3YghP"},[_c('a',{staticClass:"list_link__2rDA1 text-ellipsis color-primary--hover",attrs:{"href":"#"},on:{"click":function($event){$event.preventDefault();_vm.putThisReviewToTop($event);}}},[_vm._m(2),_vm._v(" "),_c('span',{staticClass:"list_text__35R07"},[_vm._v(_vm._s(_vm.oTranslation.unPinToTopOfReview))])])]):_vm._e(),_vm._v(" "),(_vm.canAddReview)?_c('li',{staticClass:"list_item__3YghP"},[_c('a',{staticClass:"list_link__2rDA1 text-ellipsis color-primary--hover",attrs:{"href":"#"},on:{"click":_vm.addNewReview}},[_vm._m(3),_vm._v(" "),_c('span',{staticClass:"list_text__35R07"},[_vm._v(_vm._s(_vm.oTranslation.addReview))])])]):_vm._e(),_vm._v(" "),(_vm.oReview.isAuthor=='yes')?_c('li',{staticClass:"list_item__3YghP"},[_c('a',{staticClass:"list_link__2rDA1 text-ellipsis color-primary--hover",attrs:{"href":"#"},on:{"click":function($event){$event.preventDefault();_vm.onOpenEditReview(_vm.oReview.ID);}}},[_vm._m(4),_vm._v(" "),_c('span',{staticClass:"list_text__35R07"},[_vm._v(_vm._s(_vm.oTranslation.editReview))])])]):_vm._e(),_vm._v(" "),(_vm.canDoAnything=='yes' || _vm.oReview.isAuthor=='yes')?_c('li',{staticClass:"list_item__3YghP"},[_c('a',{class:_vm.deleteReviewClass,attrs:{"href":"#"},on:{"click":function($event){$event.preventDefault();_vm.onOpenDeleteReviewPopup($event);}}},[_vm._m(5),_vm._v(" "),_c('span',{staticClass:"list_text__35R07"},[_vm._v(_vm._s(_vm.oTranslation.delete))])])]):_vm._e()])])])])]),_vm._v(" "),_c('div',{staticClass:"comment-review_body__qhUqq"},[_c('div',{staticClass:"comment-review_content__1jFfZ"},[_c('h3',{staticClass:"comment-review_title__2WbAh"},[_vm._v(_vm._s(_vm.oReview.postTitle))]),_vm._v(" "),_c('div',{domProps:{"innerHTML":_vm._s(_vm.oReview.postContent)}})]),_vm._v(" "),_c('wiloke-gallery',{attrs:{"additional-class":"comment-review_gallery__2Tyry","item-class":"col-xs-12 col-sm-3","number-of-items":"100","size":"medium","a-real-gallery":_vm.oReview.gallery}}),_vm._v(" "),_c('div',{staticClass:"comment-review_meta__1chzm"},[_c('span',[_vm._v(_vm._s(_vm.printLiked))]),(_vm.oReview.isEnabledDiscussion=='yes')?_c('span',[_vm._v(_vm._s(_vm.printNumberOfComments))]):_vm._e(),_c('span',[_vm._v(_vm._s(_vm.printCountShared))])])],1),_vm._v(" "),_c('footer',{staticClass:"comment-review_footer__3XR0_"},[_c('div',{staticClass:"comment-review_btnGroup__1PqPh"},[_c('div',{staticClass:"comment-review_btn__32CMP"},[_c('a',{class:_vm.likeCssClass,attrs:{"href":"#"},on:{"click":function($event){$event.preventDefault();_vm.thankForYourReview($event);}}},[_c('i',{staticClass:"la la-thumbs-up"}),_vm._v(_vm._s(_vm.likeOrLiked))])]),_vm._v(" "),(_vm.oReview.isEnabledDiscussion=='yes')?_c('div',{staticClass:"comment-review_btn__32CMP"},[_c('a',{staticClass:"utility-meta_module__mfOnV",attrs:{"href":"#"},on:{"click":function($event){$event.preventDefault();_vm.writeAComment($event);}}},[_c('i',{staticClass:"la la-comments-o"}),_vm._v(_vm._s(_vm.oTranslation.comment))])]):_vm._e(),_vm._v(" "),_c('div',{staticClass:"comment-review_btn__32CMP"},[_c('a',{staticClass:"utility-meta_module__mfOnV",attrs:{"href":"#"},on:{"click":function($event){$event.preventDefault();_vm.toggleSocialsSharing($event);}}},[_c('i',{staticClass:"la la-share-square-o"}),_vm._v(_vm._s(_vm.oTranslation.share)+" ")]),_vm._v(" "),_c('div',{directives:[{name:"show",rawName:"v-show",value:(_vm.isOpenSocialsSharing),expression:"isOpenSocialsSharing"}],staticClass:"comment-review_shareContent__UGmyE active"},[_c('wiloke-socials-sharing',{attrs:{"post-link":_vm.oReview.postLink,"post-title":_vm.oReview.postTitle,"post-id":_vm.oReview.ID,"wrapper-class":"comment-review_btn__32CMP"}})],1)])]),_vm._v(" "),_c('wiloke-discussion',{attrs:{"parent-id":_vm.oReview.ID,"toggle-review-discussion":_vm.oReview.isEnabledDiscussion,"o-user":_vm.oUser,"a-old-discussions":_vm.oReview.aDiscussions,"max-discussions":_vm.oReview.maxDiscussions}})],1)])},staticRenderFns: [function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('span',{staticClass:"list_icon__2YpTp"},[_c('i',{staticClass:"la la-flag-o"})])},function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('span',{staticClass:"list_icon__2YpTp"},[_c('i',{staticClass:"la la-thumb-tack"})])},function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('span',{staticClass:"list_icon__2YpTp"},[_c('i',{staticClass:"la la-thumb-tack"})])},function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('span',{staticClass:"list_icon__2YpTp"},[_c('i',{staticClass:"la la-star-o"})])},function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('span',{staticClass:"list_icon__2YpTp"},[_c('i',{staticClass:"la la-edit"})])},function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('span',{staticClass:"list_icon__2YpTp"},[_c('i',{staticClass:"la la-trash"})])}],
        data: function data(){
            return {
                isOpenSocialsSharing: false,
                canAddReview: this.$parent.isHiddenAddNewBtn,
                isUpdatingLike: null,
                isLiked: this.oReview.isLiked,
                oLikedData: {},
                oDiscussions: {},
                countLiked: this.oReview.countLiked,
                countDiscussion: this.oReview.countDiscussion,
                countShared: this.oReview.countShared,
                oTranslation: WILCITY_I18,
                isShowingToolbar: false
            }
        },
        components: {
            WilokeGallery: WilokeGallery,
            WilokeSocialsSharing: WilokeSocialsSharing,
            WilokeDiscussion: WilokeDiscussion
        },
        props: ['reviewId', 'order', 'oReview', 'oUser', 'canDoAnything'],
        computed: {
            deleteReviewClass: function deleteReviewClass(){
                return 'list_link__2rDA1 text-ellipsis color-primary--hover wilcity-edit-review wilcity-delete-review-'+this.oReview.ID;
            },
            wrapperClass: function wrapperClass(){
                return 'comment-review_module__-Z5tr wilcity-js-review-item-' + this.oReview.ID;
            },
            likeOrLiked: function likeOrLiked(){
                if ( this.isLiked == null ){
                    this.isLiked = this.liked;
                }
                return this.isLiked == 'yes' ? this.oTranslation.liked : this.oTranslation.like;
            },
            likeCssClass: function likeCssClass(){
                return {
                    'utility-meta_module__mfOnV': 1==1,
                    'color-primary': this.isLiked == 'yes'
                }
            },
            printLiked: function printLiked(){
                return this.countLiked + ' ' + (this.countLiked > 1 ? this.oTranslation.likes : this.oTranslation.like);
            },
            printNumberOfComments: function printNumberOfComments(){
                return this.countDiscussion + ' ' + (this.countDiscussion > 1 ? this.oTranslation.comments : this.oTranslation.comment);
            },
            printCountShared: function printCountShared(){
                return this.countShared + ' ' + (this.countShared > 1 ? this.oTranslation.shares : this.oTranslation.share);
            },
            wrapperToolbarClass: function wrapperToolbarClass(){
                return this.isShowingToolbar ? 'dropdown_itemsWrap__2fuze active' : 'dropdown_itemsWrap__2fuze';
            }
        },
        methods:{
            onOpenDeleteReviewPopup: function onOpenDeleteReviewPopup(){
				this.$store.commit('updatePopupStatus', {
					id: 'wilcity-delete-review-popup',
					status: 'open'
				});
				this.$store.commit('updatePopupArgs', {
					id: 'wilcity-delete-review-popup',
					oArgs: {
						reviewID: this.oReview.ID
					}
				});
			},
            toggleToolbar: function toggleToolbar(){
                this.isShowingToolbar = !this.isShowingToolbar;
            },
            dataRated: function dataRated(average){
                if ( this.oReview.oRating.mode == 5 ){
                    return parseFloat(average)*2;
                }
                return average;
            },
            onOpenEditReview: function onOpenEditReview(reviewID){
                this.$emit('onEditReview', reviewID);
            },
            toTenMode: function toTenMode(oReview){
                if ( oReview.oRating.mode == 10 ){
                    return oReview.oRating.average;
                }

                return parseFloat(oReview.oRating.average)*2;
            },
            onOpenReportPopup: function onOpenReportPopup(reviewID){
                this.$emit('onOpenReportPopup', reviewID);
            },
            addNewReview: function addNewReview(){
                this.$emit('onOpenReviewPopup', this.oReview.ID);
            },
            stickyClass: function stickyClass(oReview){
                return oReview.isPintToTop == 'yes' ? 'comment-review_sticky__3iQ8y color-primary fs-20 d-inline-block v-middle' : 'comment-review_sticky__3iQ8y color-primary fs-20 v-middle visible-hidden';
            },
            putThisReviewToTop: function putThisReviewToTop(){
                var this$1 = this;

                jQuery.ajax({
                    type: 'POST',
                    url: WILOKE_GLOBAL.ajaxurl,
                    data: {
                        action: 'wilcity_pin_review_to_top',
                        reviewID: this.reviewId,
                        postID: this.oReview.parentID
                    },
                    success: function (response) {
                        if ( response.data.is == 'removed' ){
                            window.location.reload();
                        }else{
                            this$1.$store.commit('updatePinReviewToTop', this$1.order);
                        }
                    }
                });
            },
            toggleSocialsSharing: function toggleSocialsSharing(){
                this.isOpenSocialsSharing = !this.isOpenSocialsSharing;
            },
            writeAComment: function writeAComment(){
                this.$emit('focusingNewCommentBox', true);
            },
            thankForYourReview: function thankForYourReview(){
                if ( this.isLiked == 'yes' ){
                    this.isLiked = 'no';
                    this.countLiked -= 1;
                }else{
                    this.isLiked = 'yes';
                    this.countLiked += 1;
                }

                if ( this.isUpdatingLike !== null && this.isUpdatingLike.status !== 200 ){
                    this.isUpdatingLike.abort();
                }

                this.isUpdatingLike = jQuery.ajax({
                    url: WILOKE_GLOBAL.ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'wilcity_review_is_update_like',
                        reviewID: this.reviewId
                    },
                    success: function(response){

                    }
                });
            }
        },
        mounted: function mounted(){
        }

    };

var BlockLoading = {render: function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{directives:[{name:"show",rawName:"v-show",value:(_vm.isLoading=='yes'),expression:"isLoading=='yes'"}],staticClass:"full-load"},[_c('div',{class:_vm.wrapperClass},[_c('div',{staticClass:"pill-loading_loader__3LOnT"})])])},staticRenderFns: [],
    computed: {
        wrapperClass: function wrapperClass(){
            return 'pill-loading_module__3LZ6v ' + this.position;
        }
    },
    props: ['position', 'isLoading']
};

var WilokeSingleListingReviews = {render: function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{class:_vm.wrapperClass,attrs:{"id":"single-reviews"}},[_c('div',{staticClass:"wil-colLarge js-sticky pos-r"},[_c('wiloke-message',{directives:[{name:"show",rawName:"v-show",value:(_vm.errMsg!==''),expression:"errMsg!==''"}],attrs:{"icon":"la la-frown-o","status":"danger","msg":_vm.errMsg}}),_vm._v(" "),(_vm.aReviews.length)?_c('div',{attrs:{"id":"wilcity-review-wrapper"}},[_vm._l((_vm.aReviews),function(oReview,order){return _c('review-item',{attrs:{"o-review":oReview,"o-user":_vm.oUser,"review-id":oReview.ID,"order":order,"can-do-anything":_vm.canDoAnything},on:{"onOpenReportPopup":_vm.onOpenReportPopup,"onEditReview":_vm.onOpenReviewPopup,"onOpenReviewPopup":_vm.onOpenReviewPopup}})}),_vm._v(" "),(!_vm.isFetchAllReviews)?_c('a',{class:_vm.loadmoreBtnClass,attrs:{"href":"#"},on:{"click":function($event){$event.preventDefault();_vm.oLoadMoreReviews($event);}}},[_vm._v(_vm._s(_vm.oTranslation.seeMoreReview)+" "),_c('div',{directives:[{name:"show",rawName:"v-show",value:(_vm.isLoading),expression:"isLoading"}],staticClass:"pill-loading_module__3LZ6v"},[_c('div',{staticClass:"pill-loading_loader__3LOnT"})])]):_vm._e()],2):_vm._e()],1),_vm._v(" "),_c('div',{staticClass:"wil-colSmall js-sticky"},[_c('div',{staticClass:"content-box_module__333d9"},[_c('wiloke-total-reviews',{attrs:{"total-reviews":_vm.finalTotalReviews,"post-title":_vm.postTitle}}),_vm._v(" "),_c('div',{staticClass:"content-box_body__3tSRB"},[_c('div',{staticClass:"rated-info-group_module__20kAf"},[(_vm.oReviewDetails)?_c('div',{staticClass:"rated-info-group_body__2yvB5"},_vm._l((_vm.oReviewDetailsResult),function(oReviewDetail,order){return _c('div',{staticClass:"rated-info_module__KsMQP"},[_c('div',{staticClass:"rated-info_title__2Oido"},[_vm._v(_vm._s(oReviewDetail.name))]),_vm._v(" "),_c('div',{staticClass:"rated-info_wrap__AI5nf"},[_c('div',{staticClass:"rated-info_progressBar__1pCWE"},[_c('div',{staticClass:"rated-info_bar__1T7U7",style:({left: _vm.printPercentage(oReviewDetail)})})])]),_vm._v(" "),_c('div',{staticClass:"rated-info_overallRating__1Js4A"},[_vm._v(_vm._s(oReviewDetail.average))])])})):_vm._e(),_vm._v(" "),_c('div',{directives:[{name:"show",rawName:"v-show",value:(_vm.averageReview>0),expression:"averageReview>0"}],staticClass:"rated-info-group_footer__2mcef",on:{"onOpenReviewPopup":_vm.onOpenReviewPopup}},[_c('h3',[_vm._v(_vm._s(_vm.oTranslation.averageRating))]),_vm._v(" "),_c('div',{staticClass:"rated-small_module__1vw2B rated-info-group_rated__29vEF pos-a-center-right"},[_c('div',{staticClass:"rated-small_wrap__2Eetz",attrs:{"data-rated":_vm.dataRated(_vm.averageReview)}},[_c('div',{staticClass:"rated-small_overallRating__oFmKR"},[_vm._v(_vm._s(_vm.finalAverageReviews))]),_vm._v(" "),_c('div',{staticClass:"rated-small_ratingWrap__3lzhB"},[_c('div',{staticClass:"rated-small_maxRating__2D9mI"},[_vm._v(_vm._s(_vm.mode))]),_vm._v(" "),_c('div',{staticClass:"rated-small_ratingOverview__2kCI_"},[_vm._v(_vm._s(_vm.finalReviewQuality))])])])])])])])],1),_vm._v(" "),(!_vm.isHiddenAddNewBtn)?_c('a',{staticClass:"wil-btn wil-btn--primary wil-btn--round wil-btn--md wil-btn--block",attrs:{"href":"#"},on:{"click":function($event){$event.preventDefault();_vm.onOpenReviewPopup(null);}}},[_c('i',{staticClass:"la la-star-o"}),_vm._v(" "+_vm._s(_vm.oTranslation.addReview)+" ")]):_vm._e()])])},staticRenderFns: [],
    data: function data(){
        return {
            oHeaderSettings: WILCITY_SINGLE_LISTING.navigation.draggable.reviews,
            isFetching: 'no',
            key: 'reviews',
            errMsg: '',
            content: '',
            oTranslation: WILCITY_I18,
            isFetchAllReviews: false,
            totalReviewsScore: 0,
            page: 1,
            isLoading: false,
            totalReviewScoreExceptLast: 0,
            oReviewDetailsResult: {},
            isHiddenAddNewBtn: this.isUserReviewed == 'yes',
            oReviewDetails: typeof WILCITY_REVIEW_SETTINGS !== 'undefined' ? WILCITY_REVIEW_SETTINGS.details : {},
            finalTotalReviews: null,
            finalReviewQuality: null,
            finalAverageReviews: null,
            isFirstLoaded: false,
            btnClass: 'wil-btn wil-btn--light wil-btn--round wil-btn--md wil-btn--block',
            oUser: {}
        }
    },
    props: ['averageReview', 'reviewQuality', 'rawReviewDetails', 'totalReviews', 'postTitle', 'mode', 'rawReviewDetailsResult', 'isUserReviewed', 'canDoAnything'],
    components:{
        HeaderComponent: HeaderComponent,
        ReviewItem: ReviewItem,
        BlockLoading: BlockLoading,
        WilokeReviewStatistic: WilokeReviewStatistic,
        WilokeTotalReviews: WilokeTotalReviews,
        WilokeMessage: WilokeMessage
    },
    computed: {
        wrapperClass: function wrapperClass(){
            var cl = 'single-tab-content';
            return this.key == this.$store.getters.getCurrentNavTab ? cl + ' active' : cl + ' hidden';
        },
        aReviews: function aReviews(){
            this.errMsg = '';
            return this.$store.getters.getReviews;
        },
        loadmoreBtnClass: function loadmoreBtnClass(){
            return !this.isLoading ? this.btnClass : this.btnClass + ' wil-btn--loading';
        }
    },
    methods: {
        printPercentage: function printPercentage(oReviewDetail){
            return oReviewDetail.percentage + '%';
        },
        dataRated: function dataRated(average){
            if ( this.mode == 5 ){
                return parseFloat(average)*2;
            }
            return average;
        },
        fetchContent: function fetchContent(){
            var this$1 = this;

            if ( this.isFetchAllReviews ){
                return true;
            }
            this.isFetching = 'yes';
            this.$root.componentLoading = this.key;
            this.$root.ajaxFetching = jQuery.ajax({
                type: 'POST',
                url: WILOKE_GLOBAL.ajaxurl,
                data: {
                    action: 'wilcity_fetch_single_review',
                    postID: WILOKE_GLOBAL.postID,
                    page: this.page
                },
                success: (function (response){
                    this$1.isLoading = false;
                    if ( response.success ){
                        this$1.errMsg = '';
                        if ( typeof response.data.isFinished !== 'undefined' ){
                            this$1.isFetchAllReviews = true;
                        }else{
                            this$1.$store.commit('updateReviews', response.data.reviews);
                            this$1.oUser = response.data.user;
                            this$1.$root.componentLoading = '';
                        }

                        setTimeout(function (){
                            this$1.scrollToReview();
                        }, 200);

                        if ( response.data.maxPages <= 1 ){
                            this$1.isFetchAllReviews = true;
                        }
                    }else{
                        this$1.errMsg = response.data.msg;
                        this$1.isFetchAllReviews = true;
                    }
                    this$1.isFirstLoaded = true;
                    this$1.isFetching = 'no';
                })
            });
        },
        oLoadMoreReviews: function oLoadMoreReviews(){
            this.page += 1;
            this.isLoading = true;
            this.fetchContent();
        },
        reviewItemPercentage: function reviewItemPercentage(oReviewItem){
            return oReviewItem.average*100 + '%';
        },
        listenReviews: function listenReviews(){
            var this$1 = this;

            this.$parent.$on('updatedReviewedID', (function (oNewReview){
                if ( typeof oNewReview.ID == 'undefined' ){
                    this$1.oReviews.push(oNewReview);
                }else{
                    for ( var order in this$1.oReviews ){
                        if ( this$1.oReviews[order].ID == oNewReview.ID  ){
                            this$1.$set(this$1.oReviews, order, oNewReview);
                            break;
                        }
                    }
                }
            }));
        },
        parseReviewDetailsResults: function parseReviewDetailsResults(){
            this.oReviewDetailsResult = JSON.parse(this.rawReviewDetailsResult);
            this.finalTotalReviews = this.totalReviews;
            this.finalAverageReviews = this.averageReview;
            this.finalReviewQuality = this.reviewQuality;
        },
        listenChanged: function listenChanged(){
            var this$1 = this;

            jQuery('body').on('changedGeneralReview', (function (event, oData){
                if ( typeof oData.aDetails !== 'undefined' ){
                    this$1.oReviewDetailsResult = oData.aDetails;
                }

                if ( typeof oData.total !== 'undefined' ){
                    this$1.finalTotalReviews = oData.total;
                }

                if ( typeof oData.average !== 'undefined' ){
                    this$1.finalAverageReviews = oData.average;
                }

                if ( typeof oData.quality !== 'undefined' ){
                    this$1.finalReviewQuality = oData.quality;
                }
            }));
        },
        onOpenReportPopup: function onOpenReportPopup(reviewID){
            this.$emit('on-open-report-popup', reviewID);
        },
        onOpenReviewPopup: function onOpenReviewPopup(reviewID){
            this.$emit('on-open-review-popup', reviewID);
        },
        scrollToReview: function scrollToReview(){
            var nodeName = WilCityHelpers.getParamFromUrl('st');
            if ( !nodeName ){
                return false;
            }

            var $el = jQuery('#'+nodeName);

            if ( !$el.length ){
                var el = '.'+nodeName;
                if ( jQuery(el).length ){
                    $el = jQuery(el).last();
                }
            }
            if ( $el.length ){
                jQuery("html, body").animate({ scrollTop: $el.offset().top - 100 }, 1000);
            }
        }
    },
    beforeMount: function beforeMount(){
        this.parseReviewDetailsResults();
    },
    mounted: function mounted(){
        var this$1 = this;

        if ( this.isFirstLoaded ){
            return false;
        }
        this.$parent.$on('fetchContent',  function (mode) {
            if ( mode == this$1.key  ){
                this$1.fetchContent();
            }
        });
    }
};

var WilokeVideoGallery = {render: function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{staticClass:"content-box_body__3tSRB"},[_c('div',{staticClass:"gallery_module__2AbLA"},[_c('div',{staticClass:"row",attrs:{"data-col-xs-gap":_vm.xsGap,"data-col-sm-gap":_vm.smGap}},_vm._l((_vm.aVideos),function(oItem){return _c('div',{directives:[{name:"show",rawName:"v-show",value:(_vm.aVideos.length),expression:"aVideos.length"}],class:_vm.itemClass},[_c('div',{staticClass:"gallery-item_module__1wn6T"},[_c('div',{staticClass:"video-popup_module__2P6ZG video-popup_sm__11-9c"},[_c('div',{staticClass:"video-popup_media__dEwwq"},[_c('div',{staticClass:"video-popup_overlay__2lJoC"}),_vm._v(" "),_c('div',{staticClass:"video-popup_img__3zV5d bg-cover",style:({ 'background-image': 'url(' + oItem.thumbnail + ')' })}),_vm._v(" "),_c('a',{staticClass:"video-popup_popup__17b-F js-video-popup",attrs:{"href":oItem.src}},[_c('i',{staticClass:"la la-play"})])])])])])}))])])},staticRenderFns: [],
    data: function data(){
        return{
            xsGap:5,
            fullVideos: {},
            aFullVideos: [],
            aVideos: [],
            smGap:10,
        }
    },
    props: ['itemClass', 'numberOfItems', 'rawVideos', 'aRealVideoGallery'],
    watch: {
        'aRealVideoGallery': function(oNewVideoGallery){
            this.aVideos = this.aVideos.concat(oNewVideoGallery);
            this.aFullVideos = this.aVideos;
            this.destroyMagnific();
            this.magnific();
        }
    },
    methods: {
        setup: function setup(){
            var this$1 = this;

            if ( !this.itemClass ){
                this.itemClass = 'col-xs-12 col-sm-4';
            }

            if ( !this.numberOfItems ){
                this.numberOfItems = 0;
            }

            var numberOfItems = this.numberOfItems;

            try {
                if ( this.aRealVideoGallery.length ){
                    this.fullVideos = this.aRealVideoGallery;
                }else if( this.rawVideos.length ){
                    this.fullVideos = JSON.parse(this.rawVideos);
                }
                if ( this.fullVideos.length ){
                    if (this.fullVideos.length < this.numberOfItems){
                        numberOfItems = this.fullVideos.length;
                    }

                    for ( var order in this$1.fullVideos ){
                        if ( order < numberOfItems ){
                            this$1.aVideos.push(this$1.fullVideos[order]);
                        }

                        this$1.aFullVideos.push(this$1.fullVideos[order]);
                    }
                }
            }catch(err){

            }
        },
        magnific: function magnific(){
            jQuery(this.$el).magnificPopup({
                fixedContentPos: true,
                delegate: 'a', // the selector for gallery item
                gallery: {
                  enabled: true
                },
                type: 'iframe' // this is default type
            });
        },
        destroyMagnific: function destroyMagnific(){
            jQuery(this.$el).removeData('magnificPopup');
        }
    },
    beforeMount: function beforeMount(){
        this.setup();
    },
    mounted: function mounted(){
        this.magnific();
    }
};

var WilokeHeader = {render: function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('header',{staticClass:"content-box_header__xPnGx clearfix"},[_c('div',{staticClass:"wil-float-left"},[_c('h4',{staticClass:"content-box_title__1gBHS"},[_c('i',{class:_vm.icon}),_c('span',[_vm._v(_vm._s(_vm.heading))])])])])},staticRenderFns: [],
    props: ['icon', 'heading']
};

var WilokeDynamicPopup = {render: function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{class:_vm.wrapperClass},[_c('div',{staticClass:"wil-overlay js-popup-overlay"}),_vm._v(" "),_c('div',{staticClass:"wil-tb"},[_c('div',{staticClass:"wil-tb__cell"},[_c('div',[(_vm.title!='')?_c('header',[_vm._m(0),_vm._v(" "),_c('div',[_c('span',{staticClass:"js-toggle-close",on:{"click":function($event){$event.preventDefault();_vm.closedPopup($event);}}},[_c('i',{staticClass:"la la-close"})])])]):_vm._e(),_vm._v(" "),_c('div',{staticClass:"popup_body__1wtsy wil-scroll-bar",domProps:{"innerHTML":_vm._s(_vm.content)}}),_vm._v(" "),_c('div',[_c('a',{staticClass:"wil-btn wil-btn--danger wil-btn--round",attrs:{"href":"#"},on:{"click":function($event){$event.preventDefault();_vm.cancelAction($event);}}},[_vm._v(_vm._s(_vm.oTranslation.cancel))]),_vm._v(" "),_c('a',{staticClass:"wil-btn wil-btn--success wil-btn--round",attrs:{"href":"#"},on:{"click":function($event){$event.preventDefault();_vm.executeAction($event);}}},[_vm._v(_vm._s(_vm.oTranslation.oke))])])])])])])},staticRenderFns: [function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('h3',[_c('i',{staticClass:"la la la-angle-double-right"}),_c('span',[_vm._v("A taste of life")])])}],
    data: function data(){
        return{
            oTranslation: WILCITY_I18,
            isOpen: false,
            title: '',
            icon: 'la la-bell-o',
            content: ''
        }
    },
    computed:{
        wrapperClass: function wrapperClass(){
            return {
                'popup_module__3M-0- pos-f-full popup_sm__Rc24D popup_mobile-full__1hyc4 js-popup': 1==1,
                'active': this.isOpen
            }
        }
    },
    methods: {
        openPopup: function openPopup(oData){
            if ( typeof oData.icon !== 'undefined' ){
                this.icon = oData.icon;
            }

            if ( typeof oData.title !== 'undefined' ){
                this.icon = oData.title;
            }

            if ( typeof oData.content !== 'undefined' ){
                this.icon = oData.content;
            }

            this.isOpen = true;
        },
        closedPopup: function closedPopup(){
            this.isOpen = false;
        },
        cancelAction: function cancelAction(){
            this.$emit('confirmationStatus', false);
            this.closedPopup();
        },
        executeAction: function executeAction(){
            this.$emit('confirmationStatus', true);
            this.closedPopup();
        }
    },
    mounted: function mounted(){
        var this$1 = this;

        this.$nextTick(function (){
            this$1.$on('openPopup', this$1.openPopup);
        });

    }
};

var WilokeCommentForm = {render: function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{staticClass:"content-box_module__333d9"},[_c('wiloke-header',{attrs:{"icon":_vm.headingIcon,"heading":_vm.heading}}),_vm._v(" "),_c('div',{staticClass:"content-box_body__3tSRB"},[_c('wiloke-message',{directives:[{name:"show",rawName:"v-show",value:(_vm.errorMsg.length),expression:"errorMsg.length"}],attrs:{"icon":"la la-bullhorn","msg":_vm.errorMsg}}),_vm._v(" "),_c('div',{staticClass:"row"},[(_vm.isUserLoggedIn == 'no')?_c('div',{staticClass:"col-xs-6"},[_c('div',{class:_vm.nameWrapperClass},[_c('div',{staticClass:"field_wrap__Gv92k"},[_c('input',{staticClass:"field_field__3U_Rt",attrs:{"type":"text"},on:{"keyup":_vm.removeNameError}}),_vm._v(" "),_c('span',{staticClass:"field_label__2eCP7 text-ellipsis required"},[_vm._v(_vm._s(_vm.oTranslation.name))]),_vm._v(" "),_c('span',{staticClass:"bg-color-primary"}),_vm._v(" "),_c('span',{directives:[{name:"show",rawName:"v-show",value:(_vm.isNameError),expression:"isNameError"}],staticClass:"field_message__3Z6FX color-quaternary"},[_vm._v(_vm._s(_vm.errorNameMsg))])])])]):_vm._e(),_vm._v(" "),(_vm.isUserLoggedIn == 'no')?_c('div',{staticClass:"col-xs-6"},[_c('div',{class:_vm.emailWrapperClass},[_c('div',{staticClass:"field_wrap__Gv92k"},[_c('input',{staticClass:"field_field__3U_Rt",attrs:{"type":"email"},on:{"keyup":_vm.removeEmailError}}),_vm._v(" "),_c('span',{staticClass:"field_label__2eCP7 text-ellipsis required"},[_vm._v(_vm._s(_vm.oTranslation.email))]),_vm._v(" "),_c('span',{staticClass:"bg-color-primary"}),_vm._v(" "),_c('span',{directives:[{name:"show",rawName:"v-show",value:(_vm.isEmailError),expression:"isEmailError"}],staticClass:"field_message__3Z6FX color-quaternary"},[_vm._v(_vm._s(_vm.errorEmailMsg))])])])]):_vm._e(),_vm._v(" "),_c('div',{staticClass:"col-xs-12"},[_c('div',{class:_vm.commentWrapperClass},[_c('div',{staticClass:"field_wrap__Gv92k"},[_c('textarea',{directives:[{name:"model",rawName:"v-model",value:(_vm.content),expression:"content"}],staticClass:"field_field__3U_Rt",attrs:{"placeholder":_vm.label},domProps:{"value":(_vm.content)},on:{"keyup":_vm.removeCommentError,"input":function($event){if($event.target.composing){ return; }_vm.content=$event.target.value;}}}),_c('span',{staticClass:"field_label__2eCP7 text-ellipsis"}),_c('span',{staticClass:"bg-color-primary"})]),_vm._v(" "),_c('span',{directives:[{name:"show",rawName:"v-show",value:(_vm.isCommentError),expression:"isCommentError"}],staticClass:"field_message__3Z6FX color-quaternary"},[_vm._v(_vm._s(_vm.errorCommentMsg))])])])]),_vm._v(" "),_c('a',{staticClass:"wil-btn wil-btn--md wil-btn--gray wil-btn--block wil-btn--round",attrs:{"href":"#"},on:{"click":function($event){$event.preventDefault();_vm.submitContent($event);}}},[_vm._v(_vm._s(_vm.postCommentText))])],1)],1)},staticRenderFns: [],
    data: function data(){
        return{
            errorMsg:'',
            errorNameMsg:'',
            errorEmailMsg:'',
            errorCommentMsg:'',
            content:'',
            name:'',
            email:'',
            xhr: null,
            oTranslation: WILCITY_I18,
            isCommentError: false,
            isNameError: false,
            isEmailError: false
        }
    },
    components:{
       WilokeHeader: WilokeHeader,
       WilokeMessage: WilokeMessage
    },
    props: ['heading', 'headingIcon', 'postCommentText', 'postId', 'label', 'isUserLoggedIn'],
    methods: {
        validateEmail: function validateEmail() {
            var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
            return re.test(this.email);
        },
        removeCommentError: function removeCommentError(){
            this.isCommentError = false;
        },
        removeEmailError: function removeEmailError(){
            if ( this.validateEmail(this.email) ){
                this.isEmailError = false;
            }
        },
        removeNameError: function removeNameError(){
            this.isNameError = false;
        },
        setErrorMsg: function setErrorMsg(oError){
            switch(oError.type){
                case 'email':
                    this.isEmailError = true;
                    this.errorEmailMsg = oError.msg;
                    break;
                case 'name':
                    this.isNameError = true;
                    this.errorNameMsg = oError.msg;
                    break;
                case 'comment':
                    this.isCommentError = true;
                    this.errorCommentMsg = oError.msg;
                    break;
                default:
                    this.errorMsg = oError.msg;
                    break;
            }
        },
        submitContent: function submitContent(){
            var this$1 = this;

            var $body = jQuery('body');

            if ( this.isUserLoggedIn == 'yes' ){
                if ( !this.name.length ){
                    this.isNameError = true;
                }

                if ( this.validateEmail() ){
                    this.isEmailError = true;
                }
            }

            if ( this.content.length === 0 ){
                this.isCommentError = true;
                return false;
            }

            if ( this.xhr !== null && this.xhr.status !== 200 ){
                return false;
            }


            $body.trigger('topLoading');

            this.xhr = jQuery.ajax({
                type: 'POST',
                url: WILOKE_GLOBAL.ajaxurl,
                data:{
                    name: this.name,
                    email: this.email,
                    action: 'wilcity_post_comment',
                    content: this.content,
                    postID: this.postId
                },
                success: function (response){
                    $body.trigger('topHideLoading');
                    if ( response.success ){
                        jQuery('#wilcity-render-comments').prepend(response.data.html);
                    }else{
                        this$1.setErrorMsg(response.data);
                    }
                }
            });
        }
    },
    computed:{
        emailWrapperClass: function emailWrapperClass(){
            return {
                'field_module__1H6kT field_style2__2Znhe mb-15': 1==1,
                'active': this.email.length,
                'error': this.isEmailError
            }
        },
        nameWrapperClass: function nameWrapperClass(){
            return {
                'field_module__1H6kT field_style2__2Znhe mb-15': 1==1,
                'active': this.name.length,
                'error': this.isNameError
            }
        },
        commentWrapperClass: function commentWrapperClass(){
            return {
                'field_module__1H6kT field_style2__2Znhe mb-15': 1==1,
                'error':this.isCommentError
            }
        }
    },
    mounted: function mounted(){
    }
};

var WilokePopup = {render: function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{class:_vm.parseWrapperClass,attrs:{"data-popup-content":_vm.popupId}},[_c('div',{staticClass:"wil-overlay js-popup-overlay"}),_vm._v(" "),_c('div',{staticClass:"wil-tb"},[_c('div',{staticClass:"wil-tb__cell"},[_c('div',{staticClass:"popup_content__3CJVi"},[_c('header',{staticClass:"popup_header__2QTxC clearfix"},[_c('h3',{staticClass:"popup_title__3q6Xh"},[(_vm.icon)?_c('i',{class:_vm.icon}):_vm._e(),_c('span',{domProps:{"innerHTML":_vm._s(_vm.popupTitle)}})]),_vm._v(" "),_c('div',{staticClass:"popup_headerRight__c4FcP"},[_c('span',{staticClass:"popup_close__mJx2A color-primary--hover js-toggle-close",on:{"click":_vm.closePopup}},[_c('i',{staticClass:"la la-close"})])])]),_vm._v(" "),_c('div',{staticClass:"popup_body__1wtsy wil-scroll-bar"},[_c('div',{class:_vm.loadingClass,attrs:{"id":"popup-line-loading"}},[_c('div',{staticClass:"line-loading_loader__FjIcM"})]),_vm._v(" "),_vm._t("body")],2),_vm._v(" "),_vm._t("before-footer"),_vm._v(" "),_vm._t("footer")],2)])])])},staticRenderFns: [],
    data: function data(){
        return {
            isLoading: false,
            isOpenPopup: this.status == 'open'
        }
    },
    props: {
        'popupId': {
            type: String,
            default: ''
        },
        'popupTitle': {
            type: String,
            default: ''
        },
        'icon': {
            type: String,
            default: ''
        },
        'wrapperClass':{
            type: String,
            default: 'popup_module__3M-0- pos-f-full popup_md__3El3k popup_mobile-full__1hyc4'
        }
    },
    computed: {
        loadingClass: function loadingClass(){
            return this.isLoading ? 'line-loading_module__SUlA1 pos-a-top' : 'line-loading_module__SUlA1 pos-a-top hidden';
        },
        parseWrapperClass: function parseWrapperClass(){
            var cl = this.wrapperClass;

            if ( this.isOpenPopup || ( (typeof this.$store !== 'undefined') && this.$store.getters.getPopupStatus(this.popupId) == 'open') ){
               return cl + ' active';
            }

            return cl;
        }
    },
    methods:{
        closePopup: function closePopup(){
            this.isOpenPopup = false;
            if ( typeof this.$store !== 'undefined' && this.$store.getters.getPopupStatus(this.popupId) !== 'close' ){
                this.$store.dispatch('closePopup', {
                    id: this.popupId,
                    status: 'close'
                });
            }
        }
    },
    mounted: function mounted(){
        var this$1 = this;

        this.$parent.$on('line-loading', function (status){
            this$1.isLoading = status == 'yes';
        });

        this.$parent.$on('onOpenPopup', function (){
            this$1.isOpenPopup = true;
        });

        this.$parent.$on('onClosePopup', function (){
            this$1.c = false;
            this$1.closePopup();
        });
    }
};

var WilokeMessagePopup = {render: function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('wiloke-popup',{attrs:{"popup-id":"wilcity-message-popup","popup-title":_vm.oTranslation.newMessage,"icon":"la la-envelope"},on:{"on-close-popup":_vm.closePopup}},[_c('div',{attrs:{"slot":"body"},slot:"body"},[_c('wiloke-message',{directives:[{name:"show",rawName:"v-show",value:(_vm.errorMsg.length),expression:"errorMsg.length"}],attrs:{"msg":_vm.errorMsg,"status":"danger","icon":"la la-bullhorn"}}),_vm._v(" "),_c('wiloke-message',{directives:[{name:"show",rawName:"v-show",value:(_vm.successMsg.length),expression:"successMsg.length"}],attrs:{"msg":_vm.successMsg,"status":"success","icon":"la la-bullhorn"}}),_vm._v(" "),_c('div',{staticClass:"pos-r"},[_c('div',{staticClass:"field_module__1H6kT field_style2__2Znhe mb-15 active"},[_c('div',{staticClass:"field_wrap__Gv92k"},[_c('input',{directives:[{name:"model",rawName:"v-model",value:(_vm.displayName),expression:"displayName"}],staticClass:"field_field__3U_Rt",attrs:{"type":"text","readonly":"readonly"},domProps:{"value":(_vm.displayName)},on:{"input":function($event){if($event.target.composing){ return; }_vm.displayName=$event.target.value;}}}),_vm._v(" "),_c('span',{staticClass:"field_label__2eCP7 text-ellipsis"},[_vm._v(_vm._s(_vm.oTranslation.to))]),_vm._v(" "),_c('span',{staticClass:"bg-color-primary"})])]),_vm._v(" "),_c('div',{class:_vm.messageWrapper},[_c('div',{staticClass:"field_wrap__Gv92k"},[_c('textarea',{directives:[{name:"model",rawName:"v-model",value:(_vm.message),expression:"message"}],staticClass:"field_field__3U_Rt",domProps:{"value":(_vm.message)},on:{"input":function($event){if($event.target.composing){ return; }_vm.message=$event.target.value;}}}),_vm._v(" "),_c('span',{staticClass:"field_label__2eCP7 text-ellipsis"},[_vm._v(_vm._s(_vm.oTranslation.message))]),_vm._v(" "),_c('span',{staticClass:"bg-color-primary"})])])]),_vm._v(" "),_c('button',{class:_vm.btnClass,attrs:{"type":"submit"},on:{"click":_vm.sendMessage}},[_vm._v(_vm._s(_vm.oTranslation.send))])],1)])},staticRenderFns: [],
    data: function data(){
        return{
            oTranslation: WILCITY_I18,
            message: '',
            errorMsg: '',
            successMsg: '',
            isSending: 'no',
            xhr: null
        }
    },
    props: ['displayName', 'receiveId'],
    computed: {
        messageWrapper: function messageWrapper(){
            var cl = 'field_module__1H6kT field_style2__2Znhe mb-15';
            if ( this.message.length ){
                return cl + ' active';
            }
            return cl;
        },
        btnClass: function btnClass(){
            var cl = 'wil-btn wil-btn--primary wil-btn--block wil-btn--md';
            if ( this.isSending = 'no' && this.message.length  ){
                return cl;
            }

            return cl + ' disable'
        }
    },
    methods: {
        closePopup: function closePopup(){
            this.$store.dispatch('closePopup', {
                id: 'wilcity-signin-popup',
                status: 'close'
            });
        },
        sendMessage: function sendMessage(){
            var this$1 = this;

            if ( this.xhr !== null && this.xhr.status !== 200 ){
                this.xhr.abort();
            }

            this.successMsg = '';
            this.errorMsg = '';

            this.$emit('line-loading', 'yes');
            this.isSending = 'yes';
            this.xhr = jQuery.ajax({
                type: 'POST',
                url: WILOKE_GLOBAL.ajaxurl,
                data: {
                    action: 'wilcity_submit_new_msg',
                    message: this.message,
                    receiveID: this.receiveId
                },
                success: function (response) {
                    this$1.$emit('line-loading', 'no');
                    if ( response.success ){
                        this$1.successMsg = response.data.instantMessage;
                    }else{
                        this$1.errorMsg = response.data.msg;
                    }

                    setTimeout(function (){
                        this$1.successMsg = '';
                        this$1.errorMsg = '';
                        this$1.message = '';

                        jQuery(this$1.$el).find('.js-toggle-close').trigger('click');
                    }, 3000);
                    this$1.isSending = 'no';
                }

            });
        }
    },
    components:{
        WilokePopup: WilokePopup,
        WilokeMessage: WilokeMessage
    }
};

var Listing = {render: function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('article',{class:_vm.articleClass},[_c('div',{staticClass:"listing_firstWrap__36UOZ"},[_c('header',{staticClass:"listing_header__2pt4D"},[_c('a',{attrs:{"href":_vm.oListing.permalink}},[(_vm.oListing.featuredImage)?_c('div',{staticClass:"listing_img__3pwlB pos-a-full bg-cover",style:({'background-image':'url('+_vm.oListing.featuredImage+')'})},[_c('img',{attrs:{"src":_vm.oListing.featuredImage,"alt":_vm.oListing.postTitle}})]):_vm._e(),_vm._v(" "),(_vm.oListing.oReviews && _vm.oListing.oReviews.average > 0)?_c('div',{staticClass:"listing_rated__1y7qV"},[_c('div',{staticClass:"rated-small_module__1vw2B rated-small_style-2__3lb7d"},[_c('div',{staticClass:"rated-small_wrap__2Eetz",attrs:{"data-rated":_vm.detailDataRated(_vm.oListing.oReviews.average)}},[_c('div',{staticClass:"rated-small_overallRating__oFmKR",domProps:{"textContent":_vm._s(_vm.oListing.oReviews.average)}}),_vm._v(" "),_c('div',{staticClass:"rated-small_ratingWrap__3lzhB"},[_c('div',{staticClass:"rated-small_maxRating__2D9mI"},[_vm._v(_vm._s(_vm.oListing.oReviews.mode))]),_vm._v(" "),_c('div',{staticClass:"rated-small_ratingOverview__2kCI_"},[_vm._v(_vm._s(_vm.oListing.oReviews.quality))])])])])]):_vm._e()])]),_vm._v(" "),_c('div',{staticClass:"listing_body__31ndf"},[(_vm.oListing.logo)?_c('a',{staticClass:"listing_goo__3r7Tj",attrs:{"href":_vm.oListing.permalink}},[_c('div',{staticClass:"listing_logo__PIZwf bg-cover",style:({'background-image': 'url('+_vm.oListing.logo+')'})})]):_vm._e(),_vm._v(" "),_c('h2',{staticClass:"listing_title__2920A text-ellipsis"},[_c('a',{attrs:{"href":_vm.oListing.permalink},domProps:{"innerHTML":_vm._s(_vm.oListing.postTitle)}})]),_vm._v(" "),_c('div',{staticClass:"listing_tagline__1cOB3 text-ellipsis"},[_vm._v(_vm._s(_vm.oListing.excerpt))]),_vm._v(" "),_c('div',{staticClass:"listing_meta__6BbCG vertical"},[(_vm.oListing.oAddress)?_c('a',{staticClass:"text-ellipsis",attrs:{"target":"_blank","href":_vm.oListing.oAddress.addressOnGGMap}},[_c('i',{staticClass:"la la-map-marker color-primary"}),_vm._v(_vm._s(_vm.oListing.oAddress.address))]):_vm._e(),_vm._v(" "),(_vm.oListing.phone)?_c('a',{staticClass:"text-ellipsis",attrs:{"href":"tel:{{oListing.phone}}"}},[_c('i',{staticClass:"la la-phone color-primary"}),_vm._v(_vm._s(_vm.oListing.phone))]):_vm._e()])])]),_vm._v(" "),_c('footer',{staticClass:"listing_footer__1PzMC"},[_c('div',{staticClass:"text-ellipsis"},[_c('div',{staticClass:"icon-box-1_module__uyg5F text-ellipsis icon-box-1_style2__1EMOP"},[(_vm.oListing.oListingCat)?_c('div',{staticClass:"icon-box-1_block1__bJ25J"},[_c('a',{staticClass:"text-ellipsis",attrs:{"href":_vm.oListing.oListingCat.link}},[(_vm.oListing.oListingCat.oIcon.type=='icon')?_c('div',{staticClass:"icon-box-1_icon__3V5c0 rounded-circle",style:({'background-color': _vm.oListing.oListingCat.oIcon.color})},[_c('i',{class:_vm.oListing.oListingCat.oIcon.icon})]):_c('div',{staticClass:"bg-trasparent icon-box-1_icon__3V5c0"},[_c('img',{attrs:{"src":_vm.oListing.oListingCat.oIcon.url}})]),_vm._v(" "),_c('div',{staticClass:"icon-box-1_text__3R39g",domProps:{"innerHTML":_vm._s(_vm.oListing.oListingCat.name)}})])]):_vm._e(),_vm._v(" "),(_vm.oListing.oBusinessHours)?_c('div',{staticClass:"icon-box-1_block2__1y3h0"},[_c('span',{class:_vm.oListing.oBusinessHours.class},[_vm._v(_vm._s(_vm.oListing.oBusinessHours.text))])]):_vm._e()])]),_vm._v(" "),_c('div',{staticClass:"listing_footerRight__2398w"},[(_vm.oListing.gallery)?_c('a',{staticClass:"wilcity-preview-gallery",attrs:{"href":"#","data-tooltip":_vm.oTranslation.gallery,"data-tooltip-placement":"top","data-gallery":_vm.oListing.gallery}},[_c('i',{staticClass:"la la-search-plus"})]):_vm._e(),_vm._v(" "),_c('a',{staticClass:"wilcity-js-favorite",attrs:{"data-post-id":_vm.oListing.postID,"href":"#","data-tooltip":_vm.oTranslation.favoriteTooltip,"data-tooltip-placement":"top"}},[_c('i',{class:_vm.favoriteClass(_vm.oListing)})])])])])},staticRenderFns: [],
    data: function data(){
        return{
            oTranslation: WILCITY_I18,
            defaultArticleClass: 'listing_module__2EnGq wil-shadow mb-30 mb-sm-20 js-listing-module',
        }
    },
    props: ['oListing', 'layout'],
    computed: {
        articleClass: function articleClass(){
            if ( this.layout == 'grid' ){
                return this.defaultArticleClass;
            }

            return this.defaultArticleClass + ' js-listing-list';
        },
    },
    methods: {
        favoriteClass: function favoriteClass(oListing){
            return oListing.isMyFavorite == 'yes' ? 'la la-heart color-primary' : 'la la-heart-o';
        },
        detailDataRated: function detailDataRated(average){
            if ( this.mode == 5 ){
                return parseFloat(average)*2;
            }
            return average;
        }
    }
};

var WilokeListingsNearByMe = {render: function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{staticClass:"pos-r",staticStyle:{"min-height":"100px"}},[_c('block-loading',{attrs:{"position":"pos-a-center","is-loading":_vm.isLoading}}),_vm._v(" "),_c('div',{directives:[{name:"show",rawName:"v-show",value:(_vm.errorMsg.length),expression:"errorMsg.length"}],staticClass:"row"},[_c('div',{staticClass:"col-md-12 text-center",staticStyle:{"margin-top":"40px"},domProps:{"innerHTML":_vm._s(_vm.errorMsg)}})]),_vm._v(" "),_c('div',{directives:[{name:"show",rawName:"v-show",value:(!_vm.errorMsg.length),expression:"!errorMsg.length"}],staticClass:"row js-listing-grid",attrs:{"id":_vm.gridID}},_vm._l((_vm.aListings),function(oListing){return _c('div',{class:_vm.gridClass,style:(_vm.styleObject)},[_c('listing',{attrs:{"o-listing":oListing,"layout":_vm.style}})],1)}))],1)},staticRenderFns: [],
    data: function data(){
        return{
            isLoading: 'no',
            errorMsg:'',
            style: 'grid',
            aListings: [],
            oGeoLocation: {
                lat: '',
                lng: ''
            },
            oTranslation: WILCITY_I18
        }
    },
    props: ['postType', 'gridClass', 'postsPerPage', 'unit', 'radius', 'oAjaxData', 'tabId'],
    computed: {
        styleObject: function styleObject(){
            if ( this.style == 'grid' ){
                return {};
            }

            return {
                width: '100%',
                opacity: 1
            };
        }
    },
    components:{
        Listing: Listing,
        BlockLoading: BlockLoading,
        WilokeMessage: WilokeMessage
    },
    mounted: function mounted(){
        var this$1 = this;

        if ( this.tabId.length ){
            jQuery('a[href="#'+this.tabId+'"]').on('click', (function (event) {
                var $target = jQuery(event.currentTarget);
                if ( !$target.data('clicked') ){
                    this$1.getGeoLocationLatLng();
                    $target.data('clicked', true);
                }
            }));
        }else{
            this.getGeoLocationLatLng();
        }
    },
    methods: {
        getGeoLocationLatLng: function getGeoLocationLatLng(){
            this.isLoading = 'yes';
            var geocode = localStorage.getItem('geocode');
            if ( typeof geocode === 'undefined' ){
                this.askForLocation();
            }else{
                var savedAt = localStorage.getItem('geocode_saved_at');
                var now = Date.now();
                var millis = now - savedAt;

                if ( millis/10000 >= 600 ){
                    this.askForLocation();
                }else{
                    var aParse = geocode.split('-');
                    this.oGeoLocation.lat = aParse[0];
                    this.oGeoLocation.lng = aParse[1];
                    this.fetchListings();
                }
            }
        },
        showPosition: function showPosition(position){
            this.oGeoLocation.lat = position.coords.latitude;
            this.oGeoLocation.lng = position.coords.longitude;
            var now = Date.now();
            localStorage.setItem('geocode_saved_at', now);
            localStorage.setItem('geocode', position.coords.latitude+'-'+position.coords.longitude);

            this.fetchListings();
        },
        askForLocation: function askForLocation(){
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(this.showPosition);
            } else {
                this.errorMsg = this.oTranslation.geolocationError;
            }
        },
        fetchListings: function fetchListings(){
            var this$1 = this;

            jQuery.ajax({
                type: 'POST',
                data: {
                    action: 'wilcity_get_listings_nearbyme',
                    data: this.oAjaxData,
                    postType: this.postType,
                    postsPerPage: this.postsPerPage,
                    oAddress:{
                        lat: this.oGeoLocation.lat,
                        lng: this.oGeoLocation.lng,
                        unit: this.unit,
                        radius: this.radius
                    }
                },
                url: WILOKE_GLOBAL.ajaxurl,
                success: function (response) {
                    if ( response.success ){
                        this$1.aListings = response.data.aResults;
                    }else{
                        this$1.errorMsg = response.data.msg;
                    }
                    this$1.isLoading = 'no';

                    setTimeout(function (){
                        jQuery(this$1.$el).find('.wilcity-js-favorite').each(function(){
                            jQuery(this).wilcityFavoriteStatistic();
                        });
                        jQuery(this$1.$el).find('.wilcity-preview-gallery').each(function(){
                            jQuery(this).wilcityMagnificGalleryPopup();
                        });
                    }, 3000);

                }
            });
        }
    }

};

var WilokeErrorMsg = {render: function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{directives:[{name:"show",rawName:"v-show",value:(!_vm.isHidden),expression:"!isHidden"}],staticClass:"alert_module__Q4QZx alert_danger__2ajVf"},[_vm._m(0),_vm._v(" "),_c('div',{staticClass:"alert_content__1ntU3",domProps:{"innerHTML":_vm._s(_vm.msg)}}),(_vm.hasRemove)?_c('a',{staticClass:"alert_close__3PtGd",attrs:{"href":"#"},on:{"click":function($event){$event.preventDefault();_vm.hideMsg($event);}}},[_c('i',{staticClass:"la la-times"})]):_vm._e()])},staticRenderFns: [function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{staticClass:"alert_icon__1bDKL"},[_c('i',{staticClass:"la la-warning"})])}],
    data: function data(){
        return {
            isHidden: false
        }
    },
    props: ['msg', 'hasRemove'],
    methods: {
        hideMsg: function hideMsg(){
            event.preventDefault();
            this.isHidden = true;
        }
    }
};

var WilokeImgCheckbox = {render: function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{staticClass:"image-radio-checkbox_module__29DK2"},[_c('label',{staticClass:"image-radio-checkbox_inner__1VI-d"},[_c('input',{directives:[{name:"model",rawName:"v-model",value:(_vm.value),expression:"value"}],staticClass:"js-image-radio-checkbox",attrs:{"true-value":"yes","false-value":"no","type":"checkbox"},domProps:{"checked":Array.isArray(_vm.value)?_vm._i(_vm.value,null)>-1:_vm._q(_vm.value,"yes")},on:{"change":[function($event){var $$a=_vm.value,$$el=$event.target,$$c=$$el.checked?("yes"):("no");if(Array.isArray($$a)){var $$v=null,$$i=_vm._i($$a,$$v);if($$el.checked){$$i<0&&(_vm.value=$$a.concat([$$v]));}else{$$i>-1&&(_vm.value=$$a.slice(0,$$i).concat($$a.slice($$i+1)));}}else{_vm.value=$$c;}},_vm.changed]}}),_vm._v(" "),_c('div',{staticClass:"image-radio-checkbox_img__1_YKz"},[_c('img',{attrs:{"src":_vm.preview}}),_vm._v(" "),_vm._m(0)]),_vm._v(" "),_c('div',{staticClass:"image-radio-checkbox_footer__1jn1d"},[_c('h6',{domProps:{"innerHTML":_vm._s(_vm.label)}})])])])},staticRenderFns: [function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{staticClass:"image-radio-checkbox_icon__1LtKv"},[_c('i',{staticClass:"la la-check"})])}],
    props: ['preview', 'label', 'index', 'oOther'],
    data: function data(){
        return {
            value: 'no'
        }
    },
    methods: {
        changed: function changed(){
            this.$emit('checkboxChanged', this.value, {
                index: this.index,
                key: this.key,
                oOther: this.oOther
            });
        }
    }
};

var WilokePromotionPopup = {render: function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('wiloke-popup',{attrs:{"popup-id":"wilcity-promotion-popup","icon":"la la la-bullhorn","popupTitle":_vm.oTranslation.boostPost},on:{"on-close-popup":_vm.closePopup}},[_c('div',{staticStyle:{"min-height":"200px"},attrs:{"slot":"body"},slot:"body"},[_c('wiloke-error-msg',{directives:[{name:"show",rawName:"v-show",value:(_vm.errorMsg.length),expression:"errorMsg.length"}],attrs:{"msg":_vm.errorMsg}}),_vm._v(" "),_c('wiloke-message',{directives:[{name:"show",rawName:"v-show",value:(_vm.successMsg.length),expression:"successMsg.length"}],attrs:{"msg":_vm.successMsg,"icon":"la la-bullhorn","has-remove":"no","status":"success","msg":_vm.successMsg}}),_vm._v(" "),_c('div',{directives:[{name:"show",rawName:"v-show",value:(_vm.aPlans.length),expression:"aPlans.length"}]},[_c('div',{staticClass:"promo-item_module__24ZhT"},[_c('div',{staticClass:"promo-item_group__2ZJhC"},[_c('h3',{staticClass:"promo-item_title__3hfHG"},[_vm._v(_vm._s(_vm.oTranslation.selectAdsPosition))]),_vm._v(" "),_c('p',{staticClass:"promo-item_description__2nc26"},[_vm._v(_vm._s(_vm.oTranslation.selectAdsDesc))])])]),_vm._v(" "),_c('div',{staticClass:"row",attrs:{"data-col-xs-gap":"20"}},_vm._l((_vm.aPlans),function(oPlan,index){return _c('div',{class:{'col-md-4 col-lg-4':1==1, 'disable is-available': oPlan.isUsing=='yes'}},[_c('wiloke-img-checkbox',{attrs:{"preview":oPlan.preview,"index":index,"label":_vm.renderName(oPlan.name, oPlan.price),"o-other":{price: oPlan.price}},on:{"checkboxChanged":_vm.updatePromotion}})],1)}))])],1),_vm._v(" "),_c('div',{directives:[{name:"show",rawName:"v-show",value:(_vm.aPaymentGateways.length),expression:"aPaymentGateways.length"}],class:_vm.paymentGatewayWrapperClass,attrs:{"slot":"before-footer"},slot:"before-footer"},_vm._l((_vm.aPaymentGateways),function(gateway){return _c('div',{class:_vm.paymentGatewayWrapper},[_c('div',{staticClass:"image-radio-checkbox_module__29DK2"},[_c('label',{staticClass:"image-radio-checkbox_inner__1VI-d"},[_c('input',{directives:[{name:"model",rawName:"v-model",value:(_vm.selectedGateway),expression:"selectedGateway"}],attrs:{"type":"radio"},domProps:{"value":gateway,"checked":_vm._q(_vm.selectedGateway,gateway)},on:{"change":function($event){_vm.selectedGateway=gateway;}}}),_vm._v(" "),_c('div',{staticClass:"image-radio-checkbox_img__1_YKz"},[_c('div',{class:_vm.paymentClassItem(gateway)},[_c('a',{attrs:{"href":"#"},on:{"click":function($event){$event.preventDefault();_vm.payVia(gateway);}}},[_c('div',{staticClass:"icon-box-2_icon__ZqobK"},[_c('i',{class:_vm.paymentIcon(gateway)})]),_vm._v(" "),_c('p',{staticClass:"icon-box-2_content__1J1Eb",domProps:{"textContent":_vm._s(_vm.gatewayName(gateway))}})])]),_vm._v(" "),_c('div',{staticClass:"image-radio-checkbox_icon__1LtKv"},[_c('i',{staticClass:"la la-check"})])])])])])})),_vm._v(" "),_c('footer',{staticClass:"popup_footer__2pUrl clearfix",attrs:{"slot":"footer"},slot:"footer"},[_c('div',{staticClass:"float-left"},[_c('span',[_vm._v(_vm._s(_vm.oTranslation.totalLabel)+": ")]),_c('span',{domProps:{"innerHTML":_vm._s(_vm.totalPrice)}})]),_vm._v(" "),_c('div',{staticClass:"popup_footerRight__qvdP6"},[_c('button',{staticClass:"wil-btn wil-btn--gray wil-btn--sm wil-btn--round",attrs:{"type":"submit"},on:{"click":function($event){$event.preventDefault();_vm.closePopup($event);}}},[_vm._v(_vm._s(_vm.oTranslation.cancel))]),_vm._v(" "),_c('button',{class:_vm.boostBtnClass,attrs:{"type":"submit"},on:{"click":function($event){$event.preventDefault();_vm.boostNow($event);}}},[_vm._v(_vm._s(_vm.oTranslation.boostSaleBtn))])])])])},staticRenderFns: [],
    data: function data(){
        return {
            oTranslation: WILCITY_I18,
            successMsg: '',
            errorMsg: '',
            selectedGateway: '',
            currencySymbol: '',
            currencyPosition: '',
            postID: null,
            total: 0,
            xhr: null,
            isProgressing: false,
            aPlans: [],
            aSelectedPlans: [],
            aPaymentGateways: [],
            aWooCommerceIDs: [],
            status: 'close'
        }
    },
    computed: {
        paymentGatewayWrapperClass: function paymentGatewayWrapperClass(){
            return this.aSelectedPlans.length ? 'row payment-gateways active' : 'row payment-gateways';
        },
        boostBtnClass: function boostBtnClass(){
            var cl = 'wil-btn wil-btn--primary wil-btn--sm wil-btn--round';

            if ( this.isProgressing || !this.aSelectedPlans.length ){
                return cl + ' disable';
            }

            if ( this.total == 0 ){
                return cl + ' disable';
            }else{
                if ( this.aPaymentGateways.length ){
                    if ( this.selectedGateway == '' ){
                        return cl + ' disable';
                    }
                }

                return cl;
            }
        },
        totalPrice: function totalPrice(){
            return this.renderPrice(this.total);
        },
        paymentGatewayWrapper: function paymentGatewayWrapper(){
            var count = this.aPaymentGateways.length;
            if ( count == 1 ){
                return 'col-md-12 col-lg-12';
            }else if ( count == 2 ){
                return 'col-md-6 col-lg-6';
            }else{
                return 'col-md-4 col-lg-4';
            }
        }
    },
    components:{
        WilokeMessage: WilokeMessage,
        WilokeErrorMsg: WilokeErrorMsg,
        WilokePopup: WilokePopup,
        WilokeImgCheckbox: WilokeImgCheckbox
    },
    mounted: function mounted(){
        var this$1 = this;

        this.$parent.$on('on-open-promotion-popup', function (postID) {
            this$1.postID = postID;
            this$1.fetchPlans();
            this$1.$emit('onOpenPopup', true);
            this$1.getPaymentGateways();
        });
    },
    methods: {
        addProductsToCart: function addProductsToCart(resolve, reject){
            if ( this.aWooCommerceIDs.length == 0 ){
                resolve(true);
            }

            var self = this,
                productID = this.aWooCommerceIDs.shift(),
                oData = {
                    product_id: productID,
                    quantity: 1
                };

            jQuery.post( wc_add_to_cart_params.wc_ajax_url.toString().replace( '%%endpoint%%', 'add_to_cart' ), oData, function( response ) {
                if ( ! response ) {
                    reject(self.oTranslation.couldNotAddProduct);
                }

                if ( response.error ) {
                    reject(self.oTranslation.couldNotAddProduct);
                }

                self.addProductsToCart(resolve, reject);
            });
        },
        gatewayName: function gatewayName(gateway){
            if ( typeof this.oTranslation[gateway] == 'undefined' ){
                return gateway[0].toUpperCase()+gateway.substr(1);
            }else{
                return this.oTranslation[gateway];
            }
        },
        paymentClassItem: function paymentClassItem(gateway){
            return 'icon-box-2_module__AWd3Y wil-text-center bg-color-primary bg-color-'+gateway;
        },
        paymentIcon: function paymentIcon(gateway){
            return gateway == 'banktransfer' ? 'la la-money' : 'la la-cc-'+gateway;
        },
        renderName: function renderName(name, price){
            return name + ' - ' + this.renderPrice(price);
        },
        renderPrice: function renderPrice(price){
            switch(this.currencyPosition){
                case 'left':
                    return this.currencySymbol + price;
                    break;
                case 'right':
                    return price + this.currencySymbol;
                    break;
                case 'left_space':
                    return this.currencySymbol + ' ' + price;
                    break;
                case 'right_space':
                    return price + ' ' + this.currencySymbol;
                    break;
            }
        },
        closePopup: function closePopup(){
            this.$emit('onClosePopup', true);
        },
        boostNow: function boostNow(){
            var this$1 = this;

            if ( !this.aSelectedPlans.length ){
                return false;
            }

            if ( this.xhr !== null && this.xhr.status !== 200 ){
                this.xhr.abort();
            }
            this.errorMsg = '';
            this.successMsg = '';
            this.$emit('line-loading', 'yes');
            console.log(this.postID);
            this.isProgressing = true;
            this.xhr = jQuery.ajax({
                type: 'POST',
                url: WILOKE_GLOBAL.ajaxurl,
                data: {
                    action: 'wilcity_boost_listing',
                    gateway: this.selectedGateway,
                    aPlans: this.aPlans,
                    postID: this.postID
                },
                success: function (response) {
                    this$1.isProgressing = false;
                    this$1.$emit('line-loading', 'no');

                    if ( response.success ){
                        if ( typeof response.data.redirectTo !== 'undefined' ){
                            window.location.replace(response.data.redirectTo);
                        }else if ( typeof response.data.productIDs !== 'undefined' ){
                            this$1.aWooCommerceIDs = response.data.productIDs;
                            var promise = new Promise(function (resolve, reject) {
                                this$1.addProductsToCart(resolve, reject);
                            });

                            promise.then( function (data) {
                                window.location.replace(response.data.cartUrl);
                            }, function (error) {
                                this$1.errorMsg = error;
                            });
                        }else{
                            this$1.successMsg = response.data.msg;
                            setTimeout(function (){
                                this$1.closePopup();
                            }, 3000);
                        }
                    }else{
                        this$1.errorMsg = response.data.msg;
                    }
                }
            });
        },
        updatePromotion: function updatePromotion(value, oSettings){
            this.aPlans[oSettings.index].value = value;

            if ( value == 'yes' ){
                this.total = this.total + parseInt(oSettings.oOther.price);
                this.aSelectedPlans.push(oSettings.index);
            }else{
                this.total = this.total - parseInt(oSettings.oOther.price);
                var order = this.aSelectedPlans.indexOf(oSettings.index);
                this.aSelectedPlans.splice(order, 1);
            }
        },
        fetchPlans: function fetchPlans(){
            var this$1 = this;

            if ( this.aPlans.length ){
                return this.aPlans;
            }

            this.$emit('update-block-loading-status', 'yes');
            jQuery.ajax({
                type: 'POST',
                url: WILOKE_GLOBAL.ajaxurl,
                data: {
                    action: 'wilcity_fetch_promotion_plans',
                    postID: this.postID
                },
                success: (function (response){
                    this$1.aPlans = response.data.plans;
                    this$1.currencySymbol = response.data.symbol;
                    this$1.currencyPosition = response.data.position;
                    this$1.$emit('update-block-loading-status', 'no');
                })
            });

        },
        getPaymentGateways: function getPaymentGateways(){
            var this$1 = this;

            jQuery.ajax({
                type: 'POST',
                url: WILOKE_GLOBAL.ajaxurl,
                data: {
                    action: 'wilcity_get_payment_gateways'
                },
                success: (function (response){
                    if ( !response.success ){
                        if ( typeof response.data !== 'undefined' ){
                            this$1.errorMsg = response.data.msg;
                        }
                    }else{
                        this$1.aPaymentGateways = response.data.split(',');
                    }
                })
            });
        }
    }
};

var WilokeUploadImg = {render: function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return (!_vm.isMultiple)?_c('div',{class:_vm.wrapperClass},[_c('div',{staticClass:"field_wrap__Gv92k"},[_vm._t("wiloke-uploader-action",[_c('input',{staticClass:"field_field__3U_Rt",attrs:{"id":_vm.generateID,"type":"file","name":_vm.paramName}}),_vm._v(" "),_c('span',{staticClass:"input-filename",attrs:{"data-text":_vm.singleImgName},on:{"click":_vm.listenUploadEvent}},[_c('span',{staticClass:"input-fileimg",style:({backgroundImage: 'url('+_vm.singleBgImg+')'})})]),_vm._v(" "),_c('span',{staticClass:"field_label__2eCP7 text-ellipsis",class:{required: _vm.isRequired}},[_vm._v(_vm._s(_vm.labelName))]),_vm._v(" "),_c('span',{staticClass:"bg-color-primary"})],{paramName:_vm.paramName,isRequired:_vm.isRequired,labelName:_vm.labelName}),_vm._v(" "),_vm._t("wiloke-uploader-preview",[_c('div',{staticClass:"field_right__2qM90 pos-a-center-right"},[_c('a',{staticClass:"wil-btn wil-btn--primary wil-btn--round wil-btn--xxs",attrs:{"href":"#"},on:{"click":function($event){$event.preventDefault();_vm.listenUploadEvent($event);}}},[_vm._v(_vm._s(_vm.btnName))])])],{aUploadedImages:_vm.aUploadedImages})],2),_vm._v(" "),_c('span',{directives:[{name:"show",rawName:"v-show",value:(_vm.settings.errMsg!=''),expression:"settings.errMsg!=''"}],staticClass:"field_message__3Z6FX color-quaternary"},[_vm._v(_vm._s(_vm.settings.errMsg))])]):_c('div',{class:_vm.wrapperClass},[_c('div',{staticClass:"upload-image_row__2UK1p clearfix"},[(_vm.hasUploadedImages())?_c('div',_vm._l((_vm.aUploadedImages),function(oUploadedImg,order){return _c('div',{staticClass:"upload-image_thumb__V-SH7"},[_c('div',{staticClass:"upload-image_image__17ttf cover-after",style:({backgroundImage: 'url('+oUploadedImg.src+')'})}),_vm._v(" "),_c('span',{staticClass:"upload-image_remove__3Oa_t color-primary--hover",on:{"click":function($event){$event.preventDefault();_vm.removeImg(order);}}},[_c('i',{staticClass:"la la-close"})])])})):_vm._e(),_vm._v(" "),_c('div',{class:_vm.uploadClass},[_c('input',{attrs:{"id":_vm.generateID,"type":"file","name":_vm.paramName,"multiple":"multiple"}}),_vm._v(" "),_vm._m(0)])]),_vm._v(" "),_c('div',{directives:[{name:"show",rawName:"v-show",value:(_vm.settings.errMsg!=''),expression:"settings.errMsg!=''"}],staticClass:"clearfix"}),_vm._v(" "),_c('span',{directives:[{name:"show",rawName:"v-show",value:(_vm.settings.errMsg!=''),expression:"settings.errMsg!=''"}],staticClass:"field_message__3Z6FX color-quaternary"},[_vm._v(_vm._s(_vm.settings.errMsg))]),_vm._v(" "),_c('span',{directives:[{name:"show",rawName:"v-show",value:(_vm.warning!=''),expression:"warning!=''"}],staticClass:"field_message__3Z6FX color-quaternary"},[_vm._v(_vm._s(_vm.warning))])])},staticRenderFns: [function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{staticClass:"upload-image_buttonContent__fjl5V"},[_c('i',{staticClass:"la la-image"})])}],
        data: function data(){
            var oCommon = {
                isRequired: false,
                oPlanSettings: {},
                value: [],
                maximumImages: 0,
                aUploadedImages: typeof this.settings.value == 'object' && this.settings.value.length ? this.settings.value : [],
                oTranslation: WILCITY_I18
            }, oIndividual = {};

            if ( !this.settings.isMultiple ){
                oIndividual = Object.assign({}, {
                    uploadFieldID: '',
                    isMultiple: false,
                    paramName: 'image',
                    labelName: WILCITY_I18.image,
                    btnName: WILCITY_I18.uploadSingleImageTitle,
                    wrapperClassName: 'field_module__1H6kT field_style2__2Znhe mb-15',
                }, this.settings);
            }else{
                oIndividual = Object.assign({}, {
                    isMultiple: true,
                    labelName: WILCITY_I18.images,
                    paramName: 'images[]',
                    btnName: WILCITY_I18.uploadMultipleImagesTitle,
                    wrapperClassName: 'upload-image_module__3I5sF',
                }, this.settings);
            }

            return Object.assign({}, oCommon, oIndividual);
        },
        props: {
            settings: {
                type: Object,
                default: function (){ return ({
                    isMultiple: false,
                    value: [],
                    wrapperClassName: 'field_module__1H6kT field_style2__2Znhe mb-15',
                }); }
            },
            field:{
                type: Object
            }
	    },
	    watch: {
	        settings: {
	            handler: function handler(oNewVal){
	                this.aUploadedImages = oNewVal.value;
	                if ( typeof oNewVal.oPlanSettings !== 'undefined' && oNewVal.oPlanSettings ){
	                    this.oPlanSettings = oNewVal.oPlanSettings;
	                    this.setupConfiguration();
	                }
	            },
	            deep: true
	        }
	    },
	    computed:{
	        uploadClass: function uploadClass(){
	            return {
	                'upload-image_button__3-6QW color-primary--hover': 1==1,
	                'disable': this.maximumImages != 0 && this.aUploadedImages.length >= this.maximumImages
	            }
	        },
	        conditional: function conditional(){
	            var getConditional = '';
	            if ( this.isRequired ){
	                getConditional += 'required|';
	            }

	            getConditional = 'image|size:'+WILOKE_GLOBAL.maxUpload;
	            return getConditional;
	        },

            singleBgImg: function singleBgImg(){
                if ( !this.isValidImg() ){
                    return '';
                }

                return this.aUploadedImages[0].src;
            },
            singleImgName: function singleImgName(){
                if ( !this.isValidImg() ){
                    return '';
                }

                return this.aUploadedImages[0].fileName;
            },

            generateID: function generateID(){
	            this.uploadFieldID = 'wilcity-upload-' + new Date().getTime();
	            return this.uploadFieldID;
	        },
            warning: function warning(){
                if ( this.maximumImages != 0 && !isNaN(this.maximumImages) ){
                    return this.oTranslation.maximumImgsWarning.replace('%s', this.oPlanSettings.maximumGalleryImages);
                }
                return '';
	        },
            wrapperClass: function wrapperClass(){
                var wrapperClass = this.wrapperClassName;

                if ( typeof this.oPlanSettings.toggle_gallery !== 'undefined' && this.oPlanSettings.toggle_gallery == 'disable' ){
                    wrapperClass += ' disable';
                }

                if ( !this.hasUploadedImages() ){
                    return wrapperClass;
                }

                if ( typeof this.settings.errMsg !== 'undefined' && this.settings.errMsg != '' ){
                    wrapperClass = wrapperClass + ' error';
                }else{
                    wrapperClass = wrapperClass + ' active';
                }

                return wrapperClass;
            }
        },
        methods: {
            setupConfiguration: function setupConfiguration(){
                if ( typeof this.oPlanSettings.maximumGalleryImages !== 'undefined' && this.oPlanSettings.maximumGalleryImages != 0 ){
                    this.maximumImages = parseInt(this.oPlanSettings.maximumGalleryImages, 10);
                }
            },
            hasUploadedImages: function hasUploadedImages(){
                return this.aUploadedImages.length;
            },
            isValidImg: function isValidImg(){
                if ( (typeof this.settings.errMsg !== 'undefined' && this.settings.errMsg != '') || !this.hasUploadedImages() ){
                    return false;
                }
                return true;
            },
            getBase64Img: function getBase64Img(img, oFile){
                var canvas = document.createElement('canvas');
                canvas.width = img.width;
                canvas.height = img.height;
                var ctx = canvas.getContext('2d');
                ctx.drawImage(img, 0, 0);

                return canvas.toDataURL(oFile.type);
            },
            getImage: function getImage(file){
                var this$1 = this;

                return new Promise(function (resolve, reject){
                    var instFileReader = new FileReader();

                    var img = document.createElement('img');

                    instFileReader.onload = function () {
                        img.src = instFileReader.result;
                        img.onload = function () {
                            resolve(this$1.getBase64Img(img, file));
                        };
                    };

                    instFileReader.readAsDataURL(file);
                });
            },
            startUploading: function startUploading(instFormData){
                var this$1 = this;

                var images = instFormData.getAll(this.paramName);

                if ( !this.aUploadedImages.length && this.maximumImages != 0 && images.length > this.maximumImages ){
                    images = images.splice(0, this.maximumImages);
                }

                var promises = images.map(function (x){ return this$1.getImage(x).then(function (img){
                        return {
                            fileType: x.type,
                            fileSize: x.size,
                            originalName: x.name,
                            fileName: x.name,
                            src: img
                        }
                    }); });

                return Promise.all(promises);
            },
            uploadFieldListener: function uploadFieldListener(){
                var this$1 = this;

                if ( document.getElementById(this.uploadFieldID) === null ){
                    this.aUploadedImages = [];
                    return false;
                }

                document.getElementById(this.uploadFieldID).addEventListener('change', (function (event){
                    var aFileLists = event.target.files;

                    if ( !aFileLists.length ){
                        return false;
                    }
                    var instFormData = new FormData();

                    for ( var i = 0, totalFiles = aFileLists.length; i < totalFiles; i++ ){
                        instFormData.append(this$1.paramName, aFileLists[i], aFileLists[i].name);
                    }

                    this$1.startUploading(instFormData).then(function (x){
                        if ( this$1.isMultiple && this$1.hasUploadedImages() ){
                           this$1.aUploadedImages = Array.isArray(this$1.aUploadedImages) ? this$1.aUploadedImages.concat(x) : Object.assign({}, this$1.aUploadedImages, x);
                        }else{
                            this$1.aUploadedImages = x;
                        }

                        if ( this$1.maximumImages != 0 && this$1.aUploadedImages.length > this$1.maximumImages ){
                            this$1.aUploadedImages = this$1.aUploadedImages.splice(0, this$1.maximumImages);
                        }

                        if ( typeof this$1.field !== 'undefined' ){
                            this$1.field.value = this$1.aUploadedImages;
                        }

                        this$1.$emit('uploadImgChanged', this$1.aUploadedImages, this$1.settings);
                    });
                }));
            },
            listenUploadEvent: function listenUploadEvent(event){
                jQuery(event.currentTarget).closest('.field_wrap__Gv92k').find('.field_field__3U_Rt').trigger('click');
            },
            removeImg: function removeImg(order){
                this.aUploadedImages.splice(order, 1);
                this.$emit('uploadImgChanged', this.aUploadedImages, this.settings);
            }
        },
        mounted: function(){
            this.setupConfiguration();
            this.uploadFieldListener();
        }
    };

var WilokeCategory = {render: function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',[_c('div',{class:_vm.wrapperClass},[_c('div',{staticClass:"field_wrap__Gv92k"},[_c('select',{directives:[{name:"model",rawName:"v-model",value:(_vm.selectedCat),expression:"selectedCat"}],class:_vm.selectTwoClass,on:{"change":[function($event){var $$selectedVal = Array.prototype.filter.call($event.target.options,function(o){return o.selected}).map(function(o){var val = "_value" in o ? o._value : o.value;return val}); _vm.selectedCat=$event.target.multiple ? $$selectedVal : $$selectedVal[0];},_vm.fetchTags]}},_vm._l((_vm.options),function(option){return _c('option',{class:_vm.printOptionClass(option),domProps:{"value":_vm.printOptionValue(option),"innerHTML":_vm._s(option.name)}})})),_vm._v(" "),_c('span',{staticClass:"field_label__2eCP7 text-ellipsis",class:{required: _vm.settings.isRequired=='yes'}},[_vm._v(_vm._s(_vm.settings.label))]),_vm._v(" "),_c('span',{staticClass:"bg-color-primary"})]),_vm._v(" "),(_vm.settings.errMsg)?_c('span',{staticClass:"field_message__3Z6FX color-quaternary"},[_vm._v(_vm._s(_vm.settings.errMsg))]):_vm._e()]),_vm._v(" "),_c('div',{directives:[{name:"show",rawName:"v-show",value:(_vm.oTagsBelongTo),expression:"oTagsBelongTo"}],staticClass:"row"},_vm._l((_vm.oTagsBelongTo),function(oTag){return _c('div',{staticClass:"col-md-6 col-lg-4"},[_c('div',{staticClass:"checkbox_module__1K5IS mb-20"},[_c('label',{staticClass:"checkbox_label__3cO9k"},[_c('input',{directives:[{name:"model",rawName:"v-model",value:(_vm.aSelectedTags),expression:"aSelectedTags"}],staticClass:"checkbox_inputcheck__1_X9Z",attrs:{"type":"checkbox"},domProps:{"value":oTag.value,"checked":Array.isArray(_vm.aSelectedTags)?_vm._i(_vm.aSelectedTags,oTag.value)>-1:(_vm.aSelectedTags)},on:{"change":function($event){var $$a=_vm.aSelectedTags,$$el=$event.target,$$c=$$el.checked?(true):(false);if(Array.isArray($$a)){var $$v=oTag.value,$$i=_vm._i($$a,$$v);if($$el.checked){$$i<0&&(_vm.aSelectedTags=$$a.concat([$$v]));}else{$$i>-1&&(_vm.aSelectedTags=$$a.slice(0,$$i).concat($$a.slice($$i+1)));}}else{_vm.aSelectedTags=$$c;}}}}),_vm._v(" "),_vm._m(0,true),_vm._v(" "),_c('span',{staticClass:"checkbox_text__3Go1u text-ellipsis",domProps:{"innerHTML":_vm._s(oTag.name)}},[_c('span',{staticClass:"checkbox-border"})])])])])}))])},staticRenderFns: [function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('span',{staticClass:"checkbox_icon__28tFk bg-color-primary--checked-after bd-color-primary--checked"},[_c('i',{staticClass:"la la-check"}),_vm._v(" "),_c('span',{staticClass:"checkbox-iconBg"})])}],
    data: function data(){
        return {
            isTax: null,
            optionClass: null,
            oTagsBelongTo: {},
            isFetchingAjax: null,
            aSelectedTags: typeof this.settings.tags !== 'undefined' && this.settings.tags.length ? this.settings.tags : [],
            selectedCat: typeof this.settings.category !== 'undefined' ? this.settings.category : '',
            aTagsCache: []
        }
    },
    watch: {
        aSelectedTags: 'updateTags'
    },
    props: ['settings', 'target'],
    computed:{
        wrapperClass: function wrapperClass(){
            return this.selectedCat !== '' ? 'field_module__1H6kT field_style2__2Znhe mb-15 active' : 'field_module__1H6kT field_style2__2Znhe mb-15';
        },
        selectTwoClass: function selectTwoClass(){
            var createClass = 'wilcity-select2';
            if ( this.settings.isAjax && this.settings.isAjax == 'yes' ){
                createClass += ' is-ajax';
            }
            return createClass;
        },
        options: function options(){
            if ( this.settings.terms ){
                return this.settings.terms;
            }
            return this.settings.options;
        }
    },
    methods:{
        fetchTags: function fetchTags(){
            var this$1 = this;

            if ( typeof this.aTagsCache[this.selectedCat] !== 'undefined' ){
                this.oTagsBelongTo = this.aTagsCache[this.selectedCat];
                return true;
            }

            if ( this.isFetchingAjax !== null && this.isFetchingAjax.status !== 200 ){
                this.isFetchingAjax.abort();
            }

            this.isFetchingAjax = jQuery.ajax({
                type: 'POST',
                url: WILOKE_GLOBAL.ajaxurl,
                data: {
                    action: 'fetch_tags_of_listing_cat',
                    termID: this.selectedCat
                },
                success: (function (response){
                    if ( !response.success ){
                        this$1.oTagsBelongTo = {};
                        this$1.aTagsCache[this$1.selectedCat] = [];
                    }else{
                        this$1.aTagsCache[this$1.selectedCat] = response.data;
                        this$1.oTagsBelongTo = response.data;
                    }
                })
            });
        },
        updateValue: function updateValue(val){
            this.selectedCat = val;
            this.settings.value = val;
            this.settings.category = val;

            this.$emit('selectTwoChanged', this.selectedCat, this.settings);

            if ( !WilCityHelpers.isNull(this.selectedCat) ){
                this.fetchTags();
            }

        },
        updateTags: function updateTags(){
            this.settings.tags = this.aSelectedTags;
        },
        printOptionValue: function printOptionValue(option){
            return typeof option.value !== 'undefined' ? option.value : option;
        },
        printOptionName: function printOptionName(option){
            return typeof option.name !== 'undefined' ? option.name : option;
        },
        printOptionClass: function printOptionClass(option){
            if ( this.isTax ){
                if ( option.parent!==0 ){
                    return 'has-parent-term';
                }
            }
            return '';
        },
        maximumSelectionLength: function maximumSelectionLength(){
            if ( this.settings.maximum ){
                return this.settings.maximum;
            }

            return 10000;
        },
        selectTwo: function selectTwo(){
            var this$1 = this;

            var $select2 = jQuery(this.$el).find('.wilcity-select2');

            if ( $select2.hasClass('is-ajax') ){
                var taxonomy = this.target;
                $select2.select2({
                    ajax:{
                        url: WILOKE_GLOBAL.ajaxurl,
                        data: function (params) {
                          return {
                            search: params.term,
                            action: 'wilcity_select2_fetch_term',
                            taxonomy: taxonomy
                          };
                        },
                        processResults: function (data, params) {
                            if ( !data.success ){
                                return false;
                            }else{
                                return data.data.msg;
                            }
                        },
                        cache: true,
                    },
                    minimumInputLength: 1
                }).on('select2:open', (function (event){
                    //jQuery(event.currentTarget).closest('.field_module__1H6kT').addClass('active');
                })).on('select2:close', (function (event){
                    this$1.updateValue(jQuery(event.currentTarget).val());
                }));
            }else{
                $select2.select2({
                    templateResult: function(state){
                        if (!state.id) {
                            return state.text;
                        }

                        var treeItemClass = jQuery(state.element).hasClass('has-parent-term') ? 'has-parent-term' : '';

                        var $state = jQuery('<span class="'+treeItemClass+'">'+state.text+'</span>');
                        return $state;
                    },
                    templateSelection: function(repo){
                        return repo.text.replace('&amp;', '&');
                    },
                    maximumSelectionLength: this.maximumSelectionLength()
                }).on('select2:close', (function (event){
                    var $select2 = jQuery(event.currentTarget);
                    //$select2.closest('.field_module__1H6kT').addClass('active');
                    var val = $select2.val();

                    //if (WilCityHelpers.isNull(val)){
                      //$select2.closest('.field_module__1H6kT').removeClass('active');
                    //}else{
                        //$select2.closest('.field_module__1H6kT').find('.select2-selection__rendered').attr('style', '');
                    //}

                    this$1.updateValue(val);
                }));
            }
            //if (!WilCityHelpers.isNull(this.settings.category)){
                //$select2.closest('.field_module__1H6kT').addClass('active');
            //}

        }
    },
    mounted: function mounted(){
        this.selectTwo();
        if ( !WilCityHelpers.isNull(this.settings.category) ){
            this.fetchTags();
        }
    }
};

var WilokeInput = {render: function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{class:_vm.wrapperClass},[_c('div',{staticClass:"field_wrap__Gv92k"},[(_vm.isRequired(_vm.settings.isRequired))?_c('input',{directives:[{name:"model",rawName:"v-model",value:(_vm.value),expression:"value"}],staticClass:"field_field__3U_Rt",attrs:{"type":"text"},domProps:{"value":(_vm.value)},on:{"keyup":_vm.changed,"input":function($event){if($event.target.composing){ return; }_vm.value=$event.target.value;}}}):_c('input',{directives:[{name:"model",rawName:"v-model",value:(_vm.value),expression:"value"}],staticClass:"field_field__3U_Rt",attrs:{"type":"text"},domProps:{"value":(_vm.value)},on:{"keyup":_vm.changed,"input":function($event){if($event.target.composing){ return; }_vm.value=$event.target.value;}}}),_vm._v(" "),_c('span',{staticClass:"field_label__2eCP7 text-ellipsis",class:{'required': _vm.isRequired(_vm.settings.isRequired)}},[_vm._v(_vm._s(_vm.settings.label))]),_vm._v(" "),_c('span',{staticClass:"bg-color-primary"})]),_vm._v(" "),_c('span',{directives:[{name:"show",rawName:"v-show",value:(_vm.settings.errMsg!=''),expression:"settings.errMsg!=''"}],staticClass:"field_message__3Z6FX color-quaternary"},[_vm._v(_vm._s(_vm.settings.errMsg))])])},staticRenderFns: [],
    data: function data(){
        return {
          value: typeof this.settings.value !== 'undefined' ? this.settings.value : '',
          oPlanSettings: typeof this.$store !== 'undefined' && typeof this.$store.getters.getPlanSettings !== 'undefined' ? this.$store.getters.getPlanSettings : {}
        }
    },
    props: ['settings'],
    computed: {
        wrapperClass: function wrapperClass(){
            return {
                'field_module__1H6kT field_style2__2Znhe mb-15': 1==1,
                'active': this.value.length,
                'disable': typeof this.oPlanSettings['toggle_'+this.settings.key] !== 'undefined' && this.oPlanSettings['toggle_'+this.settings.key] == 'disable'
            }
        }
    },
    watch: {
        settings: {
            handler: function(){
                if ( this.value !== this.settings.value ){
                    this.value = this.settings.value;
                }
            },
            deep: true
        }
    },
    methods: {
        isRequired: function isRequired(pattern){
            if ( pattern == 'yes' || pattern === 'true' || pattern === true ){
                return true;
            }

            return false;
        },
        changed: function changed(){
            this.settings.value = this.value;
            this.$emit('inputChanged', this.value, this.settings);
        }
    }
};

var WilokeAutoComplete = {render: function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{class:_vm.parseWrapperClass},[_c('div',{staticClass:"field_wrap__Gv92k"},[(_vm.settings.isRequired)?_c('input',{directives:[{name:"model",rawName:"v-model",value:(_vm.place),expression:"place"}],staticClass:"field_field__3U_Rt",attrs:{"id":"wilcity-searchbox-field","type":"text","placeholder":_vm.placeholder},domProps:{"value":(_vm.place)},on:{"change":_vm.onChanged,"input":function($event){if($event.target.composing){ return; }_vm.place=$event.target.value;}}}):_c('input',{directives:[{name:"model",rawName:"v-model",value:(_vm.place),expression:"place"}],staticClass:"field_field__3U_Rt",attrs:{"id":"wilcity-searchbox-field","type":"text","placeholder":_vm.placeholder},domProps:{"value":(_vm.place)},on:{"change":_vm.onChanged,"input":function($event){if($event.target.composing){ return; }_vm.place=$event.target.value;}}}),_vm._v(" "),_c('span',{staticClass:"bg-color-primary"}),_vm._v(" "),(_vm.settings.askVisitorForLocation=='yes')?_c('div',{staticClass:"field_right__2qM90 pos-a-center-right"},[_vm._m(0)]):_vm._e()]),_vm._v(" "),_c('span',{directives:[{name:"show",rawName:"v-show",value:(_vm.settings.errMsg!=''),expression:"settings.errMsg!=''"}],staticClass:"field_message__3Z6FX color-quaternary"},[_vm._v(_vm._s(_vm.settings.errMsg))])])},staticRenderFns: [function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('span',{staticClass:"field_icon__1_sOi"},[_c('i',{staticClass:"la la-search"})])}],
        props: {
            settings: {
                type: Object,
                default: function (){
                    return {
                        value: '',
                        askVisitorForLocation: 'no',
                        isRequired: 'no',
                        placeholder: ''
                    }
                }
            },
            wrapperClass: {
                type: String,
                default: 'field_module__1H6kT field_style2__2Znhe mb-15'
            }
        },
        data: function data(){
            return {
                instSearchBox: {},
                instAutocomplete: {},
                oGeoCode: {},
                place: typeof this.settings.value != 'undefined' ? this.settings.value : '',
                placeholder: typeof this.settings.placeholder !== 'undefined' ? this.settings.placeholder : this.place,
                originalValue: this.settings.value
            }
        },
        computed: {
            parseWrapperClass: function parseWrapperClass(){
                if ( this.place.length ){
                    return this.wrapperClass + ' active';
                }

                return this.wrapperClass;
            }
        },
        methods: {
            lookingAddress: function lookingAddress(){
                var oPlaces = this.instSearchBox.getPlaces();

                if (oPlaces.length === 0) {
                    this.$emit('geocode-changed', '', this.settings);
					return false;
				}

                var oPlace = oPlaces.pop(),
                    aGeoCode = {
                        address: oPlace.formatted_address,
                        lat: oPlace.geometry.location.lat(),
                        lng: oPlace.geometry.location.lng()
                    };
                this.place = oPlace.formatted_address;
                this.$emit('geocode-changed', aGeoCode, this.settings);
            },
            initAutocomplete: function initAutocomplete(){
                var this$1 = this;

                new Promise(function (resolve, reject){
                    if ( typeof google !== 'undefined' ){
                        resolve('loaded');
                    }
                }).then(function (msg){
                    var target = document.getElementById('wilcity-searchbox-field');
                    if ( target ){
                        this$1.instSearchBox = new google.maps.places.SearchBox(target);
                        this$1.instSearchBox.addListener('places_changed', this$1.lookingAddress);
                    }
                });
            },
            resetValue: function resetValue(){
                var this$1 = this;

                this.$parent.$on('resetEverything', function (){
                    this$1.place = this$1.originalValue;
                });
            },
            onChanged: function onChanged(event){
                if ( event.target.value==='' ){
                    this.$emit('geocode-changed', '', this.settings);
                }
            }
        },
        mounted: function mounted(){
            this.resetValue();
            this.initAutocomplete();
        }
    };

var WilokeCheckbox = {render: function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{staticClass:"field_module__1H6kT field_style2__2Znhe mb-15"},[_c('div',{class:_vm.getClass},[_c('label',{staticClass:"checkbox_label__3cO9k"},[_c('input',{directives:[{name:"model",rawName:"v-model",value:(_vm.checked),expression:"checked"}],staticClass:"checkbox_inputcheck__1_X9Z",attrs:{"type":"checkbox","true-value":"yes","false-value":"no"},domProps:{"checked":Array.isArray(_vm.checked)?_vm._i(_vm.checked,null)>-1:_vm._q(_vm.checked,"yes")},on:{"change":[function($event){var $$a=_vm.checked,$$el=$event.target,$$c=$$el.checked?("yes"):("no");if(Array.isArray($$a)){var $$v=null,$$i=_vm._i($$a,$$v);if($$el.checked){$$i<0&&(_vm.checked=$$a.concat([$$v]));}else{$$i>-1&&(_vm.checked=$$a.slice(0,$$i).concat($$a.slice($$i+1)));}}else{_vm.checked=$$c;}},_vm.changed]}}),_vm._v(" "),_vm._m(0),_vm._v(" "),_c('span',{staticClass:"checkbox_text__3Go1u text-ellipsis"},[_vm._v(_vm._s(_vm.settings.label)+" "),_c('span',{staticClass:"checkbox-border"})])])])])},staticRenderFns: [function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('span',{staticClass:"checkbox_icon__28tFk bg-color-primary--checked-after bd-color-primary--checked"},[_c('i',{staticClass:"la la-check"}),_vm._v(" "),_c('span',{staticClass:"checkbox-iconBg"})])}],
    data: function data(){
        return {
            defaultWrapperClass: 'checkbox_module__1K5IS checkbox_toggle__vd6vd checkbox_full__jTSmg  mb-20',
            checked: this.settings.value
        }
    },
    props: {
        settings: {
            type: Object,
            default: {}
        },
        wrapperClass: ''
    },
    computed:{
        getClass: function getClass(){
            return typeof this.wrapperClass != 'undefined' ? this.wrapperClass : this.defaultWrapperClass;
        }
    },
    methods: {
        changed: function changed(){
            this.settings.value = this.checked;
            this.$emit('checkboxChanged', this.checked, this.settings);
        }
    }
};

var WilokeHeading = {render: function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{staticClass:"promo-item_group__2ZJhC"},[(_vm.title!=='')?_c('h3',{staticClass:"promo-item_title__3hfHG"},[_vm._v(_vm._s(_vm.title))]):_vm._e(),_vm._v(" "),(_vm.desc!=='')?_c('p',{staticClass:"promo-item_description__2nc26"},[_vm._v(_vm._s(_vm.desc))]):_vm._e()])},staticRenderFns: [],
    props: {
        title: {
            type: String,
            value: ''
        },
        desc: {
            type: String,
            value: ''
        }
    }
};

var WilokeCheckboxTwo = {render: function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{staticClass:"field_module__1H6kT field_style2__2Znhe mb-15 js-field",class:{error: _vm.isReachedMaximum}},[_c('div',{staticClass:"row"},[(_vm.settings.label)?_c('div',{staticClass:"col-md-12"},[_c('wiloke-heading',{attrs:{"title":_vm.settings.label,"desc":_vm.settings.desc}})],1):_vm._e(),_vm._v(" "),_vm._l((_vm.settings.options),function(oOption,index){return _c('div',{class:_vm.itemClass},[_c('div',{staticClass:"checkbox_module__1K5IS mb-20 js-checkbox"},[_c('label',{staticClass:"checkbox_label__3cO9k"},[(_vm.isUnChecked(oOption.value) && _vm.isReachedMaximum)?_c('input',{directives:[{name:"model",rawName:"v-model",value:(_vm.settings.value),expression:"settings.value"}],staticClass:"checkbox_inputcheck__1_X9Z",attrs:{"type":"checkbox","disabled":"disabled"},domProps:{"value":oOption.value,"checked":Array.isArray(_vm.settings.value)?_vm._i(_vm.settings.value,oOption.value)>-1:(_vm.settings.value)},on:{"change":[function($event){var $$a=_vm.settings.value,$$el=$event.target,$$c=$$el.checked?(true):(false);if(Array.isArray($$a)){var $$v=oOption.value,$$i=_vm._i($$a,$$v);if($$el.checked){$$i<0&&(_vm.settings.value=$$a.concat([$$v]));}else{$$i>-1&&(_vm.settings.value=$$a.slice(0,$$i).concat($$a.slice($$i+1)));}}else{_vm.$set(_vm.settings, "value", $$c);}},_vm.checkboxTwoChanged]}}):_c('input',{directives:[{name:"model",rawName:"v-model",value:(_vm.settings.value),expression:"settings.value"}],staticClass:"checkbox_inputcheck__1_X9Z",attrs:{"type":"checkbox"},domProps:{"value":oOption.value,"checked":Array.isArray(_vm.settings.value)?_vm._i(_vm.settings.value,oOption.value)>-1:(_vm.settings.value)},on:{"change":[function($event){var $$a=_vm.settings.value,$$el=$event.target,$$c=$$el.checked?(true):(false);if(Array.isArray($$a)){var $$v=oOption.value,$$i=_vm._i($$a,$$v);if($$el.checked){$$i<0&&(_vm.settings.value=$$a.concat([$$v]));}else{$$i>-1&&(_vm.settings.value=$$a.slice(0,$$i).concat($$a.slice($$i+1)));}}else{_vm.$set(_vm.settings, "value", $$c);}},_vm.changed]}}),_vm._v(" "),_vm._m(0,true),_vm._v(" "),_c('span',{staticClass:"checkbox_text__3Go1u text-ellipsis"},[_vm._v(_vm._s(oOption.label)+" "),_c('span',{staticClass:"checkbox-border"})])])])])})],2),_vm._v(" "),_c('span',{directives:[{name:"show",rawName:"v-show",value:(_vm.isReachedMaximum),expression:"isReachedMaximum"}],staticClass:"field_message__3Z6FX color-quaternary"},[_vm._v(_vm._s(_vm.settings.overMaximumTagsWarning))])])},staticRenderFns: [function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('span',{staticClass:"checkbox_icon__28tFk bg-color-primary--checked-after bd-color-primary--checked"},[_c('i',{staticClass:"la la-check"}),_vm._v(" "),_c('span',{staticClass:"checkbox-iconBg"})])}],
    data: function data(){
        return {
            isReachedMaximum: false
        }
    },
    components: {
        WilokeHeading: WilokeHeading
    },
    props: ['settings'],
    computed: {
        itemClass: function itemClass(){
            return this.settings.itemClass !== 'undefined' ? this.settings.itemClass : 'col-md-6 col-lg-4';
        }
    },
    watch: {
        settings: {
            handler: function handler(oNewVal){
                if ( oNewVal.value.length >= oNewVal.maximum ){
                    this.isReachedMaximum = true;
                }else{
                    this.isReachedMaximum = false;
                }

                this.$emit('checkboxTwoChanged', this.settings.value, this.settings);
            },
            deep: true
        }
    },
    methods:{
        isUnChecked: function isUnChecked(val){
            if ( this.settings.value.length && this.isReachedMaximum ){
                return this.settings.value.indexOf(val) == -1;
            }
            return false;
        },
        changed: function changed(){
            this.$emit('checkboxTwoChanged', this.settings.value, this.settings);
        }
    },
    beforeMount: function beforeMount(){
        if ( this.settings.value === '' || (this.settings.value === null) ){
            this.settings.value = [];
        }
    },
    mounted: function mounted(){
    }
};

var WilokeRadio = {render: function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{class:_vm.wrapperClass},[(_vm.settings.label)?_c('div',[_c('wiloke-heading',{attrs:{"title":_vm.settings.label,"desc":_vm.settings.desc}})],1):_vm._e(),_vm._v(" "),_vm._l((_vm.settings.options),function(oOption){return _c('div',[_c('div',{staticClass:"checkbox_module__1K5IS checkbox_radio__1pYzR mb-15 js-checkbox"},[_c('label',{staticClass:"checkbox_label__3cO9k"},[_c('input',{directives:[{name:"model",rawName:"v-model",value:(_vm.selected),expression:"selected"}],staticClass:"checkbox_inputcheck__1_X9Z",attrs:{"type":"radio"},domProps:{"value":_vm.printOptionValue(oOption),"checked":_vm._q(_vm.selected,_vm.printOptionValue(oOption))},on:{"change":[function($event){_vm.selected=_vm.printOptionValue(oOption);},_vm.changed]}}),_vm._v(" "),_vm._m(0,true),_vm._v(" "),_c('span',{staticClass:"checkbox_text__3Go1u text-ellipsis"},[_vm._v(_vm._s(_vm.printOptionName(oOption))+" "),_c('span',{staticClass:"checkbox-border"})])])])])})],2)},staticRenderFns: [function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('span',{staticClass:"checkbox_icon__28tFk bg-color-primary--checked-after bd-color-primary--checked"},[_c('i',{staticClass:"la la-check"}),_vm._v(" "),_c('span',{staticClass:"checkbox-iconBg"})])}],
    data: function data(){
        return {
            selected: this.settings.value
        }
    },
    props: ['settings', 'wrapperClass'],
    methods: {
        printOptionValue: function printOptionValue(oOption){
            return typeof oOption.value !== 'undefined' ? oOption.value : oOption;
        },
        printOptionName: function printOptionName(oOption){
            return typeof oOption.label !== 'undefined' ? oOption.label : oOption;
        },
        changed: function changed(){
            this.settings.value = this.selected;
            this.$emit('radioChanged', this.selected, this.settings);
        }
    },
    computed:{
        getGridClass: function getGridClass(){
            if ( typeof this.gridClass === 'undefined' ){
                return 'col-md-12';
            }

            return this.gridClass;
        }
    },
    components: {
        WilokeHeading: WilokeHeading
    },
    mounted: function mounted(){
    }
};

var WilokeTextarea = {render: function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{class:_vm.wrapperClass},[_c('div',{staticClass:"field_wrap__Gv92k"},[(_vm.settings.isRequired)?_c('textarea',{directives:[{name:"model",rawName:"v-model",value:(_vm.value),expression:"value"}],staticClass:"field_field__3U_Rt",domProps:{"value":(_vm.value)},on:{"keyup":_vm.changed,"input":function($event){if($event.target.composing){ return; }_vm.value=$event.target.value;}}}):_c('textarea',{directives:[{name:"model",rawName:"v-model",value:(_vm.value),expression:"value"}],staticClass:"field_field__3U_Rt",domProps:{"value":(_vm.value)},on:{"keyup":_vm.changed,"input":function($event){if($event.target.composing){ return; }_vm.value=$event.target.value;}}}),_vm._v(" "),_c('span',{staticClass:"field_label__2eCP7 text-ellipsis",class:{required: _vm.settings.isRequired}},[_vm._v(_vm._s(_vm.settings.label))]),_vm._v(" "),_c('span',{staticClass:"bg-color-primary"})]),_vm._v(" "),_c('span',{directives:[{name:"show",rawName:"v-show",value:(_vm.settings.errMsg!=''),expression:"settings.errMsg!=''"}],staticClass:"field_message__3Z6FX color-quaternary"},[_vm._v(_vm._s(_vm.settings.errMsg))])])},staticRenderFns: [],
    data: function data(){
        return {
          value: typeof this.settings.value !== 'undefined' ? this.settings.value : ''
        }
    },
    props: ['settings'],
    computed: {
        wrapperClass: function wrapperClass(){
            return {
                'field_module__1H6kT field_style2__2Znhe field-autoHeight mb-15': 1==1,
                'active': this.value.length
            }
        }
    },
    methods: {
        changed: function changed(){
            this.settings.value = this.value;
            this.$emit('textareaChanged', this.value, this.settings);
        }
    }
};

var WilokeTags = {render: function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{class:_vm.wrapperClass},[_c('div',{staticClass:"row"},_vm._l((_vm.settings.options),function(oTag,index){return _c('div',{staticClass:"col-md-4 col-lg-4 col-md-4-clear col-lg-4-clear"},[_c('div',{staticClass:"checkbox_module__1K5IS mb-20"},[_c('label',{staticClass:"checkbox_label__3cO9k"},[(_vm.isReachedMaximum)?_c('input',{directives:[{name:"model",rawName:"v-model",value:(_vm.aTags),expression:"aTags"}],staticClass:"checkbox_inputcheck__1_X9Z",attrs:{"type":"checkbox","disabled":"disabled"},domProps:{"value":oTag.value,"checked":Array.isArray(_vm.aTags)?_vm._i(_vm.aTags,oTag.value)>-1:(_vm.aTags)},on:{"change":function($event){var $$a=_vm.aTags,$$el=$event.target,$$c=$$el.checked?(true):(false);if(Array.isArray($$a)){var $$v=oTag.value,$$i=_vm._i($$a,$$v);if($$el.checked){$$i<0&&(_vm.aTags=$$a.concat([$$v]));}else{$$i>-1&&(_vm.aTags=$$a.slice(0,$$i).concat($$a.slice($$i+1)));}}else{_vm.aTags=$$c;}}}}):_c('input',{directives:[{name:"model",rawName:"v-model",value:(_vm.aTags),expression:"aTags"}],staticClass:"checkbox_inputcheck__1_X9Z",attrs:{"type":"checkbox"},domProps:{"value":oTag.value,"checked":Array.isArray(_vm.aTags)?_vm._i(_vm.aTags,oTag.value)>-1:(_vm.aTags)},on:{"change":function($event){var $$a=_vm.aTags,$$el=$event.target,$$c=$$el.checked?(true):(false);if(Array.isArray($$a)){var $$v=oTag.value,$$i=_vm._i($$a,$$v);if($$el.checked){$$i<0&&(_vm.aTags=$$a.concat([$$v]));}else{$$i>-1&&(_vm.aTags=$$a.slice(0,$$i).concat($$a.slice($$i+1)));}}else{_vm.aTags=$$c;}}}}),_vm._v(" "),_vm._m(0,true),_vm._v(" "),_c('span',{staticClass:"checkbox_text__3Go1u text-ellipsis",domProps:{"innerHTML":_vm._s(oTag.label)}},[_c('span',{staticClass:"checkbox-border"})])])])])})),_vm._v(" "),_c('span',{directives:[{name:"show",rawName:"v-show",value:(_vm.isReachedMaximum),expression:"isReachedMaximum"}],staticClass:"field_message__3Z6FX color-quaternary"},[_vm._v(_vm._s(_vm.settings.overMaximumTagsWarning))])])},staticRenderFns: [function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('span',{staticClass:"checkbox_icon__28tFk bg-color-primary--checked-after bd-color-primary--checked"},[_c('i',{staticClass:"la la-check"}),_vm._v(" "),_c('span',{staticClass:"checkbox-iconBg"})])}],
    data: function data(){
        return {
            isReachedMaximum: false,
            oPlanSettings: typeof this.$store !== 'undefined' && typeof this.$store.getters.getPlanSettings !== 'undefined' ? this.$store.getters.getPlanSettings : {},
            aTags: typeof this.settings.value === 'object' ? this.settings.value : [],
            maximumTags: typeof this.settings.maximum !== 'undefined' ? parseInt(this.settings.maximum, 10) : -1
        }
    },
    props: ['settings'],
    components:{
        WilokeCheckbox: WilokeCheckbox
    },
    computed: {
        wrapperClass: function wrapperClass(){
            return {
                'field_module__1H6kT field_style2__2Znhe mb-15': 1==1,
                'disable': typeof this.oPlanSettings['toggle_'+this.settings.key] !== 'undefined' && this.oPlanSettings['toggle_'+this.settings.key] == 'disable',
                'error': this.isReachedMaximum
            }
        }
    },
    watch: {
        aTags: {
            handler: function(){
                this.checkReachedMaximum();
            },
            immediate: true
        }
    },
    methods:{
        checkReachedMaximum: function checkReachedMaximum(){
            this.settings.value = this.aTags;
            if ( this.maximumTags == -1 ){
                return false;
            }
            this.isReachedMaximum = this.aTags.length >= this.maximumTags;
        }
    },
    mounted: function mounted(){
    }
};

var WilokeDateTime = {render: function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{staticClass:"field_module__1H6kT field_style2__2Znhe mb-15 js-field"},[_c('div',{staticClass:"field_wrap__Gv92k"},[(_vm.settings.isRequired)?_c('input',{directives:[{name:"model",rawName:"v-model",value:(_vm.settings.value),expression:"settings.value"}],staticClass:"field_field__3U_Rt",attrs:{"type":"datetime-local","required":""},domProps:{"value":(_vm.settings.value)},on:{"change":_vm.updatedDateTime,"input":function($event){if($event.target.composing){ return; }_vm.$set(_vm.settings, "value", $event.target.value);}}}):_c('input',{directives:[{name:"model",rawName:"v-model",value:(_vm.settings.value),expression:"settings.value"}],staticClass:"field_field__3U_Rt",attrs:{"type":"datetime-local"},domProps:{"value":(_vm.settings.value)},on:{"change":_vm.updatedDateTime,"input":function($event){if($event.target.composing){ return; }_vm.$set(_vm.settings, "value", $event.target.value);}}}),_vm._v(" "),_c('span',{staticClass:"field_label__2eCP7 text-ellipsis"},[_vm._v(_vm._s(_vm.settings.placeholder))]),_vm._v(" "),_c('span',{staticClass:"bg-color-primary"})]),_vm._v(" "),(_vm.settings.errMsg)?_c('span',{staticClass:"field_message__3Z6FX color-quaternary"},[_vm._v(_vm._s(_vm.settings.errMsg))]):_vm._e()])},staticRenderFns: [],
    props: ['settings'],
    methods:{
        updatedDateTime: function updatedDateTime(){
            this.$emit('dateTimeChanged', this.settings.value, this.settings);
        }
    }
};

var WilokeSelectTwo = {render: function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{class:_vm.parseWrapperClass},[_c('div',{staticClass:"field_wrap__Gv92k"},[(_vm.settings.isMultiple=='yes')?_c('select',{directives:[{name:"model",rawName:"v-model",value:(_vm.selected),expression:"selected"}],class:_vm.selectTwoClass,attrs:{"multiple":"multiple"},on:{"change":function($event){var $$selectedVal = Array.prototype.filter.call($event.target.options,function(o){return o.selected}).map(function(o){var val = "_value" in o ? o._value : o.value;return val}); _vm.selected=$event.target.multiple ? $$selectedVal : $$selectedVal[0];}}},_vm._l((_vm.aOptions),function(option){return _c('option',{class:_vm.printOptionClass(option),domProps:{"value":_vm.printOptionValue(option),"innerHTML":_vm._s(_vm.printOptionName(option))}})})):_c('select',{directives:[{name:"model",rawName:"v-model",value:(_vm.selected),expression:"selected"}],class:_vm.selectTwoClass,on:{"change":function($event){var $$selectedVal = Array.prototype.filter.call($event.target.options,function(o){return o.selected}).map(function(o){var val = "_value" in o ? o._value : o.value;return val}); _vm.selected=$event.target.multiple ? $$selectedVal : $$selectedVal[0];}}},_vm._l((_vm.aOptions),function(option){return _c('option',{class:_vm.printOptionClass(option),domProps:{"value":_vm.printOptionValue(option),"innerHTML":_vm._s(_vm.printOptionName(option))}})})),_vm._v(" "),_c('span',{staticClass:"field_label__2eCP7 text-ellipsis",class:{required: _vm.settings.isRequired=='yes'}},[_vm._v(_vm._s(_vm.settings.label))]),_vm._v(" "),_c('span',{staticClass:"bg-color-primary"})]),_vm._v(" "),(_vm.settings.errMsg)?_c('span',{staticClass:"field_message__3Z6FX color-quaternary"},[_vm._v(_vm._s(_vm.settings.errMsg))]):_vm._e()])},staticRenderFns: [],
    data: function data(){
        return {
            isTax: null,
            optionClass: null,
            selected: null,
            $select2: null
        }
    },
    components: {
        WilokeHeading: WilokeHeading
    },
    props: {
        'cId': {
            type: String,
            default: ''
        },
        'settings':{
            type: Object,
            default: {}
        },
        wrapperClass: {
            type: String,
            default: ''
        }
    },
    computed:{
        parseWrapperClass: function parseWrapperClass(){
            var wrapperClass = this.wrapperClass;

            if ( !wrapperClass.length ){
                wrapperClass = 'field_module__1H6kT field_style2__2Znhe mb-15';
            }
            return this.selected != null && this.selected.length ? wrapperClass + ' active' : wrapperClass;
        },
        selectTwoClass: function selectTwoClass(){
            var createClass = 'wilcity-select-2';
            if ( this.settings.isAjax && this.settings.isAjax == 'yes' ){
                createClass += ' is-ajax';
            }

            return createClass;
        },
        aOptions: function aOptions(){

            if ( typeof this.$store !== 'undefined' && typeof this.$store.state.aNewBusinessHoursOptions !== 'undefined' && this.$store.state.aNewBusinessHoursOptions.aHours.length && this.$store.state.aNewBusinessHoursOptions.cId == this.cId ){
                return this.$store.state.aNewBusinessHoursOptions.aHours;
            }else{
                if ( this.settings.terms ){
                    return typeof this.settings.terms == 'string' ? JSON.parse(this.settings.terms) : this.settings.terms;
                }else{
                    return typeof this.settings.options == 'string' ? JSON.parse(this.settings.options) : this.settings.options;
                }
            }
        }
    },
    watch: {
        //'settings': {
            //handler: function(oNewValue){
                //jQuery(this.$el).find('.wilcity-select-2').select2().val(oNewValue.value).trigger('change.select2');
            //},
            //deep: true
        //}
    },
    methods:{
        resetValue: function resetValue(){
            var this$1 = this;

            this.$parent.$on('resetEverything', function (){
                this$1.selected = this$1.settings.isMultiple == 'yes' ? [] : '';
                jQuery(this$1.$el).find('.wilcity-select-2').val(this$1.selected).trigger('change');
            });
        },
        setDefault: function setDefault(){
            if ( this.settings.isMultiple === 'yes' ){
                this.selected = this.settings.value.length ? this.settings.value : [];
            }else{
                this.selected = !WilCityHelpers.isNull(this.settings.value) ? this.settings.value : '';
            }
        },
        updateValue: function updateValue(val){
            if ( typeof val == 'undefined' ){
                return false;
            }

            if ( val === null ){
                val = this.settings.isMultiple == 'yes' ? [] : '';
            }

            if ( this.settings.isMultiple == 'yes' ){
                this.selected = val;
                this.settings.value = this.selected;
                this.$emit('selectTwoChanged', this.settings.value, this.settings);
            }else{
                if ( this.selected == val ){
                    return false;
                }
                this.selected = val;
                this.settings.value = this.selected;
                this.$emit('selectTwoChanged', this.settings.value, this.settings);
            }
        },
        printOptionValue: function printOptionValue(option){
            return typeof option.value !== 'undefined' ? option.value : option;
        },
        printOptionName: function printOptionName(option){
            return typeof option.name !== 'undefined' ? option.name : option;
        },
        printOptionClass: function printOptionClass(option){
            return typeof option.parent!== 'undefined' && option.parent !== 0 ? 'has-parent-term' : '';
        },
        maximumSelectionLength: function maximumSelectionLength(){
            if ( this.settings.maximum ){
                return this.settings.maximum;
            }

            return 10000;
        },
        selectTwo: function selectTwo(){
            var this$1 = this;

            this.$select2 = jQuery(this.$el).find('.wilcity-select-2');
            if ( this.$select2.hasClass('is-ajax') ){
                var oArgs = {
                    action: this.settings.ajaxAction
                };

                if ( typeof this.settings.ajaxArgs !== 'undefined' ){
                    oArgs = Object.assign({}, oArgs, this.settings.ajaxArgs);
                }

                this.$select2.select2({
                    ajax:{
                        url: WILOKE_GLOBAL.ajaxurl,
                        data: function (params) {
                            return Object.assign({}, {search: params.term}, oArgs);
                        },
                        processResults: function (data, params) {
                            if ( !data.success ){
                                return false;
                            }else{
                                return data.data.msg;
                            }
                        },
                        cache: true
                    },
                    minimumInputLength: 1
                }).on('select2:open', (function (event){
                    jQuery(event.currentTarget).closest('.field_module__1H6kT').addClass('active');
                })).on('change', (function (event){
                    this$1.updateValue(jQuery(event.currentTarget).val());
                }));
            }else{
                this.$select2.select2({
                    templateResult: function(state){
                        if (!state.id) {
                            return state.text;
                        }

                        var treeItemClass = jQuery(state.element).hasClass('has-parent-term') ? 'has-parent-term' : '';

                        var $state = jQuery('<span class="'+treeItemClass+'">'+state.text+'</span>');
                        return $state;
                    },
                    templateSelection: function(repo){
                        return repo.text.replace('&amp;', '&');
                    },
                    allowClear: true,
                    placeholder: '',
                    maximumSelectionLength: this.maximumSelectionLength()
                }).on('change', (function (event){
                    var $select2 = jQuery(event.currentTarget);
                    $select2.closest('.field_module__1H6kT').addClass('active');
                    var val = $select2.val();
                    if (WilCityHelpers.isNull(val)){
                        $select2.closest('.field_module__1H6kT').removeClass('active');
                    }else{
                        $select2.closest('.field_module__1H6kT').find('.select2-selection__rendered').attr('style', '');
                    }
                    console.log(val);
                    this$1.updateValue(val);
                }));
            }

            this.triggerDefault();
        },
        triggerDefault: function triggerDefault(){
            if (!WilCityHelpers.isNull(this.settings.value)){
                this.$select2.closest('.field_module__1H6kT').addClass('active');
            }
        },
        updateOptions: function updateOptions(){
            var this$1 = this;

            this.$parent.$on('onUpdateOptions', function (options){
                this$1.settings.options = options;
                this$1.triggerDefault();
            });
        }
    },
    mounted: function mounted(){
        this.selectTwo();
        this.resetValue();
        this.updateOptions();
    },
    beforeMount: function beforeMount(){
        this.setDefault();
    }
};

var WilokeEmail = {render: function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{class:_vm.wrapperClass},[_c('div',{staticClass:"field_wrap__Gv92k"},[(_vm.settings.isRequired)?_c('input',{directives:[{name:"model",rawName:"v-model",value:(_vm.value),expression:"value"}],staticClass:"field_field__3U_Rt",attrs:{"type":"email","required":""},domProps:{"value":(_vm.value)},on:{"input":function($event){if($event.target.composing){ return; }_vm.value=$event.target.value;}}}):_c('input',{directives:[{name:"model",rawName:"v-model",value:(_vm.value),expression:"value"}],staticClass:"field_field__3U_Rt",attrs:{"type":"email"},domProps:{"value":(_vm.value)},on:{"input":function($event){if($event.target.composing){ return; }_vm.value=$event.target.value;}}}),_vm._v(" "),_c('span',{staticClass:"field_label__2eCP7 text-ellipsis",class:{'required': _vm.settings.isRequired=='yes'}},[_vm._v(_vm._s(_vm.settings.label))]),_vm._v(" "),_c('span',{staticClass:"bg-color-primary"})]),_vm._v(" "),(_vm.settings.errMsg)?_c('span',{staticClass:"field_message__3Z6FX color-quaternary"},[_vm._v(_vm._s(_vm.settings.errMsg))]):_vm._e()])},staticRenderFns: [],
    data: function data(){
        return {
            oPlanSettings: typeof this.$store !== 'undefined' && typeof this.$store.getters.getPlanSettings !== 'undefined' ? this.$store.getters.getPlanSettings : {},
            value: typeof this.settings.value !== 'undefined' ? this.settings.value : ''
        }
    },
    props: ['settings'],
    computed: {
        wrapperClass: function wrapperClass(){
            return {
                'field_module__1H6kT field_style2__2Znhe mb-15': 1==1,
                'active': this.value.length,
                'disable': typeof this.oPlanSettings['toggle_'+this.settings.key] !== 'undefined' && this.oPlanSettings['toggle_'+this.settings.key] == 'disable'
            }
        }
    },
    watch: {
        value: 'updateValue'
    },
    methods:{
        updateValue: function updateValue(){
            this.settings.value = this.value;
        }
    }
};

var WilokeUrl = {render: function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{class:_vm.wrapperClass},[_c('div',{staticClass:"field_wrap__Gv92k"},[(_vm.settings.isRequired)?_c('input',{directives:[{name:"model",rawName:"v-model",value:(_vm.value),expression:"value"}],staticClass:"field_field__3U_Rt",attrs:{"type":"url","required":""},domProps:{"value":(_vm.value)},on:{"input":function($event){if($event.target.composing){ return; }_vm.value=$event.target.value;}}}):_c('input',{directives:[{name:"model",rawName:"v-model",value:(_vm.value),expression:"value"}],staticClass:"field_field__3U_Rt",attrs:{"type":"url"},domProps:{"value":(_vm.value)},on:{"input":function($event){if($event.target.composing){ return; }_vm.value=$event.target.value;}}}),_vm._v(" "),_c('span',{staticClass:"field_label__2eCP7 text-ellipsis",class:{'required': _vm.settings.isRequired=='yes'}},[_vm._v(_vm._s(_vm.settings.label))]),_vm._v(" "),_c('span',{staticClass:"bg-color-primary"})]),_vm._v(" "),(_vm.settings.errMsg)?_c('span',{staticClass:"field_message__3Z6FX color-quaternary"},[_vm._v(_vm._s(_vm.settings.errMsg))]):_vm._e()])},staticRenderFns: [],
    props: ['settings'],
    data: function data(){
        return {
          value: typeof this.settings.value !== 'undefined' ? this.settings.value : '',
          oPlanSettings: typeof this.$store !== 'undefined' && typeof this.$store.getters.getPlanSettings !== 'undefined' ? this.$store.getters.getPlanSettings : {}
        }
    },
    watch: {
        value: {
            handler: function(newVal){
                this.settings.value = newVal;
                this.$emit('urlChanged', this.value, this.settings);
            },
            immediate: true
        }
    },
    computed: {
        wrapperClass: function wrapperClass(){
            return {
                'field_module__1H6kT field_style2__2Znhe mb-15': 1==1,
                'active': this.value.length,
                'disable': typeof this.oPlanSettings['toggle_'+this.settings.key] !== 'undefined' && this.oPlanSettings['toggle_'+this.settings.key] == 'disable'
            }
        }
    }
};

var WilokeSocialNetworks = {render: function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{class:_vm.wrapperClass},[_c('div',{staticClass:"field-has-close"},[_c('div',[_vm._l((_vm.aUsedSocialNetworks),function(oSocialItem,index){return _c('div',{staticClass:"row"},[_c('div',{staticClass:"col-xs-6 col-sm-4"},[_c('div',{class:_vm.wrapperSocialItem(oSocialItem)},[_c('div',{staticClass:"field_wrap__Gv92k"},[_c('select',{directives:[{name:"model",rawName:"v-model",value:(oSocialItem.name),expression:"oSocialItem.name"}],staticClass:"wilcity-select-2",attrs:{"data-index":index},on:{"change":function($event){var $$selectedVal = Array.prototype.filter.call($event.target.options,function(o){return o.selected}).map(function(o){var val = "_value" in o ? o._value : o.value;return val}); _vm.$set(oSocialItem, "name", $event.target.multiple ? $$selectedVal : $$selectedVal[0]);}}},_vm._l((_vm.oAllSocialNetworks),function(social){return _c('option',{domProps:{"value":social}},[_vm._v(_vm._s(_vm.renderSocialName(social)))])})),_vm._v(" "),_c('span',{staticClass:"field_label__2eCP7 text-ellipsis"},[_vm._v(_vm._s(_vm.settings.socialNameLabel))]),_vm._v(" "),_c('span',{staticClass:"bg-color-primary"})])])]),_vm._v(" "),_c('div',{staticClass:"col-xs-6 col-sm-8"},[_c('div',{class:_vm.wrapperSocialUrlClass(oSocialItem)},[_c('div',{staticClass:"field_wrap__Gv92k"},[_c('input',{directives:[{name:"model",rawName:"v-model",value:(oSocialItem.url),expression:"oSocialItem.url"}],staticClass:"field_field__3U_Rt",attrs:{"type":"text"},domProps:{"value":(oSocialItem.url)},on:{"input":function($event){if($event.target.composing){ return; }_vm.$set(oSocialItem, "url", $event.target.value);}}}),_vm._v(" "),_c('span',{staticClass:"field_label__2eCP7 text-ellipsis"},[_vm._v(_vm._s(_vm.settings.socialLinkLabel))]),_vm._v(" "),_c('span',{staticClass:"bg-color-primary"})])])])])}),_vm._v(" "),(_vm.index != 0)?_c('a',{staticClass:"wil-btn mb-15 wil-btn--gray wil-btn--round wil-btn--xs wil-btn--icon",attrs:{"href":"#"},on:{"click":function($event){$event.preventDefault();_vm.deleteSocial($event);}}},[_c('i',{staticClass:"la la-close"})]):_vm._e()],2)]),_vm._v(" "),_c('a',{directives:[{name:"show",rawName:"v-show",value:(_vm.aAvailableSocialNetworksKeys.length>1),expression:"aAvailableSocialNetworksKeys.length>1"}],staticClass:"wil-btn mb-5 wil-btn--gray wil-btn--round wil-btn--xs",attrs:{"href":"#"},on:{"click":function($event){$event.preventDefault();_vm.addSocial($event);}}},[_c('i',{staticClass:"la la-plus"}),_vm._v(_vm._s(_vm.oTranslation.addSocial))])])},staticRenderFns: [],
     props: ['settings'],
     data: function data(){
        return {
            aTemporarySaveUsedSocialKeys: [],
            aTemporarySaveUsedSocialUrls: [],
            oAllSocialNetworks: Object.values(WILOKE_GLOBAL.oSocialNetworks),
            aUsedSocialNetworks: [],
            aUsedSocialNetworksKeys: [],
            aAvailableSocialNetworksKeys: [],
            isFirstTimeChanged: true,
            oPlanSettings: typeof this.$store !== 'undefined' && typeof this.$store.getters.getPlanSettings !== 'undefined' ? this.$store.getters.getPlanSettings : {},
            oTranslation: WILCITY_I18
        }
     },
     computed: {
        wrapperClass: function wrapperClass(){
            return {
                'disable': typeof this.oPlanSettings['toggle_'+this.settings.key] !== 'undefined' && this.oPlanSettings['toggle_'+this.settings.key] == 'disable'
            }
        }
     },
     watch: {
        aUsedSocialNetworks: {
            handler: function handler(){
                this.parseSocialKeys();
                this.updateValues();
                this.parseAvailableSocialNetworks();
                if ( !this.isFirstTimeChanged ){
                    this.$emit('socialNetworksChanged', this.aUsedSocialNetworks, this.settings);
                }
                this.isFirstTimeChanged = false;
            },
            deep: true
        }
     },
     methods:{
        wrapperSocialItem: function wrapperSocialItem(oSocial){
            return {
                'field_module__1H6kT field_style2__2Znhe mb-15': 1==1,
                'active': oSocial.name.length
            }
        },
        wrapperSocialUrlClass: function wrapperSocialUrlClass(oSocial){
            return {
                'field_module__1H6kT field_style2__2Znhe mb-15': 1==1,
                'active': oSocial.url.length
            }
        },
        updateValues: function updateValues(){
            this.settings.value = this.aUsedSocialNetworks;
        },
        updateSocialName: function updateSocialName($target){
            var index = $target.data('index');
            this.aUsedSocialNetworks[index].name = $target.val();
            this.updateValues();
        },
        deleteSocial: function deleteSocial(event){
            var $target = jQuery(event.currentTarget),
                index = $target.data('index');
            this.aUsedSocialNetworks.splice(index, 1);

            this.$nextTick(function(){
                this.runSelect2('refreshAll');
            });
        },
        addSocial: function addSocial(){
            this.aUsedSocialNetworks.push({
                name: this.aAvailableSocialNetworksKeys[0],
                url: ''
            });

            this.$nextTick(function(){
                this.runSelect2('add');
            });
        },
        renderSocialName: function renderSocialName(social){
            if ( social == 'google-plus' ){
                return 'Google+';
            }
            return social.charAt(0).toUpperCase() + social.slice(1);
        },
        parseSocialKeys: function parseSocialKeys(){
            this.aUsedSocialNetworksKeys = this.aUsedSocialNetworks.map(function (oSocial){
                return oSocial.name;
            });
        },
        parseAllSocialNetworks: function parseAllSocialNetworks(){
            var this$1 = this;

            if ( typeof this.settings.excludingSocialNetworks !== 'undefined' ){
                this.oAllSocialNetworks.forEach(function (social, index){
                    if ( this$1.settings.excludingSocialNetworks.indexOf(social) != -1 ){
                        this$1.oAllSocialNetworks.splice(index, 1);
                    }
                });
            }
        },
        parseAvailableSocialNetworks: function parseAvailableSocialNetworks(){
            var this$1 = this;

            if ( this.settings.excludingSocialNetworks == '' || !this.aUsedSocialNetworksKeys.length ){
                this.aAvailableSocialNetworksKeys = this.oAllSocialNetworks;
            }else{
                this.aAvailableSocialNetworksKeys = [];
                this.oAllSocialNetworks.forEach(function (social){
                    if ( !this$1.aUsedSocialNetworksKeys.length || (this$1.aUsedSocialNetworksKeys.indexOf(social) == -1) ){
                        this$1.aAvailableSocialNetworksKeys.push(social);
                    }
                });
            }
        },
        parseUsedSocialNetworks: function parseUsedSocialNetworks(){
            if ( typeof this.settings.value == 'object' && this.settings.value.length ){
                this.aUsedSocialNetworks = this.settings.value;

                this.aUsedSocialNetworksKeys = this.aUsedSocialNetworks.map(function (oSocial){
                    return oSocial.name;
                });
            }else{
                this.aUsedSocialNetworks = [{
                    name: this.oAllSocialNetworks[0],
                    url: ''
                }];
            }

            this.parseSocialKeys();
        },
        runSelect2: function runSelect2(method){
            var this$1 = this;

            var $select2 = jQuery(this.$el).find('.wilcity-select-2');

            switch(method){
                case 'add':
                    $select2.last().select2().on('select2:close', (function (event){
                        this$1.updateSocialName(jQuery(event.currentTarget));
                    }));
                break;
                case 'refreshAll':
                    $select2.each(function(event){
                        var $item = jQuery(event.currentTarget);
                        if ( $item.data('select2') !== 'undefined' ){
                            $item.select2('destroy');
                        }
                    });
                    $select2.select2().on('select2:close', (function (event){
                        this$1.updateSocialName(jQuery(event.currentTarget));
                    }));
                break;
                default:
                    $select2.select2().on('select2:close', (function (event){
                        this$1.updateSocialName(jQuery(event.currentTarget));
                    }));
                    break;
            }
        }
     },
     beforeMount: function beforeMount(){
        this.parseAllSocialNetworks();
        this.parseUsedSocialNetworks();
        this.parseAvailableSocialNetworks();
     },
     mounted: function mounted(){
        this.runSelect2();
     }
};

var WilokeVideo = {render: function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{class:_vm.wrapperClass},[_vm._l((_vm.aAddedVideos),function(aItem,index){return _c('div',{staticClass:"field-has-close"},[_c('div',{class:_vm.wrapperFieldItemClass(aItem.src)},[_c('div',{staticClass:"field_wrap__Gv92k"},[(_vm.settings.isRequired)?_c('div',{staticClass:"video-field"},[_c('input',{directives:[{name:"model",rawName:"v-model",value:(aItem.src),expression:"aItem.src"}],staticClass:"field_field__3U_Rt",attrs:{"type":"text","required":""},domProps:{"value":(aItem.src)},on:{"keyup":_vm.updatingVideoUrl,"input":function($event){if($event.target.composing){ return; }_vm.$set(aItem, "src", $event.target.value);}}}),_vm._v(" "),_c('input',{directives:[{name:"model",rawName:"v-model",value:(aItem.thumbnail),expression:"aItem.thumbnail"}],staticClass:"field_field__3U_Rt",attrs:{"type":"hidden","required":""},domProps:{"value":(aItem.thumbnail)},on:{"input":function($event){if($event.target.composing){ return; }_vm.$set(aItem, "thumbnail", $event.target.value);}}}),_vm._v(" "),_c('span',{staticClass:"field_label__2eCP7 text-ellipsis"},[_vm._v(_vm._s(_vm.settings.placeholder))]),_vm._v(" "),_c('span',{staticClass:"bg-color-primary"})]):_c('div',{staticClass:"video-field"},[_c('input',{directives:[{name:"model",rawName:"v-model",value:(aItem.src),expression:"aItem.src"}],staticClass:"field_field__3U_Rt",attrs:{"type":"text"},domProps:{"value":(aItem.src)},on:{"keyup":_vm.updatingVideoUrl,"input":function($event){if($event.target.composing){ return; }_vm.$set(aItem, "src", $event.target.value);}}}),_vm._v(" "),_c('input',{directives:[{name:"model",rawName:"v-model",value:(aItem.thumbnail),expression:"aItem.thumbnail"}],staticClass:"field_field__3U_Rt",attrs:{"type":"hidden"},domProps:{"value":(aItem.thumbnail)},on:{"input":function($event){if($event.target.composing){ return; }_vm.$set(aItem, "thumbnail", $event.target.value);}}}),_vm._v(" "),_c('span',{staticClass:"field_label__2eCP7 text-ellipsis"},[_vm._v(_vm._s(_vm.settings.placeholder))]),_vm._v(" "),_c('span',{staticClass:"bg-color-primary"})])])]),_vm._v(" "),(index != 0)?_c('a',{staticClass:"wil-btn mb-15 wil-btn--gray wil-btn--round wil-btn--xs wil-btn--icon",attrs:{"href":"#"},on:{"click":function($event){$event.preventDefault();_vm.deleteVideo(index);}}},[_c('i',{staticClass:"la la-close"})]):_vm._e()])}),_vm._v(" "),_c('a',{class:_vm.addMoreBtnClass,attrs:{"href":"#"},on:{"click":function($event){$event.preventDefault();_vm.addMore($event);}}},[_c('i',{staticClass:"la la-plus"}),_vm._v(_vm._s(_vm.settings.addMoreBtnName))]),_vm._v(" "),_c('p',{directives:[{name:"show",rawName:"v-show",value:(_vm.warning!=''),expression:"warning!=''"}],staticClass:"field_message__3Z6FX color-quaternary"},[_vm._v(_vm._s(_vm.warning))])],2)},staticRenderFns: [],
        data: function data(){
            return {
                maximumVideos: 0,
                oTranslation: WILCITY_I18,
                maximumVideos: null,
                oPlanSettings: typeof this.$store !== 'undefined' && typeof this.$store.getters.getPlanSettings !== 'undefined' ? this.$store.getters.getPlanSettings : {},
                aAddedVideos: typeof this.settings.value !== 'undefined' && this.settings.value.length ? this.settings.value : [{src: '', thumbnail: ''}],
                toggle: 'enable'
            }
        },
        props: ['settings'],
        computed: {
            wrapperClass: function wrapperClass(){
                return {
                    'disable': typeof this.oPlanSettings['toggle_'+this.settings.key] !== 'undefined' && this.oPlanSettings['toggle_'+this.settings.key] == 'disable'
                }
            },
            addMoreBtnClass: function addMoreBtnClass(){
                return {
                    'wil-btn input-upload mb-5 wil-btn--gray wil-btn--round wil-btn--xs': 1==1,
                    'disable': this.maximumVideos != 0 && this.aAddedVideos.length  >= this.maximumVideos
                }
            },
            warning: function warning(){
                if ( this.maximumVideos != 0 && !isNaN(this.maximumVideos) ){
                    return this.oTranslation.maximumVideosWarning.replace('%s', this.maximumVideos);
                }
                return '';
	        }
        },
        methods:{
            updatingVideoUrl: function updatingVideoUrl(){
                this.settings.value = this.aAddedVideos;
                this.$emit('videoChanged', this.aAddedVideos, this.settings);
            },
            wrapperFieldItemClass: function wrapperFieldItemClass(url){
                return url.length ? 'field_module__1H6kT field_style2__2Znhe mb-15 active' : 'field_module__1H6kT field_style2__2Znhe mb-15';
            },
            setupConfiguration: function setupConfiguration(){
                if ( this.oPlanSettings !== null ){
                    this.maximumVideos = parseInt(this.oPlanSettings.maximumVideos, 10);
                    this.toggle = this.oPlanSettings.toggle_video;
                }
            },
            checkValue: function checkValue(){
                if ( this.settings.value == '' ){
                    this.settings.value = [''];
                }

                this.aAddedVideos = this.settings.value;
                this.oPlanSettings = typeof this.settings.oPlanSettings !== 'undefined' ? this.settings.oPlanSettings : this.oPlanSettings;
            },
            deleteVideo: function deleteVideo(index){
                this.aAddedVideos.splice(index, 1);
                this.updatingVideoUrl();
            },
            addMore: function addMore(){
                if ( this.maximumVideos != 0 && this.aAddedVideos.length >= this.maximumVideos ){
                    return false;
                }

                this.aAddedVideos.push({src: '', thumbnail:''});
            },
            updateDefault: function updateDefault(aVideos){
                this.aAddedVideos = aVideos;
            }
        },
        mounted: function mounted(){
            this.setupConfiguration();
        }
    };

var WilokeBusinessHours = {render: function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{class:_vm.wrapperClass},[_c('wiloke-radio',{attrs:{"settings":{value:_vm.settings.value.hourMode, options: _vm.oHourOptions},"wrapper-class":'col-md-4 col-lg-4'},on:{"radioChanged":_vm.updateHourOption}}),_vm._v(" "),_c('div',{directives:[{name:"show",rawName:"v-show",value:(_vm.isShowForm),expression:"isShowForm"}],staticClass:"col-xs-12"},[_c('wiloke-select-two',{attrs:{"settings":{options: _vm.oTimeFormats, value: _vm.settings.value.timeFormat, label: _vm.settings.timeFormatLabel}}}),_vm._v(" "),_c('div',{staticClass:"list-hours_module__CE4hn js-list-hours"},[_c('table',{staticClass:"list-hours_table__1B4UJ"},[_c('tbody',_vm._l((_vm.oDaysOfWeeks),function(dayName,dayKey){return _c('tr',{staticClass:"list-hours_tr__1TA9B"},[_c('td',{staticClass:"list-hours_day__11G3l"},[_c('wiloke-checkbox',{attrs:{"settings":{label:dayName, value: _vm.settings.value.businessHours[dayKey].isOpen, dayKey: dayKey},"wrapperClass":'checkbox_module__1K5IS mt-25 js-checkbox'},on:{"checkboxChanged":_vm.updateOpenedDay}})],1),_vm._v(" "),_c('td',{staticClass:"list-hours_hours__3uYGr"},_vm._l((_vm.settings.value.businessHours[dayKey].operating_times),function(oOperatingTime,index){return _c('div',{staticClass:"list-hours_hoursItem__3kmyv"},[_c('div',{staticClass:"row"},[_c('div',{staticClass:"col-xs-6"},[_c('div',{staticClass:"field_module__1H6kT field_style2__2Znhe mb-15 js-field"},[_c('wiloke-select-two',{attrs:{"settings":{label: _vm.oTimeRangeLabel.from, options:_vm.aBusinessHoursOptions, value: _vm.settings.value.businessHours[dayKey].operating_times[index].from, dayKey:dayKey, index: index},"c-id":_vm.cId},on:{"selectTwoChanged":_vm.updateOpenFrom}})],1)]),_vm._v(" "),_c('div',{staticClass:"col-xs-6"},[_c('div',{staticClass:"field_module__1H6kT field_style2__2Znhe mb-15 js-field"},[_c('wiloke-select-two',{attrs:{"settings":{label: _vm.oTimeRangeLabel.to, options:_vm.aBusinessHoursOptions, value: _vm.settings.value.businessHours[dayKey].operating_times[index].to, dayKey:dayKey, index: index},"c-id":_vm.cId},on:{"selectTwoChanged":_vm.updateOpenTo}})],1)]),_vm._v(" "),(_vm.settings.value.businessHours[dayKey].operating_times.length==1)?_c('a',{staticClass:"list-hours_button__1MWV9",on:{"click":function($event){$event.preventDefault();_vm.addOperatingTime(dayKey);}}},[_c('i',{staticClass:"la la-plus"})]):_vm._e(),_vm._v(" "),(_vm.settings.value.businessHours[dayKey].operating_times.length==2 && index==1)?_c('a',{staticClass:"list-hours_button__1MWV9",on:{"click":function($event){$event.preventDefault();_vm.removeOperatingTime(dayKey, index);}}},[_c('i',{staticClass:"la la-close"})]):_vm._e()])])}))])}))])])],1)],1)},staticRenderFns: [],
    props: ['settings'],
    data: function data(){
        return {
            cId: 'wilcity-business-hour-settings',
            aTwelveFormat: WILCITY_ADDLISTING.oBusinessHours,
            aTwentyFourFormat: [],
            aBusinessHoursOptions: WILCITY_ADDLISTING.oBusinessHours,
            oDaysOfWeeks: WILCITY_ADDLISTING.oDayOfWeek,
            oTimeRangeLabel: WILCITY_ADDLISTING.oTimeRange,
            oTimeFormats: WILCITY_ADDLISTING.oTimeFormats,
            isShowForm: false,
            isChangedTimeFormat: false,
            isTwentyFormat: true,
            oPlanSettings: typeof this.$store !== 'undefined' && typeof this.$store.getters.getPlanSettings !== 'undefined' ? this.$store.getters.getPlanSettings : {}
        }
    },
    computed:{
        wrapperClass: function wrapperClass(){
            return {
                'row': 1==1,
                'disable': typeof this.oPlanSettings['toggle_'+this.settings.key] !== 'undefined' && this.oPlanSettings['toggle_'+this.settings.key] == 'disable'
            }
        },
        oHourOptions: function oHourOptions(){
            return [
                {
                    value: 'open_for_selected_hours',
                    label: this.settings.openForSelectedHoursLabel
                },
                {
                    value: 'always_open',
                    label: this.settings.alwaysOpenLabel
                },
                {
                    value: 'no_hours_available',
                    label: this.settings.noHoursAvailableLabel
                }
            ]
        }
    },
    methods:{
        updateTimeFormat: function updateTimeFormat(val){
            var this$1 = this;

            this.$set(this.settings.value, 'timeFormat', val);
            if ( val == 12 ){
                this.aBusinessHoursOptions = this.aTwelveFormat;
            }else{
                if ( this.aTwentyFourFormat.length ){
                    this.aBusinessHoursOptions = this.aTwentyFourFormat;
                }else{
                    for ( var index in this$1.aTwelveFormat ){
                        this$1.aTwentyFourFormat[index] = {};
                        this$1.aTwentyFourFormat[index].name = this$1.aTwelveFormat[index].value;
                        this$1.aTwentyFourFormat[index].value = this$1.aTwelveFormat[index].value;
                    }
                    this.aBusinessHoursOptions = this.aTwentyFourFormat;
                }
            }

            this.$store.commit('updateNewBusinessHours', {
                cId: this.cId,
                aHours: this.aBusinessHoursOptions
            });
        },
        updateHourOption: function updateHourOption(val){
            this.settings.value.hourMode = val;
            if ( val === 'open_for_selected_hours' ){
                this.isShowForm = true;
            }else{
                this.isShowForm = false;
            }
        },
        updateOpenedDay: function updateOpenedDay(val, oSettings){
            this.settings.value.businessHours[oSettings.dayKey].isOpen = val;
        },
        updateOpenFrom: function updateOpenFrom(val, oSettings){
            this.settings.value.businessHours[oSettings.dayKey].operating_times[oSettings.index]['from'] = val;
        },
        updateOpenTo: function updateOpenTo(val, oSettings){
            this.settings.value.businessHours[oSettings.dayKey].operating_times[oSettings.index]['to'] = val;
        },
        setDefault: function setDefault(){
            var oDefaultBusinessHours = {
                monday: this.defaultDaySetting(),
                tuesday: this.defaultDaySetting(),
                wednesday: this.defaultDaySetting(),
                thursday: this.defaultDaySetting(),
                friday: this.defaultDaySetting(),
                saturday: this.defaultDaySetting(),
                sunday: this.defaultDaySetting()
            };

            if (typeof this.settings.value.businessHours=='undefined'){
                this.settings.value = {};
                Vue.set(this.settings.value, 'businessHours', oDefaultBusinessHours);
            }else{
                this.settings.value.businessHours = Object.assign(oDefaultBusinessHours, this.settings.value.businessHours);
            }

            if ( typeof this.settings.value.timeFormat == 'undefined' ){
                Vue.set(this.settings.value, 'timeFormat', 12);
            }

            if ( typeof this.settings.value.hourMode == 'undefined' ){
                Vue.set(this.settings.value, 'hourMode', 'open_for_selected_hours');
            }

            if ( this.settings.value.hourMode == 'open_for_selected_hours' ){
                this.isShowForm = true;
            }
        },
        defaultDaySetting: function defaultDaySetting(){
            return {
                isOpen: 'no',
                operating_times: [
                    {
                        from: this.settings.stdOpeningTime,
                        to: this.settings.stdClosedTime
                    }
                ]
            };
        },
        addOperatingTime: function addOperatingTime(dayKey){
            if ( this.settings.value.businessHours[dayKey].operating_times.length > 1 ){
                return false;
            }

            this.settings.value.businessHours[dayKey].operating_times.push({
                from: '',
                to: ''
            });
        },
        removeOperatingTime: function removeOperatingTime(dayKey, index){
            this.settings.value.businessHours[dayKey].operating_times.splice(index, 1);
        }
    },
    components:{
        WilokeCheckbox: WilokeCheckbox,
        WilokeInput: WilokeInput,
        WilokeSelectTwo: WilokeSelectTwo,
        WilokeRadio: WilokeRadio
    },
    beforeMount: function beforeMount(){
        this.setDefault();
    }
};

var WilokePriceRange = {render: function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{class:_vm.wrapperClass},[_c('div',{staticClass:"row"},[_c('div',{staticClass:"col-md-12 col-lg-12"},[_c('wiloke-input',{attrs:{"settings":_vm.oPriceDesc},on:{"inputChanged":_vm.updatePriceRange}})],1),_vm._v(" "),_c('div',{staticClass:"col-md-4 col-lg-4"},[_c('wiloke-select-two',{attrs:{"settings":_vm.oPriceRange},on:{"selectTwoChanged":_vm.updatePriceRange}})],1),_vm._v(" "),_c('div',{staticClass:"col-md-4 col-lg-4"},[_c('wiloke-input',{attrs:{"settings":_vm.oMinimumPrice},on:{"inputChanged":_vm.updateMinimumPrice}})],1),_vm._v(" "),_c('div',{staticClass:"col-md-4 col-lg-4"},[_c('wiloke-input',{attrs:{"settings":_vm.oMaximumPrice},on:{"inputChanged":_vm.updateMaximumPrice}})],1)])])},staticRenderFns: [],
    props: ['settings'],
    data: function data(){
        return {
            oPlanSettings: typeof this.$store !== 'undefined' && typeof this.$store.getters.getPlanSettings !== 'undefined' ? this.$store.getters.getPlanSettings : {},
            oPriceRange: {
                value: this.settings.value.price_range,
                options: WILCITY_ADDLISTING.aPriceRange,
                label: this.settings.rangeLabel
            },
            oMinimumPrice: {
                value: this.settings.value.maximum_price,
                label: this.settings.minimumPriceLabel
            },
            oMaximumPrice: {
                value: this.settings.value.maximum_price,
                label: this.settings.maximumPriceLabel
            },
            oPriceDesc: {
                value: this.settings.value.price_desc,
                label: this.settings.priceDescLabel
            },
            oValues: typeof this.settings.value !== 'undefined' && this.settings.value !== '' ? this.settings.value: {
                price_range: '',
                price_range_desc: '',
                minimum_price: '',
                maximum_price: ''
            }
        }
    },
    components:{
        WilokeSelectTwo: WilokeSelectTwo,
        WilokeInput: WilokeInput
    },
    methods:{
        updatePriceRange: function updatePriceRange(val){
            this.oPriceDesc.value = val;
            this.settings.value.price_range_desc = val;
        },
        updatePriceRange: function updatePriceRange(val){
            this.oPriceRange.value = val;
            this.settings.value.price_range = val;
        },
        updateMinimumPrice: function updateMinimumPrice(val){
            this.oMinimumPrice.value = val;
            this.settings.value.minimum_price = val;
        },
        updateMaximumPrice: function updateMaximumPrice(val){
            this.oMaximumPrice.value = val;
            this.settings.value.maximum_price = val;
        },
        parsePriceDesc: function parsePriceDesc(){
            this.oPriceDesc.value = this.settings.value.price_desc;
            this.oPriceDesc.label = this.settings.priceDescLabel;
        }
    },
    computed:{
        wrapperClass: function wrapperClass(){
            return {
                'field_module__1H6kT field_style2__2Znhe mb-15': 1==1,
                'disable': typeof this.oPlanSettings['toggle_'+this.settings.key] !== 'undefined' && this.oPlanSettings['toggle_'+this.settings.key] == 'disable'
            }
        }
    }
};

var WilokeTime = {render: function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{staticClass:"field_module__1H6kT field_style2__2Znhe mb-15 js-field"},[_c('div',{staticClass:"field_wrap__Gv92k"},[(_vm.settings.isRequired)?_c('input',{directives:[{name:"model",rawName:"v-model",value:(_vm.settings.value),expression:"settings.value"}],staticClass:"field_field__3U_Rt",attrs:{"type":"time","required":""},domProps:{"value":(_vm.settings.value)},on:{"change":_vm.updatedTime,"input":function($event){if($event.target.composing){ return; }_vm.$set(_vm.settings, "value", $event.target.value);}}}):_c('input',{directives:[{name:"model",rawName:"v-model",value:(_vm.settings.value),expression:"settings.value"}],staticClass:"field_field__3U_Rt",attrs:{"type":"time"},domProps:{"value":(_vm.settings.value)},on:{"change":_vm.updatedTime,"input":function($event){if($event.target.composing){ return; }_vm.$set(_vm.settings, "value", $event.target.value);}}}),_vm._v(" "),_c('span',{staticClass:"field_label__2eCP7 text-ellipsis"},[_vm._v(_vm._s(_vm.settings.label))]),_c('span',{staticClass:"bg-color-primary"}),_vm._v(" "),_vm._m(0)]),_vm._v(" "),(_vm.settings.errMsg)?_c('span',{staticClass:"field_message__3Z6FX color-quaternary"},[_vm._v(_vm._s(_vm.settings.errMsg))]):_vm._e()])},staticRenderFns: [function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{staticClass:"field_right__2qM90 pos-a-center-right"},[_c('span',{staticClass:"field_icon__1_sOi"},[_c('i',{staticClass:"la la-clock-o"})])])}],
    props: {
        settings: {
            type: Object,
            value: '00:00:AM',
            isRequired: 'no'
        }
    },
    methods:{
        updatedTime: function updatedTime(){
            this.$emit('timeChanged', this.settings.value, this.settings);
        }
    }
};

var WilokeDatepicker = {render: function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{class:_vm.wrapperClass},[_c('div',{staticClass:"field_wrap__Gv92k"},[(_vm.settings.isRequired)?_c('input',{directives:[{name:"model",rawName:"v-model",value:(_vm.value),expression:"value"}],staticClass:"field_field__3U_Rt wilcity_datepicker",attrs:{"type":"text"},domProps:{"value":(_vm.value)},on:{"change":_vm.updatedDatepicker,"input":function($event){if($event.target.composing){ return; }_vm.value=$event.target.value;}}}):_c('input',{directives:[{name:"model",rawName:"v-model",value:(_vm.value),expression:"value"}],staticClass:"field_field__3U_Rt wilcity_datepicker",attrs:{"type":"text"},domProps:{"value":(_vm.value)},on:{"change":_vm.updatedDatepicker,"input":function($event){if($event.target.composing){ return; }_vm.value=$event.target.value;}}}),_vm._v(" "),_c('span',{staticClass:"field_label__2eCP7 text-ellipsis"},[_vm._v(_vm._s(_vm.settings.label))]),_c('span',{staticClass:"bg-color-primary"}),_vm._v(" "),_vm._m(0)]),_vm._v(" "),(_vm.settings.errMsg)?_c('span',{staticClass:"field_message__3Z6FX color-quaternary"},[_vm._v(_vm._s(_vm.settings.errMsg))]):_vm._e()])},staticRenderFns: [function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{staticClass:"field_right__2qM90 pos-a-center-right"},[_c('span',{staticClass:"field_icon__1_sOi"},[_c('i',{staticClass:"la la-calendar-check-o"})])])}],
    data: function data(){
        return {
            value: typeof this.settings.value !== 'undefined' ? this.settings.value : ''
        }
    },
    props: {
        settings: {
            type: Object,
            value: '',
            label: '',
            isRequired: 'no'
        },
        wrapperClass: {
            type: String,
            default: 'field_module__1H6kT field_style2__2Znhe mb-15'
        }
    },
    computed:{
        parseWrapperClass: function parseWrapperClass(){
            return this.selected != null && this.selected.length ? this.wrapperClass + ' active' : this.wrapperClass;
        },
    },
    methods: {
        updatedDatepicker: function updatedDatepicker(){
            this.settings.value = this.value;
            this.$emit('datepickerChanged', this.value, this.settings);
        },
        datepicker: function datepicker(){
            var self = this;
            jQuery(this.$el).find('.wilcity_datepicker').each(function(){
                jQuery(this).datepicker({
                    dateFormat: 'mm/dd/yy',
                    onSelect: function(dateText){
                        self.value = dateText;
                        self.$emit('input', self.value);
                        self.updatedDatepicker();
                    }
                });
            });
        }
    },
    mounted: function mounted(){
        var this$1 = this;

        this.$nextTick(function (){
            this$1.datepicker();
        });
    }
};

var WilokeDaily = {render: function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{staticClass:"bg-color-gray-3 bd-color-gray-1 pd-15 mb-20"},[_c('div',{staticClass:"row"},[_c('div',{staticClass:"col-md-2 col-lg-2"},[_c('div',{staticClass:"mt-25"},[_vm._v(_vm._s(_vm.oTranslation.starts))])]),_vm._v(" "),_c('div',{staticClass:"col-md-10 col-lg-10"},[_c('wiloke-datepicker',{attrs:{"settings":{value: _vm.oDailySettings.starts, isRequired: 'yes'}},on:{"datepickerChanged":_vm.startsChanged}})],1)]),_vm._v(" "),_c('div',{staticClass:"row"},[_c('div',{staticClass:"col-md-2 col-lg-2"},[_c('div',{staticClass:"mt-25"},[_vm._v(_vm._s(_vm.oTranslation.endson))])]),_vm._v(" "),_c('div',{staticClass:"col-md-10 col-lg-10"},[_c('wiloke-datepicker',{attrs:{"settings":{value: _vm.oDailySettings.endsOn, isRequired: 'yes'}},on:{"datepickerChanged":_vm.endsOnChanged}})],1)]),_vm._v(" "),_c('div',{staticClass:"row"},[_c('div',{staticClass:"col-md-2 col-lg-2"},[_c('div',{staticClass:"mt-25"},[_vm._v(_vm._s(_vm.oTranslation.time))])]),_vm._v(" "),_c('div',{staticClass:"col-md-5 col-lg-5"},[_c('wiloke-time',{attrs:{"settings":{value: _vm.oDailySettings.openingAt, isRequired: 'yes'}},on:{"timeChanged":_vm.openingAtChanged}})],1),_vm._v(" "),_c('div',{staticClass:"col-md-5 col-lg-5"},[_c('wiloke-time',{attrs:{"settings":{value: _vm.oDailySettings.closedAt}, isRequired: 'yes'},on:{"timeChanged":_vm.closedAtChanged}})],1)])])},staticRenderFns: [],
    data: function data(){
        return {
            oTranslation: WILCITY_I18,
            oDailySettings: {
                starts: '',
                endsOn: '',
                openingAt: '',
                closedAt: ''
            }
        }
    },
    props: ['oData'],
    watch: {
        'oData': {
            handler: function(oNewValue){
                this.oDailySettings = this.oData;
            },
            deep: true
        }
    },
    methods: {
        startsChanged: function startsChanged(newVal){
            this.$set(this.oDailySettings, 'starts', newVal);
            this.emitUpdate(newVal, 'starts');
        },
        endsOnChanged: function endsOnChanged(newVal){
            this.$set(this.oDailySettings, 'endsOn', newVal);
            this.emitUpdate(newVal, 'endsOn');
        },
        openingAtChanged: function openingAtChanged(newVal){
            this.$set(this.oDailySettings, 'openingAt', newVal);
            this.emitUpdate(newVal, 'openingAt');
        },
        closedAtChanged: function closedAtChanged(newVal){
            this.$set(this.oDailySettings, 'closedAt', newVal);
            this.emitUpdate(newVal, 'closedAt');
        },
        emitUpdate: function emitUpdate(newVal, key){
            this.$emit('dailyChanged', newVal, key);
        }
    },
    components:{
        WilokeTime: WilokeTime,
        WilokeDatepicker: WilokeDatepicker
    },
    mounted: function mounted(){
        this.oDailySettings = this.oData;
    }
};

var WilokeOccursOnce = {render: function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{staticClass:"bg-color-gray-3 bd-color-gray-1 pd-15 mb-20"},[_c('div',{staticClass:"row"},[_c('div',{staticClass:"col-md-2 col-lg-2"},[_c('div',{staticClass:"mt-25"},[_vm._v(_vm._s(_vm.oTranslation.starts))])]),_vm._v(" "),_c('div',{staticClass:"col-md-6 col-lg-6"},[_c('wiloke-datepicker',{attrs:{"settings":{value: _vm.oData.starts, isRequired: 'yes'}},on:{"datepickerChanged":_vm.startsChanged}})],1),_vm._v(" "),_c('div',{staticClass:"col-md-4 col-lg-4"},[_c('wiloke-time',{attrs:{"settings":{value: _vm.oData.openingAt, isRequired: 'yes'}},on:{"timeChanged":_vm.openingAtChanged}})],1)]),_vm._v(" "),_c('div',{staticClass:"row"},[_c('div',{staticClass:"col-md-2 col-lg-2"},[_c('div',{staticClass:"mt-25"},[_vm._v(_vm._s(_vm.oTranslation.endson))])]),_vm._v(" "),_c('div',{staticClass:"col-md-6 col-lg-6"},[_c('wiloke-datepicker',{attrs:{"settings":{value: _vm.oData.endsOn, isRequired: 'yes'}},on:{"datepickerChanged":_vm.endsonChanged}})],1),_vm._v(" "),_c('div',{staticClass:"col-md-4 col-lg-4"},[_c('wiloke-time',{attrs:{"settings":{value: _vm.oData.closedAt, isRequired: 'yes'}},on:{"timeChanged":_vm.closedAtChanged}})],1)])])},staticRenderFns: [],
    data: function data(){
        return {
            oTranslation: WILCITY_I18,
            oOccursOnce: {}
        }
    },
    props: ['oData'],
    methods: {
        startsChanged: function startsChanged(newVal){
            this.$set(this.oOccursOnce, 'starts', newVal);
            this.emitUpdate(newVal, 'starts');
        },
        openingAtChanged: function openingAtChanged(newVal){
            this.$set(this.oOccursOnce, 'openingAt', newVal);
            this.emitUpdate(newVal, 'openingAt');
        },
        endsonChanged: function endsonChanged(newVal){
            this.$set(this.oOccursOnce, 'endsOn', newVal);
            this.emitUpdate(newVal, 'endsOn');
        },
        closedAtChanged: function closedAtChanged(newVal){
            this.$set(this.oOccursOnce, 'closedAt', newVal);
            this.emitUpdate(newVal, 'closedAt');
        },
        emitUpdate: function emitUpdate(newVal, key){
            this.$emit('occursOnceChanged', newVal, key);
        }
    },
    components:{
        WilokeTime: WilokeTime,
        WilokeDatepicker: WilokeDatepicker
    },
    mounted: function mounted(){
        this.oOccursOnce = this.oData;
    }
};

var WilokeDaysChecked = {render: function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{staticClass:"field_module__1H6kT field_style2__2Znhe mb-15 js-field"},_vm._l((_vm.oDaysOfWeek),function(oDayOfWeek){return _c('div',{class:_vm.getClass},[_c('label',{staticClass:"checkbox_label__3cO9k"},[_c('input',{directives:[{name:"model",rawName:"v-model",value:(_vm.aDaysChecked),expression:"aDaysChecked"}],staticClass:"checkbox_inputcheck__1_X9Z",attrs:{"type":"checkbox"},domProps:{"value":oDayOfWeek.value,"checked":Array.isArray(_vm.aDaysChecked)?_vm._i(_vm.aDaysChecked,oDayOfWeek.value)>-1:(_vm.aDaysChecked)},on:{"change":[function($event){var $$a=_vm.aDaysChecked,$$el=$event.target,$$c=$$el.checked?(true):(false);if(Array.isArray($$a)){var $$v=oDayOfWeek.value,$$i=_vm._i($$a,$$v);if($$el.checked){$$i<0&&(_vm.aDaysChecked=$$a.concat([$$v]));}else{$$i>-1&&(_vm.aDaysChecked=$$a.slice(0,$$i).concat($$a.slice($$i+1)));}}else{_vm.aDaysChecked=$$c;}},_vm.changed]}}),_vm._v(" "),_vm._m(0,true),_vm._v(" "),_c('span',{staticClass:"checkbox_text__3Go1u text-ellipsis"},[_vm._v(_vm._s(oDayOfWeek.label)+" "),_c('span',{staticClass:"checkbox-border"})])])])}))},staticRenderFns: [function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('span',{staticClass:"checkbox_icon__28tFk bg-color-primary--checked-after bd-color-primary--checked"},[_c('i',{staticClass:"la la-check"}),_vm._v(" "),_c('span',{staticClass:"checkbox-iconBg"})])}],
    data: function data(){
        return {
            oDaysOfWeek: WILCITY_I18.oDaysOfWeek,
            aDaysChecked: [],
            defaultWrapperClass: 'checkbox_module__1K5IS checkbox_inline__6eS6J mb-20 js-checkbox'
        }
    },
    props: {
        wrapperClass: '',
        aChecked: {
            type: Array,
            default: []
        }
    },
    computed:{
        getClass: function getClass(){
            return typeof this.wrapperClass != 'undefined' ? this.wrapperClass : this.defaultWrapperClass;
        }
    },
    methods: {
        init: function init(){
            this.aDaysChecked = this.aChecked;
        },
        changed: function changed(){
            this.$emit('daysCheckedChanged', this.aDaysChecked, this.settings);
        }
    },
    mounted: function mounted(){
        this.init();
    }
};

var WilokeWeekly = {render: function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{staticClass:"bg-color-gray-3 bd-color-gray-1 pd-15 mb-20"},[_c('div',{staticClass:"row"},[_c('div',{staticClass:"col-md-2 col-lg-2"},[_c('div',{staticClass:"mt-5 mb-10"},[_vm._v(_vm._s(_vm.oTranslation.day))])]),_vm._v(" "),_c('div',{staticClass:"col-md-10 col-lg-10"},[_c('div',{staticClass:"row"},[_c('wiloke-radio',{attrs:{"settings":{value: _vm.checkedDay, options: _vm.oTranslation.oDaysOfWeek}},on:{"radioChanged":_vm.specifyDaysChecked}})],1)])]),_vm._v(" "),_c('div',{staticClass:"row"},[_c('div',{staticClass:"col-md-2 col-lg-2"},[_c('div',{staticClass:"mt-25"},[_vm._v(_vm._s(_vm.oTranslation.starts))])]),_vm._v(" "),_c('div',{staticClass:"col-md-10 col-lg-10"},[_c('wiloke-datepicker',{attrs:{"settings":{value: _vm.oWeekly.starts}},on:{"datepickerChanged":_vm.startsChanged}})],1)]),_vm._v(" "),_c('div',{staticClass:"row"},[_c('div',{staticClass:"col-md-2 col-lg-2"},[_c('div',{staticClass:"mt-25"},[_vm._v(_vm._s(_vm.oTranslation.endsOn))])]),_vm._v(" "),_c('div',{staticClass:"col-md-10 col-lg-10"},[_c('wiloke-datepicker',{attrs:{"settings":{value: _vm.oWeekly.endsOn}},on:{"datepickerChanged":_vm.endsonChanged}})],1)]),_vm._v(" "),_c('div',{staticClass:"row"},[_c('div',{staticClass:"col-md-2 col-lg-2"},[_c('div',{staticClass:"mt-25"},[_vm._v(_vm._s(_vm.oTranslation.time))])]),_vm._v(" "),_c('div',{staticClass:"col-md-5 col-lg-5"},[_c('wiloke-time',{attrs:{"settings":{value: _vm.oWeekly.openingAt}},on:{"timeChanged":_vm.openingAtChanged}})],1),_vm._v(" "),_c('div',{staticClass:"col-md-5 col-lg-5"},[_c('wiloke-time',{attrs:{"settings":{value: _vm.oWeekly.closedAt}},on:{"timeChanged":_vm.closedAtChanged}})],1)])])},staticRenderFns: [],
    data: function data(){
        return {
            oTranslation: WILCITY_I18,
            oWeekly: {
                starts: '',
                endsOn: '',
                specifyDays: [],
            },
            checkedDay: ''
        }
    },
    props:['oData'],
    methods: {
        specifyDaysChecked: function specifyDaysChecked(newVal){
            this.$set(this.oWeekly, 'specifyDays', newVal);
            this.emitUpdate(newVal, 'specifyDays');
        },
        startsChanged: function startsChanged(newVal){
            this.$set(this.oWeekly, 'starts', newVal);
            this.emitUpdate(newVal, 'starts');
        },
        endsonChanged: function endsonChanged(newVal){
            this.$set(this.oWeekly, 'endsOn', newVal);
            this.emitUpdate(newVal, 'endsOn');
        },
        openingAtChanged: function openingAtChanged(newVal){
            this.$set(this.oWeekly, 'openingAt', newVal);
            this.emitUpdate(newVal, 'openingAt');
        },
        closedAtChanged: function closedAtChanged(newVal){
            this.$set(this.oWeekly, 'closedAt', newVal);
            this.emitUpdate(newVal, 'closedAt');
        },
        emitUpdate: function emitUpdate(newVal, key){
            this.$emit('weeklyChanged', newVal, key);
        }
    },
    components:{
        WilokeDatepicker: WilokeDatepicker,
        WilokeTime: WilokeTime,
        WilokeDaysChecked: WilokeDaysChecked,
        WilokeRadio: WilokeRadio
    },
    mounted: function mounted(){
        this.oWeekly = this.oData;
    }
};

var WilokeEventCalendar = {render: function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',[_c('wiloke-select-two',{attrs:{"settings":{value: _vm.oSettings.frequency, isRequired:"yes", options: _vm.oTranslation.aEventFrequency, isMultiple: "no", label: _vm.oTranslation.frequency}},on:{"selectTwoChanged":_vm.frequencyMode}}),_vm._v(" "),_c('wiloke-occurs-once',{directives:[{name:"show",rawName:"v-show",value:(_vm.oSettings.frequency==='occurs_once'),expression:"oSettings.frequency==='occurs_once'"}],attrs:{"o-data":{starts: _vm.oSettings.starts, openingAt: _vm.oSettings.openingAt, endsOn: _vm.oSettings.endsOn, closedAt: _vm.oSettings.closedAt}},on:{"occursOnceChanged":_vm.updateSettings}}),_vm._v(" "),_c('wiloke-daily',{directives:[{name:"show",rawName:"v-show",value:(_vm.oSettings.frequency==='daily'),expression:"oSettings.frequency==='daily'"}],attrs:{"o-data":{starts: _vm.oSettings.starts, openingAt: _vm.oSettings.openingAt, endsOn: _vm.oSettings.endsOn, closedAt: _vm.oSettings.closedAt}},on:{"dailyChanged":_vm.updateSettings}}),_vm._v(" "),_c('wiloke-weekly',{directives:[{name:"show",rawName:"v-show",value:(_vm.oSettings.frequency==='weekly'),expression:"oSettings.frequency==='weekly'"}],attrs:{"o-data":{starts: _vm.oSettings.starts, openingAt: _vm.oSettings.openingAt, endson: _vm.oSettings.endsOn, closedAt: _vm.oSettings.closedAt, specifyDays: _vm.oSettings.specifyDays}},on:{"weeklyChanged":_vm.updateSettings}})],1)},staticRenderFns: [],
    data: function data(){
        return{
            oSettings:{
                frequency: 'occurs_once',
                starts: '',
                closedAt: '',
                openingAt: '',
                endsOn: '',
                specifyDays: [],
            },
            oTranslation: WILCITY_I18
        }
    },
    props: ['settings'],
    components:{
       WilokeDaily: WilokeDaily,
       WilokeOccursOnce: WilokeOccursOnce,
       WilokeWeekly: WilokeWeekly,
       WilokeSelectTwo: WilokeSelectTwo
    },
    methods: {
        frequencyMode: function frequencyMode(newVal){
            this.oSettings.frequency = newVal;
            this.settings.value = this.oSettings;
        },
        updateSettings: function updateSettings(newVal, key){
            this.oSettings[key] = newVal;
            this.settings.value = this.oSettings;
        }
    },
    beforeMount: function beforeMount(){
        if ( !WilCityHelpers.isNull(this.settings.value) ){
            this.oSettings = Object.assign({}, this.oSettings, this.settings.value);
        }
    }
};

var WilokeMap = {render: function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{class:_vm.wrapperClass},[_c('div',{staticClass:"field_wrap__Gv92k"},[(_vm.settings.isRequired)?_c('input',{directives:[{name:"model",rawName:"v-model",value:(_vm.address),expression:"address"}],staticClass:"field_field__3U_Rt",attrs:{"id":"wilcity-address-field","type":"text","required":""},domProps:{"value":(_vm.address)},on:{"keyup":_vm.findGeocode,"input":function($event){if($event.target.composing){ return; }_vm.address=$event.target.value;}}}):_c('input',{directives:[{name:"model",rawName:"v-model",value:(_vm.address),expression:"address"}],staticClass:"field_field__3U_Rt",attrs:{"id":"wilcity-address-field","type":"text"},domProps:{"value":(_vm.address)},on:{"keyup":_vm.findGeocode,"input":function($event){if($event.target.composing){ return; }_vm.address=$event.target.value;}}}),_vm._v(" "),_c('span',{staticClass:"field_label__2eCP7 text-ellipsis required"},[_vm._v(_vm._s(_vm.settings.label))]),_vm._v(" "),_c('span',{staticClass:"bg-color-primary"}),_vm._v(" "),_vm._m(0)]),_vm._v(" "),_c('div',{staticClass:"field_map__2UiNc",attrs:{"id":"wilcity-show-map"}})])},staticRenderFns: [function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{staticClass:"field_right__2qM90 pos-a-center-right"},[_c('span',{staticClass:"field_icon__1_sOi"},[_c('i',{staticClass:"la la-map-marker"})])])}],
    data: function data(){
        return {
            oLatLng: {
                lat: '',
                lng: ''
            },
            address: '',
            oMap: null,
            instMarker: null,
            instGeocode: null,
            defaultClass: 'field_module__1H6kT field_style2__2Znhe mb-15'
        }
    },
    computed: {
        wrapperClass: function wrapperClass(){
            return this.address.length ? this.defaultClass + ' active' : this.defaultClass;
        }
    },
    watch:{
        oLatLng: {
            handler: function handler(oNewVal){
                this.settings.value.latLng = oNewVal.lat + ',' + oNewVal.lng;
            },
            deep: true
        },
        address: function(newVal){
            this.settings.value.address = newVal;
        }
    },
    props: ['settings', 'isShowingMap'],
    methods:{
        decimalAdjust: function decimalAdjust(value, exp) {
            exp = typeof exp !== 'undefined' ? exp : -4;
            // If the exp is undefined or zero...
            if (typeof exp === 'undefined' || +exp === 0) {
                return Math.round(value);
            }
            value = +value;
            exp = +exp;
            // If the value is not a number or the exp is not an integer...
            if (isNaN(value) || !(typeof exp === 'number' && exp % 1 === 0)) {
                return NaN;
            }
            // Shift
            value = value.toString().split('e');
            value = Math.round(+(value[0] + 'e' + (value[1] ? (+value[1] - exp) : -exp)));
            // Shift back
            value = value.toString().split('e');
            return +(value[0] + 'e' + (value[1] ? (+value[1] + exp) : exp));
        },
        setDefault: function setDefault(){
            if ( this.settings.value == '' ){
                this.settings.value = {};
                this.settings.value.address = '';

                if ( typeof this.settings.defaultLocation != 'undefined' && this.settings.defaultLocation != '' ){
                    this.settings.value.latLng = this.settings.defaultLocation;
                }else{
                    this.settings.value.latLng = '21.027764,105.834160';
                }
            }

            var oParse = this.settings.value.latLng.split(',');
                this.oLatLng.lat = parseFloat(oParse[0].trim());
                this.oLatLng.lng = parseFloat(oParse[1].trim());

            this.address = this.settings.value.address;
            if ( typeof this.settings.defaultZoom == 'undefined' ){
                this.settings.defaultZoom = 8;
            }

            this.settings.defaultZoom = parseInt(this.settings.defaultZoom);
        },
        geocodePosition: function geocodePosition(pos) {
            var this$1 = this;

            this.instGeocode.geocode({
                latLng: pos
            }, function (responses){
                if (responses && responses.length > 0) {
                    if ( typeof responses[1] !== 'undefined' ){
                        this$1.address = responses[1].formatted_address;
                    }else{
                        this$1.address = responses[0].formatted_address;
                    }
                }
            });
        },
        setLatLng: function setLatLng(oLatLng){
            this.oLatLng = {
                lat: oLatLng.lat,
                lng: oLatLng.lng
            };
            this.findAddress();
        },
        initMap: function initMap(){
            var this$1 = this;

            new Promise(function (resolve, reject){
                if ( typeof google !== 'undefined' ){
                    resolve('loaded');
                }
            }).then(function (msg){
                this$1.oMap = new google.maps.Map(document.getElementById('wilcity-show-map'), {
                    zoom: this$1.settings.defaultZoom,
                    center: {
                        lat: this$1.oLatLng.lat,
                        lng: this$1.oLatLng.lng
                    }
                });

                this$1.instMarker = new google.maps.Marker({
                    map: this$1.oMap,
                    position: {
                        lat: this$1.oLatLng.lat,
                        lng: this$1.oLatLng.lng
                    },
                    draggable: true,
                    anchorPoint: new google.maps.Point(0, -29)
                });

                this$1.instGeocode = new google.maps.Geocoder;

                google.maps.event.addListener(this$1.instMarker, 'dragend', (function (oMarker){
                    var oLatLng = oMarker.latLng;

                    this$1.setLatLng({
                        lat: this$1.decimalAdjust(oLatLng.lat()),
                        lng: this$1.decimalAdjust(oLatLng.lng())
                    });

                    this$1.geocodePosition(this$1.oLatLng);
                }));

            });
        },
        findAddress: function findAddress(){
            this.address = document.getElementById('wilcity-address-field').value;
        },
        findGeocode: function findGeocode(){
            var this$1 = this;

            if ( this.oMap === null ){
                return false;
            }

            var $addressField = document.getElementById('wilcity-address-field'),
                instAutocomplete = new google.maps.places.Autocomplete($addressField);
                instAutocomplete.bindTo('bounds', this.oMap);
            instAutocomplete.addListener('place_changed', (function (){
                var oPlace = instAutocomplete.getPlace();

                if (!oPlace.geometry) {
                    return false;
                }

                if (oPlace.geometry.viewport) {
                    this$1.oMap.fitBounds(oPlace.geometry.viewport);
                }else{
                    this$1.oMap.setCenter(oPlace.geometry.location);
                    this$1.oMap.setZoom(17);  // Why 17? Because it looks good.
                }

                this$1.instMarker.setPosition(oPlace.geometry.location);
                this$1.instMarker.setVisible(true);

                this$1.setLatLng({
                    lat: this$1.decimalAdjust(oPlace.geometry.location.lat()),
                    lng: this$1.decimalAdjust(oPlace.geometry.location.lng())
                });
            }));
        }
    },
    beforeMount: function beforeMount(){
        this.setDefault();
    },
    mounted: function mounted(){
        this.initMap();
    }
};

var WilokeRangeDate = {render: function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{staticClass:"row"},[_c('div',{staticClass:"col-md-6"},[_c('date-picker',{attrs:{"settings":{value: _vm.from, label: _vm.fromLabel},"wrapper-class":"field_module__1H6kT field_style2__2Znhe"},model:{value:(_vm.from),callback:function ($$v) {_vm.from=$$v;},expression:"from"}})],1),_vm._v(" "),_c('div',{staticClass:"col-md-6"},[_c('date-picker',{attrs:{"settings":{value: _vm.to, label: _vm.toLabel},"wrapper-class":"field_module__1H6kT field_style2__2Znhe"},model:{value:(_vm.to),callback:function ($$v) {_vm.to=$$v;},expression:"to"}})],1)])},staticRenderFns: [],
    data: function data(){
        return {
            from: typeof this.value.from !== 'undefined' ? this.value.from :'',
            to: typeof this.value.to !== 'undefined' ? this.value.to :''
        }
    },
    props: ['fromLabel', 'toLabel', 'value'],
    components:{
        DatePicker: WilokeDatepicker
    },
    watch: {
        from: 'onChangedRange',
        to: 'onChangedRange'
    },
    methods: {
        onChangedRange: function onChangedRange(){
            this.$emit('onChangedRange', {
                from: this.from,
                to: this.to
            }, {key: 'date_range'});
        }
    }
};

var WpSearch = {render: function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{class:_vm.parseWrapperClass},[_c('div',{staticClass:"field_wrap__Gv92k"},[_c('input',{directives:[{name:"model",rawName:"v-model",value:(_vm.s),expression:"s"}],staticClass:"field_field__3U_Rt",attrs:{"type":"text"},domProps:{"value":(_vm.s)},on:{"keyup":_vm.changedValue,"input":function($event){if($event.target.composing){ return; }_vm.s=$event.target.value;}}}),_vm._v(" "),_c('span',{staticClass:"field_label__2eCP7 text-ellipsis"},[_vm._v(_vm._s(_vm.label))]),_vm._v(" "),_c('span',{staticClass:"bg-color-primary"}),_vm._v(" "),_vm._m(0)])])},staticRenderFns: [function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{staticClass:"field_right__2qM90 pos-a-center-right"},[_c('span',{staticClass:"field_icon__1_sOi"},[_c('i',{staticClass:"la la-search"})])])}],
    data: function data(){
        return {
            s: this.value,
            onSearchChanged: null
        }
    },
    props: {
        label: {
            default: '',
            type: String
        },
        value: {
            default: '',
            type: String
        },
        wrapperClass: {
            default: 'field_module__1H6kT field_style2__2Znhe mb-15 select-text',
            type: String
        }
    },
    computed: {
        parseWrapperClass: function parseWrapperClass(){
            if ( this.s.length ){
                return this.wrapperClass + ' active';
            }
            return this.wrapperClass;
        }
    },
    mounted: function mounted(){
        this.resetValue();
    },
    methods: {
        resetValue: function resetValue(){
            var this$1 = this;

            this.$parent.$on('resetEverything', function (){ return this$1.s = ''; });
        },
        changedValue: function changedValue(){
            var this$1 = this;

            if ( this.onSearchChanged !== null ){
                clearTimeout(this.onSearchChanged);
            }

            this.onSearchChanged = setTimeout(function (){
                this$1.$emit('changedValue', this$1.s, {key: 'wp_search'});
                clearTimeout(this$1.onSearchChanged);
            }, 1000);
        }
    }
};

var WilokeCheckboxThree = {render: function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{staticClass:"checkbox_module__1K5IS checkbox_full__jTSmg mb-15 js-checkbox"},[_c('label',{staticClass:"checkbox_label__3cO9k"},[_c('input',{directives:[{name:"model",rawName:"v-model",value:(_vm.status),expression:"status"}],staticClass:"checkbox_inputcheck__1_X9Z",attrs:{"type":"checkbox","true-value":"yes","false-value":"no"},domProps:{"checked":Array.isArray(_vm.status)?_vm._i(_vm.status,null)>-1:_vm._q(_vm.status,"yes")},on:{"change":[function($event){var $$a=_vm.status,$$el=$event.target,$$c=$$el.checked?("yes"):("no");if(Array.isArray($$a)){var $$v=null,$$i=_vm._i($$a,$$v);if($$el.checked){$$i<0&&(_vm.status=$$a.concat([$$v]));}else{$$i>-1&&(_vm.status=$$a.slice(0,$$i).concat($$a.slice($$i+1)));}}else{_vm.status=$$c;}},_vm.changed]}}),_vm._v(" "),_vm._m(0),_vm._v(" "),_c('span',{staticClass:"checkbox_text__3Go1u text-ellipsis"},[_vm._v(_vm._s(_vm.settings.label)+" "),_c('span',{staticClass:"checkbox-border"})])])])},staticRenderFns: [function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('span',{staticClass:"checkbox_icon__28tFk bg-color-primary--checked-after bd-color-primary--checked"},[_c('i',{staticClass:"la la-check"}),_vm._v(" "),_c('span',{staticClass:"checkbox-iconBg"})])}],
    data: function data(){
        return {
             originalVal: this.settings.value,
             status: this.settings.value
        }
    },
    props: {
        settings: {
            type: Object,
            default: {}
        },
        wrapperClass: ''
    },
    methods: {
        changed: function changed(){
            this.settings.value = this.status;
            this.$emit('checkboxChanged', this.status, this.settings);
        },
        resetValue: function resetValue(){
            var this$1 = this;

            this.$parent.$on('resetEverything', function (){
                this$1.status = this$1.originalVal;
            });
        }
    },
    mounted: function mounted(){
        this.resetValue();
    }
};

var WilokeSlider = {render: function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{staticClass:"field_module__1H6kT field_style2__2Znhe mb-15 js-field"},[_c('div',{staticClass:"field_wrap__Gv92k"},[_c('div',{staticClass:"js-slider-info",attrs:{"data-active":_vm.infoStatus}},[_c('span',{staticClass:"js-slider-info__number"},[_vm._v(_vm._s(_vm.value))]),_vm._v(" "),(_vm.settings.unit)?_c('span',{staticClass:"js-slider-info__unit"},[_vm._v(" "+_vm._s(_vm.settings.unit))]):_vm._e()]),_vm._v(" "),_c('div',{staticClass:"js-slider"}),_vm._v(" "),_c('span',{staticClass:"field_label__2eCP7 text-ellipsis"},[_vm._v(_vm._s(_vm.settings.label))]),_vm._v(" "),_c('span',{staticClass:"bg-color-primary"})])])},staticRenderFns: [],
    data: function data(){
        return {
            originalVal: this.settings.value,
            value: this.settings.value,
            $slider: null
        }
    },
    props: ['settings', 'wrapperClass'],
    mounted: function mounted(){
        this.slider();
        this.resetValue();
    },
    computed: {
        infoStatus: function infoStatus(){
           return this.value > 0 ? 'true' : '';
        }
    },
    methods: {
        resetValue: function resetValue(){
            var this$1 = this;

            this.$parent.$on('resetEverything', function (){
                this$1.value = this$1.originalVal;
                this$1.$slider.slider('value', this$1.originalVal);
            });
        },
        slider: function slider(){
            var this$1 = this;

            var self = jQuery(this.$el),
                info = self.find('.js-slider-info');
                this.$slider = self.find('.js-slider');
            this.$slider.slider({
                range: 'min',
                min: 0,
                max: this.settings.maximum,
                value: this.value,
                slide: function (event, ui) {
                    this$1.value = ui.value;
                    this$1.$slider.attr('data-slider-value', this$1.value);
                },
                stop: function (event, ui){
                    this$1.$emit('sliderChanged', ui.value);
                }
            });
        }
    }
};

var GooglePlace = {render: function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',[_c('wiloke-auto-complete',{attrs:{"settings":{placeholder: _vm.oField.label, askVisitorForLocation:'yes', value: _vm.value}},on:{"geocode-changed":_vm.onAddressChanged}}),_vm._v(" "),_c('wiloke-slider',{directives:[{name:"show",rawName:"v-show",value:(_vm.oAddress.address.length || _vm.defaultAddress.length),expression:"oAddress.address.length || defaultAddress.length"}],attrs:{"settings":{label: _vm.oField.radiusLabel, maximum: _vm.oField.maxRadius, value: _vm.oField.defaultRadius, unit: _vm.unit}},on:{"sliderChanged":_vm.radiusChanged}})],1)},staticRenderFns: [],
    data: function data(){
        return {
            oAddress: this.oField,
            defaultAddress: this.value,
            unit: typeof this.oField.unit !== 'undefined' ? this.oField.unit : 'km',
            radius: typeof this.oField.defaultRadius !== 'undefined' ? this.oField.defaultRadius : 0,
            originalAddress: this.value
        }
    },
    props: ['oField', 'value'],
    components:{
        WilokeAutoComplete: WilokeAutoComplete,
        WilokeSlider: WilokeSlider
    },
    methods: {
        resetValue: function resetValue(){
            var this$1 = this;

            this.$parent.$on('resetEverything', function (){
                this$1.oAddress.address = this$1.originalAddress;
                this$1.oAddress.defaultRadius = this$1.oField.defaultRadius;
                this$1.$emit('resetEverything', true);
            });
        },
        onAddressChanged: function onAddressChanged(oNewVal){
            if ( oNewVal == '' ){
                this.$set(this.oAddress, 'address', '');
                this.$set(this.oAddress, 'lat', '');
                this.$set(this.oAddress, 'lng', '');
                this.$emit('addressChanged', '');
            }else{
                this.$set(this.$data, 'oAddress', oNewVal);
                this.$emit('addressChanged', {
                    address: this.oAddress.address,
                    lat: oNewVal.lat,
                    lng: oNewVal.lng,
                    radius: this.radius,
                    unit: this.unit
                });
            }
            this.defaultAddress = '';
        },
        radiusChanged: function radiusChanged(newVal){
            this.radius = newVal;
            this.$emit('addressChanged', {
                address: this.oAddress.address,
                lat: this.oAddress.lat,
                lng: this.oAddress.lng,
                radius: this.radius,
                unit: this.unit
            });
            this.defaultAddress = '';
        }
    },
    mounted: function mounted(){
        this.resetValue();
    }
};

var WilokeTags$1 = {render: function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',[_c('div',{staticClass:"checkbox_module__1K5IS checkbox_full__jTSmg checkbox_toggle__vd6vd mb-20 js-checkbox"},[_c('label',{staticClass:"checkbox_label__3cO9k"},[_c('input',{directives:[{name:"model",rawName:"v-model",value:(_vm.isFilterByTag),expression:"isFilterByTag"}],staticClass:"checkbox_inputcheck__1_X9Z",attrs:{"type":"checkbox","true-value":"yes","false-value":"no"},domProps:{"checked":Array.isArray(_vm.isFilterByTag)?_vm._i(_vm.isFilterByTag,null)>-1:_vm._q(_vm.isFilterByTag,"yes")},on:{"change":[function($event){var $$a=_vm.isFilterByTag,$$el=$event.target,$$c=$$el.checked?("yes"):("no");if(Array.isArray($$a)){var $$v=null,$$i=_vm._i($$a,$$v);if($$el.checked){$$i<0&&(_vm.isFilterByTag=$$a.concat([$$v]));}else{$$i>-1&&(_vm.isFilterByTag=$$a.slice(0,$$i).concat($$a.slice($$i+1)));}}else{_vm.isFilterByTag=$$c;}},_vm.tagChanged]}}),_vm._v(" "),_vm._m(0),_vm._v(" "),_c('span',{staticClass:"checkbox_text__3Go1u text-ellipsis"},[_vm._v(_vm._s(_vm.oTranslation.filterByTags)+" "),_c('span',{staticClass:"checkbox-border"})])])]),_vm._v(" "),_c('div',{directives:[{name:"show",rawName:"v-show",value:(_vm.isFilterByTag=='yes'),expression:"isFilterByTag=='yes'"}],staticClass:"wil-visible wil-scroll-bar"},_vm._l((_vm.settings.options),function(oTag){return _c('div',[_c('div',{staticClass:"checkbox_module__1K5IS mb-20 js-checkbox"},[_c('label',{staticClass:"checkbox_label__3cO9k"},[_c('input',{directives:[{name:"model",rawName:"v-model",value:(_vm.aTags),expression:"aTags"}],staticClass:"checkbox_inputcheck__1_X9Z",attrs:{"type":"checkbox"},domProps:{"value":oTag.value,"checked":Array.isArray(_vm.aTags)?_vm._i(_vm.aTags,oTag.value)>-1:(_vm.aTags)},on:{"change":[function($event){var $$a=_vm.aTags,$$el=$event.target,$$c=$$el.checked?(true):(false);if(Array.isArray($$a)){var $$v=oTag.value,$$i=_vm._i($$a,$$v);if($$el.checked){$$i<0&&(_vm.aTags=$$a.concat([$$v]));}else{$$i>-1&&(_vm.aTags=$$a.slice(0,$$i).concat($$a.slice($$i+1)));}}else{_vm.aTags=$$c;}},_vm.tagChanged]}}),_vm._v(" "),_vm._m(1,true),_vm._v(" "),_c('span',{staticClass:"checkbox_text__3Go1u text-ellipsis"},[_vm._v(_vm._s(oTag.name)+" "),_c('span',{staticClass:"checkbox-border"})])])])])}))])},staticRenderFns: [function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('span',{staticClass:"checkbox_icon__28tFk bg-color-primary--checked-after bd-color-primary--checked"},[_c('i',{staticClass:"la la-check"}),_vm._v(" "),_c('span',{staticClass:"checkbox-iconBg"})])},function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('span',{staticClass:"checkbox_icon__28tFk bg-color-primary--checked-after bd-color-primary--checked"},[_c('i',{staticClass:"la la-check"}),_vm._v(" "),_c('span',{staticClass:"checkbox-iconBg"})])}],
    data: function data(){
        return {
            aTags: [],
            isFilterByTag: this.isExpandTag,
            oTranslation: WILCITY_I18
        }
    },
    props: ['isExpandTag', 'settings'],
    components:{
        WilokeCheckboxTwo: WilokeCheckboxTwo
    },
    mounted: function mounted(){
        this.resetValue();
        this.scrollBar();
    },
    methods: {
        resetValue: function resetValue(){
            var this$1 = this;

            this.$parent.$on('resetEverything', function (){ return this$1.aTags = []; });
        },
        tagChanged: function tagChanged(){
            if ( this.isFilterByTag == 'no' ){
                this.$emit('onTagChanged', []);
            }else{
                this.$emit('onTagChanged', this.aTags);
            }
        },
        scrollBar: function scrollBar() {
            var this$1 = this;

            this.$nextTick( function (){
                jQuery(this$1.$el).find(".wil-scroll-bar").wrapInner('<div class="wil-scroll-container"></div>');
                var scrollBar = this$1.$el.querySelector(".wil-scroll-bar");
                    new PerfectScrollbar(scrollBar);
            });
        }
    }
};

var SearchForm = {render: function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{class:_vm.wrapperClass,staticStyle:{"min-height":"150px"}},[_c('div',{class:_vm.innerClass},[_c('wiloke-message',{directives:[{name:"show",rawName:"v-show",value:(_vm.errorMsg!==''),expression:"errorMsg!==''"}],attrs:{"msg":_vm.errorMsg,"icon":"la la-frown-o"}}),_vm._v(" "),_c('block-loading',{attrs:{"position":"pos-a-center","is-loading":_vm.isLoading}}),_vm._v(" "),_c('div',{directives:[{name:"show",rawName:"v-show",value:(_vm.aSearchFields.length),expression:"aSearchFields.length"}],class:_vm.wrapperFieldsClass},_vm._l((_vm.aSearchFields),function(oField){return _c('div',{class:_vm.wrapperFieldClass(oField)},[(oField.type=='wp_search')?_c('wp-search',{attrs:{"label":oField.label,"value":_vm.searchKeyDefault},on:{"changedValue":_vm.onSearchChanged}}):_vm._e(),_vm._v(" "),(oField.type=='date_range')?_c('wiloke-range-date',{attrs:{"from-label":oField.fromLabel,"to-label":oField.toLabel,"value":_vm.defaultDateRange},on:{"onChangedRange":_vm.onChangedRange}}):(oField.type=='checkbox' && oField.key!=='listing_tag')?_c('wiloke-checkbox-three',{attrs:{"settings":{label: oField.label, value: 'no', key: oField.key}},on:{"checkboxChanged":_vm.checkboxChanged}}):(oField.type=='checkbox2' && oField.key=='listing_tag')?_c('wiloke-tags',{directives:[{name:"show",rawName:"v-show",value:(_vm.aTags.length),expression:"aTags.length"}],attrs:{"settings":{value: oField.key, key: oField.key, options: _vm.aTags},"is-expand-tag":_vm.mayTagOpen},on:{"onTagChanged":_vm.onTagChanged}}):(oField.type=='slider')?_c('wiloke-slider',{attrs:{"settings":{label: oField.label, value: oField.key, maximum: oField.maxRadius, value: oField.defaultRadius, unit: oField.unit}}}):(oField.type=='autocomplete')?_c('google-place',{attrs:{"o-field":_vm.googlePlaceField(oField),"value":_vm.address},on:{"addressChanged":_vm.addressChanged}}):(oField.type=='select2')?_c('wiloke-select-two',{attrs:{"settings":{isAjax: oField.isAjax, options:oField.options, label: oField.label, isMultiple: oField.isMultiple, value: _vm.selectTwoDefault(oField), key: oField.key, ajaxAction: oField.ajaxAction, ajaxArgs:{taxonomy: oField.key, postType: _vm.post_type, get: 'slug'}},"c-id":oField.key},on:{"selectTwoChanged":_vm.selectTwoChanged}}):_vm._e()],1)}))],1)])},staticRenderFns: [],
    data: function data(){
        return{
            aSearchFields: [],
            post_type: this.type,
            isLoading: 'yes',
            errorMsg: '',
            successMsg: '',
            xhrSearchField: null,
            oArgs: {
                oAddress: {},
                s: typeof this.s !== 'undefined' ? this.s : '',
                listing_cat: '',
                listing_tag: [],
                post_type: this.type,
                listing_location: ''
            },
            aFullTags: [],
            aTags: [],
            isFindingFullTags: false,
            oTagsCache: {},
            isSetDefault: false,
            isFocusHidden: false,
            xhrTag: null,
            xhrSearch: null,
            oCacheArgs: [],
            foundPosts: 0,
            oTaxonomies: {}
        }
    },
    props: ['type', 'postsPerPage', 's', 'latLng', 'address', 'isMap', 'formItemClass', 'isPopup', 'rawTaxonomies', 'rawDateRange'],
    computed: {
        wrapperClass: function wrapperClass(){
            return this.isPopup=='no' ? 'content-box_module__333d9' : '';
        },
        innerClass: function innerClass(){
            return this.isPopup=='no' ? 'content-box_body__3tSRB': '';
        },
        searchKeyDefault: function searchKeyDefault(){
            return typeof this.s !== 'undefined' ? this.s : '';
        },
        wrapperFieldsClass: function wrapperFieldsClass(){
            if ( this.isMap == 'no' ){
                return '';
            }
            return 'row';
        },
        mayTagOpen: function mayTagOpen(){
           return this.isMap=='yes' ? 'no' : 'yes';
        },
        defaultDateRange: function defaultDateRange(){
            if ( typeof this.rawDateRange !== 'undefined' && this.rawDateRange.length ){

                return JSON.parse(this.rawDateRange);
            }

            return '';
        }
    },
    components:{
        BlockLoading: BlockLoading,
        WilokeMessage: WilokeMessage,
        WilokeInput: WilokeInput,
        WilokeCheckboxThree: WilokeCheckboxThree,
        WpSearch: WpSearch,
        WilokeSelectTwo: WilokeSelectTwo,
        GooglePlace: GooglePlace,
        WilokeSlider: WilokeSlider,
        WilokeTags: WilokeTags$1,
        WilokeRangeDate: WilokeRangeDate
    },
    watch: {
        'oArgs': {
            handler: function handler(oNewVal){
                this.updateState();
            },
            deep: true
        },
        post_type: 'updateState'
    },
    methods: {
        googlePlaceField: function googlePlaceField(oField){
            if ( !WilCityHelpers.isNull(this.latLng) ){
                var aParseLatLng = this.latLng.split(',');
                oField.lat = aParseLatLng[0];
                oField.lng = aParseLatLng[1];
            }

            if ( !WilCityHelpers.isNull(this.address) ){
                oField.address = this.address;
            }

            return oField;
        },
        buildArgs: function buildArgs(oArgs){
            var this$1 = this;

            var oBuildArgs = {};
            oArgs.forEach(function (oField, order){
                switch(oField.type){
                    case 'select2':
                        if ( this$1.oTaxonomies !== null && typeof this$1.oTaxonomies[oField.key] !== 'undefined' ){
                            if ( typeof oField.isMultiple == 'undefined' || oField.isMultiple!='yes' ){
                                oBuildArgs[oField.key] = this$1.oTaxonomies[oField.key];
                            }else{
                                oBuildArgs[oField.key] = this$1.oTaxonomies[oField.key].split(',');
                            }
                        }else{
                            if ( typeof oField.isMultiple !== 'undefined' && oField.isMultiple=='yes' ){
                                oBuildArgs[oField.key] = typeof oField.value !== 'undefined' ? oField.value : [];
                            }else{
                                oBuildArgs[oField.key] = typeof oField.value !== 'undefined' ? oField.value : '';
                            }
                        }
                        break;
                    case 'listing_tag':
                        if ( this$1.oTaxonomies !== null && typeof this$1.oTaxonomies['listing_tag'] !== 'undefined'  ){
                            oBuildArgs['listing_tag'] = this$1.oTaxonomies['listing_tag'].split(',');
                        }else{
                            oBuildArgs['listing_tag'] = [];
                        }
                        break;
                    case 'autocomplete':
                        var aParseLatLng = [];
                        if ( typeof this$1.latLng !== 'undefined' && this$1.latLng.length ){
                            aParseLatLng = this$1.latLng.split(',');
                        }else{
                            aParseLatLng = ['', ''];
                        }

                        oBuildArgs['oAddress'] = {
                            address: typeof this$1.address !== 'undefined' ? this$1.address: '',
                            lat: aParseLatLng[0],
                            lng: aParseLatLng[1]
                        };
                        break;
                    case 'date_range':
                        if ( !WilCityHelpers.isNull(this$1.rawDateRange) ){
                            var oParseDateRange = JSON.parse(this$1.rawDateRange);
                            oBuildArgs[oField.key] = {
                                from: oParseDateRange.from,
                                to: oParseDateRange.to
                            };
                        }
                        break;
                    default:
                        oBuildArgs[oField.key] = typeof oField.value !== 'undefined' ? oField.value : '';
                        break;
                }
            });

            oBuildArgs['post_type'] = this.post_type;
            oBuildArgs['s'] = this.s;
            this.oCacheArgs[this.post_type] = oBuildArgs;
            this.oArgs = oBuildArgs;
        },
        updateState: function updateState(){
            if ( typeof this.$store !== 'undefined' ){
                this.$store.commit('updateOSearchArgs', this.oArgs);
                this.$store.commit('updatePostType', this.post_type);
                this.$store.commit('updateSearchField', {
                    postType: this.post_type,
                    aFields: this.aSearchFields
                });
                this.$emit('fetch-listings', this.isReset);
                this.isReset = false;
            }
        },
        wrapperFieldClass: function wrapperFieldClass(oField){
            if ( this.isMap == 'no' ){
                return '';
            }

            if ( oField.key == 'listing_tag' ){
                return 'col-md-12 col-lg-12';
            }
            return this.formItemClass;
        },
        setDefault: function setDefault(){
            if ( typeof this.taxonomy !== 'undefined' && this.taxonomy.length ){
                this.oArgs[this.taxonomy] = this.term;
            }

            if ( typeof this.latLng !== 'undefined' && this.latLng.length ){
                this.oArgs.oAddress = {
                    latLng: this.latLng,
                    address: this.address
                };
            }
        },
        resetSearchForm: function resetSearchForm(){
            var this$1 = this;

            this.$parent.$on('resetSearchForm', function (){
                this$1.oArgs = {};
                this$1.$set(this$1.$data, 'oArgs', {});
                this$1.$emit('resetEverything', true);
                this$1.searchListings();
            });
        },
        selectTwoDefault: function selectTwoDefault(oField){
            if ( typeof this.oArgs[oField.key] !== 'undefined' ) {
                return this.oArgs[oField.key];
            }

            if ( typeof oField.isMultiple !== 'undefined' && oField.isMultiple == 'yes' ){
                return [];
            }

            return '';
        },
        setFullTags: function setFullTags(aFields){
            var this$1 = this;

            if ( !this.isFindingFullTags ){
                this.isFindingFullTags = true;
                aFields.forEach(function (oField) {
                    if ( typeof oField.key !== 'undefined' && oField.key == 'listing_tag' ){
                        this$1.aFullTags = Object.values(oField.options);
                        this$1.aTags     = this$1.aFullTags;
                        return true;
                    }
                });
            }
        },
        fetchIndividualTag: function fetchIndividualTag(termSlug){
            var this$1 = this;

            if ( typeof this.oTagsCache[termSlug] !== 'undefined' ){
                this.aTags = this.oTagsCache[termSlug];
                return true;
            }

            if ( termSlug == -1 ){
                this.aTags = this.aFullTags;
            }

            if ( this.xhrTag !== null && this.xhrTag.status !== 200 ){
                this.xhrTag.abort();
            }

            this.xhrTag = jQuery.ajax({
                type: 'POST',
                url: WILOKE_GLOBAL.ajaxurl,
                data: {
                    action: 'wilcity_fetch_individual_cat_tags',
                    termSlug: termSlug
                },
                success: function (response) {
                    if ( response.success ){
                        this$1.aTags = response.data;
                        this$1.oTagsCache[termSlug] = response.data;
                    }else{
                        this$1.aTags = this$1.aFullTags;
                        this$1.oTagsCache[termSlug] = this$1.aFullTags;
                    }
                }
            });
        },
        getSearchFields: function getSearchFields(){
            var this$1 = this;

            if ( this.$store.getters.getSearchFields(this.post_type) ){
                this.aSearchFields = this.$store.getters.getSearchFields(this.post_type);
                this.buildArgs(this.aSearchFields);
                this.setFullTags(this.aSearchFields);
                return true;
            }

            if ( this.xhrSearchField !== null && this.xhrSearchField.status !== 200 ){
                this.xhrSearchField.abort();
            }

            this.isLoading = 'yes';

            this.xhrSearchField = jQuery.ajax({
                type: 'POST',
                url: WILOKE_GLOBAL.ajaxurl,
                data: {
                    action: 'wilcity_get_search_fields',
                    postType: this.post_type
                },
                success: function (response) {
                    if ( response.success ){
                        this$1.aSearchFields = Object.values(response.data.fields);
                        this$1.setFullTags(this$1.aSearchFields);
                        this$1.buildArgs(this$1.aSearchFields);
                        this$1.updateState();
                    }
                    this$1.isLoading = 'no';
                }
            });
        },
        deleteMapBounds: function deleteMapBounds(){
            if ( typeof this.oArgs.aBounds !== 'undefined' ){
                delete this.oArgs.aBounds;
            }
        },
        onTagChanged: function onTagChanged(aTags){
            this.$set(this.oArgs, 'listing_tag', aTags);
            if ( !WilCityHelpers.isMobile() ){
                this.searchListings();
            }
        },
        onChangedRange: function onChangedRange(oRange){
            if ( oRange.from.length && oRange.to.length ){
                this.$set(this.oArgs, 'date_range', oRange);
                if ( !WilCityHelpers.isMobile() ){
                    this.searchListings();
                }
            }
        },
        onSearchChanged: function onSearchChanged(newVal, oSettings){
            this.$set(this.oArgs, 's', newVal);
            if ( !WilCityHelpers.isMobile() ){
                this.searchListings();
            }
        },
        checkboxChanged: function checkboxChanged(newVal, oSettings){
            this.$set(this.oArgs, oSettings.key, newVal);
            if ( !WilCityHelpers.isMobile() ){
                this.searchListings();
            }
        },
        addressChanged: function addressChanged(oNewVal){
            this.oArgs.oAddress = oNewVal;
            this.deleteMapBounds();
            if ( !WilCityHelpers.isMobile() ){
                this.searchListings();
            }
        },
        selectTwoChanged: function selectTwoChanged(newVal, oSettings){
            if ( oSettings.key == 'listing_cat' ){
                this.fetchIndividualTag(newVal);
            }else if ( oSettings.key == 'listing_location' ){
                this.deleteMapBounds();
            }else if (oSettings.key == 'post_type'){
                this.post_type = newVal;
                this.getSearchFields();
            }
            this.$set(this.oArgs, oSettings.key, newVal);

            if ( !WilCityHelpers.isMobile() ){
                this.searchListings();
            }
        },
        maybeDefault: function maybeDefault(fieldKey){
            if ( typeof this.oQuery[fieldKey] !== 'undefined' ){
                return this.oQuery[fieldKey];
            }
            return '';
        },
        searchListings: function searchListings(){
            var this$1 = this;

            if ( this.isMap == 'yes' ){
                return false;
            }

            this.$emit('searching', false);
            if ( this.xhrSearch !== null && this.xhrSearch.status !== 200 ){
                this.xhrSearch.abort();
            }

            this.$emit('searching', true);

            var $pagination = jQuery('#wilcity-search-pagination');
            $pagination.data('oArgs', this.oArgs);

            this.xhrSearch = jQuery.ajax({
                type: 'POST',
                url: WILOKE_GLOBAL.ajaxurl,
                data: {
                    postType: this.post_type,
                    oArgs: this.$store.getters.getSearchArgs,
                    action: 'wilcity_search_listings',
                    postsPerPage: this.postsPerPage
                },
                success: function (response) {
                    if ( response.success ){
                        document.getElementById('wilcity-search-results').innerHTML = response.data.msg;
                        var $grid = jQuery('#wilcity-search-results');
                        $grid.find('.wilcity-preview-gallery').each(function () {
                            jQuery(this).wilcityMagnificGalleryPopup();
                        });
                        $grid.find('.wilcity-js-favorite').each(function () {
                            jQuery(this).wilcityFavoriteStatistic();
                        });
                        jQuery('body, html').stop().animate({scrollTop:0}, 500, 'swing');
                    }else{
                        document.getElementById('wilcity-search-results').innerHTML = response.data.msg;
                    }

                    this$1.$parent.$emit('foundPosts', response.data.maxPosts);
                    $pagination.trigger('resetPagination', [1, response.data.maxPosts]);
                    this$1.$emit('searching', false);
                }
            });
        }
    },
    mounted: function mounted(){
        var this$1 = this;

        this.oTaxonomies = this.rawTaxonomies.length ? JSON.parse(this.rawTaxonomies) : {};
        this.resetSearchForm();
        this.getSearchFields();
        this.setDefault();
        jQuery('#wilcity-search-pagination').data('oArgs', this.oArgs);

        if ( this.taxonomy == 'listing_cat' ){
            this.fetchIndividualTag(this.term);
        }

        this.$parent.$on('onSearchListings', function (){
            this$1.searchListings();
        });
    }
};

var WilokeSearchFormPopup = {render: function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return (_vm.isMobile)?_c('wiloke-popup',{attrs:{"wrapper-class":"popup_module__3M-0- pos-f-full popup_mobile-full__1hyc4","popup-id":"wilcity-search-form-popup","popup-title":_vm.popupTitle}},[_c('div',{attrs:{"slot":"body"},slot:"body"},[_c('search-form',{attrs:{"type":_vm.type,"posts-per-page":_vm.postsPerPage,"s":_vm.s,"lat-lng":_vm.latLng,"address":_vm.address,"is-map":_vm.isMap,"raw-taxonomies":_vm.rawTaxonomies,"raw-data-range":_vm.rawDateRange,"form-item-class":_vm.formItemClass,"is-popup":"yes"},on:{"fetch-listings":_vm.triggerFetchListing}}),_vm._v(" "),_c('a',{staticClass:"wil-btn wil-btn--primary wil-btn--round wil-btn--md wil-btn--block",attrs:{"href":"#"},on:{"click":function($event){$event.preventDefault();_vm.searchNow($event);}}},[_c('i',{staticClass:"la la-search"}),_vm._v(" "+_vm._s(_vm.oTranslation.search)+" ")])],1)]):_vm._e()},staticRenderFns: [],
    data: function data(){
        return {
            oTranslation: WILCITY_I18
        }
    },
    props: ['type', 'postsPerPage', 'rawTaxonomies', 's', 'latLng', 'address', 'isMap', 'rawDateRange', 'formItemClass', 'popupTitle'],
    components:{
        SearchForm: SearchForm,
        WilokePopup: WilokePopup
    },
    methods: {
        triggerFetchListing: function triggerFetchListing(){
            if ( this.isMap == 'yes' ){
                WilcityMap.$emit('fetchListings');
            }
        },
        searchNow: function searchNow(){
            if ( this.isMap == 'yes' ){
                WilcityMap.$emit('fetchListings');
            }else{
                WILOKE_GLOBAL.vmNoMap.$emit('onSearchListings');
            }

            this.$store.dispatch('closeSearchFormPopup');
        }
    },
    computed: {
        isMobile: function isMobile(){
            return WilCityHelpers.isMobile();
        }
    }
};

var WilokeTerms = {render: function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{class:_vm.parseWrapperClass},[_c('div',{staticClass:"field_wrap__Gv92k"},[(_vm.settings.isMultiple=='yes')?_c('select',{directives:[{name:"model",rawName:"v-model",value:(_vm.selected),expression:"selected"}],class:_vm.selectTwoClass,attrs:{"multiple":"multiple"},on:{"change":function($event){var $$selectedVal = Array.prototype.filter.call($event.target.options,function(o){return o.selected}).map(function(o){var val = "_value" in o ? o._value : o.value;return val}); _vm.selected=$event.target.multiple ? $$selectedVal : $$selectedVal[0];}}},_vm._l((_vm.aOptions),function(oOption){return _c('option',{class:_vm.printOptionClass(oOption),domProps:{"value":oOption.value,"innerHTML":_vm._s(oOption.name)}})})):_c('select',{directives:[{name:"model",rawName:"v-model",value:(_vm.selected),expression:"selected"}],class:_vm.selectTwoClass,on:{"change":function($event){var $$selectedVal = Array.prototype.filter.call($event.target.options,function(o){return o.selected}).map(function(o){var val = "_value" in o ? o._value : o.value;return val}); _vm.selected=$event.target.multiple ? $$selectedVal : $$selectedVal[0];}}},_vm._l((_vm.aOptions),function(oOption){return _c('option',{class:_vm.printOptionClass(oOption),domProps:{"value":oOption.value,"innerHTML":_vm._s(oOption.name)}})})),_vm._v(" "),_c('span',{staticClass:"field_label__2eCP7 text-ellipsis",class:{required: _vm.settings.isRequired=='yes'}},[_vm._v(_vm._s(_vm.settings.label))]),_vm._v(" "),_c('span',{staticClass:"bg-color-primary"})]),_vm._v(" "),(_vm.settings.errMsg)?_c('span',{staticClass:"field_message__3Z6FX color-quaternary"},[_vm._v(_vm._s(_vm.settings.errMsg))]):_vm._e()])},staticRenderFns: [],
    data: function data(){
        return {
            isTax: null,
            optionClass: null,
            selected: null,
            $select2: null,
            aTermOptions: null,
            aOptions: []
        }
    },
    props: {
        'settings':{
            type: Object,
            default: {}
        },
        wrapperClass: {
            type: String,
            default: ''
        },
        target: {
            type: String,
            default: 'id'
        }
    },
    components: {
        WilokeHeading: WilokeHeading
    },
    computed:{
        parseWrapperClass: function parseWrapperClass(){
            var wrapperClass = this.wrapperClass;

            if ( !wrapperClass.length ){
                wrapperClass = 'field_module__1H6kT field_style2__2Znhe mb-15';
            }
            return this.selected != null && this.selected.length ? wrapperClass + ' active' : wrapperClass;
        },
        selectTwoClass: function selectTwoClass(){
            var createClass = 'wilcity-select-2';
            if ( this.settings.isAjax && this.settings.isAjax == 'yes' ){
                createClass += ' is-ajax';
            }

            return createClass;
        }
    },
    methods:{
        resetValue: function resetValue(){
            var this$1 = this;

            this.$parent.$on('resetEverything', function (){
                this$1.selected = this$1.settings.isMultiple == 'yes' ? [] : '';
                jQuery(this$1.$el).find('.wilcity-select-2').val(this$1.selected).trigger('change');
            });
        },
        setDefault: function setDefault(){
            if ( this.settings.isMultiple === 'yes' ){
                this.selected = typeof this.settings.value !== 'undefined' && this.settings.value.length ? this.settings.value : [];
            }else{
                this.selected = !WilCityHelpers.isNull(this.settings.value) ? this.settings.value : '';
            }
        },
        updateValue: function updateValue(val){
            if ( typeof val == 'undefined' || val === null ){
                return false;
            }
            if ( this.settings.isMultiple == 'yes' ){
                this.selected = val;
                this.settings.value = this.selected;
                this.$emit('term-changed', this.settings.value, this.settings);
            }else{
                if ( this.selected == val ){
                    return false;
                }
                this.selected = val;
                this.settings.value = this.selected;
                this.$emit('term-changed', this.settings.value, this.settings);
            }
        },
        printOptionClass: function printOptionClass(oOption){
            return typeof oOption.parent!== 'undefined' && oOption.parent !== 0 ? 'has-parent-term' : '';
        },
        maximumSelectionLength: function maximumSelectionLength(){
            if ( this.settings.maximum ){
                return this.settings.maximum;
            }

            return 10000;
        },
        selectTwo: function selectTwo(){
            var this$1 = this;

            this.$select2 = jQuery(this.$el).find('.wilcity-select-2');
            if ( this.$select2.hasClass('is-ajax') ){
                var oArgs = {
                    action: this.settings.ajaxAction,
                    taxonomy: this.settings.key,
                    get: this.target
                };

                if ( typeof this.settings.ajaxArgs !== 'undefined' ){
                    oArgs = Object.assign({}, oArgs, this.settings.ajaxArgs);
                }

                this.$select2.select2({
                    ajax:{
                        url: WILOKE_GLOBAL.ajaxurl,
                        data: function (params) {
                            return Object.assign({}, {search: params.term}, oArgs);
                        },
                        processResults: function (data, params) {
                            if ( !data.success ){
                                return false;
                            }else{
                                return data.data.msg;
                            }
                        },
                        cache: true
                    },
                    templateResult: function(state){
                        if (!state.id) {
                            return state.text;
                        }

                        var treeItemClass = jQuery(state.element).hasClass('has-parent-term') ? 'has-parent-term' : '';
                        var $state = jQuery('<span class="'+treeItemClass+'">'+state.text+'</span>');
                        return $state;
                    },
                    templateSelection: function(repo){
                        return repo.text.replace('&amp;', '&');
                    },
                    minimumInputLength: 1
                }).on('select2:open', (function (event){
                    jQuery(event.currentTarget).closest('.field_module__1H6kT').addClass('active');
                })).on('select2:close', (function (event){
                    this$1.updateValue(jQuery(event.currentTarget).val());
                }));
            }else{
                this.$select2.select2({
                    templateResult: function(state){
                        if (!state.id) {
                            return state.text;
                        }

                        var treeItemClass = jQuery(state.element).hasClass('has-parent-term') ? 'has-parent-term' : '';
                        var $state = jQuery('<span class="'+treeItemClass+'">'+state.text+'</span>');
                        return $state;
                    },
                    templateSelection: function(repo){
                        return repo.text.replace('&amp;', '&');
                    },
                    allowClear: true,
                    placeholder: '',
                    maximumSelectionLength: this.maximumSelectionLength()
                }).on('select2:close', (function (event){
                    var $select2 = jQuery(event.currentTarget);
                    $select2.closest('.field_module__1H6kT').addClass('active');
                    var val = $select2.val();

                    if (WilCityHelpers.isNull(val)){
                        $select2.closest('.field_module__1H6kT').removeClass('active');
                    }else{
                        $select2.closest('.field_module__1H6kT').find('.select2-selection__rendered').attr('style', '');
                    }
                    this$1.updateValue(val);
                }));
            }

            this.triggerDefault();
        },
        triggerDefault: function triggerDefault(){
            if (!WilCityHelpers.isNull(this.settings.value)){
                this.$select2.closest('.field_module__1H6kT').addClass('active');
            }
        },
        fetchOptions: function fetchOptions(){
            var this$1 = this;

            if ( this.settings.isAjax == 'yes' ){
                return false;
            }

            if ( this.$store.getters.getTermOptions(this.settings.key) ){
                this.aOptions = this.$store.getters.getTermOptions(this.settings.key);
                return true;
            }

            jQuery.ajax({
                type: 'POST',
                url: WILOKE_GLOBAL.ajaxurl,
                data: {
                    action: 'wilcity_fetch_terms_options',
                    taxonomy: this.settings.key,
                    isShowParentOnly: this.settings.isShowParentOnly
                },
                success: function (response) {
                    this$1.aOptions = response.data;
                    this$1.$store.commit('updateTermOptions', {
                        taxonomy: this$1.settings.key,
                        aOptions: this$1.aOptions
                    });
                }
            });
        }
    },
    mounted: function mounted(){
        this.selectTwo();
        this.resetValue();
    },
    beforeMount: function beforeMount(){
        this.setDefault();
        this.fetchOptions();
    }
};

var WilokeHeroSearchForm = {render: function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',[_vm._l((_vm.aFields),function(oField,order){return _c('div',{class:_vm.wrapperClass(order)},[(oField.group=='term')?_c('wiloke-terms',{attrs:{"settings":oField,"value":'',"target":"slug","wrapper-class":"field_module__1H6kT field_style2__2Znhe"},on:{"term-changed":_vm.oUpdateArgs}}):(oField.type=='autocomplete')?_c('wiloke-auto-complete',{attrs:{"settings":_vm.autoCompleteFields(oField),"wrapper-class":"field_module__1H6kT field_style2__2Znhe"},on:{"geocode-changed":_vm.oUpdateArgs}}):_vm._e(),_vm._v(" "),(oField.type=='wp_search')?_c('wp-search',{attrs:{"label":oField.label,"value":"","wrapper-class":"field_module__1H6kT field_style2__2Znhe select-text"},on:{"changedValue":_vm.oUpdateArgs}}):_vm._e(),_vm._v(" "),(oField.type=='date_range')?_c('wiloke-range-date',{attrs:{"from-label":oField.fromLabel,"to-label":oField.toLabel,"value":""},on:{"onChangedRange":_vm.oUpdateArgs}}):_vm._e()],1)}),_vm._v(" "),_c('div',{staticClass:"col-sm-3"},[_c('button',{directives:[{name:"show",rawName:"v-show",value:(_vm.aFields.length),expression:"aFields.length"}],staticClass:"wil-btn wil-btn--primary wil-btn--md wil-btn--round wil-btn--block",on:{"click":function($event){$event.preventDefault();_vm.submitSearch($event);}}},[_c('i',{staticClass:"la la-search"}),_vm._v(" "+_vm._s(_vm.oTranslation.searchNow))])])],2)},staticRenderFns: [],
    data: function data(){
        return{
            oHandled: {},
            aForm: {},
            aFields: [],
            errorMsg: '',
            xhr: null,
            currentType: '',
            oArgs: {},
            type: this.postType,
            oTranslation: WILCITY_I18
        }
    },
    props: ['instantLoadFields', 'postType', 'searchUrl', 'rawFields'],
    components:{
        WilokeSelectTwo: WilokeSelectTwo,
        WilokeAutoComplete: WilokeAutoComplete,
        WilokeTerms: WilokeTerms,
        WpSearch: WpSearch,
        WilokeRangeDate: WilokeRangeDate
    },
    methods: {
        autoCompleteFields: function autoCompleteFields(oField){
            oField.placeholder = oField.label;
            return oField;
        },
        wrapperClass: function wrapperClass(order){
            if ( this.aFields.length == 3 ){
                return 'col-sm-3';
            }

            if ( order == 0 ){
                return 'col-md-5';
            }

            return 'col-md-4';
        },
        fetchFields: function fetchFields(){
            var this$1 = this;

            if ( typeof this.aForm[this.type] !== 'undefined' ){
                this.aFields = this.aForm[this.type];
                return true;
            }

            if ( typeof this.oHandled[this.type] !== 'undefined' ){
                return true;
            }

            this.oHandled[this.type] = '1';

            if ( !this.currentType.length ){
                this.currentType = this.type;
            }

            jQuery.ajax({
                type: 'POST',
                url: WILOKE_GLOBAL.ajaxurl,
                data: {
                    action: 'wilcity_fetch_hero_search_fields',
                    postType: this.type
                },
                success: function (response) {
                    if ( response.success ){
                        if ( this$1.currentType == this$1.type ){
                            this$1.aFields = response.data;
                        }
                        this$1.aForm[this$1.currentType] = this$1.aFields;
                    }else{
                        this$1.errorMsg =  response.data.msg;
                    }
                    this$1.$parent.$emit('formLoaded', true);
                    this$1.currentType = '';
                }
            });
        },
        oUpdateArgs: function oUpdateArgs(val, oSettings){
            switch ( oSettings.key ){
                case 'google_place':
                    if ( val !== '' ){
                        this.oArgs.latLng = encodeURIComponent(val.lat+','+val.lng);
                        this.oArgs.address = encodeURIComponent(val.address);
                        this.oArgs.radius = encodeURIComponent(oSettings.maxRadius);
                        this.oArgs.unit = encodeURIComponent(oSettings.unit);
                    }else{
                        delete this.oArgs.latLng;
                        delete this.oArgs.address;
                        delete this.oArgs.unit;
                        delete this.oArgs.radius;
                    }
                    break;
                case 'wp_search':
                    if ( val !== '' ){
                        this.oArgs.title = encodeURIComponent(val);
                    }else{
                        delete this.oArgs.title;
                    }
                    break;
                case 'date_range':
                    if ( val !== '' ){
                        this.oArgs.date_range = encodeURIComponent(val.from+','+val.to);
                    }else{
                        delete this.oArgs.date_range;
                    }
                    break;
                case 'listing_cat':
                    if ( val.length ){
                        if ( typeof val == 'object' ){
                            var parseCats = val.join(',');
                            this.oArgs.listing_cat = encodeURIComponent(parseCats);
                        }else{
                            this.oArgs.listing_cat = encodeURIComponent(val);
                        }
                    }else{
                        delete this.oArgs.listing_cat;
                    }
                    break
                default:
                    this.oArgs[oSettings.key] = val;
                    break;
            }
        },
        buildSearchArgs: function buildSearchArgs(){
            var this$1 = this;

            var query = '';
            for ( var key in this$1.oArgs ){
                query += key +'='+this$1.oArgs[key]+'&';
            }

            query = query.slice(0, -1);
            return query;
        },
        submitSearch: function submitSearch(){
             this.oArgs.type = this.$parent.type;
                var query = this.buildSearchArgs();
                var redirectTo = this.searchUrl + '?' + query;
                window.location.href = redirectTo;
        }
    },
    mounted: function mounted(){
        var this$1 = this;

        if ( this.instantLoadFields == 'yes' ){
            if ( typeof this.rawFields !== 'undefined' ){
                try{
                    this.aFields = JSON.parse(this.rawFields);
                    this.aForm[this.type] = this.aFields;
                }catch(e){
                    console.log('Could not parse search form json.');
                }
            }
        }

        this.$parent.$on('switched-tab', (function (postType){
            this$1.type = postType;
            this$1.fetchFields();
        }));
    }
};

var WilokeSwitchPostTypeBtn = {render: function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('a',{class:_vm.wrapperClass,attrs:{"href":"#"},on:{"click":function($event){$event.preventDefault();_vm.switchPostType($event);}}},[_c('i',{class:_vm.iconClass}),_vm._v(_vm._s(_vm.tabName))])},staticRenderFns: [],
    data: function data(){
        return{
            wrapperClass: this.isDefaultActive == 'yes' ? 'active' : 'not-active'
        }
    },
    props: ['isDefaultActive', 'postType', 'iconClass', 'tabName'],
    methods: {
        switchPostType: function switchPostType(){
            this.$emit('on-switch-post-type', this.postType);
        }
    }
};

var General = function General(){
	this.textUpWhenFocusOn(jQuery);
};

General.prototype.textUpWhenFocusOn = function textUpWhenFocusOn ($){
	var $textField = $('body').find('input[type="text"], textarea, input[type="email"], input[type="url"]');

	$textField.each(function () {
		var $this = $(this);
		if ( $this.val() !=='' ){
			$this.closest('.js-field').addClass('active');
		}
	});

	$textField.on('focus', function (event) {
		$(event.currentTarget).closest('.js-field').addClass('active');
	}).on('blur', function (event) {
		if ( $(event.currentTarget).val() === '' ){
			$(event.currentTarget).closest('.js-field').removeClass('active');
		}
	});
};

var QuickNotifications = {render: function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',[_c('a',{staticClass:"header_loginHead__3HoVP",attrs:{"href":"#","data-toggle-minWidth":"768","dataTooltip":_vm.oTranslation.notifications,"data-tooltip-placement":"top","data-tooltip-theme":"light"},on:{"click":function($event){$event.preventDefault();_vm.toggleShowing($event);}}},[_c('i',{staticClass:"la la-bell-o"}),_c('span',{directives:[{name:"show",rawName:"v-show",value:(_vm.countNewNotifications),expression:"countNewNotifications"}],staticClass:"header_number__1a6F5 bg-color-quaternary"},[_vm._v(_vm._s(_vm.countNewNotifications))])]),_vm._v(" "),_c('div',{class:_vm.wrapperNotificationsClass},[_c('div',{directives:[{name:"show",rawName:"v-show",value:(_vm.isLoading=='yes'),expression:"isLoading=='yes'"}],staticClass:"pos-r",staticStyle:{"min-height":"100px"}},[_c('block-loading',{attrs:{"is-loading":_vm.isLoading,"position":"pos-a-center"}})],1),_vm._v(" "),_c('ul',{directives:[{name:"show",rawName:"v-show",value:(_vm.aNotifications.length),expression:"aNotifications.length"}],staticClass:"list-utility_module__32oNg list-none list-utility_abs__OYiyL arrow--top-right"},[_vm._l((_vm.aNotifications),function(oNotification){return _c('li',{staticClass:"list-utility_list__1DzGk"},[_c('a',{staticClass:"list-utility_link__3BRZx",attrs:{"href":oNotification.link}},[_c('span',{staticClass:"list-utility_remove__1Vlf4 color-primary--hover",attrs:{"title":_vm.oTranslation.deleteMsg},on:{"click":function($event){$event.preventDefault();_vm.deleteNotification(oNotification, _vm.order);}}},[_c('i',{staticClass:"la la-close"})]),_vm._v(" "),_c('div',{staticClass:"utility-box-1_module__MYXpX utility-box-1_sm__mopok utility-box-1_boxLeft__3iS6b clearfix"},[(oNotification.featuredImg)?_c('div',{staticClass:"utility-box-1_avatar__DB9c_ rounded-circle",style:({'background-image': 'url('+oNotification.featuredImg+')'})},[_c('img',{attrs:{"src":oNotification.featuredImg,"alt":oNotification.title}})]):_vm._e(),_vm._v(" "),_c('div',{staticClass:"utility-box-1_body__8qd9j"},[_c('div',{staticClass:"utility-box-1_group__2ZPA2 text-ellipsis"},[(oNotification.title)?_c('h3',{staticClass:"utility-box-1_title__1I925",domProps:{"innerHTML":_vm._s(oNotification.title)}}):_vm._e(),_vm._v(" "),_c('div',{staticClass:"utility-box-1_content__3jEL7"},[_vm._v(_vm._s(oNotification.content)+" "),(oNotification.contentHighlight)?_c('span',{staticClass:"color-dark-1",domProps:{"innerHTML":_vm._s(oNotification.contentHighlight)}}):_vm._e()])]),_vm._v(" "),_c('div',{staticClass:"utility-box-1_description__2VDJ6"},[_c('i',{staticClass:"la la-comments color-primary"}),_vm._v(" "+_vm._s(oNotification.time))])])])])])}),_vm._v(" "),_c('li',{directives:[{name:"show",rawName:"v-show",value:(_vm.aNotifications.length),expression:"aNotifications.length"}],staticClass:"list-utility_list__1DzGk"},[_c('a',{staticClass:"list-utility_more__2Y_w7 wil-text-center color-primary--hover",attrs:{"href":_vm.notificationDashboardUrl}},[_vm._v(_vm._s(_vm.oTranslation.viewAll))])])],2),_vm._v(" "),_c('ul',{directives:[{name:"show",rawName:"v-show",value:(_vm.errMsg.length),expression:"errMsg.length"}],staticClass:"list-utility_module__32oNg list-none list-utility_abs__OYiyL arrow--top-right"},[_c('li',{staticClass:"list-utility_list__1DzGk"},[_c('a',{staticClass:"list-utility_link__3BRZx",attrs:{"href":"#"}},[_c('div',{staticClass:"utility-box-1_module__MYXpX utility-box-1_sm__mopok utility-box-1_boxLeft__3iS6b clearfix"},[_c('div',{staticClass:"utility-box-1_body__8qd9j"},[_c('div',{staticClass:"utility-box-1_group__2ZPA2 text-ellipsis"},[_c('div',{staticClass:"utility-box-1_content__3jEL7",domProps:{"innerHTML":_vm._s(_vm.errMsg)}})])])])])])])])])},staticRenderFns: [],
    data: function data(){
        return{
            oTranslation: WILCITY_I18,
            isShowing: 'no',
            isLoading: 'no',
            needDashboardUrl: 'yes',
            aNotifications: [],
            errMsg: '',
            notificationDashboardUrl: '#',
            countNewNotifications: 0
        }
    },
    components: {
        BlockLoading: BlockLoading
    },
    computed:{
        wrapperNotificationsClass: function wrapperNotificationsClass(){
            return this.isShowing == 'no' ? 'header_loginBody__2hz2g' : 'header_loginBody__2hz2g active';
        },
    },
    mounted: function mounted(){
        this.fetchNewNotifications();
    },
    methods: {
        deleteNotification: function deleteNotification(oNotification, order){
            var this$1 = this;

            var askHim = confirm(this.oTranslation.confirmDeleteNotification);
            if ( !askHim ){
                return false;
            }

            jQuery.ajax({
                type: 'POST',
                url: WILOKE_GLOBAL.ajaxurl,
                data: {
                    action: 'wilcity_delete_notification',
                    ID: oNotification.ID
                },
                success: function (response) {
                    if ( response.success ){
                        this$1.aNotifications.splice(order, 1);
                    }else{
                        this$1.errorMsg = response.data.msg;
                    }
                }
            });
        },
        fetchNewNotifications: function fetchNewNotifications(){
            var this$1 = this;

            jQuery.ajax({
                type: 'POST',
                url: WILOKE_GLOBAL.ajaxurl,
                data: {
                    action: 'wilcity_count_new_notifications'
                },
                success: function (response) {
                    this$1.countNewNotifications = response.data;
                }
            });
        },
        resetNotification: function resetNotification(){
            console.log(this.countNewNotifications);
            if ( this.countNewNotifications < 1 ){
                return false;
            }
            this.countNewNotifications = 0;
            setTimeout(function (){
                jQuery.ajax({
                    type: 'POST',
                    url: WILOKE_GLOBAL.ajaxurl,
                    data: {
                        action: 'wilcity_reset_new_notifications'
                    }
                });
            }, 4000);
        },
        toggleShowing: function toggleShowing(){
            var this$1 = this;

            this.isShowing = this.isShowing == 'no' ? 'yes' : 'no';

            this.isLoading = 'yes';
            this.resetNotification();

            jQuery.ajax({
                type: 'post',
                url: WILOKE_GLOBAL.ajaxurl,
                data: {
                    action: 'wilcity_fetch_list_notifications',
                    needDashboardUrl: this.needDashboardUrl,
                    limit: 5
                },
                success: function (response) {
                    if ( response.success ){
                        this$1.aNotifications = response.data.oInfo;
                        if ( this$1.notificationDashboardUrl === '#' ){
                             this$1.notificationDashboardUrl = response.data.dashboardUrl;
                             this$1.needDashboardUrl = 'no';
                        }
                    }else{
                        this$1.errMsg = response.data.msg;
                    }
                    this$1.isLoading = 'no';
                }
            });
        },
    }
};

var MessageNotifications = {render: function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',[_c('a',{staticClass:"header_loginHead__3HoVP",attrs:{"href":"#","data-toggle-minWidth":"768","dataTooltip":_vm.oTranslation.messages,"data-tooltip-placement":"top","data-tooltip-theme":"light"},on:{"click":function($event){$event.preventDefault();_vm.toggleShowing($event);}}},[_c('i',{staticClass:"la la-envelope"}),_c('span',{directives:[{name:"show",rawName:"v-show",value:(_vm.countNewNotifications),expression:"countNewNotifications"}],staticClass:"header_number__1a6F5 bg-color-quaternary"},[_vm._v(_vm._s(_vm.countNewNotifications))])]),_vm._v(" "),_c('div',{class:_vm.wrapperNotificationsClass},[_c('div',{directives:[{name:"show",rawName:"v-show",value:(_vm.isLoading=='yes'),expression:"isLoading=='yes'"}],staticClass:"pos-r",staticStyle:{"min-height":"100px"}},[_c('block-loading',{attrs:{"is-loading":_vm.isLoading,"position":"pos-a-center"}})],1),_vm._v(" "),_c('ul',{directives:[{name:"show",rawName:"v-show",value:(_vm.aNotifications.length && _vm.isLoading=='no'),expression:"aNotifications.length && isLoading=='no'"}],staticClass:"list-utility_module__32oNg list-none list-utility_abs__OYiyL arrow--top-right"},[_vm._l((_vm.aNotifications),function(oNotification){return _c('li',{staticClass:"list-utility_list__1DzGk"},[_c('a',{staticClass:"list-utility_link__3BRZx",attrs:{"href":oNotification.link}},[_c('div',{staticClass:"utility-box-1_module__MYXpX utility-box-1_sm__mopok utility-box-1_boxLeft__3iS6b clearfix"},[(oNotification.avatar)?_c('div',{staticClass:"utility-box-1_avatar__DB9c_ rounded-circle",style:({'background-image': 'url('+oNotification.avatar+')'})},[_c('img',{attrs:{"src":oNotification.avatar,"alt":oNotification.displayName}})]):_vm._e(),_vm._v(" "),_c('div',{staticClass:"utility-box-1_body__8qd9j"},[_c('div',{staticClass:"utility-box-1_group__2ZPA2 text-ellipsis"},[(oNotification.displayName)?_c('h3',{staticClass:"utility-box-1_title__1I925",domProps:{"innerHTML":_vm._s(oNotification.displayName)}}):_vm._e(),_vm._v(" "),_c('div',{staticClass:"utility-box-1_content__3jEL7"},[_vm._v(_vm._s(oNotification.content))])]),_vm._v(" "),_c('div',{staticClass:"utility-box-1_description__2VDJ6"},[_c('i',{staticClass:"la la-comments color-primary"}),_vm._v(" "+_vm._s(oNotification.time))])])])])])}),_vm._v(" "),_c('li',{directives:[{name:"show",rawName:"v-show",value:(_vm.aNotifications.length),expression:"aNotifications.length"}],staticClass:"list-utility_list__1DzGk"},[_c('a',{staticClass:"list-utility_more__2Y_w7 wil-text-center color-primary--hover",attrs:{"href":_vm.notificationDashboardUrl}},[_vm._v(_vm._s(_vm.oTranslation.viewAll))])])],2),_vm._v(" "),_c('ul',{directives:[{name:"show",rawName:"v-show",value:(_vm.errMsg.length),expression:"errMsg.length"}],staticClass:"list-utility_module__32oNg list-none list-utility_abs__OYiyL arrow--top-right"},[_c('li',{staticClass:"list-utility_list__1DzGk"},[_c('a',{staticClass:"list-utility_link__3BRZx",attrs:{"href":"#"}},[_c('div',{staticClass:"utility-box-1_module__MYXpX utility-box-1_sm__mopok utility-box-1_boxLeft__3iS6b clearfix"},[_c('div',{staticClass:"utility-box-1_body__8qd9j"},[_c('div',{staticClass:"utility-box-1_group__2ZPA2 text-ellipsis"},[_c('div',{staticClass:"utility-box-1_content__3jEL7",domProps:{"innerHTML":_vm._s(_vm.errMsg)}})])])])])])])])])},staticRenderFns: [],
    data: function data(){
        return{
            oTranslation: WILCITY_I18,
            isShowing: 'no',
            isLoading: 'no',
            needDashboardUrl: 'yes',
            aNotifications: [],
            errMsg: '',
            notificationDashboardUrl: '#',
            countNewNotifications: 0
        }
    },
    components: {
        BlockLoading: BlockLoading
    },
    computed:{
        wrapperNotificationsClass: function wrapperNotificationsClass(){
            return this.isShowing == 'no' ? 'header_loginBody__2hz2g' : 'header_loginBody__2hz2g active';
        },
    },
    mounted: function mounted(){
        this.fetchNewNotifications();
        this.fakeSocket();
    },
    methods: {
        fakeSocket: function fakeSocket(){
            var this$1 = this;

            setInterval(function (){
                this$1.fetchNewNotifications();
            }, 300000);
        },
        fetchNewNotifications: function fetchNewNotifications(){
            var this$1 = this;

            jQuery.ajax({
                type: 'POST',
                url: WILOKE_GLOBAL.ajaxurl,
                data: {
                    action: 'wilcity_count_new_messages'
                },
                success: function (response) {
                    this$1.countNewNotifications = response.data;
                }
            });
        },
        resetNotification: function resetNotification(){
            if ( this.countNewNotifications < 1 ){
                return false;
            }
            this.countNewNotifications = 0;
            setTimeout(function (){
                jQuery.ajax({
                    type: 'POST',
                    url: WILOKE_GLOBAL.ajaxurl,
                    data: {
                        action: 'wilcity_reset_new_messages'
                    }
                });
            }, 4000);
        },
        toggleShowing: function toggleShowing(){
            var this$1 = this;

            this.isShowing = this.isShowing == 'no' ? 'yes' : 'no';

            this.isLoading = 'yes';
            this.resetNotification();

            jQuery.ajax({
                type: 'post',
                url: WILOKE_GLOBAL.ajaxurl,
                data: {
                    action: 'wilcity_fetch_list_messages',
                    needDashboardUrl: this.needDashboardUrl,
                    limit: 5
                },
                success: function (response) {
                    if ( response.success ){
                        this$1.aNotifications = response.data.aInfo;
                        if ( this$1.notificationDashboardUrl === '#' ){
                             this$1.notificationDashboardUrl = response.data.dashboardUrl;
                             this$1.needDashboardUrl = 'no';
                        }
                    }else{
                        this$1.errMsg = response.data.msg;
                    }
                    this$1.isLoading = 'no';
                }
            });
        },
    }
};

var WilokeLogin = {render: function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',[_c('div',{class:_vm.usernameClass},[_c('div',{staticClass:"field_wrap__Gv92k"},[_c('input',{directives:[{name:"model",rawName:"v-model",value:(_vm.username),expression:"username"}],staticClass:"field_field__3U_Rt",attrs:{"type":"text"},domProps:{"value":(_vm.username)},on:{"keypress":_vm.keyboardLogin,"input":function($event){if($event.target.composing){ return; }_vm.username=$event.target.value;}}}),_c('span',{staticClass:"field_label__2eCP7 text-ellipsis required"},[_vm._v(_vm._s(_vm.oTranslation.usernameOrEmail))]),_c('span',{staticClass:"bg-color-primary"})])]),_vm._v(" "),_c('div',{class:_vm.passwordClass},[_c('div',{staticClass:"field_wrap__Gv92k"},[_c('input',{directives:[{name:"model",rawName:"v-model",value:(_vm.password),expression:"password"}],staticClass:"field_field__3U_Rt",attrs:{"type":"password"},domProps:{"value":(_vm.password)},on:{"keypress":_vm.keyboardLogin,"input":function($event){if($event.target.composing){ return; }_vm.password=$event.target.value;}}}),_c('span',{staticClass:"field_label__2eCP7 text-ellipsis required"},[_vm._v(_vm._s(_vm.oTranslation.password))]),_c('span',{staticClass:"bg-color-primary"})])]),_vm._v(" "),_c('div',{staticClass:"o-hidden ws-nowrap"},[_c('div',{staticClass:"checkbox_module__1K5IS mb-15 d-inline-block"},[_c('label',{staticClass:"checkbox_label__3cO9k"},[_c('input',{directives:[{name:"model",rawName:"v-model",value:(_vm.isRemember),expression:"isRemember"}],staticClass:"checkbox_inputcheck__1_X9Z",attrs:{"type":"checkbox","true-value":"yes","no-value":"false"},domProps:{"checked":Array.isArray(_vm.isRemember)?_vm._i(_vm.isRemember,null)>-1:_vm._q(_vm.isRemember,"yes")},on:{"change":function($event){var $$a=_vm.isRemember,$$el=$event.target,$$c=$$el.checked?("yes"):(false);if(Array.isArray($$a)){var $$v=null,$$i=_vm._i($$a,$$v);if($$el.checked){$$i<0&&(_vm.isRemember=$$a.concat([$$v]));}else{$$i>-1&&(_vm.isRemember=$$a.slice(0,$$i).concat($$a.slice($$i+1)));}}else{_vm.isRemember=$$c;}}}}),_vm._m(0),_c('span',{staticClass:"checkbox_text__3Go1u text-ellipsis"},[_vm._v(_vm._s(_vm.oTranslation.rememberMe)),_c('span',{staticClass:"checkbox-border"})])])]),_vm._v(" "),_c('a',{staticClass:"wil-float-right td-underline",attrs:{"href":"#"},on:{"click":function($event){$event.preventDefault();_vm.lostPassword($event);}}},[_vm._v(_vm._s(_vm.oTranslation.lostPassword))])]),_vm._v(" "),_c('button',{class:_vm.btnClass,attrs:{"type":"submit"},on:{"click":function($event){$event.preventDefault();_vm.submitLogin($event);}}},[_vm._v(_vm._s(_vm.oTranslation.login))]),_vm._v(" "),(_vm.toggleRegister)?_c('div',{staticClass:"mt-15"},[_vm._v(_vm._s(_vm.oTranslation.donthaveanaccount)),_c('a',{staticClass:"wil-float-right td-underline",attrs:{"href":"#"},on:{"click":function($event){$event.preventDefault();_vm.switchToRegister($event);}}},[_vm._v(_vm._s(_vm.oTranslation.register))])]):_vm._e()])},staticRenderFns: [function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('span',{staticClass:"checkbox_icon__28tFk bg-color-primary--checked-after bd-color-primary--checked"},[_c('i',{staticClass:"la la-check"}),_c('span',{staticClass:"checkbox-iconBg"})])}],
    data: function data(){
        return{
            oTranslation: WILCITY_I18,
            toggleRegister: WILCITY_REGISTER_LOGIN.toggleRegister == 1,
            username:'',
            password: '',
            isRemember: 'no',
            isDisable: false
        }
    },
    computed:{
        btnClass: function btnClass(){
            return {
                'wil-btn wil-btn--primary wil-btn--block wil-btn--md wil-btn--round': 1==1,
                'disable': !this.username.length || !this.password.length
            }
        },
        usernameClass: function usernameClass(){
            return {
                'field_module__1H6kT field_style2__2Znhe mb-15': 1==1,
                'active': this.username.length
            }
        },
        passwordClass: function passwordClass(){
            return {
                'field_module__1H6kT field_style2__2Znhe mb-15': 1==1,
                'active': this.password.length
            }
        }
    },
    methods: {
        keyboardLogin: function keyboardLogin(event){
            if ( event.keyCode == 13 && this.username.length && this.password.length ){
                this.submitLogin();
            }
        },
        lostPassword: function lostPassword(){
            this.$emit('switch-mode', 'lost-password');
        },
        switchToRegister: function switchToRegister(){
            this.$emit('switch-mode', 'register');
        },
        submitLogin: function submitLogin(){
            var this$1 = this;

            this.isDisable = true;
            this.$emit('line-loading', 'yes');

            jQuery.ajax({
                type: 'POST',
                url: WILOKE_GLOBAL.ajaxurl,
                data: {
                    username: this.username,
                    password: this.password,
                    isRemember: this.isRemember,
                    action: 'wilcity_login'
                },
                success: function (response) {
                    this$1.$emit('update-msg', response);
                    this$1.isDisable = false;
                    this$1.$emit('line-loading', 'no');
                }
            });
        }
    }
};

var WilokeRegister = {render: function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',[_c('div',{class:_vm.usernameClass},[_c('div',{staticClass:"field_wrap__Gv92k"},[_c('input',{directives:[{name:"model",rawName:"v-model",value:(_vm.username),expression:"username"}],staticClass:"field_field__3U_Rt",attrs:{"type":"text"},domProps:{"value":(_vm.username)},on:{"input":function($event){if($event.target.composing){ return; }_vm.username=$event.target.value;}}}),_c('span',{staticClass:"field_label__2eCP7 text-ellipsis required"},[_vm._v(_vm._s(_vm.oTranslation.username))]),_c('span',{staticClass:"bg-color-primary"})])]),_vm._v(" "),_c('div',{class:_vm.emailClass},[_c('div',{staticClass:"field_wrap__Gv92k"},[_c('input',{directives:[{name:"model",rawName:"v-model",value:(_vm.email),expression:"email"}],staticClass:"field_field__3U_Rt",attrs:{"type":"text"},domProps:{"value":(_vm.email)},on:{"input":function($event){if($event.target.composing){ return; }_vm.email=$event.target.value;}}}),_c('span',{staticClass:"field_label__2eCP7 text-ellipsis required"},[_vm._v(_vm._s(_vm.oTranslation.email))]),_c('span',{staticClass:"bg-color-primary"})])]),_vm._v(" "),_c('div',{class:_vm.passwordClass},[_c('div',{staticClass:"field_wrap__Gv92k"},[_c('input',{directives:[{name:"model",rawName:"v-model",value:(_vm.password),expression:"password"}],staticClass:"field_field__3U_Rt",attrs:{"type":"password"},domProps:{"value":(_vm.password)},on:{"input":function($event){if($event.target.composing){ return; }_vm.password=$event.target.value;}}}),_c('span',{staticClass:"field_label__2eCP7 text-ellipsis required"},[_vm._v(_vm._s(_vm.oTranslation.password))]),_c('span',{staticClass:"bg-color-primary"})])]),_vm._v(" "),(_vm.togglePrivacyPolicy==1)?_c('div',[_c('div',{staticClass:"o-hidden ws-nowrap"},[_c('div',{class:['checkbox_module__1K5IS mb-15', _vm.isAgreeToPrivacyPolicy == 'yes']},[_c('label',{staticClass:"checkbox_label__3cO9k"},[_c('input',{directives:[{name:"model",rawName:"v-model",value:(_vm.isAgreeToPrivacyPolicy),expression:"isAgreeToPrivacyPolicy"}],staticClass:"checkbox_inputcheck__1_X9Z",attrs:{"type":"checkbox","true-value":"yes","false-value":"no"},domProps:{"checked":Array.isArray(_vm.isAgreeToPrivacyPolicy)?_vm._i(_vm.isAgreeToPrivacyPolicy,null)>-1:_vm._q(_vm.isAgreeToPrivacyPolicy,"yes")},on:{"change":function($event){var $$a=_vm.isAgreeToPrivacyPolicy,$$el=$event.target,$$c=$$el.checked?("yes"):("no");if(Array.isArray($$a)){var $$v=null,$$i=_vm._i($$a,$$v);if($$el.checked){$$i<0&&(_vm.isAgreeToPrivacyPolicy=$$a.concat([$$v]));}else{$$i>-1&&(_vm.isAgreeToPrivacyPolicy=$$a.slice(0,$$i).concat($$a.slice($$i+1)));}}else{_vm.isAgreeToPrivacyPolicy=$$c;}}}}),_vm._v(" "),_vm._m(0)]),_vm._v(" "),_c('span',{staticClass:"checkbox_text__3Go1u text-ellipsis",domProps:{"innerHTML":_vm._s(_vm.privacyPolicyDesc)}},[_c('span',{staticClass:"checkbox-border"})])])])]):_vm._e(),_vm._v(" "),(_vm.toggleTermsAndConditionals==1)?_c('div',[_c('div',{staticClass:"o-hidden ws-nowrap"},[_c('div',{class:['checkbox_module__1K5IS mb-15', _vm.isAgreeToTermsAndConditionals == 'yes']},[_c('label',{staticClass:"checkbox_label__3cO9k"},[_c('input',{directives:[{name:"model",rawName:"v-model",value:(_vm.isAgreeToTermsAndConditionals),expression:"isAgreeToTermsAndConditionals"}],staticClass:"checkbox_inputcheck__1_X9Z",attrs:{"type":"checkbox","true-value":"yes","false-value":"no"},domProps:{"checked":Array.isArray(_vm.isAgreeToTermsAndConditionals)?_vm._i(_vm.isAgreeToTermsAndConditionals,null)>-1:_vm._q(_vm.isAgreeToTermsAndConditionals,"yes")},on:{"change":function($event){var $$a=_vm.isAgreeToTermsAndConditionals,$$el=$event.target,$$c=$$el.checked?("yes"):("no");if(Array.isArray($$a)){var $$v=null,$$i=_vm._i($$a,$$v);if($$el.checked){$$i<0&&(_vm.isAgreeToTermsAndConditionals=$$a.concat([$$v]));}else{$$i>-1&&(_vm.isAgreeToTermsAndConditionals=$$a.slice(0,$$i).concat($$a.slice($$i+1)));}}else{_vm.isAgreeToTermsAndConditionals=$$c;}}}}),_vm._v(" "),_vm._m(1)]),_vm._v(" "),_c('span',{staticClass:"checkbox_text__3Go1u text-ellipsis",domProps:{"innerHTML":_vm._s(_vm.termsAndConditionalDesc)}},[_c('span',{staticClass:"checkbox-border"})])])])]):_vm._e(),_vm._v(" "),_c('button',{class:_vm.buttonClass,attrs:{"type":"submit"},on:{"click":function($event){$event.preventDefault();_vm.registerAnAccount($event);}}},[_vm._v(_vm._s(_vm.oTranslation.register))]),_vm._v(" "),_c('div',{staticClass:"mt-15"},[_vm._v(_vm._s(_vm.oTranslation.ihaveanaccount)),_c('a',{staticClass:"wil-float-right td-underline",attrs:{"href":"#"},on:{"click":function($event){$event.preventDefault();_vm.switchMode($event);}}},[_vm._v(_vm._s(_vm.oTranslation.login))])])])},staticRenderFns: [function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('span',{staticClass:"checkbox_icon__28tFk bg-color-primary--checked-after bd-color-primary--checked"},[_c('i',{staticClass:"la la-check"}),_c('span',{staticClass:"checkbox-iconBg"})])},function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('span',{staticClass:"checkbox_icon__28tFk bg-color-primary--checked-after bd-color-primary--checked"},[_c('i',{staticClass:"la la-check"}),_c('span',{staticClass:"checkbox-iconBg"})])}],
    data: function data(){
        return{
            oTranslation: WILCITY_I18,
            email:'',
            username:'',
            password: '',
            isAgreeToPrivacyPolicy: WILCITY_REGISTER_LOGIN.togglePrivacyPolicy == 1 ? 'no' : 'yes',
            isAgreeToTermsAndConditionals: WILCITY_REGISTER_LOGIN.toggleTermsAndConditionals == 1 ? 'no' : 'yes',
            togglePrivacyPolicy: WILCITY_REGISTER_LOGIN.togglePrivacyPolicy,
            privacyPolicyDesc: WILCITY_REGISTER_LOGIN.privacyPolicyDesc,
            toggleTermsAndConditionals: WILCITY_REGISTER_LOGIN.toggleTermsAndConditionals,
            termsAndConditionalDesc:WILCITY_REGISTER_LOGIN.termsAndConditionals,
            isDisable: false
        }
    },
    computed: {
        buttonClass: function buttonClass(){
            return {
                'wil-btn wil-btn--primary wil-btn--block wil-btn--md wil-btn--round': 1==1,
                'disable': this.isDisable || this.isAgreeToPrivacyPolicy == 'no' || this.isAgreeToTermsAndConditionals == 'no' || !this.password.length || !this.username.length || !WilCityHelpers.validEmail(this.email)
            }
        },
        usernameClass: function usernameClass(){
            return {
                'field_module__1H6kT field_style2__2Znhe mb-15': 1==1,
                'active': this.username.length
            }
        },
        emailClass: function emailClass(){
            return {
                'field_module__1H6kT field_style2__2Znhe mb-15': 1==1,
                'active': this.email.length,
                'error': this.email.length && !WilCityHelpers.validEmail(this.email)
            }
        },
        passwordClass: function passwordClass(){
            return {
                'field_module__1H6kT field_style2__2Znhe mb-15': 1==1,
                'active': this.password.length
            }
        }
    },
    methods: {
        switchMode: function switchMode(){
            this.$emit('switch-mode', 'login');
        },
        registerAnAccount: function registerAnAccount(){
            var this$1 = this;

            this.isDisable = true;
            this.$emit('line-loading', 'yes');

            jQuery.ajax({
                type: 'POST',
                url: WILOKE_GLOBAL.ajaxurl,
                data: {
                    email: this.email,
                    username: this.username,
                    password: this.password,
                    isAgreeToTermsAndConditionals: this.isAgreeToTermsAndConditionals,
                    isAgreeToPrivacyPolicy: this.isAgreeToPrivacyPolicy,
                    action: 'wilcity_register'
                },
                success: function (response) {
                    this$1.$emit('update-msg', response);
                    this$1.isDisable = false;
                    this$1.$emit('line-loading', 'no');
                }
            });
        }
    }
};

var WilokeLostPassword = {render: function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',[_c('div',{class:_vm.usernameClass},[_c('div',{staticClass:"field_wrap__Gv92k"},[_c('input',{directives:[{name:"model",rawName:"v-model",value:(_vm.username),expression:"username"}],staticClass:"field_field__3U_Rt",attrs:{"type":"text"},domProps:{"value":(_vm.username)},on:{"input":function($event){if($event.target.composing){ return; }_vm.username=$event.target.value;}}}),_c('span',{staticClass:"field_label__2eCP7 text-ellipsis required"},[_vm._v(_vm._s(_vm.oTranslation.usernameOrEmail))]),_c('span',{staticClass:"bg-color-primary"})])]),_vm._v(" "),_c('button',{class:['wil-btn wil-btn--primary wil-btn--block wil-btn--md wil-btn--round', _vm.isDisable],attrs:{"type":"submit"},on:{"click":function($event){$event.preventDefault();_vm.resetPassword($event);}}},[_vm._v(_vm._s(_vm.oTranslation.resetPassword))]),_vm._v(" "),_c('div',{staticClass:"mt-15 mb-15 wil-float-right"},[_c('a',{attrs:{"href":"#"},on:{"click":function($event){$event.preventDefault();_vm.switchToLogin($event);}}},[_vm._v(_vm._s(_vm.oTranslation.cancel))])])])},staticRenderFns: [],
    data: function data(){
        return{
            username: '',
            oTranslation: WILCITY_I18
        }
    },
    computed: {
        usernameClass: function usernameClass(){
            return {
                'field_module__1H6kT field_style2__2Znhe mb-15': 1==1,
                'active': this.username.length
            }
        }
    },
    methods: {
        switchToLogin: function switchToLogin(){
            this.$emit('switch-mode', 'login');
        },
        resetPassword: function resetPassword(){
            var this$1 = this;

            this.isDisable = true;
            this.$emit('line-loading', 'yes');

            jQuery.ajax({
                type: 'POST',
                url: WILOKE_GLOBAL.ajaxurl,
                data: {
                    username: this.username,
                    action: 'wilcity_reset_password'
                },
                success: function (response) {
                    this$1.$emit('update-msg', response);
                    this$1.isDisable = false;
                    this$1.$emit('line-loading', 'no');
                }
            });
        }
    }
};

var LoginRegisterPopup = {render: function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('wiloke-popup',{attrs:{"popup-id":"wilcity-signin-popup","popup-title":_vm.popupTitle,"icon":_vm.popupIcon,"wrapper-class":"popup_module__3M-0- pos-f-full popup_sm__Rc24D popup_mobile-full__1hyc4"},on:{"on-close-popup":_vm.closePopup}},[_c('div',{attrs:{"slot":"body"},slot:"body"},[_c('wiloke-message',{directives:[{name:"show",rawName:"v-show",value:(_vm.msg.length),expression:"msg.length"}],attrs:{"msg":_vm.msg,"has-remove":"false","icon":_vm.msgIcon,"status":_vm.msgStatus}}),_vm._v(" "),_c('keep-alive',[_c(_vm.mode,{directives:[{name:"show",rawName:"v-show",value:(!_vm.isHideComponent),expression:"!isHideComponent"}],tag:"component",on:{"switch-mode":_vm.onUpdateMode,"line-loading":_vm.updateLoadingStatus,"update-msg":_vm.updateMsg}})],1)],1)])},staticRenderFns: [],
    data: function data(){
        return {
            oIcon: {
                login: 'la la la-unlock',
                register: 'la la la-user-plus',
                'lost-password': 'la la la-refresh'
            },
            oTitle: {
                login: WILCITY_I18.login,
                register: WILCITY_I18.register,
                'lost-password': WILCITY_I18.resetPassword
            },
            msg: '',
            msgStatus: '',
            msgIcon: '',
            isHideComponent: false,
            oTranslation: WILCITY_I18
        }
    },
    computed: {
        mode: function mode(){
            return this.$store.getters.getDefaultComponentOfRegisterLogin;
        },
        popupTitle: function popupTitle(){
            return this.oTitle[this.mode];
        },
        popupIcon: function popupIcon(){
            return this.oIcon[this.mode];
        }
    },
    components:{
        WilokePopup: WilokePopup,
        WilokeMessage: WilokeMessage,
        'register': WilokeRegister,
        'login': WilokeLogin,
        'lost-password': WilokeLostPassword
    },
    methods: {
        closePopup: function closePopup(){
            this.$store.dispatch('closeRegisterLoginPopup');
        },
        onUpdateMode: function onUpdateMode(mode){
            if ( mode == 'register' ){
                this.$store.dispatch('setRegisterAsDefaultComponentOfRegisterLoginPopup');
            }else if ( mode == 'login' ){
                this.$store.dispatch('setLoginAsDefaultComponentOfRegisterLoginPopup');
            }else{
                this.$store.dispatch('setLostPasswordAsDefaultComponentOfRegisterLoginPopup');
            }
        },
        updateMsg: function updateMsg(response){
            if ( response.success ){
                this.msgStatus = 'success';
                this.msgIcon = 'la la-smile-o';
                if ( typeof response.data.isFocusHideForm !== 'undefined' ){
                    this.isHideComponent = true;
                }

                if ( typeof response.data.redirectTo !== 'undefined' && response.data.redirectTo.length ){
                    this.isHideComponent = true;
                    setTimeout(function (){
                        if ( response.data.redirectTo == 'self' ){
                            location.reload();
                        }else{
                           window.location.href = decodeURIComponent(response.data.redirectTo);
                        }
                    }, 3000);
                }
            }else{
                this.msgStatus = 'error';
                this.msgIcon = 'la la-frown-o';
                this.msgStatus = 'danger';
            }
            this.msg = response.data.msg;
        },
        updateLoadingStatus: function updateLoadingStatus(status){
            this.$emit('line-loading', status);
        }
    }
};

var LoginBtn = {render: function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('a',{staticClass:"wil-btn wil-btn--primary2 wil-btn--round wil-btn--xs",attrs:{"id":"wilcity-login-btn","href":"#"},domProps:{"innerHTML":_vm._s(_vm.btnName)},on:{"click":function($event){$event.preventDefault();_vm.onOpenLoginPopup($event);}}})},staticRenderFns: [],
       props: ['btnName'],
       methods:{
            onOpenLoginPopup: function onOpenLoginPopup(){
				this.$store.dispatch('setLoginAsDefaultComponentOfRegisterLoginPopup');
				this.$store.dispatch('openRegisterLoginPopup');
			},
       }
    };

var RegisterBtn = {render: function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('a',{staticClass:"wil-btn wil-btn--secondary wil-btn--round wil-btn--xs",attrs:{"id":"wilcity-register-btn","href":"#"},domProps:{"innerHTML":_vm._s(_vm.btnName)},on:{"click":function($event){$event.preventDefault();_vm.onOpenRegisterPopup($event);}}})},staticRenderFns: [],
       props: ['btnName'],
       methods:{
            onOpenRegisterPopup: function onOpenRegisterPopup(){
                this.$store.dispatch('setRegisterAsDefaultComponentOfRegisterLoginPopup');
				this.$store.dispatch('openRegisterLoginPopup');
			}
       }
    };

var WilokeSingleSidebar = {render: function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return (_vm.sidebars)?_c('ul',{staticClass:"list_module__1eis9 list-none md-hide"},_vm._l((_vm.sidebars),function(oSidebar,order){return _c('li',{class:_vm.liClass(oSidebar)},[_c('a',{staticClass:"list_link__2rDA1 text-ellipsis color-primary--hover",attrs:{"href":_vm.to(oSidebar)},on:{"click":function($event){$event.preventDefault();_vm.navigateNewTab(oSidebar, _vm.index);}}},[_c('span',{staticClass:"list_icon__2YpTp"},[_c('i',{class:oSidebar.icon})]),_c('span',{staticClass:"list_text__35R07"},[_vm._v(_vm._s(oSidebar.name))])])])})):_vm._e()},staticRenderFns: [],
    data: function data(){
        return {
            sidebars: WILCITY_SINGLE_LISTING.sidebars,
            activating: 'general'
        }
    },
    methods: {
        navigateNewTab: function navigateNewTab(oSidebar){
            this.activating = oSidebar.id;
            this.$store.commit('updateCurrentSettingTab', this.activating);
        },
        to: function to(oSidebar){
            return '#'+oSidebar.id;
        },
        liClass: function liClass(oSidebar){
            if ( this.activating === oSidebar.id ){
                return 'active list_item__3YghP';
            }

            return 'list_item__3YghP';
        }
    },
    beforeAmount: function beforeAmount(){

    }
};

var WilokeIcon = {render: function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{class:_vm.parseWrapperClass},[_c('div',{staticClass:"field_wrap__Gv92k"},[_c('select',{directives:[{name:"model",rawName:"v-model",value:(_vm.selectedIcon),expression:"selectedIcon"}],staticClass:"wilcity-select-2",on:{"change":function($event){var $$selectedVal = Array.prototype.filter.call($event.target.options,function(o){return o.selected}).map(function(o){var val = "_value" in o ? o._value : o.value;return val}); _vm.selectedIcon=$event.target.multiple ? $$selectedVal : $$selectedVal[0];}}},_vm._l((_vm.aOptions),function(oOption){return _c('option',{domProps:{"value":oOption.icon}},[_c('i',{class:oOption.icon}),_vm._v(" "+_vm._s(oOption.name))])})),_vm._v(" "),_c('span',{staticClass:"field_label__2eCP7 text-ellipsis",class:{required: _vm.settings.isRequired=='yes'}},[_vm._v(_vm._s(_vm.settings.label))]),_vm._v(" "),_c('span',{staticClass:"bg-color-primary"})]),_vm._v(" "),(_vm.settings.errMsg)?_c('span',{staticClass:"field_message__3Z6FX color-quaternary"},[_vm._v(_vm._s(_vm.settings.errMsg))]):_vm._e()])},staticRenderFns: [],
    data: function data(){
        return {
            selectedIcon: this.value,
            aOptions: [],
            canRunSelect2: false,
            $select2: null
        }
    },
    props: {
        settings: {
            type: Object,
            default: ''
        },
        value: {
            type: String,
            default: 'la la-edit'
        },
        wrapperClass: {
            type: String,
            default: 'field_module__1H6kT field_style2__2Znhe mb-15'
        }
    },
    computed: {
        parseWrapperClass: function parseWrapperClass(){
            return this.selectedIcon.length ? this.wrapperClass + ' active' : this.wrapperClass;
        }
    },
    watch: {
        value: function(newVal){
            if ( this.$select2 ){
                this.$select2.val(newVal).trigger('change');
            }
        },
        selectedIcon: function(){
            this.$emit('changedIcon', this.selectedIcon, this.settings);
        }
    },
    methods: {
        getIcons: function getIcons(){
            var this$1 = this;

            if ( this.$store.getters.getIcons ){
                this.aOptions = this.$store.getters.getIcons;
                this.canRunSelect2 = true;
            }else{
                jQuery.ajax({
                    type: 'POST',
                    url: WILOKE_GLOBAL.ajaxurl,
                    data: {
                        action: 'wilcity_fetch_icons'
                    },
                    success: function (response) {
                        this$1.$store.commit('updateIcons', response.data);
                        this$1.aOptions = response.data;
                        this$1.canRunSelect2 = true;
                    }
                });
            }
        },
        isRequired: function isRequired(pattern){
            if ( pattern == 'yes' || pattern === 'true' || pattern === true ){
                return true;
            }

            return false;
        },
        changed: function changed(){
            this.settings.value = this.selectedIcon;
            this.$emit('inputChanged', this.selectedIcon, this.settings);
        },
        init: function init(){
            var this$1 = this;

            this.getIcons();

            var run = setInterval(function (){
                if ( this$1.canRunSelect2 ){
                    this$1.$select2 = jQuery(this$1.$el).find('.wilcity-select-2').select2({
                        allowClear: true,
                        placeholder: ''
                    }).on('select2:select', function (event){
                        this$1.selectedIcon = event.params.data.id;
                    });

                    if ( this$1.value.length ){
                        this$1.selectedIcon = this$1.value;
                        this$1.$select2.val(this$1.value).trigger('change');
                    }
                    clearInterval(run);
                }
            }, 100);
        }
    },
    mounted: function mounted(){
        this.init();
    }
};

var WilokeSingleGeneral = {render: function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{class:_vm.wrapperClass},[_c('div',{staticClass:"content-box_body__3tSRB"},[_c('div',{staticClass:"promo-item_module__24ZhT mb-15"},[_c('div',{staticClass:"promo-item_group__2ZJhC"},[_c('h3',{staticClass:"promo-item_title__3hfHG"},[_vm._v(_vm._s(_vm.oTranslation.layout))]),_vm._v(" "),_c('p',{staticClass:"promo-item_description__2nc26"},[_vm._v(_vm._s(_vm.oTranslation.layoutDesc))])])]),_vm._v(" "),_c('div',{staticClass:"d-inline-block"},[_c('div',{staticClass:"checkbox_module__1K5IS checkbox_radio__1pYzR js-checkbox"},[_c('label',{staticClass:"checkbox_label__3cO9k"},[_c('input',{directives:[{name:"model",rawName:"v-model",value:(_vm.sidebarPosition),expression:"sidebarPosition"}],staticClass:"checkbox_inputcheck__1_X9Z",attrs:{"type":"radio","value":"left"},domProps:{"checked":_vm._q(_vm.sidebarPosition,"left")},on:{"change":[function($event){_vm.sidebarPosition="left";},_vm.changedSettings]}}),_vm._m(0),_c('span',{staticClass:"checkbox_text__3Go1u text-ellipsis"},[_vm._v(_vm._s(_vm.oTranslation.leftSidebar)),_c('span',{staticClass:"checkbox-border"})])])])]),_vm._v(" "),_c('div',{staticClass:"d-inline-block mr-20"},[_c('div',{staticClass:"checkbox_module__1K5IS checkbox_radio__1pYzR js-checkbox"},[_c('label',{staticClass:"checkbox_label__3cO9k"},[_c('input',{directives:[{name:"model",rawName:"v-model",value:(_vm.sidebarPosition),expression:"sidebarPosition"}],staticClass:"checkbox_inputcheck__1_X9Z",attrs:{"type":"radio","value":"right"},domProps:{"checked":_vm._q(_vm.sidebarPosition,"right")},on:{"change":[function($event){_vm.sidebarPosition="right";},_vm.changedSettings]}}),_vm._m(1),_c('span',{staticClass:"checkbox_text__3Go1u text-ellipsis"},[_vm._v(_vm._s(_vm.oTranslation.rightSidebar)),_c('span',{staticClass:"checkbox-border"})])])])]),_vm._v(" "),_c('div',{staticClass:"wil-divider mt-20 mb-20 bg-color-gray-1"}),_vm._v(" "),_c('div',{staticClass:"promo-item_module__24ZhT mb-15"},[_c('div',{staticClass:"promo-item_group__2ZJhC"},[_c('h3',{staticClass:"promo-item_title__3hfHG",domProps:{"innerHTML":_vm._s(_vm.oTranslation.addButton)}}),_vm._v(" "),_c('p',{staticClass:"promo-item_description__2nc26",domProps:{"innerHTML":_vm._s(_vm.oTranslation.addButtonDesc)}})])]),_vm._v(" "),_c('div',{staticClass:"row"},[_c('div',{staticClass:"col-sm-6"},[_c('wiloke-input',{attrs:{"settings":{value: _vm.oButtonSettings.websiteLink, label: _vm.oTranslation.websiteLink}},on:{"inputChanged":_vm.updateButtonLink}})],1),_vm._v(" "),_c('div',{staticClass:"col-xs-4 col-sm-2"},[_c('wiloke-icon',{attrs:{"settings":{label: _vm.oTranslation.icon},"value":_vm.oButtonSettings.icon},on:{"changedIcon":_vm.updateButtonIcon}})],1),_vm._v(" "),_c('div',{staticClass:"col-xs-6 col-sm-4"},[_c('wiloke-input',{attrs:{"settings":{value: _vm.oButtonSettings.buttonName, label: _vm.oTranslation.buttonName}},on:{"inputChanged":_vm.updateButtonName}})],1)])])])},staticRenderFns: [function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('span',{staticClass:"checkbox_icon__28tFk bg-color-primary--checked-after bd-color-primary--checked"},[_c('i',{staticClass:"la la-check"}),_c('span',{staticClass:"checkbox-iconBg"})])},function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('span',{staticClass:"checkbox_icon__28tFk bg-color-primary--checked-after bd-color-primary--checked"},[_c('i',{staticClass:"la la-check"}),_c('span',{staticClass:"checkbox-iconBg"})])}],
    data: function data(){
        return{
            key: 'general',
            oTranslation: WILCITY_SINGLE_LISTING.defines,
            sidebarPosition: 'right',
            postID: WILOKE_GLOBAL.postID,
            isAjaxSaving: null,
            msg:'',
            msgStatus: '',
            msgIcon: '',
            oButtonSettings: {
                icon: '',
                websiteLink: '',
                buttonName: ''
            },
            ajaxSaveBtn: null
        }
    },
    computed: {
        wrapperClass: function wrapperClass(){
            var cl = 'wilcity-listing-settings';
            if ( this.$store.getters.getCurrentSettingTab == this.key ){
                return cl + ' active';
            }else{
                return cl + ' hidden';
            }
        }
    },
    watch: {
        oButtonSettings: {
            handler: function(){
                var this$1 = this;

                if ( this.ajaxSaveBtn !== null && this.ajaxSaveBtn.status !== 200 ){
                    this.ajaxSaveBtn.abort();
                }

                this.ajaxSaveBtn = jQuery.ajax({
                    type: 'POST',
                    url: WILOKE_GLOBAL.ajaxurl,
                    data: {
                        action: 'wilcity_save_page_button',
                        aButtons: this.oButtonSettings,
                        postID: WILOKE_GLOBAL.postID
                    },
                    success: (function (response){
                        if ( response.success ){
                            this$1.msgStatus = 'success';
                            this$1.msgIcon = 'la la-smile-o';
                        }else{
                            this$1.msgStatus = 'danger';
                            this$1.msgIcon = 'la la-frown-o';
                        }
                        this$1.msg = response.data.msg;

                        this$1.$parent.$emit('onPrintMsg', {msgIcon: this$1.msgIcon, msg: this$1.msg, msgStatus: this$1.msgStatus});
                    })
                });
            },
            deep: true
        }
    },
    methods: {
        updateButtonIcon: function updateButtonIcon(newVal){
            this.oButtonSettings.icon = newVal;
        },
        updateButtonLink: function updateButtonLink(newVal){
            this.oButtonSettings.websiteLink = newVal;
        },
        updateButtonName: function updateButtonName(newVal){
            this.oButtonSettings.buttonName = newVal;
        },
        fetchButtonSettings: function fetchButtonSettings(){
            var this$1 = this;

            jQuery.ajax({
                type: 'POST',
                url: WILOKE_GLOBAL.ajaxurl,
                data: {
                    action: 'wilcity_button_settings',
                    postID: WILOKE_GLOBAL.postID
                },
                success: (function (response){
                    this$1.oButtonSettings = response.data;
                })
            });
        },
        setDefault: function setDefault(){
            if ( WILCITY_SINGLE_LISTING_SETTINGS.general.sidebarPosition != '' ){
                this.sidebarPosition = WILCITY_SINGLE_LISTING_SETTINGS.general.sidebarPosition;

            }
        },
        changedSettings: function changedSettings(){
            var this$1 = this;

            if ( this.isAjaxSaving !== null && this.isAjaxSaving.status !== 200 ){
                this.isAjaxSaving.abort();
            }

            var self = this;

            this.isAjaxSaving = jQuery.ajax({
                type: 'POST',
                url: WILOKE_GLOBAL.ajaxurl,
                data: {
                    action: 'wilcity_listing_settings',
                    mode: 'general',
                    value: {
                        sidebarPosition: self.sidebarPosition
                    },
                    postID: WILOKE_GLOBAL.postID
                },
                success: (function (response){
                    if ( response.success ){
                        this$1.msgStatus = 'success';
                        this$1.msgIcon = 'la la-smile-o';
                    }else{
                        this$1.msgStatus = 'danger';
                        this$1.msgIcon = 'la la-frown-o';
                    }
                    this$1.msg = response.data.msg;

                    this$1.$parent.$emit('onPrintMsg', {msgIcon: this$1.msgIcon, msg: this$1.msg, msgStatus: this$1.msgStatus});
                })
            });
        }
    },
    components: {
        WilokeInput: WilokeInput,
        WilokeIcon: WilokeIcon
    },
    beforeMount: function beforeMount(){
        this.setDefault();
    },
    mounted: function mounted(){
        this.fetchButtonSettings();
    }
};

var WilokeSingleEditNavigation = {render: function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{class:_vm.wrapperClass},[_c('div',{staticClass:"content-box_body__3tSRB"},[_c('div',{staticClass:"promo-item_module__24ZhT mb-30 mb-sm-20"},[_c('div',{staticClass:"promo-item_group__2ZJhC"},[_c('h3',{staticClass:"promo-item_title__3hfHG"},[_vm._v(_vm._s(_vm.oTranslation.navigation))]),_vm._v(" "),_c('p',{staticClass:"promo-item_description__2nc26"},[_vm._v(_vm._s(_vm.oTranslation.navigationDesc))])])]),_vm._v(" "),_c('wiloke-checkbox',{attrs:{"settings":{value: _vm.isUsedDefaultNav, label:_vm.oTranslation.isUseDefaultLabel},"wrapper-class":'checkbox_module__1K5IS checkbox_toggle__vd6vd sort-box_toggle__1tHcf pos-a-center-right'},on:{"checkboxChanged":_vm.toggleDefaultNavigation}}),_vm._v(" "),_c('div',{directives:[{name:"show",rawName:"v-show",value:(_vm.isUsedDefaultNav != 'yes'),expression:"isUsedDefaultNav != 'yes'"}],staticClass:"sort-box_module__1aAtu"},[_c('div',{staticClass:"sort-box_item__1156W"},[_c('h3',{staticClass:"sort-box_text__1E0Ln"},[_vm._v(_vm._s(_vm.oFixed.home.name))])]),_vm._v(" "),_c('draggable',{staticClass:"dragArea",on:{"change":_vm.orderChanged},model:{value:(_vm.oDraggable),callback:function ($$v) {_vm.oDraggable=$$v;},expression:"oDraggable"}},_vm._l((_vm.oDraggable),function(oItem){return _c('div',{staticClass:"sort-box_item__1156W"},[_c('div',{staticClass:"pos-r"},[_c('span',{staticClass:"sort-box_iconSort__1cDhT"},[_c('i',{staticClass:"la la-bars"})]),_vm._v(" "),_c('h3',{staticClass:"sort-box_text__1E0Ln"},[_vm._v(_vm._s(oItem.name))]),_vm._v(" "),_c('div',{staticClass:"checkbox_module__1K5IS checkbox_toggle__vd6vd sort-box_toggle__1tHcf pos-a-center-right"},[_c('label',{staticClass:"checkbox_label__3cO9k"},[_c('input',{directives:[{name:"model",rawName:"v-model",value:(oItem.status),expression:"oItem.status"}],staticClass:"checkbox_inputcheck__1_X9Z",attrs:{"id":_vm.checkboxID(oItem.key, 'status'),"type":"checkbox","true-value":"yes","false-value":"no"},domProps:{"checked":Array.isArray(oItem.status)?_vm._i(oItem.status,null)>-1:_vm._q(oItem.status,"yes")},on:{"change":[function($event){var $$a=oItem.status,$$el=$event.target,$$c=$$el.checked?("yes"):("no");if(Array.isArray($$a)){var $$v=null,$$i=_vm._i($$a,$$v);if($$el.checked){$$i<0&&(oItem.status=$$a.concat([$$v]));}else{$$i>-1&&(oItem.status=$$a.slice(0,$$i).concat($$a.slice($$i+1)));}}else{_vm.$set(oItem, "status", $$c);}},_vm.orderChanged]}}),_vm._v(" "),_c('span',{staticClass:"checkbox_icon__28tFk bg-color-primary--checked-after bd-color-primary--checked"},[_c('i',{staticClass:"la la-check"}),_c('span',{staticClass:"checkbox-iconBg"})])])])]),_vm._v(" "),_c('div',{directives:[{name:"show",rawName:"v-show",value:(oItem.status=='yes'),expression:"oItem.status=='yes'"}],staticClass:"content-box_module__333d9 mb-0"},[_c('div',{staticClass:"content-box_body__3tSRB"},[_c('div',{staticClass:"checkbox_module__1K5IS mb-0"},[_c('label',{staticClass:"checkbox_label__3cO9k"},[_c('input',{directives:[{name:"model",rawName:"v-model",value:(oItem.isShowOnHome),expression:"oItem.isShowOnHome"}],staticClass:"checkbox_inputcheck__1_X9Z",attrs:{"id":_vm.checkboxID(oItem.key, 'isShowOnHome'),"type":"checkbox","true-value":"yes","false-value":"no"},domProps:{"checked":Array.isArray(oItem.isShowOnHome)?_vm._i(oItem.isShowOnHome,null)>-1:_vm._q(oItem.isShowOnHome,"yes")},on:{"change":[function($event){var $$a=oItem.isShowOnHome,$$el=$event.target,$$c=$$el.checked?("yes"):("no");if(Array.isArray($$a)){var $$v=null,$$i=_vm._i($$a,$$v);if($$el.checked){$$i<0&&(oItem.isShowOnHome=$$a.concat([$$v]));}else{$$i>-1&&(oItem.isShowOnHome=$$a.slice(0,$$i).concat($$a.slice($$i+1)));}}else{_vm.$set(oItem, "isShowOnHome", $$c);}},_vm.orderChanged]}}),_vm._v(" "),_c('span',{staticClass:"checkbox_icon__28tFk bg-color-primary--checked-after bd-color-primary--checked"},[_c('i',{staticClass:"la la-check"}),_vm._v(" "),_c('span',{staticClass:"checkbox-iconBg"})]),_vm._v(" "),_c('span',{staticClass:"checkbox_text__3Go1u text-ellipsis"},[_vm._v(" "+_vm._s(_vm.I18.isShowOnHome)+" "),_c('span',{staticClass:"checkbox-border"})])])])])])])})),_vm._v(" "),_c('div',{staticClass:"sort-box_item__1156W"},[_c('h3',{staticClass:"sort-box_text__1E0Ln"},[_vm._v(_vm._s(_vm.oFixed.insights.name))])]),_vm._v(" "),_c('div',{staticClass:"sort-box_item__1156W"},[_c('h3',{staticClass:"sort-box_text__1E0Ln"},[_vm._v(_vm._s(_vm.oFixed.settings.name))])])],1)],1)])},staticRenderFns: [],
    data: function data(){
        return{
            key: 'edit-navigation',
            I18: WILCITY_I18,
            oTranslation: WILCITY_SINGLE_LISTING.defines,
            oFixed: WILCITY_SINGLE_LISTING_SETTINGS.navigation.items.fixed,
            isUsedDefaultNav: WILCITY_SINGLE_LISTING_SETTINGS.navigation.isUsedDefaultNav,
            oDraggable: Object.values(WILCITY_SINGLE_LISTING_SETTINGS.navigation.items.draggable),
            isAjaxSaving: null,
            msg:'',
            msgStatus: '',
            msgIcon: ''
        }
    },
    components: {
        WilokeCheckbox: WilokeCheckbox
    },
    computed: {
        wrapperClass: function wrapperClass(){
            var cl = 'wilcity-listing-settings content-box_module__333d9';
            if ( this.$store.getters.getCurrentSettingTab == this.key ){
                return cl + ' active';
            }else{
                return cl + ' hidden';
            }
        }
    },
    methods: {
        commitMsg: function commitMsg(response){
             if ( response.success ){
                    this.msgStatus = 'success';
                    this.msgIcon = 'la la-smile-o';
                }else{
                    this.msgStatus = 'danger';
                    this.msgIcon = 'la la-frown-o';
                }
                this.msg = response.data.msg;

                this.$parent.$emit('onPrintMsg', {msgIcon: this.msgIcon, msg: this.msg, msgStatus: this.msgStatus});
        },
        orderChanged: function orderChanged(){
            var this$1 = this;

            if ( this.isAjaxSaving !== null && this.isAjaxSaving.status !== 200 ){
                this.isAjaxSaving.abort();
            }

            this.isAjaxSaving = jQuery.ajax({
                type: 'POST',
                url: WILOKE_GLOBAL.ajaxurl,
                data: {
                    action: 'wilcity_listing_settings',
                    mode: 'navigation',
                    value: this.oDraggable,
                    postID: WILOKE_GLOBAL.postID
                },
                success: function (response) { return this$1.commitMsg(response); }
            });
        },
        checkboxID: function checkboxID(key, prefix){
            return prefix + '_' + key;
        },
        toggleDefaultNavigation: function toggleDefaultNavigation(val){
            var this$1 = this;

            this.isUsedDefaultNav = val;
            jQuery.ajax({
                type: 'POST',
                url: WILOKE_GLOBAL.ajaxurl,
                data: {
                    action: 'wilcity_listing_set_navigation_mode',
                    isUsedDefault: val,
                    postID: WILOKE_GLOBAL.postID
                },
                success: function (response) { return this$1.commitMsg(response); }
            });
        },
    },
    mounted: function mounted(){

    }
};

var WilokeSingleEditSidebar = {render: function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{class:_vm.wrapperClass},[_c('div',{staticClass:"content-box_body__3tSRB"},[_c('div',{staticClass:"promo-item_module__24ZhT mb-30 mb-sm-20"},[_c('div',{staticClass:"promo-item_group__2ZJhC"},[_c('h3',{staticClass:"promo-item_title__3hfHG"},[_vm._v(_vm._s(_vm.oTranslation.sidebar))]),_vm._v(" "),_c('p',{staticClass:"promo-item_description__2nc26"},[_vm._v(_vm._s(_vm.oTranslation.sidebarDesc))])])]),_vm._v(" "),_c('wiloke-checkbox',{attrs:{"settings":{value: _vm.isUsedDefaultSidebar, label:_vm.oTranslation.isUseDefaultLabel},"wrapper-class":'checkbox_module__1K5IS checkbox_toggle__vd6vd sort-box_toggle__1tHcf pos-a-center-right'},on:{"checkboxChanged":_vm.toggleDefaultSidebarChanged}}),_vm._v(" "),_c('div',{directives:[{name:"show",rawName:"v-show",value:(_vm.isUsedDefaultSidebar!='yes'),expression:"isUsedDefaultSidebar!='yes'"}],staticClass:"sort-box_module__1aAtu"},[_c('draggable',{staticClass:"dragArea",on:{"change":_vm.orderChanged},model:{value:(_vm.oCustom),callback:function ($$v) {_vm.oCustom=$$v;},expression:"oCustom"}},_vm._l((_vm.oCustom),function(oItem){return _c('div',{staticClass:"sort-box_item__1156W js-sortItem"},[_c('span',{staticClass:"sort-box_iconSort__1cDhT"},[_c('i',{staticClass:"la la-bars"})]),_vm._v(" "),_c('h3',{staticClass:"sort-box_text__1E0Ln"},[_vm._v(_vm._s(oItem.name))]),_vm._v(" "),_c('div',{staticClass:"checkbox_module__1K5IS checkbox_toggle__vd6vd sort-box_toggle__1tHcf pos-a-center-right"},[_c('label',{staticClass:"checkbox_label__3cO9k"},[_c('input',{directives:[{name:"model",rawName:"v-model",value:(oItem.status),expression:"oItem.status"}],staticClass:"checkbox_inputcheck__1_X9Z",attrs:{"id":oItem.key,"type":"checkbox","true-value":"yes","false-value":"no"},domProps:{"checked":Array.isArray(oItem.status)?_vm._i(oItem.status,null)>-1:_vm._q(oItem.status,"yes")},on:{"change":[function($event){var $$a=oItem.status,$$el=$event.target,$$c=$$el.checked?("yes"):("no");if(Array.isArray($$a)){var $$v=null,$$i=_vm._i($$a,$$v);if($$el.checked){$$i<0&&(oItem.status=$$a.concat([$$v]));}else{$$i>-1&&(oItem.status=$$a.slice(0,$$i).concat($$a.slice($$i+1)));}}else{_vm.$set(oItem, "status", $$c);}},_vm.orderChanged]}}),_vm._v(" "),_c('span',{staticClass:"checkbox_icon__28tFk bg-color-primary--checked-after bd-color-primary--checked"},[_c('i',{staticClass:"la la-check"}),_c('span',{staticClass:"checkbox-iconBg"})])])])])}))],1)],1)])},staticRenderFns: [],
    data: function data(){
        return{
            key: 'edit-sidebar',
            oCustom: Object.values(WILCITY_SINGLE_LISTING_SETTINGS.sidebar.settings),
            oTranslation: WILCITY_SINGLE_LISTING.defines,
            xhr: null,
            isUsedDefaultSidebar: WILCITY_SINGLE_LISTING_SETTINGS.sidebar.isUsedDefaultSidebar,
            msg:'',
            msgStatus: '',
            msgIcon: ''
        }
    },
    components:{
        WilokeCheckbox: WilokeCheckbox
    },
    computed: {
        wrapperClass: function wrapperClass(){
            var cl = 'wilcity-listing-settings content-box_module__333d9';
            if ( this.$store.getters.getCurrentSettingTab == this.key ){
                return cl + ' active';
            }else{
                return cl + ' hidden';
            }
        }
    },
    methods: {
        saveData: function saveData(mode, oData){
            var this$1 = this;

            if ( this.xhr !== null ){
                if ( this.xhr.status !== 200 ){
                    this.xhr.abort();
                }
            }

            this.xhr = jQuery.ajax({
                type: 'POST',
                url: WILOKE_GLOBAL.ajaxurl,
                data: {
                    action: 'wilcity_save_single_sidebar_settings',
                    isUsedDefaultSidebar: mode,
                    data: oData,
                    postID: WILOKE_GLOBAL.postID
                },
                success: (function (response){
                    if ( response.success ){
                        this$1.msgStatus = 'success';
                        this$1.msgIcon = 'la la-smile-o';
                    }else{
                        this$1.msgStatus = 'danger';
                        this$1.msgIcon = 'la la-frown-o';
                    }
                    this$1.msg = response.data.msg;

                    this$1.$parent.$emit('onPrintMsg', {msgIcon: this$1.msgIcon, msg: this$1.msg, msgStatus: this$1.msgStatus});
                })
            });
        },
        orderChanged: function orderChanged(){
            this.saveData('no', this.oCustom);
        },
        toggleDefaultSidebarChanged: function toggleDefaultSidebarChanged(newVal){
            this.isUsedDefaultSidebar = newVal;
            this.saveData(newVal, this.oCustom);
        }
    },
    mounted: function mounted(){
        console.log(this.oCustom);
    }
};

var WilokeSingleListingEvents = {render: function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{class:_vm.wrapperClass,attrs:{"id":"single-events"}},[_c('div',{staticClass:"content-box_module__333d9"},[_c('header-component',{attrs:{"settings":_vm.oHeaderSettings}}),_vm._v(" "),_c('div',{staticClass:"content-box_body__3tSRB pos-r"},[_c('div',{directives:[{name:"show",rawName:"v-show",value:(_vm.content!==''),expression:"content!==''"}],staticClass:"row",attrs:{"data-col-xs-gap":"10"},domProps:{"innerHTML":_vm._s(_vm.content)}}),_vm._v(" "),_c('div',{directives:[{name:"show",rawName:"v-show",value:(_vm.noEventMsg.length),expression:"noEventMsg.length"}],staticClass:"align-center mt-20"},[_c('i',{staticClass:"la la-frown-o",staticStyle:{"font-size":"40px"}}),_c('p',{domProps:{"textContent":_vm._s(_vm.noEventMsg)}})]),_vm._v(" "),_c('div',{attrs:{"id":"wilcity-infinite-events"}}),_vm._v(" "),_c('block-loading',{attrs:{"is-loading":_vm.isFetching,"position":"pos-a-center"}})],1)],1)])},staticRenderFns: [],
    data: function data(){
        return {
            oHeaderSettings: WILCITY_SINGLE_LISTING.navigation.draggable.events,
            key: 'events',
            isLoaded: false,
            isFetching: 'no',
            content: '',
            oWaypoint: null,
            noEventMsg: '',
            aPostIDs: []
        }
    },
    components:{
        HeaderComponent: HeaderComponent,
        BlockLoading: BlockLoading,
        WilokeMessage: WilokeMessage
    },
    computed: {
        wrapperClass: function wrapperClass(){
            var cl = 'single-tab-content';
            return this.key == this.$store.getters.getCurrentNavTab ? cl + ' active' : cl + ' hidden';
        }
    },
    methods: {
        fetchContent: function fetchContent(){
            var this$1 = this;

            if ( this.isLoaded ){
                return true;
            }

            this.$root.componentLoading = this.key;
            this.isFetching = 'yes';
            this.$root.ajaxFetching = jQuery.ajax({
                type: 'GET',
                url: WILOKE_GLOBAL.ajaxurl + '?action=wilcity_fetch_events&parentID='+WILOKE_GLOBAL.postID+'&postNotIn='+this.aPostIDs,
                success: (function (response){
                    if ( response.success ){
                        this$1.content += response.data.content;
                        this$1.$root.componentLoading = '';
                        this$1.aPostIDs = this$1.aPostIDs.concat(response.data.postIDs);
                        this$1.infiniteEvents();
                    }else{
                        if ( typeof response.data.isLoaded !== 'undefined' && response.data.isLoaded == 'yes'  ){
                            this$1.isLoaded = true;
                        }else{
                            this$1.noEventMsg = response.data.msg;
                        }

                        if ( this$1.oWaypoint !== null ){
                            this$1.oWaypoint.destroy();
                        }
                    }
                    this$1.isFetching = 'no';
                })
            });
        },
        infiniteEvents: function infiniteEvents(){
            var self = this;
            this.oWaypoint = new Waypoint({
                element: document.getElementById('wilcity-infinite-events'),
                handler: function (direction) {
                    if ( direction == 'down' ){
                        self.fetchContent();
                    }
                },
                offset: 20
            });
        }
    },
    mounted: function mounted(){
        var this$1 = this;

        this.$parent.$on('fetchContent',  (function (mode){
            if ( mode == this$1.key  ){
                this$1.fetchContent();
            }
        }));
    }
};

var WilokeSingleListingVideos = {render: function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{class:_vm.wrapperClass,attrs:{"id":"single-videos"}},[_c('div',{staticClass:"content-box_module__333d9 pos-r"},[_c('header-component',{attrs:{"settings":_vm.oHeaderSettings}}),_vm._v(" "),_c('div',{directives:[{name:"show",rawName:"v-show",value:(_vm.errMsg.length),expression:"errMsg.length"}],staticClass:"align-center mt-20"},[_c('i',{staticClass:"la la-frown-o",staticStyle:{"font-size":"40px"}}),_c('p',{domProps:{"textContent":_vm._s(_vm.errMsg)}})]),_vm._v(" "),_c('wiloke-video-gallery',{attrs:{"item-class":"col-xs-12 col-sm-4","number-of-items":"3","a-real-video-gallery":_vm.content}}),_vm._v(" "),_c('block-loading',{attrs:{"is-loading":_vm.isFetching,"position":"pos-a-center"}})],1)])},staticRenderFns: [],
    data: function data(){
        return {
            oHeaderSettings: WILCITY_SINGLE_LISTING.navigation.draggable.videos,
            key: 'videos',
            content: [],
            photos: '',
            errMsg: '',
            isFetching: 'no',
            isLoaded: false
        }
    },
    components:{
        HeaderComponent: HeaderComponent,
        BlockLoading: BlockLoading,
        WilokeVideoGallery: WilokeVideoGallery,
        WilokeMessage: WilokeMessage
    },
    computed: {
        wrapperClass: function wrapperClass(){
            var cl = 'single-tab-content';
            return this.key == this.$store.getters.getCurrentNavTab ? cl + ' active' : cl + ' hidden';
        }
    },
    methods: {
        fetchContent: function fetchContent(){
            var this$1 = this;

            if ( this.isLoaded ){
                return true;
            }

            this.isFetching = 'yes';
            this.$root.componentLoading = this.key;
            this.$root.ajaxFetching = jQuery.ajax({
                type: 'GET',
                url: WILOKE_GLOBAL.ajaxurl + '?action=wilcity_fetch_single_video&postID='+WILOKE_GLOBAL.postID,
                success: (function (response){
                    if ( response.success ){
                        this$1.content = response.data;
                    }else{
                        this$1.errMsg = response.data.msg;
                    }

                    this$1.isLoaded = true;
                    this$1.$root.componentLoading = '';
                    this$1.isFetching = 'no';
                })
            });
        }
    },
    mounted: function mounted(){
        var this$1 = this;

        if ( this.isLoaded ){
            return false;
        }

        this.$parent.$on('fetchContent', (function (mode){
            if ( mode == this$1.key ){
                this$1.fetchContent();
            }
        }));
    }
};

var WilokeSingleListingPhotos = {render: function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{class:_vm.wrapperClass,attrs:{"id":"single-photos"}},[_c('div',{staticClass:"content-box_module__333d9 pos-r"},[_c('header-component',{attrs:{"settings":_vm.oHeaderSettings}}),_vm._v(" "),_c('div',{directives:[{name:"show",rawName:"v-show",value:(_vm.errMsg.length),expression:"errMsg.length"}],staticClass:"align-center mt-20"},[_c('i',{staticClass:"la la-frown-o",staticStyle:{"font-size":"40px"}}),_c('p',{domProps:{"textContent":_vm._s(_vm.errMsg)}})]),_vm._v(" "),(!_vm.errMsg.length)?_c('div',{staticClass:"content-box_body__3tSRB"},[_c('wiloke-gallery',{attrs:{"item-class":"col-xs-6 col-sm-3","number-of-items":"4","size":"thumbnail","a-real-gallery":_vm.content}})],1):_vm._e(),_vm._v(" "),_c('block-loading',{attrs:{"is-loading":_vm.isFetching,"position":"pos-a-center"}})],1)])},staticRenderFns: [],
    data: function data(){
        return {
            key: 'photos',
            oHeaderSettings: WILCITY_SINGLE_LISTING.navigation.draggable.photos,
            key: WILCITY_SINGLE_LISTING.navigation.draggable.photos.key,
            content: [],
            errMsg: '',
            isFetching: 'no',
            isLoaded: false
        }
    },
    computed: {
        wrapperClass: function wrapperClass(){
            var cl = 'single-tab-content';
            return this.key == this.$store.getters.getCurrentNavTab ? cl + ' active' : cl + ' hidden';
        }
    },
    components:{
        HeaderComponent: HeaderComponent,
        BlockLoading: BlockLoading,
        WilokeGallery: WilokeGallery,
        WilokeMessage: WilokeMessage
    },
    methods: {
        fetchContent: function fetchContent(){
            var this$1 = this;

            if ( this.isLoaded ){
                return true;
            }

            this.$root.componentLoading = this.key;
            this.isFetching = 'yes';
            this.$root.ajaxFetching = jQuery.ajax({
                type: 'GET',
                url: WILOKE_GLOBAL.ajaxurl + '?action=wilcity_fetch_single_gallery&postID='+WILOKE_GLOBAL.postID,
                success: (function (response){
                    if ( response.success ){
                        this$1.$store.commit('updateSingleGallery', {
                            id: 'single-gallery',
                            aImages: response.data
                        });
                        this$1.content =  response.data.content;
                    }else{
                        this$1.errMsg = response.data.msg;
                    }

                    this$1.isLoaded = true;
                    this$1.$root.componentLoading = '';
                    this$1.isFetching = 'no';
                })
            });
        }
    },
    mounted: function mounted(){
        var this$1 = this;

        if ( this.isLoaded  ){
            return false;
        }

        this.$parent.$on('fetchContent', (function (mode){
            if ( mode == this$1.key ){
                this$1.fetchContent();
            }
        }));
    }
};

var WilokeSliderRange = {render: function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{staticClass:"rated-slider_module__s79oc d-table"},[_c('div',{staticClass:"rated-slider_item__3pr-4 d-table-row"},[_c('div',{staticClass:"rated-slider_text__RSWfi d-table-cell"},[_vm._v(_vm._s(_vm.oItem.name))]),_vm._v(" "),_c('div',{staticClass:"rated-slider_slider__ncPAq d-table-cell"},[_c('div',{staticClass:"js-slider",attrs:{"data-key":_vm.key,"data-slider-min":_vm.minimum,"data-slider-max":_vm.maximum,"data-slider-value":_vm.changed}}),_vm._v(" "),_c('div',{staticClass:"js-slider-info"},[_c('span',{staticClass:"js-slider-info__number"},[_vm._v(_vm._s(_vm.changed))])])])])])},staticRenderFns: [],
    data: function data(){
        return {
            isUpdated: false,
            changed: this.value,
            $jsSlider: null
        }
    },
    props: ['maximum', 'minimum', 'oItem', 'value'],
    updated: function () {
        this.$nextTick(function () {
            if ( this.isUpdated ){
                return false;
            }

            this.isUpdated = true;
            this.wilSlider();
        });
    },
    watch: {
        value: function(newVal){
            this.changed = newVal;
            this.$jsSlider.slider('option', 'value', newVal);
        }
    },
    methods: {
        wilSlider: function wilSlider(){
            var this$1 = this;

            this.$jsSlider = jQuery(this.$el).find('.js-slider');

            var info = this.$jsSlider.siblings('.js-slider-info'),
            key     = this.$jsSlider.data('key'),
            number  = jQuery('.js-slider-info__number', info);

            this.$jsSlider.slider({
                range: 'min',
                min: this.$jsSlider.data('slider-min'),
                max: this.$jsSlider.data('slider-max'),
                value: this.value,
                slide: (function (event, ui){
                    info.attr('data-active', 'true');
                    number.text(ui.value);
                    this$1.changed = ui.value;
                    if (ui.value === 0) {
                        info.attr('data-active', '');
                    }
                }),
                stop: (function (event, ui){
                    this$1.$emit('sliderRangeChanged', this$1.changed, this$1.oItem);
                })
            });
        }
    },
    mounted: function mounted(){
        this.wilSlider();
    }
};

var WilokeReviewPopup = {render: function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('wiloke-popup',{attrs:{"popup-id":_vm.popupID,"btn-name":_vm.btnName,"popup-title":_vm.popupTitle,"icon":_vm.icon},on:{"on-close-popup":_vm.closePopup}},[_c('template',{slot:"body"},[_c('form',{attrs:{"action":"#","method":"POST"}},[_c('wiloke-error-msg',{directives:[{name:"show",rawName:"v-show",value:(_vm.errMsg!=''),expression:"errMsg!=''"}],attrs:{"msg":_vm.errMsg}}),_vm._v(" "),_vm._l((_vm.oReviewSettings.details),function(oReviewDetail){return (_vm.oReviewSettings.details)?_c('wiloke-slider-range',{attrs:{"maximum":_vm.oReviewSettings.mode,"minimum":"1","o-item":oReviewDetail,"value":_vm.itemVal(oReviewDetail)},on:{"sliderRangeChanged":_vm.changedReviewDetail}}):_vm._e()}),_vm._v(" "),_c('div',{staticClass:"wil-divider bg-color-gray-1 mt-20 mb-20"}),_vm._v(" "),_c('div',{class:_vm.titleClass},[_c('div',{staticClass:"field_wrap__Gv92k"},[_c('input',{directives:[{name:"model",rawName:"v-model",value:(_vm.title),expression:"title"}],staticClass:"field_field__3U_Rt",attrs:{"type":"text","required":""},domProps:{"value":(_vm.title)},on:{"input":function($event){if($event.target.composing){ return; }_vm.title=$event.target.value;}}}),_vm._v(" "),_c('span',{staticClass:"field_label__2eCP7 text-ellipsis"},[_vm._v(_vm._s(_vm.reviewTitleLabel)+"*")]),_c('span',{staticClass:"bg-color-primary"})])]),_vm._v(" "),_c('div',{class:_vm.contentClass},[_c('div',{staticClass:"field_wrap__Gv92k"},[_c('textarea',{directives:[{name:"model",rawName:"v-model",value:(_vm.content),expression:"content"}],staticClass:"field_field__3U_Rt",attrs:{"required":""},domProps:{"value":(_vm.content)},on:{"input":function($event){if($event.target.composing){ return; }_vm.content=$event.target.value;}}}),_c('span',{staticClass:"field_label__2eCP7 text-ellipsis"},[_vm._v(_vm._s(_vm.reviewContentLabel)+"*")]),_c('span',{staticClass:"bg-color-primary"})])]),_vm._v(" "),(_vm.toggleGallery=='enable')?_c('wiloke-upload-img',{attrs:{"settings":_vm.galleryData,"additional":{cssClass: 'comment-review_gallery__2Tyry'}},on:{"uploadImgChanged":_vm.galleryChanged}}):_vm._e(),_vm._v(" "),_c('div',{staticClass:"mt-10"}),_vm._v(" "),_c('button',{staticClass:"wil-btn wil-btn--primary wil-btn--round wil-btn--md wil-btn--block",on:{"click":function($event){$event.preventDefault();_vm.submitReview($event);}}},[_c('i',{staticClass:"la la-paper-plane"}),_vm._v(" "+_vm._s(_vm.btnName))])],2)])],2)},staticRenderFns: [],
    data: function data(){
        return {
            title: '',
            content: '',
            popupID: 'write-a-review-popup',
            oReviewSettings: typeof WILCITY_REVIEW_SETTINGS !='undefined' ? WILCITY_REVIEW_SETTINGS : {},
            errMsg: '',
            isFetching: false,
            xhrReviewGeneral: null,
            reviewQuality: null,
            averageReviewScore: null,
            reviewID: ''
        }
    },
    props: ['popupId', 'btnName', 'popupTitle', 'icon', 'reviewData', 'reviewSettings', 'reviewTitleLabel', 'reviewContentLabel', 'toggleGallery'],
    components:{
        WilokePopup: WilokePopup,
        WilokeUploadImg: WilokeUploadImg,
        WilokeSliderRange: WilokeSliderRange,
        WilokeErrorMsg: WilokeErrorMsg
    },
    watch: {
        oReviewData: {
            handler: function(oNewValue){
                if ( this.title !== oNewValue.title ){
                    this.title = oNewValue.title;
                }

                if ( this.content !== oNewValue.content ){
                    this.content = oNewValue.content;
                }
            },
            deep: true
        }
    },
    computed:{
        titleClass: function(){
            return {
                'field_module__1H6kT field_style2__2Znhe mb-15': 1==1,
                active: this.title.length
            }
        },
        contentClass: function(){
            return {
                'field_module__1H6kT field_style2__2Znhe mb-15': 1==1,
                active: this.content.length
            }
        },
        galleryData:  function(){
            return {
                isMultiple: true,
                value: this.$store.getters.getReviewInfo && typeof this.$store.getters.getReviewInfo.gallery !== 'undefined' ? this.$store.getters.getReviewInfo.gallery : [],
                wrapperClassName: 'upload-image_module__3I5sF upload-image_sm__25yM3',
                aUploadedImgs: []
            };
        },
        oReviewData: function(){
            var oInfo = this.$store.getters.getReviewInfo;
            if ( oInfo ){
                return oInfo;
            }else{
                return {
                    gallery: {},
                    title: '',
                    content: ''
                };
            }
        },
        oReviewDetails: function oReviewDetails(){
            var oInfo = this.$store.getters.getReviewInfo;
            if ( oInfo ){
                this.getReviewID();
                return oInfo.details;
            }else{
                return WILCITY_REVIEW_SETTINGS.details;
            }
        }
    },
    methods: {
        getReviewID: function getReviewID(){
            var oReviewArgs = this.$store.getters.getPopupArgs(this.popupID);

            if ( !oReviewArgs ){
                this.reviewID = '';
            }

            this.reviewID = typeof oReviewArgs['reviewID'] !== 'undefined' ? oReviewArgs['reviewID'] : '';
        },
        itemVal: function itemVal(oItem){
             if ( this.$store.getters.getReviewInfo ){
                return parseFloat(this.$store.getters.getReviewInfo.details[oItem.key].value);
             }else{
                return 5;
             }
        },
        changedReviewDetail: function changedReviewDetail(newValue, oReviewDetail){
            if ( typeof this.oReviewData.details == 'undefined' ){
                this.oReviewData.details = {};
            }

            if ( typeof this.oReviewData.details[oReviewDetail.key] == 'undefined' ){
                this.oReviewData.details[oReviewDetail.key] = {};
            }
            this.oReviewData.details[oReviewDetail.key].value = newValue;
        },
        updateReviewOnDOM: function updateReviewOnDOM(isGalleryChanged){
            jQuery('.wilcity-review-title-'+this.reviewID).html(this.oReviewData.title);
            jQuery('.wilcity-review-content-'+this.reviewID).html(this.oReviewData.content);

            if ( this.averageReviewScore !== null ){
                var dataRated = averageReviewScore;
                if ( this.mode == 5 ){
                    dataRated = parseFloat(dataRated)*2;
                }
                jQuery('.wilcity-data-average-review-score-'+this.reviewID).attr('data-rated', dataRated);
                jQuery('.wilcity-average-review-score-'+this.reviewID).html(this.averageReviewScore);

                if ( this.averageReviewScore > 0 ){
                    jQuery('.wilcity-data-average-review-score-'+this.reviewID).removeClass('hidden');
                }
            }

            if ( this.reviewQuality !== null ){
                jQuery('.wilcity-review-quality-'+this.reviewID).html(this.reviewQuality);
            }

            if ( jQuery('#wilcity-review-gallery-'+this.reviewID).find('.wilcity-magnific-via-jquery').length ){
                jQuery('#wilcity-review-gallery-'+this.reviewID).wilcityInitMagnific();
            }
        },
        galleryChanged: function galleryChanged(uploadImgChanged){
            this.oReviewData.gallery = uploadImgChanged;
        },
        reCalculationReviewGeneral: function reCalculationReviewGeneral(){
            if ( this.xhrReviewGeneral !== null && typeof this.xhrReviewGeneral.status !== 200 ){
                this.xhrReviewGeneral.abort();
            }

            this.xhrReviewGeneral = jQuery.ajax({
                url: WILOKE_GLOBAL.ajaxurl+'?action=wilcity_fetch_review_general&postID='+WILOKE_GLOBAL.postID,
                type: 'GET',
                success: (function (response){
                    if ( response.success ){
                        jQuery('body').trigger('changedGeneralReview', response.data);
                    }
                })
            });
        },
        closePopup: function closePopup(){
            this.$emit('onClosePopup', true);
        },
        submitReview: function submitReview(event){
            var this$1 = this;

            var $btn = jQuery(event.currentTarget);
            this.errMsg = '';
            $btn.addClass('wil-btn--disabled');
            this.$emit('line-loading', 'yes');
            this.oReviewData.title = this.title;
            this.oReviewData.content = this.content;

            console.log(this.oReviewData);

            jQuery.ajax({
                url: WILOKE_GLOBAL.ajaxurl,
                type: 'POST',
                data: {
                    action: 'wilcity_submit_review',
                    listingID: WILOKE_GLOBAL.postID,
                    reviewID: this.reviewID,
                    data: this.oReviewData
                },
                success: (function (response){
                    $btn.removeClass('wil-btn--disabled');
                    if ( !response.success ){
                        this$1.errMsg = response.data.msg;
                    }else{
                        this$1.closePopup();
                        this$1.$store.commit('updateReviews', response.data.reviews);
                        var url = WilCityHelpers.buildQuery('st', 'wilcity-js-review-item-'+response.data.reviewID);
                        //window.location.href =  WilCityHelpers.buildQuery('tab', this.$store.getters.getCurrentListingTab, url);
                    }
                    this$1.$emit('line-loading', 'no');
                })
            });
        }
    }
};

var WilokeAverageRating = {render: function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{staticClass:"content-box_module__333d9"},[_c('header',{staticClass:"content-box_header__xPnGx clearfix"},[_c('div',{staticClass:"wil-float-left"},[_c('h4',{staticClass:"content-box_title__1gBHS"},[_c('i',{staticClass:"la la-star-o"}),_c('span',[_vm._v(_vm._s(_vm.postTitle))])])])]),_vm._v(" "),_c('div',{staticClass:"content-box_body__3tSRB"},[_c('div',{staticClass:"average-rating-info_module__TOHeu"},[_c('div',{staticClass:"average-rating-info_left__255Tl"},[_c('div',{staticClass:"rated-small_module__1vw2B"},[_c('div',{staticClass:"rated-small_wrap__2Eetz",attrs:{"data-rated":_vm.dataRated}},[_c('div',{staticClass:"rated-small_overallRating__oFmKR"},[_vm._v(_vm._s(_vm.average))]),_vm._v(" "),_c('div',{staticClass:"rated-small_ratingWrap__3lzhB"},[_c('div',{staticClass:"rated-small_maxRating__2D9mI"},[_vm._v(_vm._s(_vm.mode))]),_vm._v(" "),_c('div',{staticClass:"rated-small_ratingOverview__2kCI_"},[_vm._v(_vm._s(_vm.quality))])])])])]),_vm._v(" "),_c('div',{staticClass:"average-rating-info_right__3xLnz"},_vm._l((_vm.oReviewResultDetails),function(oDetail){return _c('div',{staticClass:"average-rating-info_item__2yvNR"},[_c('span',{staticClass:"average-rating-info_text__3Cq-a"},[_vm._v(_vm._s(oDetail.name))]),_vm._v(" "),_c('div',{staticClass:"rated-small_module__1vw2B rated-small_style-4__VTQSw"},[_c('div',{staticClass:"rated-small_wrap__2Eetz",attrs:{"data-rated":_vm.detailDataRated(oDetail.average)}},[_c('div',{staticClass:"rated-small_overallRating__oFmKR"},[_vm._v(_vm._s(oDetail.average))])])])])}))])]),_vm._v(" "),(_vm.isUserReviewed=='no')?_c('footer',{staticClass:"content-box_footer__kswf3"},[_c('a',{staticClass:"content-box_link__2K0Ib wil-text-center",attrs:{"href":"#"},on:{"click":function($event){$event.preventDefault();_vm.onOpenReviewPopup($event);}}},[_c('i',{staticClass:"la la-star-o"}),_vm._v(" "+_vm._s(_vm.oTranslation.addReview))])]):_vm._e()])},staticRenderFns: [],
    data: function data(){
        return{
            oTranslation: WILCITY_I18,
        }
    },
    props: ['total', 'average', 'quality', 'mode', 'rawReviewDetailsResult', 'postTitle', 'isUserReviewed'],
    mounted: function mounted(){
    },
    methods: {
        onOpenReviewPopup: function onOpenReviewPopup(){
            this.$parent.$emit('on-open-review-popup');
        },
        detailDataRated: function detailDataRated(average){
            if ( this.mode == 5 ){
                return parseFloat(average)*2;
            }
            return average;
        },
    },
    computed:{
        dataRated: function dataRated(){
            if ( this.mode == 5 ){
                return parseFloat(this.average)*2;
            }
            return this.average;
        },
        averageRatingTitle: function averageRatingTitle(){
            return this.averageRating
        },
        oReviewResultDetails: function oReviewResultDetails(){
            if ( !WilCityHelpers.isNull(this.rawReviewDetailsResult) ){
                return JSON.parse(this.rawReviewDetailsResult);
            }else{
                return [];
            }
        }
    },
    components:{
        WilokeTotalReviews: WilokeTotalReviews
    }
};

var WilokeOccursOnce$1 = {render: function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{staticClass:"bg-color-gray-3 bd-color-gray-1 pd-15 mb-20"},[_c('div',{staticClass:"row"},[_c('div',{staticClass:"col-md-2 col-lg-2"},[_c('div',{staticClass:"mt-25"},[_vm._v(_vm._s(_vm.oTranslation.starts))])]),_vm._v(" "),_c('div',{staticClass:"col-md-6 col-lg-6"},[_c('wiloke-datepicker',{attrs:{"settings":{value: _vm.oData.starts, isRequired: 'yes'}},on:{"datepickerChanged":_vm.startsChanged}})],1),_vm._v(" "),_c('div',{staticClass:"col-md-4 col-lg-4"},[_c('wiloke-time',{attrs:{"settings":{value: _vm.oData.openingAt, isRequired: 'yes'}},on:{"timeChanged":_vm.openingAtChanged}})],1)]),_vm._v(" "),_c('div',{staticClass:"row"},[_c('div',{staticClass:"col-md-2 col-lg-2"},[_c('div',{staticClass:"mt-25"},[_vm._v(_vm._s(_vm.oTranslation.endson))])]),_vm._v(" "),_c('div',{staticClass:"col-md-6 col-lg-6"},[_c('wiloke-datepicker',{attrs:{"settings":{value: _vm.oData.endsOn, isRequired: 'yes'}},on:{"datepickerChanged":_vm.endsonChanged}})],1),_vm._v(" "),_c('div',{staticClass:"col-md-4 col-lg-4"},[_c('wiloke-time',{attrs:{"settings":{value: _vm.oData.closedAt, isRequired: 'yes'}},on:{"timeChanged":_vm.closedAtChanged}})],1)])])},staticRenderFns: [],
    data: function data(){
        return {
            oTranslation: WILCITY_I18,
            oOccursOnce: {}
        }
    },
    watch: {
        'oOccursOnce': {
            handler: function(oNewVal){
                this.$emit('occursOnceChanged', oNewVal);
            },
            deep: true
        }
    },
    props: ['oData'],
    methods: {
        startsChanged: function startsChanged(newVal){
            this.$set(this.oOccursOnce, 'starts', newVal);
        },
        openingAtChanged: function openingAtChanged(newVal){
            this.$set(this.oOccursOnce, 'openingAt', newVal);
        },
        endsonChanged: function endsonChanged(newVal){
            this.$set(this.oOccursOnce, 'endsOn', newVal);
        },
        closedAtChanged: function closedAtChanged(newVal){
            this.$set(this.oOccursOnce, 'closedAt', newVal);
        }
    },
    components:{
        WilokeTime: WilokeTime,
        WilokeDatepicker: WilokeDatepicker
    },
    mounted: function mounted(){
        this.oOccursOnce = this.oData;
    }
};

var WilokeDaily$1 = {render: function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{staticClass:"bg-color-gray-3 bd-color-gray-1 pd-15 mb-20"},[_c('div',{staticClass:"row"},[_c('div',{staticClass:"col-md-2 col-lg-2"},[_c('div',{staticClass:"mt-25"},[_vm._v(_vm._s(_vm.oTranslation.starts))])]),_vm._v(" "),_c('div',{staticClass:"col-md-10 col-lg-10"},[_c('wiloke-datepicker',{attrs:{"settings":{value: _vm.oDailySettings.starts, isRequired: 'yes'}},on:{"datepickerChanged":_vm.startsChanged}})],1)]),_vm._v(" "),_c('div',{staticClass:"row"},[_c('div',{staticClass:"col-md-2 col-lg-2"},[_c('div',{staticClass:"mt-25"},[_vm._v(_vm._s(_vm.oTranslation.endson))])]),_vm._v(" "),_c('div',{staticClass:"col-md-10 col-lg-10"},[_c('wiloke-datepicker',{attrs:{"settings":{value: _vm.oDailySettings.endsOn, isRequired: 'yes'}},on:{"datepickerChanged":_vm.endsOnChanged}})],1)]),_vm._v(" "),_c('div',{staticClass:"row"},[_c('div',{staticClass:"col-md-2 col-lg-2"},[_c('div',{staticClass:"mt-25"},[_vm._v(_vm._s(_vm.oTranslation.time))])]),_vm._v(" "),_c('div',{staticClass:"col-md-5 col-lg-5"},[_c('wiloke-time',{attrs:{"settings":{value: _vm.oDailySettings.openingAt, isRequired: 'yes'}},on:{"timeChanged":_vm.openingAtChanged}})],1),_vm._v(" "),_c('div',{staticClass:"col-md-5 col-lg-5"},[_c('wiloke-time',{attrs:{"settings":{value: _vm.oDailySettings.closedAt}, isRequired: 'yes'},on:{"timeChanged":_vm.closedAtChanged}})],1)])])},staticRenderFns: [],
    data: function data(){
        return {
            oTranslation: WILCITY_I18,
            oDailySettings: {
                starts: '',
                endsOn: '',
                openingAt: '',
                closedAt: ''
            }
        }
    },
    props: ['oData'],
    watch: {
        'oDailySettings': {
            handler: function(oNewValue){
                this.$emit('dailyChanged', oNewValue);
            },
            deep: true
        },
        'oData': {
            handler: function(oNewValue){
                this.oDailySettings = this.oData;
            },
            deep: true
        }
    },
    methods: {
        startsChanged: function startsChanged(newVal){
            this.$set(this.oDailySettings, 'starts', newVal);
        },
        endsOnChanged: function endsOnChanged(newVal){
            this.$set(this.oDailySettings, 'endsOn', newVal);
        },
        openingAtChanged: function openingAtChanged(newVal){
            this.$set(this.oDailySettings, 'openingAt', newVal);
        },
        closedAtChanged: function closedAtChanged(newVal){
            this.$set(this.oDailySettings, 'closedAt', newVal);
        }
    },
    components:{
        WilokeTime: WilokeTime,
        WilokeDatepicker: WilokeDatepicker
    },
    mounted: function mounted(){
        this.oDailySettings = this.oData;
    }
};

var WilokeWeekly$1 = {render: function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{staticClass:"bg-color-gray-3 bd-color-gray-1 pd-15 mb-20"},[_c('div',{staticClass:"row"},[_c('div',{staticClass:"col-md-2 col-lg-2"},[_c('div',{staticClass:"mt-5 mb-10"},[_vm._v(_vm._s(_vm.oTranslation.day))])]),_vm._v(" "),_c('div',{staticClass:"col-md-10 col-lg-10"},[_c('div',{staticClass:"row"},[_c('wiloke-radio',{attrs:{"settings":{value: _vm.checkedDay, options: _vm.oTranslation.oDaysOfWeek}},on:{"radioChanged":_vm.specifyDaysChecked}})],1)])]),_vm._v(" "),_c('div',{staticClass:"row"},[_c('div',{staticClass:"col-md-2 col-lg-2"},[_c('div',{staticClass:"mt-25"},[_vm._v(_vm._s(_vm.oTranslation.starts))])]),_vm._v(" "),_c('div',{staticClass:"col-md-10 col-lg-10"},[_c('wiloke-datepicker',{attrs:{"settings":{value: _vm.oWeekly.starts}},on:{"datepickerChanged":_vm.startsChanged}})],1)]),_vm._v(" "),_c('div',{staticClass:"row"},[_c('div',{staticClass:"col-md-2 col-lg-2"},[_c('div',{staticClass:"mt-25"},[_vm._v(_vm._s(_vm.oTranslation.endsOn))])]),_vm._v(" "),_c('div',{staticClass:"col-md-10 col-lg-10"},[_c('wiloke-datepicker',{attrs:{"settings":{value: _vm.oWeekly.endsOn}},on:{"datepickerChanged":_vm.endsonChanged}})],1)]),_vm._v(" "),_c('div',{staticClass:"row"},[_c('div',{staticClass:"col-md-2 col-lg-2"},[_c('div',{staticClass:"mt-25"},[_vm._v(_vm._s(_vm.oTranslation.time))])]),_vm._v(" "),_c('div',{staticClass:"col-md-5 col-lg-5"},[_c('wiloke-time',{attrs:{"settings":{value: _vm.oWeekly.openingAt}},on:{"timeChanged":_vm.openingAtChanged}})],1),_vm._v(" "),_c('div',{staticClass:"col-md-5 col-lg-5"},[_c('wiloke-time',{attrs:{"settings":{value: _vm.oWeekly.closedAt}},on:{"timeChanged":_vm.closedAtChanged}})],1)])])},staticRenderFns: [],
    data: function data(){
        return {
            oTranslation: WILCITY_I18,
            oWeekly: {
                starts: '',
                endsOn: '',
                specifyDays: [],
            },
            checkedDay: ''
        }
    },
    props:['oData'],
    watch: {
        'oWeekly': {
            handler: function(oNewVal){
                this.$emit('weeklyChanged', oNewVal);
            },
            deep: true
        }
    },
    methods: {
        specifyDaysChecked: function specifyDaysChecked(newVal){
            this.$set(this.oWeekly, 'specifyDays', newVal);
        },
        startsChanged: function startsChanged(newVal){
            this.$set(this.oWeekly, 'starts', newVal);
        },
        endsonChanged: function endsonChanged(newVal){
            this.$set(this.oWeekly, 'endsOn', newVal);
        },
        openingAtChanged: function openingAtChanged(newVal){
            this.$set(this.oWeekly, 'openingAt', newVal);
        },
        closedAtChanged: function closedAtChanged(newVal){
            this.$set(this.oWeekly, 'closedAt', newVal);
        }
    },
    components:{
        WilokeDatepicker: WilokeDatepicker,
        WilokeTime: WilokeTime,
        WilokeDaysChecked: WilokeDaysChecked,
        WilokeRadio: WilokeRadio
    },
    mounted: function mounted(){
        this.oWeekly = this.oData;
    }
};

function getDefaultData(){
    return {
        listing_name: '',
        video: '',
        address: {
            address: '',
            lng: '',
            lat: ''
        },
        img: '',
        weekly: {
            specifyDays: []
        },
        daily: {},
        occurs_once: {},
        frequency: 'occurs_once',
        listing_content: '',
        starts: '',
        endsOn: '',
        openingAt: '',
        closedAt:''
    }
}

var WilokeEventPopup = {render: function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{staticClass:"popup_module__3M-0- pos-f-full popup_md__3El3k popup_mobile-full__1hyc4",attrs:{"data-popup-content":"createEvent"}},[_c('div',{staticClass:"wil-overlay js-popup-overlay"}),_vm._v(" "),_c('div',{staticClass:"wil-tb"},[_c('div',{staticClass:"wil-tb__cell"},[_c('div',{staticClass:"popup_content__3CJVi"},[_c('header',{staticClass:"popup_header__2QTxC clearfix"},[_c('h3',{staticClass:"popup_title__3q6Xh"},[_c('i',{staticClass:"la"}),_vm._v(" "),_c('span',[_vm._v(_vm._s(_vm.popupTitle))])]),_vm._v(" "),_vm._m(0)]),_vm._v(" "),(_vm.oData)?_c('div',{staticClass:"popup_body__1wtsy wil-scroll-bar"},[_c('div',{directives:[{name:"show",rawName:"v-show",value:(_vm.isSubmitting),expression:"isSubmitting"}],staticClass:"line-loading_module__SUlA1 pos-a-top"},[_c('div',{staticClass:"line-loading_loader__FjIcM"}),_vm._v(" "),_vm._m(1)]),_vm._v(" "),_c('div',{staticClass:"promo-item_module__24ZhT"},[_c('div',{staticClass:"promo-item_group__2ZJhC"},[_c('h3',{staticClass:"promo-item_title__3hfHG"},[_vm._v(_vm._s(_vm.oTranslation.basicInfoEvent))]),_vm._v(" "),_c('p',{staticClass:"promo-item_description__2nc26"},[_vm._v(_vm._s(_vm.oTranslation.basicInfoEventDesc))])])]),_vm._v(" "),_c('wiloke-upload-img',{attrs:{"settings":{wrapperClassName: 'field_module__1H6kT field_style2__2Znhe mb-15 js-field', isMultiple: false, value:_vm.oData.img}},on:{"uploadImgChanged":_vm.imageChanged}}),_vm._v(" "),_c('wiloke-input',{attrs:{"settings":{value: _vm.oData.video, isRequired:"no", label:_vm.oTranslation.eventVideo}},on:{"inputChanged":_vm.videoChanged}}),_vm._v(" "),_c('wiloke-input',{attrs:{"settings":{value: _vm.oData.listing_title, isRequired:"yes", label:_vm.oTranslation.eventName}},on:{"inputChanged":_vm.nameChanged}}),_vm._v(" "),_c('wiloke-auto-complete',{attrs:{"settings":{value: _vm.oData.address.address, isRequired:"yes", label:_vm.oTranslation.location, placeholder: _vm.oTranslation.address}},on:{"geocode-changed":_vm.addressChanged}}),_vm._v(" "),_c('wiloke-select-two',{attrs:{"settings":{value: _vm.oData.frequency, isRequired:"yes", options: _vm.oTranslation.aEventFrequency, isMultiple: "no", label: _vm.oTranslation.frequency}},on:{"selectTwoChanged":_vm.frequencyMode}}),_vm._v(" "),_c('wiloke-daily',{directives:[{name:"show",rawName:"v-show",value:(_vm.oData.frequency==='daily'),expression:"oData.frequency==='daily'"}],attrs:{"o-data":{starts: _vm.oData.starts, openingAt: _vm.oData.openingAt, endsOn: _vm.oData.endsOn, closedAt: _vm.oData.closedAt}},on:{"dailyChanged":_vm.updateStartsAndEndsOn}}),_vm._v(" "),_c('wiloke-weekly',{directives:[{name:"show",rawName:"v-show",value:(_vm.oData.frequency==='weekly'),expression:"oData.frequency==='weekly'"}],attrs:{"o-data":{starts: _vm.oData.starts, openingAt: _vm.oData.openingAt, endson: _vm.oData.endsOn, closedAt: _vm.oData.closedAt, specifyDays: _vm.oData.weekly.specifyDays}},on:{"weeklyChanged":_vm.weeklyChanged}}),_vm._v(" "),_c('wiloke-occurs-once',{directives:[{name:"show",rawName:"v-show",value:(_vm.oData.frequency==='occurs_once'),expression:"oData.frequency==='occurs_once'"}],attrs:{"o-data":{starts: _vm.oData.starts, openingAt: _vm.oData.openingAt, endsOn: _vm.oData.endsOn, closedAt: _vm.oData.closedAt}},on:{"occursOnceChanged":_vm.updateStartsAndEndsOn}}),_vm._v(" "),_c('div',{staticClass:"promo-item_module__24ZhT"},[_c('div',{staticClass:"promo-item_group__2ZJhC"},[_c('h3',{staticClass:"promo-item_title__3hfHG"},[_vm._v(_vm._s(_vm.oTranslation.details))]),_vm._v(" "),_c('p',{staticClass:"promo-item_description__2nc26"},[_vm._v(_vm._s(_vm.oTranslation.detailsDesc))])])]),_vm._v(" "),_c('wiloke-textarea',{attrs:{"settings":{value:_vm.oData.listing_content}},on:{"textareaChanged":_vm.textareaChanged}})],1):_vm._e(),_vm._v(" "),_c('div',{staticClass:"popup_footer__2pUrl clearfix"},[_c('div',{staticClass:"popup_footerRight__qvdP6"},[_c('button',{staticClass:"wil-btn wil-btn--gray wil-btn--sm wil-btn--round",attrs:{"type":"submit"},on:{"click":function($event){$event.preventDefault();_vm.closePopup($event);}}},[_vm._v(_vm._s(_vm.oTranslation.cancel))]),_vm._v(" "),_c('button',{staticClass:"wil-btn wil-btn--primary wil-btn--sm wil-btn--round",attrs:{"type":"submit"},on:{"click":function($event){$event.preventDefault();_vm.submitEvent($event);}}},[_vm._v(_vm._s(_vm.oTranslation.publish))])])])])])])])},staticRenderFns: [function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{staticClass:"popup_headerRight__c4FcP"},[_c('span',{staticClass:"popup_close__mJx2A color-primary--hover js-toggle-close"},[_c('i',{staticClass:"la la-close"})])])},function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{staticClass:"core-code-html",staticStyle:{"height":"0","overflow":"hidden","visibility":"hidden"}},[_c('span',{attrs:{"data-toggle-html-button":"line-loading_module__SUlA1 pos-a-top","data-title":"line-loading_module","data-toggle-number-button":"65"}})])}],
    data: function data(){
        return {
            oData: getDefaultData(),
            xhr: null,
            isEditing: false,
            oTranslation: WILCITY_I18,
            isActivatePopup: false,
            eventDataID: null,
            eventID: null,
            isAddressChanged: false,
            isSubmitting: false
        }
    },
    props: ['raw-data'],
    components: {
        WilokeUploadImg: WilokeUploadImg,
        WilokeInput: WilokeInput,
        WilokeAutoComplete: WilokeAutoComplete,
        WilokeTextarea: WilokeTextarea,
        WilokeSelectTwo: WilokeSelectTwo,
        WilokeOccursOnce: WilokeOccursOnce$1,
        WilokeDaily: WilokeDaily$1,
        WilokeWeekly: WilokeWeekly$1
    },
    props: ['popupTitle'],
    methods: {
        textareaChanged: function textareaChanged(newVal){
            this.oData.listing_content = newVal;
        },
        updateStartsAndEndsOn: function updateStartsAndEndsOn(oNewVal){
            this.$set(this.oData, 'starts', oNewVal.starts);
            this.$set(this.oData, 'endsOn', oNewVal.endsOn);
            this.$set(this.oData, 'closedAt', oNewVal.closedAt);
            this.$set(this.oData, 'openingAt', oNewVal.openingAt);
        },
        weeklyChanged: function weeklyChanged(oNewVal){
            this.oData.weekly = oNewVal;
            this.updateStartsAndEndsOn(oNewVal);
        },
        imageChanged: function imageChanged(newVal){
            this.oData.img = newVal;
        },
        videoChanged: function videoChanged(newVal){
            this.oData.video = newVal;
        },
        nameChanged: function nameChanged(newVal){
            this.oData.listing_name = newVal;
        },
        addressChanged: function addressChanged(newVal){
            this.oData.address = newVal;
            this.isAddressChanged = true;
        },
        frequencyMode: function frequencyMode(value){
            this.$set(this.oData, 'frequency', value);
        },
        closePopup: function closePopup(){
            jQuery(this.$el).removeClass('active');
        },
        submitEvent: function submitEvent(event){
            var this$1 = this;

            var $target = jQuery(event.currentTarget);
            this.isSubmitting = true;

            if ( this.xhr !== null && this.xhr.status !== 200 ){
                this.xhr.abort();
            }

            $target.addClass('wil-btn--disabled');
            this.xhr = jQuery.ajax({
                type: 'POST',
                url: WILOKE_GLOBAL.ajaxurl,
                data: {
                    action: !this.isEditing ? 'wilcity_submit_event' : 'wilcity_edit_event',
                    data: this.oData,
                    listingID: WILOKE_GLOBAL.postID,
                    eventID: this.eventID,
                    eventDataID: this.eventDataID,
                    isAddressChanged: this.isAddressChanged
                },
                success: (function (response){
                    $target.removeClass('wil-btn--disabled');
                    this$1.isSubmitting = false;
                    if ( response.success ){
                        if ( typeof response.data.redirectTo !== 'undefined' ){
                            window.location.href = response.data.redirectTo;
                        }else{
                            this$1.isActivatePopup = false;
                            this$1.eventDataID = response.data.eventDataID;
                            if ( typeof response.data.img !== 'undefined' ){
                                this$1.$set(this$1.oData, 'img', response.data.img);
                            }
                        }
                        jQuery(this$1.$el).find('.js-toggle-close').trigger('click');
                    }
                })
            });
        },
        fetchEventData: function fetchEventData(eventID){
        },
        editingEvent: function editingEvent(){
            var this$1 = this;

            var $body = jQuery('body'), self = this;
            $body.on('editingEvent', (function (event, oData){
                this$1.eventID = oData.eventID;
                this$1.xhr = jQuery.ajax({
                    type: 'POST',
                    url: WILOKE_GLOBAL.ajaxurl,
                    data: {
                        eventID: oData.eventID,
                        action: 'wilcity_get_event_data'
                    },
                    success: function success(response){
                        self.isEditing = true;
                        for ( var item in response.data ){
                            self.$set(self.oData, item, response.data[item]);
                        }
                    }
                });
            }));
        },
        createNewEvent: function createNewEvent(){
            var $body = jQuery('body'), self = this;
            $body.on('createNewEvent', (function (event){
                if ( !self.isEditing ){
                    return false;
                }

                self.isEditing = false;

                var oDefault = getDefaultData();
                for ( var item in oDefault ){
                    self.$set(self.oData, item, oDefault[item]);
                }
            }));
        }
    },
    beforeMount: function beforeMount(){
        if ( !WilCityHelpers.isNull(this.rawData) ){
            this.oData = JSON.parse(this.rawData);
        }
    },
    mounted: function mounted(){
        this.createNewEvent();
        this.editingEvent();
    }
};

var WilokeClaimPopup = {render: function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('wiloke-popup',{attrs:{"popup-id":"wilcity-claim-popup","popup-title":_vm.oTranslation.claimListing},on:{"on-close-popup":_vm.closePopup}},[_c('block-loading',{attrs:{"position":"pos-a-center","is-loading":_vm.isSending}}),_vm._v(" "),_c('div',{attrs:{"slot":"body"},slot:"body"},[_c('wiloke-error-msg',{directives:[{name:"show",rawName:"v-show",value:(_vm.errMsg.length),expression:"errMsg.length"}],attrs:{"msg":_vm.errMsg,"has-remove":"false"}}),_vm._v(" "),_c('wiloke-message',{directives:[{name:"show",rawName:"v-show",value:(_vm.successMsg.length),expression:"successMsg.length"}],attrs:{"msg":_vm.successMsg,"has-remove":"false","icon":"la la-bullhorn","status":"success"}}),_vm._v(" "),(_vm.oClaimFields.noPackage)?_c('div',[_c('wiloke-error-msg',{attrs:{"msg":_vm.oTranslation.noClaimFields,"has-remove":"false"}})],1):_vm._l((_vm.oClaimFields),function(oField){return _c('div',[(oField.type == 'checkbox')?_c('wiloke-checkbox-two',{attrs:{"settings":{options: oField.options, value: _vm.getVal(oField.key, true), label: oField.label, key: oField.key}},on:{"checkboxTwoChanged":_vm.checkboxTwoChanged}}):(oField.type == 'textarea')?_c('wiloke-textarea',{attrs:{"settings":{isRequired: oField.isRequired, value: _vm.getVal(oField.key), label: oField.label, key: oField.key}},on:{"textareaChanged":_vm.textareaChanged}}):(oField.type == 'radio')?_c('wiloke-radio',{attrs:{"settings":{options: oField.options, value: _vm.getVal(oField.key), label: oField.label, key: oField.key}},on:{"radioChanged":_vm.radioChanged}}):(oField.options)?_c('wiloke-select-two',{attrs:{"settings":{options: oField.options, value: _vm.getVal(oField.key), label: oField.label, key: oField.key}},on:{"selectTwoChanged":_vm.selectTwoChanged}}):_c('wiloke-input',{attrs:{"settings":{isRequired: oField.isRequired, value:_vm.getVal(oField.key), label: oField.label, key: oField.key}},on:{"inputChanged":_vm.inputChanged}})],1)})],2),_vm._v(" "),_c('footer',{staticClass:"popup_footer__2pUrl clearfix",attrs:{"slot":"footer"},slot:"footer"},[_c('div',{staticClass:"popup_footerRight__qvdP6"},[_c('button',{staticClass:"wil-btn wil-btn--gray wil-btn--sm wil-btn--round",attrs:{"type":"submit"},on:{"click":function($event){$event.preventDefault();_vm.closePopup($event);}}},[_vm._v(_vm._s(_vm.oTranslation.cancel))]),_vm._v(" "),_c('button',{staticClass:"wil-btn wil-btn--primary wil-btn--sm wil-btn--round",attrs:{"type":"submit"},on:{"click":function($event){$event.preventDefault();_vm.submitClaimRequest($event);}}},[_vm._v(_vm._s(_vm.oTranslation.submit))])])])],1)},staticRenderFns: [],
    data: function data(){
        return {
            oTranslation: WILCITY_I18,
            oClaimFields: {},
            oData: {},
            errMsg:'',
            postID: '',
            successMsg: '',
            isSending: 'no',
            xhr: null
        }
    },
    components:{
        BlockLoading: BlockLoading,
        WilokeMessage: WilokeMessage,
        WilokePopup: WilokePopup,
        WilokeInput: WilokeInput,
        WilokeTextarea: WilokeTextarea,
        WilokeRadio: WilokeRadio,
        WilokeCheckboxTwo: WilokeCheckboxTwo,
        WilokeSelectTwo: WilokeSelectTwo,
        WilokeErrorMsg: WilokeErrorMsg
    },
    mounted: function mounted(){
        if ( document.getElementById('wilcity-single-listing-content') ){
            this.fetchClaimFields();
        }
    },
    methods: {
        getVal: function getVal(key, isArray){
            if ( typeof this.oData[key] === 'undefined' ){
                return typeof isArray === true ? [] : '';
            }

            return this.oData[key];
        },
        closePopup: function closePopup(){
            this.$store.dispatch('closePopup', {
                id: 'wilcity-claim-popup',
                status: 'close'
            });
        },
        submitClaimRequest: function submitClaimRequest(event){
            var this$1 = this;

            if ( this.xhr !== null && this.xhr.status !== 200 ){
                this.xhr.abort();
            }
            this.postID = WILOKE_GLOBAL.postID;
            this.$parent.$emit('line-loading', 'yes');
            this.isSending = 'yes';

            this.oData.postID = this.postID;
            this.xhr = jQuery.ajax({
                url: WILOKE_GLOBAL.ajaxurl,
                type: 'POST',
                data: {
                    url: WILOKE_GLOBAL.ajaxurl,
                    action: 'wilcity_claim_request',
                    data: this.oData
                },
                success: (function (response){
                    if ( response.success ){
                        this$1.errMsg = '';
                        if ( typeof response.data.redirectTo !== 'undefined' ){
                            window.location.href = response.data.redirectTo;
                        }else{
                            this$1.successMsg = response.data.msg;

                            setTimeout(function (){
                                this$1.closePopup();
                            }, 4000);
                        }
                    }else{
                        this$1.errMsg = response.data.msg;
                    }

                    this$1.$parent.$emit('line-loading', 'no');
                    this$1.isSending = 'no';
                })
            });
        },
        checkboxTwoChanged: function checkboxTwoChanged(oNewVal, oSettings){
            this.oData[oSettings.key] = oNewVal;
        },
        textareaChanged: function textareaChanged(newVal, oSettings){
            this.oData[oSettings.key] = newVal;
        },
        radioChanged: function radioChanged(newVal, oSettings){
            this.oData[oSettings.key] = newVal;
        },
        inputChanged: function inputChanged(newVal, oSettings){
            this.oData[oSettings.key] = newVal;
        },
        selectTwoChanged: function selectTwoChanged(newVal, oSettings){
            this.oData[oSettings.key] = newVal;
        },
        fetchClaimFields: function fetchClaimFields(){
            var this$1 = this;

            if ( Object.values(this.oClaimFields).length ){
                return true;
            }

            this.isSending = 'yes';
            jQuery.ajax({
                type: 'POST',
                url: WILOKE_GLOBAL.ajaxurl,
                data: {
                    action: 'wilcity_get_claim_fields',
                    postID: WILOKE_GLOBAL.postID
                },
                success: function (response) {
                    if ( response.success ){
                        this$1.$set(this$1.$data, 'oClaimFields', response.data);
                    }else{
                        this$1.errMsg = response.data.msg;
                    }

                    this$1.isSending = 'no';
                }
            });
        }
    }
};

var WilokeDeleteReviewPopup = {render: function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('wiloke-popup',{attrs:{"popup-id":"wilcity-delete-review-popup","popup-title":_vm.title,"wrapper-class":"popup_module__3M-0- pos-f-full popup_sm__Rc24D popup_mobile-full__1hyc4"},on:{"on-close-popup":_vm.closePopup}},[_c('block-loading',{attrs:{"is-loading":_vm.isLoading,"position":""}}),_vm._v(" "),_c('div',{staticClass:"wil-scroll-container",attrs:{"slot":"body"},slot:"body"},[_c('wiloke-error-msg',{directives:[{name:"show",rawName:"v-show",value:(_vm.errMsg.length),expression:"errMsg.length"}],attrs:{"msg":_vm.errMsg,"has-remove":"false"}}),_vm._v(" "),_c('wiloke-message',{directives:[{name:"show",rawName:"v-show",value:(_vm.successMsg.length),expression:"successMsg.length"}],attrs:{"msg":_vm.successMsg,"has-remove":"false","icon":"la la-bullhorn","status":"success"}}),_vm._v(" "),_c('button',{directives:[{name:"show",rawName:"v-show",value:(!_vm.successMsg.length),expression:"!successMsg.length"}],staticClass:"wil-btn wil-btn--primary wil-btn--sm wil-btn--round mr-30",attrs:{"type":"submit"},domProps:{"innerHTML":_vm._s(_vm.yes)},on:{"click":function($event){$event.preventDefault();_vm.deleteReview($event);}}}),_vm._v(" "),_c('button',{directives:[{name:"show",rawName:"v-show",value:(!_vm.successMsg.length),expression:"!successMsg.length"}],staticClass:"wil-btn wil-btn--secondary wil-btn--sm wil-btn--round",attrs:{"type":"submit"},domProps:{"innerHTML":_vm._s(_vm.cancel)},on:{"click":function($event){$event.preventDefault();_vm.closePopup($event);}}})],1)],1)},staticRenderFns: [],
        data: function data(){
            return {
                errMsg: '',
                successMsg: '',
                oTranslation: WILCITY_I18,
                isLoading: 'no'
            }
        },
        props: ['cancel', 'yes', 'title'],
        components:{
            WilokeMessage: WilokeMessage,
            WilokeErrorMsg: WilokeErrorMsg,
            WilokePopup: WilokePopup
        },
        methods: {
            deleteReview: function deleteReview(){
                var this$1 = this;

                var oArgs = this.$store.getters.getPopupArgs('wilcity-delete-review-popup');
                if ( !oArgs || typeof oArgs.reviewID == 'undefined' ){
                   this.errMsg = this.oTranslation.reviewIDIsRequired;
                }
                this.$emit('line-loading', 'yes');

                jQuery.ajax({
                    type: 'POST',
                    url: WILOKE_GLOBAL.ajaxurl,
                    data: {
                        action: 'wilcity_delete_review',
                        reviewID: oArgs.reviewID
                    },
                    success: function (response) {
                        if ( response.success ){
                            this$1.successMsg = response.data.msg;
                            if ( jQuery('.wilcity-js-review-item-'+oArgs.reviewID).length ){
                                jQuery('.wilcity-js-review-item-'+oArgs.reviewID).remove();
                            }
                            setTimeout(function (){
                                this$1.closePopup();
                                this$1.errMsg = '';
                                this$1.successMsg = '';
                            }, 1000);
                        }else{
                            this$1.errMsg = response.data.msg;
                        }

                        this$1.$emit('line-loading', 'no');
                    }
                });
            },
            closePopup: function closePopup(){
                this.$store.commit('updatePopupStatus', {
					id: 'wilcity-delete-review-popup',
					status: 'close'
				});
            }
        }
    };

var WilokeReportPopup = {render: function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('wiloke-popup',{attrs:{"popup-id":"wilcity-report-popup","popup-title":_vm.oTranslation.reportTitle},on:{"on-close-popup":_vm.closePopup}},[_c('div',{attrs:{"slot":"body"},slot:"body"},[_c('div',{directives:[{name:"show",rawName:"v-show",value:(_vm.description.length),expression:"description.length"}],domProps:{"innerHTML":_vm._s(_vm.description)}}),_vm._v(" "),_c('wiloke-error-msg',{directives:[{name:"show",rawName:"v-show",value:(_vm.errMsg.length),expression:"errMsg.length"}],attrs:{"msg":_vm.errMsg,"has-remove":"false"}}),_vm._v(" "),_c('wiloke-message',{directives:[{name:"show",rawName:"v-show",value:(_vm.successMsg.length),expression:"successMsg.length"}],attrs:{"msg":_vm.successMsg,"has-remove":"false","icon":"la la-bullhorn","status":"success"}}),_vm._v(" "),_vm._l((_vm.aFields),function(oField){return (!_vm.isStopSubmitting)?_c('div',[(oField.type == 'textarea')?_c('wiloke-textarea',{attrs:{"settings":{isRequired: oField.isRequired, value: '', label: oField.label, key: oField.key}},on:{"textareaChanged":_vm.textareaChanged}}):(oField.type == 'select')?_c('wiloke-select-two',{attrs:{"settings":{options: oField.options, value: '', label: oField.label, key: oField.key}},on:{"selectTwoChanged":_vm.selectTwoChanged}}):_c('wiloke-input',{attrs:{"settings":{isRequired: oField.isRequired, value:'', label: oField.label, key: oField.key}},on:{"inputChanged":_vm.inputChanged}})],1):_vm._e()})],2),_vm._v(" "),(!_vm.isStopSubmitting)?_c('footer',{staticClass:"popup_footer__2pUrl clearfix",attrs:{"slot":"footer"},slot:"footer"},[_c('div',{staticClass:"popup_footerRight__qvdP6"},[_c('button',{staticClass:"wil-btn wil-btn--gray wil-btn--sm wil-btn--round",attrs:{"type":"submit"},on:{"click":function($event){$event.preventDefault();_vm.closePopup($event);}}},[_vm._v(_vm._s(_vm.oTranslation.cancel))]),_vm._v(" "),_c('button',{staticClass:"wil-btn wil-btn--primary wil-btn--sm wil-btn--round",attrs:{"type":"submit"},on:{"click":function($event){$event.preventDefault();_vm.submitReport($event);}}},[_vm._v(_vm._s(_vm.oTranslation.submit))])])]):_vm._e()])},staticRenderFns: [],
    data: function data(){
        return {
            oTranslation: WILCITY_I18,
            aFields: [],
            oData: {},
            description:'',
            errMsg:'',
            postID: '',
            successMsg: '',
            aStopSubmitting: [],
            xhr: null
        }
    },
    components:{
        WilokeMessage: WilokeMessage,
        WilokePopup: WilokePopup,
        WilokeInput: WilokeInput,
        WilokeTextarea: WilokeTextarea,
        WilokeRadio: WilokeRadio,
        WilokeCheckboxTwo: WilokeCheckboxTwo,
        WilokeSelectTwo: WilokeSelectTwo,
        WilokeErrorMsg: WilokeErrorMsg
    },
    mounted: function mounted(){
       this.fetchPopupFields();
    },
    computed: {
        isStopSubmitting: function isStopSubmitting(){
            return typeof this.aStopSubmitting[this.postID] !== 'undefined';
        }
    },
    methods: {
        inputChanged: function inputChanged(val, oSetting){
            this.oData[oSetting.key] = val;
        },
        selectTwoChanged: function selectTwoChanged(val, oSetting){
            this.oData[oSetting.key] = val;
        },
        textareaChanged: function textareaChanged(val, oSetting){
            this.oData[oSetting.key] = val;
        },
        fetchPopupFields: function fetchPopupFields(){
            var this$1 = this;

            if ( this.aFields.length ){
                return false;
            }

            this.errMsg = '';
            jQuery.ajax({
                type: 'POST',
                url: WILOKE_GLOBAL.ajaxurl,
                data:{
                    action: 'wilcity_fetch_report_fields'
                },
                success: function (response) {
                    if ( response.success ){
                        this$1.aFields = response.data.fields;
                        this$1.description = response.data.description;
                    }else{
                        this$1.errMsg = response.data.msg;
                    }
                }
            });
        },
        getVal: function getVal(key, isArray){
            if ( typeof this.oData[key] === 'undefined' ){
                return typeof isArray === true ? [] : '';
            }

            return this.oData[key];
        },
        closePopup: function closePopup(){
            this.$emit('onClosePopup', true);
        },
        submitReport: function submitReport(){
            var this$1 = this;

            if ( this.xhr !== null && this.xhr.status !== 200 ){
                this.xhr.abort();
            }

            if ( this.stopSubmitting ){
                return false;
            }

            this.$emit('line-loading', 'yes');
            this.successMsg = this.errMsg = '';
            var oArgs = this.$store.getters.getPopupArgs('wilcity-report-popup');

            this.xhr = jQuery.ajax({
                url: WILOKE_GLOBAL.ajaxurl,
                type: 'POST',
                data: {
                    url: WILOKE_GLOBAL.ajaxurl,
                    action: 'wilcity_submit_report',
                    data: this.oData,
                    postID: oArgs.reviewID
                },
                success: (function (response){
                    if ( response.success ){
                        this$1.$set(this$1.aStopSubmitting, oArgs.reviewID, true);
                        this$1.successMsg = response.data.msg;
                        setTimeout(function (){
                            this$1.closePopup();
                            this$1.successMsg = '';
                        }, 3000);
                    }else{
                        this$1.errMsg = response.data.msg;
                    }

                    this$1.$emit('line-loading', 'no');
                })
            });
        }
    }
};

var WilokeAddPhotosVideos = {render: function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('wiloke-popup',{attrs:{"popup-id":"wilcity-add-photos-videos-popup","popup-title":_vm.oTranslation.addPhotoVideoPopupTitle},on:{"on-close-popup":_vm.closePopup}},[_c('block-loading',{attrs:{"position":"pos-a-center","is-loading":_vm.isSending}}),_vm._v(" "),_c('div',{attrs:{"slot":"body"},slot:"body"},[_c('wiloke-message',{directives:[{name:"show",rawName:"v-show",value:(_vm.successMsg!==''),expression:"successMsg!==''"}],attrs:{"status":"success","icon":"la la-bullhorn","msg":_vm.successMsg,"has-remove":"false"}}),_vm._v(" "),_c('div',{staticClass:"promo-item_module__24ZhT"},[_c('div',{staticClass:"promo-item_group__2ZJhC"},[_c('h3',{staticClass:"promo-item_title__3hfHG"},[_vm._v(_vm._s(_vm.oTranslation.uploadMultipleImagesTitle))]),_vm._v(" "),_c('p',{staticClass:"promo-item_description__2nc26"},[_vm._v(_vm._s(_vm.oTranslation.uploadMultipleImagesDesc))])])]),_vm._v(" "),_c('div',{directives:[{name:"show",rawName:"v-show",value:(_vm.addPhotosMsg==''),expression:"addPhotosMsg==''"}]},[_c('wiloke-upload-img',{attrs:{"settings":{isMultiple: true, value: _vm.aImagesUploaded, wrapperClassName: 'field_module__1H6kT field_style2__2Znhe mb-15', oPlanSettings: _vm.oPlanSettings}},on:{"uploadImgChanged":_vm.updatingGallery}})],1),_vm._v(" "),_c('div',{directives:[{name:"show",rawName:"v-show",value:(_vm.addPhotosMsg!=''),expression:"addPhotosMsg!=''"}]},[_c('wiloke-message',{attrs:{"msg":_vm.addPhotosMsg,"status":''}})],1),_vm._v(" "),_c('div',{staticClass:"promo-item_module__24ZhT"},[_c('div',{staticClass:"promo-item_group__2ZJhC"},[_c('h3',{staticClass:"promo-item_title__3hfHG"},[_vm._v(_vm._s(_vm.oTranslation.uploadVideosTitle))]),_vm._v(" "),_c('p',{staticClass:"promo-item_description__2nc26"},[_vm._v(_vm._s(_vm.oTranslation.uploadVideosDesc))])])]),_vm._v(" "),_c('div',{directives:[{name:"show",rawName:"v-show",value:(_vm.addVideosMsg==''),expression:"addVideosMsg==''"}]},[_c('wiloke-video',{ref:"updateAddedVideos",attrs:{"settings":{addMoreBtnName: _vm.oTranslation.addVideoBtn, oPlanSettings: _vm.oPlanSettings, value: _vm.aVideosAdded}},on:{"videoChanged":_vm.updatingVideos}})],1),_vm._v(" "),_c('div',{directives:[{name:"show",rawName:"v-show",value:(_vm.addVideosMsg!=''),expression:"addVideosMsg!=''"}]},[_c('wiloke-message',{attrs:{"msg":_vm.addVideosMsg,"status":''}})],1)],1),_vm._v(" "),_c('footer',{staticClass:"popup_footer__2pUrl clearfix",attrs:{"slot":"footer"},slot:"footer"},[_c('div',{staticClass:"popup_footerRight__qvdP6"},[_c('button',{staticClass:"wil-btn wil-btn--gray wil-btn--sm wil-btn--round wilcity-close-popup",attrs:{"type":"submit"},on:{"click":function($event){$event.preventDefault();_vm.closePopup($event);}}},[_vm._v(_vm._s(_vm.oTranslation.cancel))]),_vm._v(" "),_c('button',{staticClass:"wil-btn wil-btn--primary wil-btn--sm wil-btn--round",attrs:{"type":"submit"},on:{"click":function($event){$event.preventDefault();_vm.submit($event);}}},[_vm._v(_vm._s(_vm.oTranslation.submit))])])])],1)},staticRenderFns: [],
    data: function data(){
        return{
            oTranslation: WILCITY_I18,
            oPlanSettings: {},
            aImagesUploaded: [],
            aVideosAdded: [],
            addVideosMsg: '',
            addPhotosMsg: '',
            successMsg: '',
            isSending: 'no',
            xhr: null
        }
    },
    components:{
        BlockLoading: BlockLoading,
        WilokeMessage: WilokeMessage,
        WilokeVideo: WilokeVideo,
        WilokeUploadImg: WilokeUploadImg,
        WilokePopup: WilokePopup
    },
    methods:{
        closePopup: function closePopup(){
            this.$store.dispatch('closePopup', {
                id: 'wilcity-add-photos-videos-popup',
                status: 'close'
            });
        },
        updatePlanSettings: function updatePlanSettings(response){
            if ( !Object.values(this.oPlanSettings).length &&  response.data.oPlanSettings !== 'undefined' && response.data.oPlanSettings ){
                this.oPlanSettings = response.data.oPlanSettings;
            }
        },
        loadPhotos: function loadPhotos(){
            var this$1 = this;

            if ( !this.aImagesUploaded.length ){
                jQuery.ajax({
                    type: 'POST',
                    url: WILOKE_GLOBAL.ajaxurl,
                    data: {
                        listingID: WILOKE_GLOBAL.postID,
                        action: 'fetch_photos_of_listing'
                    },
                    success: function (response) {
                        if ( !response.success ){
                            this$1.addPhotosMsg = response.data.msg;
                        }else{
                            if ( typeof response.data.images !== 'undefined' ){
                                this$1.aImagesUploaded = response.data.images;
                            }

                            this$1.updatePlanSettings(response);
                        }

                    }
                });
            }
        },
        loadVideos: function loadVideos(){
            var this$1 = this;

            if ( !this.aVideosAdded.length ){
                jQuery.ajax({
                    type: 'POST',
                    url: WILOKE_GLOBAL.ajaxurl,
                    data: {
                        listingID: WILOKE_GLOBAL.postID,
                        action: 'fetch_videos_of_listing'
                    },
                    success: function (response) {
                        if ( !response.success ){
                            this$1.addVideosMsg = response.data.msg;
                        }else{
                            if ( typeof response.data.videos !== 'undefined' ){
                                this$1.aVideosAdded = response.data.videos;
                                this$1.$refs.updateAddedVideos.updateDefault(this$1.aVideosAdded);
                            }
                            this$1.updatePlanSettings(response);
                        }
                    }
                });
            }
        },
        updatingGallery: function updatingGallery(aAddedPhotos){
            this.aImagesUploaded = aAddedPhotos;
        },
        updatingVideos: function updatingVideos(aVideosAdded){
            this.aVideosAdded = aVideosAdded;
        },
        submit: function submit(){
            var this$1 = this;

            if ( this.xhr !== null && this.xhr.status !== 200 ){
                this.xhr.abort();
            }
            this.isSending = 'yes';
            this.xhr = jQuery.ajax({
                type: 'POST',
                url: WILOKE_GLOBAL.ajaxurl,
                data: {
                    listingID: WILOKE_GLOBAL.postID,
                    action: 'update_gallery_and_videos',
                    videos: this.aVideosAdded,
                    gallery: this.aImagesUploaded
                },
                success: function (response) {
                    this$1.successMsg = response.data.msg;
                    this$1.isSending = 'no';

                    setTimeout(function (){
                        this$1.closePopup();
                    }, 3000);
                }
            });
        }
    },
    mounted: function mounted(){
        this.loadVideos();
        this.loadPhotos();
    }
};

var WilokeCustomContent = {render: function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{class:_vm.wrapperClass,attrs:{"id":_vm.wrapperId}},[_c('div',{staticClass:"content-box_module__333d9 pos-r"},[_c('div',{staticClass:"content-box_body__3tSRB"},[_c('div',{domProps:{"innerHTML":_vm._s(_vm.content)}})]),_vm._v(" "),_c('block-loading',{attrs:{"is-loading":_vm.isFetching,"position":"pos-a-center"}})],1)])},staticRenderFns: [],
    data: function data(){
        return {
            key: WILCITY_SINGLE_LISTING.navigation.draggable.photos.key,
            aDefaultNavKeys: WILCITY_SINGLE_LISTING_SETTINGS.aDefaultNavKeys,
            content: '',
            mode: '',
            isLoaded: false,
            isFetching: false
        }
    },
    computed: {
        wrapperClass: function wrapperClass(){
            var cl = 'single-tab-content custom-content';
            return this.mode == this.$store.getters.getCurrentNavTab ? cl + ' active' : cl + ' hidden';
        },
        wrapperId: function wrapperId(){
            return 'single-'+this.mode;
        }
    },
    components:{
        HeaderComponent: HeaderComponent,
        BlockLoading: BlockLoading,
        WilokeGallery: WilokeGallery
    },
    methods: {
        fetchContent: function fetchContent(){
            var this$1 = this;

            if ( this.isLoaded ){
                return true;
            }

            this.isFetching = true;
            this.$root.componentLoading = this.key;
            this.$root.ajaxFetching = jQuery.ajax({
                type: 'POST',
                url: WILOKE_GLOBAL.ajaxurl,
                data: {
                     action: 'wilcity_fetch_custom_content',
                     postID: WILOKE_GLOBAL.postID,
                     mode: this.mode
                },
                success: function (response){
                    if ( response.success ){
                        this$1.content = response.data;
                    }
                    this$1.isFetching = false;
                }
            });
        }
    },
    mounted: function mounted(){
        var this$1 = this;

        this.$parent.$on('fetchContent', (function (mode){
            if ( this$1.aDefaultNavKeys.indexOf(mode) === -1 ){
                this$1.mode = mode;
                this$1.fetchContent();
            }
        }));
    }
};

var WilokeDescription = {render: function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{class:_vm.wrapperClass,attrs:{"id":"single-content"}},[_c('div',{staticClass:"content-box_module__333d9 pos-r"},[_c('div',{staticClass:"content-box_body__3tSRB"},[_c('div',{domProps:{"innerHTML":_vm._s(_vm.content)}})]),_vm._v(" "),_c('block-loading',{attrs:{"is-loading":_vm.isFetching,"position":"pos-a-center"}})],1)])},staticRenderFns: [],
    data: function data(){
        return {
            key: 'content',
            oHeaderSettings: WILCITY_SINGLE_LISTING.navigation.draggable.content,
            key: WILCITY_SINGLE_LISTING.navigation.draggable.content.key,
            content: '',
            isFetching: 'yes',
            isLoaded: false
        }
    },
    computed: {
        wrapperClass: function wrapperClass(){
            var cl = 'single-tab-content';
            return this.key == this.$store.getters.getCurrentNavTab ? cl + ' active' : cl + ' hidden';
        }
    },
    components:{
        BlockLoading: BlockLoading,
        HeaderComponent: HeaderComponent
    },
    methods: {
        fetchContent: function fetchContent(){
            var this$1 = this;

            if ( this.isLoaded ){
                this.isFetching = 'no';
                return true;
            }

            this.$root.componentLoading = this.key;
            this.$root.ajaxFetching = jQuery.ajax({
                type: 'GET',
                url: WILOKE_GLOBAL.ajaxurl + '?action=wilcity_fetch_content&postID='+WILOKE_GLOBAL.postID,
                success: (function (response){
                    if ( response.success ){
                        this$1.content = response.data;
                    }else{
                        this$1.content = response.data.msg;
                    }
                    this$1.isFetching = 'no';
                })
            });
        }
    },
    mounted: function mounted(){
        var this$1 = this;

        if ( this.isLoaded ){
            return false;
        }
        this.$parent.$on('fetchContent', (function (mode){
            if ( this$1.key == mode ){
                this$1.fetchContent();
                this$1.isLoaded = true;
            }
        }));
    }
};

var WilokeSingleCompareViews = {render: function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{class:_vm.wrapperClass},[_c('div',{class:_vm.statusClass},[_c('div',{staticClass:"stats-nuggets_icon__3r4oz"},[_c('i',{class:_vm.iconClass})]),_vm._v(" "),_c('span',{staticClass:"stats-nuggets_total__33yvd"},[_vm._v(_vm._s(_vm.countChanged))]),_c('span',{staticClass:"stats-nuggets_text__1bYqn"},[_vm._v(_vm._s(_vm.text))])])])},staticRenderFns: [],
    data: function data(){
        return{
            is: '', // is up or down
            countChanged: 0,
            text: ''
        }
    },
    props: ['wrapperClass', 'placeHolderText'],
    computed: {
        statusClass: function statusClass(){
            return 'stats-nuggets_module__2P_dg stats-nuggets_'+this.is+'__1X_Mp mb-15';
        },
        iconClass: function iconClass(){
            return 'la la-arrow-'+this.is;
        }
    },
    mounted: function mounted(){
        var this$1 = this;

        this.viewText = this.placeHolderText;
        this.is = 'up';

        jQuery.ajax({
            type: 'POST',
            data: {
                action: 'wilcity_fetch_compare_views',
                postID: WILOKE_GLOBAL.postID
            },
            url: WILOKE_GLOBAL.ajaxurl,
            success: function (response) {
                this$1.is = response.data.is;
                this$1.text = response.data.text;
                this$1.countChanged = response.data.number;
            }
        });
    }
};

var WilokeSingleCompareFavorites = {render: function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{class:_vm.wrapperClass},[_c('div',{class:_vm.statusClass},[_c('div',{staticClass:"stats-nuggets_icon__3r4oz"},[_c('i',{class:_vm.iconClass})]),_vm._v(" "),_c('span',{staticClass:"stats-nuggets_total__33yvd"},[_vm._v(_vm._s(_vm.countChanged))]),_c('span',{staticClass:"stats-nuggets_text__1bYqn"},[_vm._v(_vm._s(_vm.text))])])])},staticRenderFns: [],
    data: function data(){
        return{
            is: '', // is up or down
            countChanged: 0,
            text: ''
        }
    },
    props: ['wrapperClass', 'placeHolderText'],
    computed: {
        statusClass: function statusClass(){
            return 'stats-nuggets_module__2P_dg stats-nuggets_'+this.is+'__1X_Mp mb-15';
        },
        iconClass: function iconClass(){
            return 'la la-arrow-'+this.is;
        }
    },
    mounted: function mounted(){
        var this$1 = this;

        this.viewText = this.placeHolderText;
        this.is = 'up';

        jQuery.ajax({
            type: 'POST',
            data: {
                action: 'wilcity_fetch_compare_favorites',
                postID: WILOKE_GLOBAL.postID
            },
            url: WILOKE_GLOBAL.ajaxurl,
            success: function (response) {
                this$1.is = response.data.is;
                this$1.text = response.data.text;
                this$1.countChanged = response.data.number;
            }
        });
    }
};

var WilokeSingleCompareShares = {render: function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{class:_vm.wrapperClass},[_c('div',{class:_vm.statusClass},[_c('div',{staticClass:"stats-nuggets_icon__3r4oz"},[_c('i',{class:_vm.iconClass})]),_vm._v(" "),_c('span',{staticClass:"stats-nuggets_total__33yvd"},[_vm._v(_vm._s(_vm.countChanged))]),_c('span',{staticClass:"stats-nuggets_text__1bYqn"},[_vm._v(_vm._s(_vm.text))])])])},staticRenderFns: [],
    data: function data(){
        return{
            is: '', // is up or down
            countChanged: 0,
            text: ''
        }
    },
    props: ['wrapperClass', 'placeHolderText'],
    computed: {
        statusClass: function statusClass(){
            return 'stats-nuggets_module__2P_dg stats-nuggets_'+this.is+'__1X_Mp mb-15';
        },
        iconClass: function iconClass(){
            return 'la la-arrow-'+this.is;
        }
    },
    mounted: function mounted(){
        var this$1 = this;

        this.viewText = this.placeHolderText;
        this.is = 'up';

        jQuery.ajax({
            type: 'POST',
            data: {
                action: 'wilcity_fetch_compare_shares',
                postID: WILOKE_GLOBAL.postID
            },
            url: WILOKE_GLOBAL.ajaxurl,
            success: function (response) {
                this$1.is = response.data.is;
                this$1.text = response.data.text;
                this$1.countChanged = response.data.number;
            }
        });
    }
};

var WilokeMessageBtn = {render: function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('a',{class:_vm.wrapperClass,attrs:{"href":"#"},on:{"click":function($event){$event.preventDefault();_vm.openMessagePopup($event);}}},[_c('i',{staticClass:"la la-envelope color-1"}),_vm._v(_vm._s(_vm.btnName)+" ")])},staticRenderFns: [],
        props:{
            btnName: {
                type: String,
                default: ''
            },
            wrapperClass: {
                type: String,
                default: 'wil-btn wil-btn--border wil-btn--round wil-btn--sm'
            }
        },
        methods:{
            openMessagePopup: function openMessagePopup(){
                if ( WILOKE_GLOBAL.isUserLoggedIn !== 'yes' ){
					this.$store.commit('updatePopupStatus', {
						id: 'wilcity-signin-popup',
						status: 'open'
					});
				}else{
					this.$store.commit('updatePopupStatus', {
						id: 'wilcity-message-popup',
						status: 'open'
					});
				}
            }
        }
    };

var WilokeReviewPopupBtn = {render: function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('a',{class:_vm.wrapperClass,attrs:{"href":"#"},on:{"click":function($event){$event.preventDefault();_vm.onOpenReviewPopup($event);}}},[_c('span',{staticClass:"list_icon__2YpTp"},[_c('i',{class:_vm.icon})]),_c('span',{staticClass:"list_text__35R07",domProps:{"innerHTML":_vm._s(_vm.btnName)}})])},staticRenderFns: [],
    data: function data(){
        return{
            oTranslation: WILCITY_I18
        }
    },
    props: ['reviewId', 'btnName', 'icon', 'wrapperClass'],
    methods:{
        onOpenReviewPopup: function onOpenReviewPopup(){
            this.$emit('on-open-review-popup', this.reviewId);
        }
    }
};

var WilokeReportPopupBtn = {render: function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('a',{class:_vm.wrapperClass,attrs:{"href":"#"},on:{"click":function($event){$event.preventDefault();_vm.onOpenReportPopup($event);}}},[_vm._t("insideBtn")],2)},staticRenderFns: [],
        props: {
            targetId:{
                type: String,
                default: ''
            },
            wrapperClass:{
                type: String,
                default: 'wil-btn wil-btn--border wil-btn--round wil-btn--sm'
            }
        },
        methods: {
            onOpenReportPopup: function onOpenReportPopup(){
                this.$store.commit('updatePopupStatus', {
					id: 'wilcity-report-popup',
					status: 'open'
				});

				this.$store.commit('updatePopupArgs', {
					id: 'wilcity-report-popup',
					oArgs: {
						reviewID: this.targetId
					}
				});
            }
        }
    };

var WilokeClaimPopupBtn = {render: function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('a',{class:_vm.wrapperClass,attrs:{"href":"#"},on:{"click":function($event){$event.preventDefault();_vm.onOpenClaimPopup($event);}}},[_vm._t("insideBtn")],2)},staticRenderFns: [],
        props: {
            wrapperClass: {
                type: String,
                default: 'wil-btn wil-btn--dark wil-btn--sm wil-btn--round'
            },
            targetId: {
                type: String,
                default: ''
            }
        },
        methods:{
            onOpenClaimPopup: function onOpenClaimPopup(){
                if ( WILOKE_GLOBAL.isUserLoggedIn !== 'yes' && WILOKE_GLOBAL.isPaidClaim === 'no' ){
					this.$store.commit('updatePopupStatus', {
						id: 'wilcity-signin-popup',
						status: 'open'
					});
				}else{
					this.$store.commit('updatePopupStatus', {
						id: 'wilcity-claim-popup',
						status: 'open'
					});
				}
            }
        }
    };

var WilokeSwitchTabBtn = {render: function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('a',{class:_vm.wrapperClass,attrs:{"href":"#"},on:{"click":function($event){$event.preventDefault();_vm.switchTab($event);}}},[_vm._t("insideTab")],2)},staticRenderFns: [],
    props: {
        wrapperClass:{
            type: String,
            default: 'list_link__2rDA1 text-ellipsis color-primary--hover'
        },
        tabKey: {
            type: String,
            default: ''
        }
    },
    methods: {
        switchTab: function switchTab(){
            this.$emit('on-switch-tab', this.tabKey);
            jQuery('.list_item__3YghP[data-tab-key="'+this.tabKey+'"]').find('.list_link__2rDA1').trigger('click');
        }
    }
};

Vue.config.devtools = false;

var $body = jQuery('body');
jQuery.fn.setCursorPosition = function (pos) {
	var elem = jQuery(this)[0];

	if (typeof elem.setSelectionRange !== 'undefined') {
		elem.setSelectionRange(pos, pos);
	} else if (this.createTextRange) {
		var range = elem.createTextRange();
		range.collapse(true);
		range.moveEnd('character', pos);
		range.moveStart('character', pos);
		range.select();
	}
	return this;
};

window.WilCityHelpers = Helpers;

// Shortcodes
Vue.use(Vuex);

window.WILCITY_VUEX = new Vuex.Store({
	state: {
		oPopupStatus: {
			'wilcity-signin-popup': 'close',
			'wilcity-message-popup': 'close',
			'wilcity-claim-popup':'close',
			'wilcity-add-photos-videos-popup': 'close',
			'wilcity-search-form-popup': 'close',
			'wilcity-delete-review-popup': 'close',
			'wilcity-report-popup': 'close',
			'write-a-review-popup': 'close'
		},
		currentListingTab: '',
		defaultComponentOfRegisterLogin: 'login',
		oSettingTabs: {
			currentTab: 'general'
		},
		oNavTab: {
			current: 'home'
		},
		oSingleGallery: {
			id: '',
			aImages: []
		},
		oPopUpArgs: {
			'wilcity-signin-popup': {},
			'wilcity-report-popup': {},
			'wilcity-message-popup': {},
			'wilcity-claim-popup': {},
			'wilcity-add-photos-videos-popup': {},
			'wilcity-search-form-popup': {},
			'wilcity-delete-review-popup': {},
			'write-a-review-popup': {},
			'wilcity-review-info': {}
		},
		oReviewsInfo: {},
		compareTimeout: 100,
		aRequireLoggedIn: ['wilcity-add-photos-videos-popup', 'wilcity-message-popup'],
		aReviews: [],
		oSearchArgs: {},
		postType: '',
		maxPages: 0,
		foundPosts: 0,
		currentPage: 0,
		postsPerPage: 0,
		elMouseOn: null,
		isSearchAsIMoveTheMap: false,
		aNewListings: [],
		oTermOptions: {},
		aNewBusinessHoursOptions: {
			cId: '',
			aHours: []
		},
		aIcons: [],
		oSearchFields: {},
		oPlanSettings: typeof WILCITY_ADDLISTING !== 'undefined' ? WILCITY_ADDLISTING.aPlanSettings : {}
	},
	mutations: {
		updateSearchField: function updateSearchField(state, oData){
			state.oSearchFields[oData.postType] = oData.aFields;
		},
		updateNewBusinessHours: function updateNewBusinessHours(state, aNewHours){
			state.aNewBusinessHoursOptions.cId = aNewHours.cId;
			state.aNewBusinessHoursOptions.aHours = aNewHours.aHours;
		},
		updatePlanSettings: function updatePlanSettings(state, oPlanSettings){
			state.oPlanSettings.cId = oPlanSettings;
		},
		setDefaultComponentOfRegisterLoginPopup: function setDefaultComponentOfRegisterLoginPopup(state, component){
			state.defaultComponentOfRegisterLogin = component;
		},
		updateRegisterLoginPopupStatus: function updateRegisterLoginPopupStatus(state, status){
			state.oPopupStatus['wilcity-signin-popup'] = status;
		},
		updatePopupArgs: function updatePopupArgs(state, oPopupInfo){
			if ( typeof state.oPopUpArgs[oPopupInfo.id] === 'undefined' ){
				state.oPopUpArgs[oPopupInfo.id] = oPopupInfo.oArgs;
			}else{
				state.oPopUpArgs[oPopupInfo.id] = oPopupInfo.oArgs;
			}
		},
		updatePopupStatus: function updatePopupStatus(state, oPopupInfo){
			if ( typeof state.oPopupStatus[oPopupInfo.id] === 'undefined' ){
				state.oPopupStatus[oPopupInfo.id] = oPopupInfo.status;
			}else{
				state.oPopupStatus[oPopupInfo.id] = oPopupInfo.status;
			}
		},
		updateCurrentSettingTab: function updateCurrentSettingTab(state, tabKey){
			state.oSettingTabs.currentTab = tabKey;
		},
		updateCurrentNavTab: function updateCurrentNavTab(state, tabKey){
			state.oNavTab.current = tabKey;
		},
		updateSingleGallery: function updateSingleGallery(state, oGallery){
			state.oSingleGallery.id = oGallery.id;
			state.oSingleGallery.aImages = state.oSingleGallery.aImages.concat(oGallery.aImages);
		},
		updateReviews: function updateReviews(state, aReviews){
			state.aReviews = aReviews.concat(state.aReviews);
		},
		updatePinReviewToTop: function updatePinReviewToTop(state, order){
			var oReview = state.aReviews[order];
			oReview.isPintToTop = 'yes';

			state.aReviews.splice(order, 1);
			state.aReviews.splice(0, 0, oReview);
		},
		updateCompareTimeout: function updateCompareTimeout(){

		},
		updateNewListings: function updateNewListings(state, aListings){
			state.aNewListings = aListings;
		},
		updateOSearchArgs: function updateOSearchArgs(state, oNewVal){
			state.oSearchArgs = oNewVal;
		},
		updatePostType: function updatePostType(state, newVal){
			state.postType = newVal;
		},
		updateTotalListings: function updateTotalListings(state, foundPosts){
			state.foundPosts = parseInt(foundPosts, 10);
		},
		updateCurrentPage: function updateCurrentPage(state, currentPage){
			state.currentPage = parseInt(currentPage, 10);
		},
		updateMaxPages: function updateMaxPages(state, maxPages){
			state.maxPages = parseInt(maxPages, 10);
		},
		updatePostsPerPage: function updatePostsPerPage(state, postsPerPage){
			state.postsPerPage = parseInt(postsPerPage, 10);
		},
		updateMouseOn: function updateMouseOn(state, oListing){
			state.elMouseOn = oListing;
		},
		updateTermOptions: function updateTermOptions(state, oData){
			state.oTermOptions[oData.taxonomy] = oData.aOptions;
		},
		updateReviewInfo: function updateReviewInfo(state, oInfo){
			state.oReviewsInfo = oInfo;
		},
		updateCurrentListingTab: function updateCurrentListingTab(state, tabKey){
			state.currentListingTab = tabKey;
		},
		updateIcons: function updateIcons(state, aIcons){
			state.aIcons = aIcons;
		}
	},
	getters: {
		getSearchFields: function (state) { return function (postType) {
			if ( typeof state.oSearchFields[postType] !== 'undefined' ){
				return state.oSearchFields[postType];
			}

			return false;
		}; },
		getNewListings: function getNewListings(state){
			return state.aNewListings;
		},
		getTotalListings: function getTotalListings(state){
			return state.foundPosts;
		},
		getCurrentPage: function getCurrentPage(state){
			return state.currentPage;
		},
		getMaxPages: function getMaxPages(state){
			return state.maxPages;
		},
		getSearchArgs: function getSearchArgs(state){
			return state.oSearchArgs;
		},
		getPostType: function getPostType(state){
			return state.postType;
		},
		getPostsPerPage: function getPostsPerPage(state){
			return state.postsPerPage;
		},
		getIsSearchAsIMoveTheMap: function getIsSearchAsIMoveTheMap(state){
			return state.isSearchAsIMoveTheMap;
		},
		getDefaultComponentOfRegisterLogin: function (state) {
			return state.defaultComponentOfRegisterLogin;
		},
		getPopupStatus: function (state) { return function (popupID) {
			if ( typeof popupID !== 'undefined' ){
				return state.oPopupStatus[popupID];
			}
			return state.oPopupStatus;
		}; },
		getPopupArgs: function (state) { return function (popupID) {
			if ( typeof state.oPopUpArgs[popupID] === 'undefined' ){
				return false;
			}
			return state.oPopUpArgs[popupID];

		}; },
		getCurrentSettingTab: function (state){
			return state.oSettingTabs.currentTab;
		},
		getCurrentNavTab: function (state){
			return state.oNavTab.current;
		},
		getSingleGallery: function (state) {
			return state.oSingleGallery.aImages;
		},
		getReviews: function (state) {
			return state.aReviews;
		},
		getTermOptions: function (state) { return function (taxonomy) {
			if ( typeof state.oTermOptions[taxonomy] !== 'undefined' ){
				return state.oTermOptions[taxonomy];
			}
			return false;
		}; },
		changedBusinessHoursOptions: function (state) {
			return state.aBusinessHoursOptions.aHours
		},
		getPlanSettings: function (state) {
			return state.oPlanSettings;
		},
		getReviewInfo: function (state) {
			if ( Object.values(state.oReviewsInfo).length ){
				return state.oReviewsInfo;
			}
			return false;
		},
		getCurrentListingTab: function getCurrentListingTab(state){
			return state.currentListingTab;
		},
		getIcons: function getIcons(state){
			return state.aIcons.length ? state.aIcons : false;
		}
	},

	actions: {
		openRegisterLoginPopup: function openRegisterLoginPopup(context){
			context.commit('updateRegisterLoginPopupStatus', 'open');
		},
		setRegisterAsDefaultComponentOfRegisterLoginPopup: function setRegisterAsDefaultComponentOfRegisterLoginPopup(context){
			context.commit('setDefaultComponentOfRegisterLoginPopup', 'register');
		},
		setLoginAsDefaultComponentOfRegisterLoginPopup: function setLoginAsDefaultComponentOfRegisterLoginPopup(context){
			context.commit('setDefaultComponentOfRegisterLoginPopup', 'login');
		},
		setLostPasswordAsDefaultComponentOfRegisterLoginPopup: function setLostPasswordAsDefaultComponentOfRegisterLoginPopup(context){
			context.commit('setDefaultComponentOfRegisterLoginPopup', 'lost-password');
		},
		closeRegisterLoginPopup: function closeRegisterLoginPopup(context){
			context.commit('updateRegisterLoginPopupStatus', 'close');
		},
		closePopup: function closePopup(context, oPopupInfo){
			context.commit('updatePopupStatus', oPopupInfo);
		},
		openSearchFormPopup: function openSearchFormPopup(context){
			context.commit('updatePopupStatus', {
				id: 'wilcity-search-form-popup',
				status: 'open'
			});
		},
		closeSearchFormPopup: function closeSearchFormPopup(context){
			context.commit('updatePopupStatus', {
				id: 'wilcity-search-form-popup',
				status: 'close'
			});
		}
	}
});

if ( document.getElementById('wilcity-addlisting') ){
	WILOKE_GLOBAL.vm = new Vue({
		el: '#wilcity-addlisting',
		store: WILCITY_VUEX,
		components: {
			WilokeUploadImg: WilokeUploadImg,
			WilokeInput: WilokeInput,
			WilokeCheckbox: WilokeCheckbox,
			WilokeCheckboxTwo: WilokeCheckboxTwo,
			WilokeTextarea: WilokeTextarea,
			WilokeRadio: WilokeRadio,
			WilokeDateTime: WilokeDateTime,
			WilokeSelectTwo: WilokeSelectTwo,
			WilokeEmail: WilokeEmail,
			WilokeUrl: WilokeUrl,
			WilokeSocialNetworks: WilokeSocialNetworks,
			WilokeMap: WilokeMap,
			WilokeVideo: WilokeVideo,
			WilokeTags: WilokeTags,
			WilokeBusinessHours: WilokeBusinessHours,
			WilokeCategory: WilokeCategory,
			WilokeEventCalendar: WilokeEventCalendar,
			WilokePriceRange: WilokePriceRange
		},
		data: function data(){
			return {
				oUsedSections: WILCITY_ADDLISTING.aUsedSections,
				oAllSections: WILCITY_ADDLISTING.aAllSections,
				oSectionValues: WILCITY_ADDLISTING.aSectionValues,
				oAllSocialNetworks: WILCITY_ADDLISTING.oSocialNetworks,
				oPlanSettings: WILCITY_ADDLISTING.aPlanSettings,
				ajaxHandleReview: null,
				listingID: WILCITY_ADDLISTING.listingID,
				planID: WILCITY_ADDLISTING.planID,
				listingType: WILCITY_ADDLISTING.listingType,
				isWorkingOn: '',
				isSubmitting: false,
				aSectionsRange: [],
				scrollBarTop: 0,
				aErrors:[]
			}
		},
		mounted: function mounted(){
			this.calculateMaxMinHeightOfSection();
		},
		methods:{
			activeSidebar: function activeSidebar(sectionID){
				jQuery(this.$el).find('.list_item__3YghP').removeClass('active');
				jQuery(this.$el).find('a[href="#'+sectionID+'"]').parent().addClass('active');
			},
			calculateMaxMinHeightOfSection: function calculateMaxMinHeightOfSection(){
				var $sections = jQuery(this.$el).find('#wilcity-addlisting-form').children();
				if ( !$sections.length ){
					return false;
				}
				var self = this;
				$sections.each(function () {
					var sectionID = jQuery(this).attr('id');
					if ( typeof sectionID !== 'undefined' ){
						var $section = jQuery('#'+sectionID);

						self.aSectionsRange.push({
							max: $section.offset().top + $section.outerHeight(),
							min: $section.offset().top,
							id: sectionID
						});
					}
				});

				this.activeSidebar(self.aSectionsRange[0].id);
				this.listenScroll();
			},
			listenScroll: function listenScroll(){
				var this$1 = this;

				var isScrolling, windowHeight = jQuery(window).height(), countSections = this.aSectionsRange.length - 1;
				jQuery(window).scroll(function (){
					window.clearTimeout( isScrolling );
					isScrolling = setTimeout(function (){
						var scrollTop = jQuery(window).scrollTop();
						if ( scrollTop !== this$1.scrollBarTop ){
							this$1.scrollBarTop = scrollTop;
							for ( var order = 0; order <= countSections; order++ ){
								if ( this$1.aSectionsRange[order].min > (scrollTop) && this$1.aSectionsRange[order].min < (windowHeight+scrollTop) ){
									this$1.activeSidebar(this$1.aSectionsRange[order].id);
									break;
								}
							}
						}
					}, 80);
				});
			},
			scrollTo: function scrollTo(sectionKey){
				this.isWorkingOn = sectionKey;
				var sectionID = this.sectionID(sectionKey, true);

				jQuery('html').animate({
					scrollTop:  jQuery(sectionID).offset().top - Helpers.additionalHeightToScrollTop()
				}, 500);
			},
			sidebarClass: function sidebarClass(sectionKey){
				return {
					'list_item__3YghP': 1===1,
					'active': sectionKey === this.isWorkingOn
				}
			},
			sectionID: function sectionID(id, addHash){
				var generateID = 'wilcity-addlisting-'+id;
				if ( typeof addHash !== 'undefined' ){
					generateID = '#' + generateID;
				}
				return generateID;
			},
			handlePreview: function handlePreview(){
				var this$1 = this;

				if ( this.ajaxHandleReview !== null && this.ajaxHandleReview.status !== 200 ){
					this.ajaxHandleReview.abort();
				}

				$body.trigger('topLoading');
				this.isSubmitting = true;

				var oSaveValues = [], that = this;

				this.oUsedSections.forEach(function (oSection){
					oSaveValues[oSection.key] = [];
					for ( var fieldKey in oSection.fields ){
						oSaveValues[oSection.key][fieldKey] = oSection.fields[fieldKey].value;
					}
				});

				var ajaxAct = 'wilcity_handle_review_';
				if ( this.listingType === 'event' ){
					ajaxAct += 'event';
				}else{
					ajaxAct += 'listing';
				}

				this.aErrors = [];

				this.ajaxHandleReview = jQuery.ajax({
					type: 'POST',
					url: WILOKE_GLOBAL.ajaxurl,
					data: {
						action: ajaxAct,
						data: this.oUsedSections,
						listingID: this.listingID,
						listingType: this.listingType,
						planID: this.planID
					},
					success:(function (response){
						if ( !response.success ){
							if ( typeof response.data.msg === 'object' ){
								Vue.set(that.oUsedSections[response.data.msg.index]['fields'][response.data.msg.fieldKey], 'errMsg', response.data.msg.msg);
							}else{
								if ( typeof response.data.msg !== 'undefined' ){
									this$1.aErrors.push(response.data.msg);
								}else{
									this$1.aErrors.push(response.data);
								}
							}
						}else{
							window.location.href = response.data.msg;
						}

						$body.trigger('topHideLoading');
						this$1.isSubmitting = false;
					})
				});

			}
		},
		computed:{
			submitBtnClass: function submitBtnClass(){
				return {
					'disable wil-btn--loading': this.isSubmitting
				}
			}
		}
	});
}

if ( document.getElementById('wilcity-single-event-wrapper') ) {
	WILOKE_GLOBAL.vmSingleEvent = new Vue({
		el: '#wilcity-single-event-wrapper',
		components: {
			WilokeCommentForm: WilokeCommentForm
		}
	});
}

if ( document.getElementById('wilcity-single-post') ) {
	WILOKE_GLOBAL.vmSinglePost = new Vue({
		el: '#wilcity-single-post',
		data: {
			isAjaxFetching: false,
			ajaxFetching: null,
			tabKey: '',
			currentNavId: 'home',
			componentLoading: ''
		},
		components: {
			WilokeCommentForm: WilokeCommentForm
		},
		methods: {
			wrapperContentClass: function wrapperContentClass(contentID, additionalClass){
				if ( this.currentNavId === contentID ){
					return additionalClass + ' active';
				}else{
					return additionalClass + ' hidden';
				}
			},
			liClass: function liClass(target){
				if ( this.currentNavId === target ){
					return 'list_item__3YghP active';
				}

				return 'list_item__3YghP';
			},
			switchTab: function switchTab(event){
				event.preventDefault();
				var tabKey = jQuery(event.currentTarget).data('tab'), $el = jQuery('a[data-tab="' + tabKey + '"]');
				this.currentNavId = tabKey;
				this.$store.commit('updateCurrentNavTab', tabKey);

				if (this.componentLoading === tabKey) {
					return false;
				}

				if (this.ajaxFetching !== null && this.ajaxFetching.status !== 200) {
					this.ajaxFetching.abort();
				}
				this.$emit('fetchContent', tabKey);
			}
		}
	});
}

if ( document.getElementById('wilcity-single-listing-content') ) {
	WILOKE_GLOBAL.vmSingleListingSetings = new Vue({
		el: '#wilcity-single-listing-content',
		data: {
			isAjaxFetching: false,
			ajaxFetching: null,
			tabKey: '',
			msg: '',
			msgStatus: '',
			msgIcon:'',
			currentNavId: 'home',
			componentLoading: '',
			//additionalMainClass: jQuery('#single-home').parent().data('additionalClass'),
			msgTimeout: null
		},
		store: WILCITY_VUEX,
		components: {
			WilokeMessage: WilokeMessage,
			WilokeDiscussions: WilokeDiscussion,
			WilokePromotionPopup: WilokePromotionPopup,
			WilokeSingleCompareFavorites: WilokeSingleCompareFavorites,
			WilokeSingleCompareViews: WilokeSingleCompareViews,
			WilokeSingleCompareShares: WilokeSingleCompareShares,
			WilokeDescription: WilokeDescription,
			WilokeSingleEditSidebar: WilokeSingleEditSidebar,
			WilokeSingleEditNavigation: WilokeSingleEditNavigation,
			WilokeSingleGeneral: WilokeSingleGeneral,
			WilokeSingleListingReviews: WilokeSingleListingReviews,
			WilokeSingleSidebar: WilokeSingleSidebar,
			WilokeSingleHeader: WilokeHeader,
			WilokeGallery: WilokeGallery,
			WilokeCustomContent: WilokeCustomContent,
			WilokeSocialsSharing: WilokeSocialsSharing,
			WilokeVideoGallery: WilokeVideoGallery,
			WilokeSingleListingPhotos: WilokeSingleListingPhotos,
			WilokeSingleListingVideos: WilokeSingleListingVideos,
			WilokeSingleListingEvents: WilokeSingleListingEvents,
			//WilokeSingleListingReviewPopup,
			WilokeDynamicPopup: WilokeDynamicPopup,
			WilokeReviewStatistic: WilokeReviewStatistic,
			WilokeAverageRating: WilokeAverageRating,
			WilokeEventPopup: WilokeEventPopup,
			WilokeMessageBtn: WilokeMessageBtn,
			WilokeReviewPopupBtn: WilokeReviewPopupBtn,
			WilokeReportPopupBtn: WilokeReportPopupBtn,
			WilokeClaimPopupBtn: WilokeClaimPopupBtn,
			WilokeSwitchTabBtn: WilokeSwitchTabBtn
		},
		mounted: function mounted(){
			var this$1 = this;

			this.removeBtn();
			this.updateMsg();
			this.triggerTab();
			this.$on('on-open-review-popup', (function (reviewID){
				this$1.onOpenReviewPopup(reviewID);
			}));
		},
		methods: {
			openMessagePopup: function openMessagePopup(){
				if ( WILOKE_GLOBAL.isUserLoggedIn !== 'yes' ){
					this.$store.commit('updatePopupStatus', {
						id: 'wilcity-signin-popup',
						status: 'open'
					});
				}else{
					this.$store.commit('updatePopupStatus', {
						id: 'wilcity-message-popup',
						status: 'open'
					});
				}
			},
			removeBtn: function removeBtn(){
				var aButtons = this.$el.querySelectorAll('.wilcity-temporary-disable');
				if ( typeof aButtons !== 'undefined' ){
					for (var btnOrder = 0; btnOrder < aButtons.length; btnOrder++){
						aButtons[btnOrder].classList.remove('disable');
					}
				}
			},
			updateMsg: function updateMsg(){
				var this$1 = this;

				this.$on('onPrintMsg', function (oData){
					if ( this$1.msgTimeout !== null ){
						clearTimeout(this$1.msgTimeout);
					}
					this$1.msg = oData.msg;
					this$1.msgIcon = oData.msgIcon;
					this$1.msgStatus = oData.msgStatus;

					this$1.msgTimeout = setTimeout(function (){
						this$1.msg = '';
					}, 4000);
				});
			},
			wrapperContentClass: function wrapperContentClass(contentID, additionalClass){
				if ( this.currentNavId === contentID ){
					return additionalClass + ' active';
				}else{
					return additionalClass + ' hidden';
				}
			},
			liClass: function liClass(target){
				if ( this.currentNavId === target ){
					return 'list_item__3YghP active';
				}

				return 'list_item__3YghP';
			},
			switchTab: function switchTab(tabKey){
				this.currentNavId = tabKey;
				this.$store.commit('updateCurrentListingTab', tabKey);
				this.$store.commit('updateCurrentNavTab', tabKey);

				if ( this.componentLoading === tabKey ){
					return false;
				}

				if ( this.ajaxFetching !== null && this.ajaxFetching.status !== 200 ){
					this.ajaxFetching.abort();
				}

				this.$emit('fetchContent', tabKey);
			},
			onOpenReportPopup: function onOpenReportPopup(reviewID){
				this.$store.commit('updatePopupStatus', {
					id: 'wilcity-report-popup',
					status: 'open'
				});

				this.$store.commit('updatePopupArgs', {
					id: 'wilcity-report-popup',
					oArgs: {
						reviewID: reviewID
					}
				});
			},
			onOpenReviewPopup: function onOpenReviewPopup(reviewID){
				var this$1 = this;

				if ( WILOKE_GLOBAL.isUserLoggedIn === 'no' ){
					this.$store.commit('updatePopupStatus', {
						id: 'wilcity-signin-popup',
						status: 'open'
					});
					return false;
				}

				if ( typeof reviewID !== 'undefined' ){
					jQuery.ajax({
						type: 'POST',
						url: WILOKE_GLOBAL.ajaxurl,
						data: {
							action: 'wilcity_fetch_user_reviewed_data',
							reviewID: reviewID
						},
						success: (function (response){
							if ( response.success ){
								this$1.$store.commit('updateReviewInfo', response.data);
							}
						})
					});

				}

				this.$store.commit('updatePopupStatus', {
					id: 'write-a-review-popup',
					status: 'open'
				});

				this.$store.commit('updatePopupArgs', {
					id: 'write-a-review-popup',
					oArgs: {
						reviewID: reviewID,
						postID: WILOKE_GLOBAL.postID
					}
				});
			},
			onOpenClaimPopup: function onOpenClaimPopup(postID){
				if ( WILOKE_GLOBAL.isUserLoggedIn !== 'yes' && WILOKE_GLOBAL.isPaidClaim === 'no' ){
					this.$store.commit('updatePopupStatus', {
						id: 'wilcity-signin-popup',
						status: 'open'
					});
				}else{
					this.$store.commit('updatePopupStatus', {
						id: 'wilcity-claim-popup',
						status: 'open'
					});
				}
			},
			onOpenDeleteReviewPopup: function onOpenDeleteReviewPopup(reviewID){
				this.$store.commit('updatePopupStatus', {
					id: 'wilcity-delete-review-popup',
					status: 'open'
				});
				this.$store.commit('updatePopupArgs', {
					id: 'wilcity-delete-review-popup',
					oArgs: {
						reviewID: reviewID
					}
				});
			},
			onOpenPopup: function onOpenPopup(target, postID){
				if ( this.$store.state.aRequireLoggedIn.indexOf(target) !== -1 &&  WILOKE_GLOBAL.isUserLoggedIn !== 'yes'  ){
					this.$store.commit('updatePopupStatus', {
						id: 'wilcity-signin-popup',
						status: 'open'
					});
					return false;
				}
				this.$store.commit('updatePopupArgs', {
					id: target,
					oArgs: {
						postID: postID
					}
				});

				this.$store.commit('updatePopupStatus', {
					id: target,
					status: 'open'
				});
			},
			pinReviewToTop: function pinReviewToTop(reviewID, parentID){
				jQuery.ajax({
					type: 'POST',
					url: WILOKE_GLOBAL.ajaxurl,
					data: {
						action: 'wilcity_pin_review_to_top',
						reviewID: reviewID,
						postID: parentID
					},
					success: function (response) {
						window.location.reload();
					}
				});
			},
			onOpenPromotionPopup: function onOpenPromotionPopup(postID){
				this.$emit('on-open-promotion-popup', postID);
			},
			removeTemporaryHidden: function removeTemporaryHidden(){
				var $listingSettings = document.getElementById('single-listing-settings');
				if ( $listingSettings ){
					$listingSettings.classList.remove('zero-height');
				}
			},
			triggerTab: function triggerTab(){
				var tabName = WilCityHelpers.getParamFromUrl('tab');
				if ( !tabName ){
					return false;
				}
				var $el = document.querySelectorAll('[data-tab="'+tabName+'"]');
				if ( $el.length ){
					$el[0].click();
				}
			}
		},
	});
}

if ( document.getElementById('wilcity-author-listing') ){
	WILOKE_GLOBAL.vmAuthorListing = new Vue({
		el: '#wilcity-author-listing',
		store: WILCITY_VUEX,
		methods: {
			openMessagePopup: function openMessagePopup(){
				if ( WILOKE_GLOBAL.isUserLoggedIn !== 'yes' ){
					this.$store.commit('updatePopupStatus', {
						id: 'wilcity-signin-popup',
						status: 'open'
					});
				}else{
					this.$store.commit('updatePopupStatus', {
						id: 'wilcity-message-popup',
						status: 'open'
					});
				}
			}
		}
	});
}

if ( document.getElementById('wilcity-hero-search-form') ){
	WILOKE_GLOBAL.vmAuthorListing = new Vue({
		store: WILCITY_VUEX,
		data: function data(){
			return {
				isRequestedForm: false,
				latLng:'',
				address: '',
				type: 'listing',
				searchUrl: '',
				isFormLoaded: false
			}
		},
		el: '#wilcity-hero-search-form',
		components: {
			WilokeAutoComplete: WilokeAutoComplete,
			WilokeSelectTwo: WilokeSelectTwo,
			WilokeHeroSearchForm: WilokeHeroSearchForm,
			WilokeSwitchPostTypeBtn: WilokeSwitchPostTypeBtn
		},
		mounted: function mounted(){
			this.formLoaded();
		},
		methods: {
			updateGeocode: function updateGeocode(oGeoCode){
				if ( oGeoCode === '' ){
					this.latLng = '';
					this.address = '';
				}else{
					this.latLng = oGeoCode.lat + ',' + oGeoCode.lng;
					this.address = oGeoCode.address;
				}
			},
			formLoaded: function formLoaded(){
				var this$1 = this;

				this.$on('formLoaded', function (status){
					this$1.isFormLoaded = status;
				});
			},
			switchPostType: function switchPostType(postType){
				this.type = postType;
				this.$emit('switched-tab', this.type);
			},
			onSubmit: function onSubmit(formID){
				var this$1 = this;

				if ( this.isRequestedForm ){
					return true;
				}

				var form = document.getElementById(formID);

				for ( var i in this$1.$refs ){
					var hiddenField = document.createElement('input');
					hiddenField.setAttribute('type', 'hidden');

					if ( this$1.$refs[i].settings.name === 'latLng'  ){
						if ( this$1.latLng !== '' ){
							hiddenField.setAttribute('name', 'latLng');
							hiddenField.setAttribute('value', this$1.latLng);

							var addressField = document.createElement('input');
							addressField.setAttribute('name', 'address');
							addressField.setAttribute('value', this$1.address);
							form.appendChild(addressField);
						}
					}else{
						if ( typeof this$1.$refs[i].settings.value !== 'undefined' && this$1.$refs[i].settings.value !== '' ){
							hiddenField.setAttribute('name', this$1.$refs[i].settings.name);
							hiddenField.setAttribute('value', this$1.$refs[i].settings.value);
						}
					}

					form.appendChild(hiddenField);
				}
				this.isRequestedForm = true;
				form.submit();
			}
		}
	});
}

if ( document.getElementById('wilcity-wrapper-all-popup') ){
	WILOKE_GLOBAL.vmWrapperAllPopup = new Vue({
		el: '#wilcity-wrapper-all-popup',
		'store': WILCITY_VUEX,
		components: {
			LoginRegisterPopup: LoginRegisterPopup,
			WilokeMessagePopup: WilokeMessagePopup,
			WilokeClaimPopup: WilokeClaimPopup,
			WilokeReportPopup: WilokeReportPopup,
			WilokeDeleteReviewPopup: WilokeDeleteReviewPopup,
			WilokeSearchFormPopup: WilokeSearchFormPopup,
			WilokeAddPhotosVideos: WilokeAddPhotosVideos,
			WilokeReviewPopup: WilokeReviewPopup
		}
	});
}

if ( document.getElementById('wilcity-quick-notifications') ){
	WILOKE_GLOBAL.vmWrapperAllPopup = new Vue({
		el: '#wilcity-quick-notifications',
		'store': WILCITY_VUEX,
		components: {
			QuickNotifications: QuickNotifications
		}
	});
}

if ( document.getElementById('wilcity-message-notifications') ){
	WILOKE_GLOBAL.vmWrapperAllPopup = new Vue({
		el: '#wilcity-message-notifications',
		'store': WILCITY_VUEX,
		components: {
			MessageNotifications: MessageNotifications
		}
	});
}

// Register And Login
if ( document.getElementById('wilcity-login-register-controller') ){
	WILOKE_GLOBAL.vmRegisterLoginController = new Vue({
		el: '#wilcity-login-register-controller',
		'store': WILCITY_VUEX,
		methods: {
			listenjQuery: function listenjQuery(){
				var this$1 = this;

				jQuery('body').on('onOpenLoginRegisterPopup', function (){
					this$1.$store.dispatch('openRegisterLoginPopup');
				});
			}
		},
		mounted: function mounted(){
			this.listenjQuery();
		},
		components: {
			RegisterBtn: RegisterBtn,
			LoginBtn: LoginBtn
		}
	});
}

if ( document.getElementById('wilcity-become-an-author') ){
	WILOKE_GLOBAL.vmBecomeAnAuthor = new Vue({
		el: '#wilcity-become-an-author',
		data: function data(){
			return {
				agreeToTerms: 'no',
				agreeToPrivacyPolicy: 'no',
				isSubmitting: false,
				isDisableBtn: false,
				isConfirmed: false
			}
		},
		components: {

		},
		computed:{
			btnClass: function btnClass(){
				return {
					'disable' : this.agreeToTerms !== 'yes' || this.agreeToPrivacyPolicy !== 'yes' || this.isDisableBtn,
					'wil-btn--loading': this.isSubmitting
				}
			}
		},
		methods: {
			submitBecomeAnAuthor: function submitBecomeAnAuthor(){
				var this$1 = this;

				this.isDisableBtn = false;
				this.isSubmitting = true;

				jQuery.ajax({
					type: 'POST',
					url: WILOKE_GLOBAL.ajaxurl,
					data: {
						action: 'wilcity_agree_become_to_author',
						agreeToTerms: this.agreeToTerms,
						agreeToPrivacyPolicy: this.agreeToPrivacyPolicy
					},
					success: function (response) {
						if ( !response.success ){
							alert(response.data.msg);
						}else{
							this$1.isConfirmed = true;
						}
						this$1.isSubmitting = false;
					}
				});
			}
		}
	});
}

if ( document.querySelector('.wilcity-listings-nearbyme') ){
	if ( typeof WILOKE_GLOBAL.vmNearByMe === 'undefined' ){
		WILOKE_GLOBAL.vmNearByMe = {};
	}

	WILOKE_GLOBAL.vmNearByMe['listing'] = new Vue({
		el: '.wilcity-listings-nearbyme',
		components: {
			WilokeListingsNearByMe: WilokeListingsNearByMe
		}
	});
}

if ( document.getElementById('wilcity-profile-nav-menu') ){
	WILOKE_GLOBAL.vmProfileNavMenu = new Vue({
		el: '#wilcity-profile-nav-menu',
		data: function data(){
			return {
				isShowing: false
			}
		},
		computed: {
			wrapperClass: function wrapperClass(){
				return this.isShowing ? 'active' : '';
			}
		},
		methods: {
			toggleProfileMenu: function toggleProfileMenu(){
				this.isShowing = !this.isShowing;
			},
			navWrapperClass: function navWrapperClass(){
				return 'dashboard-nav_item__2798B';
			}

		}
	});
}

function countViews() {
	if ( typeof WILCITY_GLOBAL === 'undefined' ){
		return false;
	}

	setTimeout(function (){
		if ( !jQuery('body.single').length ){
			return false;
		}
		jQuery.ajax({
			type: 'POST',
			url: WILOKE_GLOBAL.ajaxurl,
			data: {
				action: 'wilcity_count_views',
				postID: WILOKE_GLOBAL.postID
			}
		});
	}, 4000);
}

function payAndPublish() {
	var $btn = jQuery('#wilcity-submit');
	$btn.removeClass('disable');
	$btn.on('click', (function (event){
		event.preventDefault();
		var $this = jQuery(event.currentTarget);

		$this.addClass('wil-btn--loading disable');
		$this.find('.pill-loading_module__3LZ6v').removeClass('hidden');

		var ajaxAct = 'wilcity_handle_submit_';
		if ( WILCITY_ADDLISTING.listingType === 'event' ){
			ajaxAct += 'event';
		}else{
			ajaxAct += 'listing';
		}

		jQuery.ajax({
			type: 'POST',
			url:WILOKE_GLOBAL.ajaxurl,
			data: {
				action: ajaxAct
			},
			success: function (response) {
				if ( response.success ){
					window.location.href = response.data.redirectTo;
				}else{
					alert(response.data.msg);
				}
				$this.removeClass('wil-btn--loading disable');
				$this.find('.pill-loading_module__3LZ6v').addClass('hidden');
			}
		});
	}));
}

function proceedPayment() {
	new WilokeStripe(jQuery('#wilcity-proceed-with-stripe'));
	new WilokePayPal(jQuery('#wilcity-proceed-with-paypal'));
	new WilokeDirectBankTransfer(jQuery('#wilcity-proceed-with-banktransfer'));
	new defaultExport$1();
	jQuery('.wilcity-gateway-box').removeClass('disable');
}

function scrollTo() {
	var tabName = WilCityHelpers.getParamFromUrl('tab');

	if ( tabName && tabName !== 'home' ){
		return false;
	}

	var nodeName = WilCityHelpers.getParamFromUrl('st');
	if ( !nodeName ){
		return false;
	}

	var $el = jQuery('#'+nodeName);

	if ( !$el.length ){
		var el = '.'+nodeName;
		if ( jQuery(el).length ){
			$el = jQuery(el).first();
		}
	}

	if ( $el.length ){
		jQuery("html, body").animate({ scrollTop: $el.offset().top - 100 }, 1000);
	}
}

function removeIfNoChild() {
	var $targets = jQuery('.wilcity-remove-if-no-child');
	if ( $targets.length ){
		$targets.each(function () {
			if ( !jQuery(this).children().length ){
				jQuery(this).remove();
			}
		});
	}
}

function toggleActiveLi(){
	var $group = jQuery('.wilcity-js-toggle-group');

	jQuery('#wilcity-single-listing-content').on('click', '.js-detail-navtop .list_link__2rDA1',  (function (event) {
		event.preventDefault();
		var $currentTarget = jQuery(event.currentTarget).parent();
		var tabKey = $currentTarget.data('tabKey');
		$currentTarget.siblings().removeClass('active');
		$currentTarget.addClass('active');

		$group.addClass('hidden');
		$group.removeClass('active');
		jQuery('.wilcity-js-toggle-group[data-tab-key="'+tabKey+'"]').addClass('active').removeClass('hidden');

	}));
}
function wilcityRemoveCommentReplyIfEmpty(){
	var $replyWrapper = jQuery('.comment-review_btn__32CMP');
	$replyWrapper.each(function () {
		if ( !jQuery(this).children().length ){
			jQuery(this).closest('footer.comment-review_footer__3XR0_').remove();
		}
	});
}

jQuery(document).ready(function ($) {
	new General;
	wilcityRemoveCommentReplyIfEmpty();
	removeIfNoChild();
	proceedPayment();
	toggleActiveLi();
	var $checkoutErrMsg = $('#wilcity-print-msg');
	$checkoutErrMsg.on('printErrMsg', function (event, msg) {
		$checkoutErrMsg.find('.alert_content__1ntU3').html(msg);
		$checkoutErrMsg.removeClass('hidden');
	});

	new PayAndPublish($('#wilcity-pay-and-publish'));

	var $lineLoading = $('#wilcity-line-loading');

	$body.on('topLoading', function () {
		$lineLoading.removeClass('hidden');
	});

	$body.on('topHideLoading', function () {
		$lineLoading.addClass('hidden');
	});

	payAndPublish();
	//matchHeight();

	$('.js-video-popup').each(function() {
		$(this).magnificPopup({
			fixedContentPos: true,
			gallery: {
				enabled: true
			},
			type: 'iframe' // this is default type
		});
	});

	$('.wilcity-pagination').each(function(){
		var $this = $(this),
			oPagination = new WilokePagination($this, $this.data('totals'), $this.data('postsPerPage'), $this.data('maxPages'), );
			$this.html(oPagination.createPagination());
			oPagination.ajaxLoading();
			oPagination.resetPagination();
	});

	$('body').on('paginationLoaded', function(event, response, gridID){
		document.getElementById(gridID).innerHTML = response.data.msg;
		var $grid = $('#'+gridID);

		$grid.find('.wilcity-preview-gallery').each(function () {
			$(this).wilcityMagnificGalleryPopup();
		});
		$grid.find('.wilcity-js-favorite').each(function () {
			$(this).wilcityFavoriteStatistic();
		});
	});
	new Follow();

	$('.wilcity-toggle-map').on('click', function () {
		var $mapID = $('#'+$(this).data('mapid'));
		var oData = $(this).data();

		setTimeout(function () {
			if ( !$mapID.data('initialized') ){
				var lat = parseFloat(oData.lat);
				var lng = parseFloat(oData.lng);

				var oMap = new google.maps.Map(document.getElementById(oData.mapid), {
					zoom: 5,
					center: {
						lat: lat,
						lng: lng
					}
				});

				new google.maps.Marker({
					map: oMap,
					position: {
						lat: lat,
						lng: lng
					},
					draggable: true,
					anchorPoint: new google.maps.Point(0, -29)
				});

				$mapID.data('initialized', true);
			}
		}, 500);

	});

	setTimeout(function () {
		jQuery('.temporary-disable').removeClass('temporary-disable');
	}, 300);
});

jQuery(window).load(function () {
	document.querySelectorAll('.wilcity-single-map').forEach(function (el) {
		new WilokeGoogleMap(el.id);
	});
	countViews();
	jQuery('.temporary-hidden').removeClass('temporary-hidden');
	scrollTo();
});

}());

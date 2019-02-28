!function(){"use strict";var t="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(t){return typeof t}:function(t){return t&&"function"==typeof Symbol&&t.constructor===Symbol&&t!==Symbol.prototype?"symbol":typeof t};!function(){function t(t){this.value=t}function e(e){function n(o,a){try{var u=e[o](a),i=u.value;i instanceof t?Promise.resolve(i.value).then(function(t){n("next",t)},function(t){n("throw",t)}):r(u.done?"return":"normal",u.value)}catch(t){r("throw",t)}}function r(t,e){switch(t){case"return":o.resolve({value:e,done:!0});break;case"throw":o.reject(e);break;default:o.resolve({value:e,done:!1})}(o=o.next)?n(o.key,o.arg):a=null}var o,a;this._invoke=function(t,e){return new Promise(function(r,u){var i={key:t,arg:e,resolve:r,reject:u,next:null};a?a=a.next=i:(o=a=i,n(t,e))})},"function"!=typeof e.return&&(this.return=void 0)}"function"==typeof Symbol&&Symbol.asyncIterator&&(e.prototype[Symbol.asyncIterator]=function(){return this}),e.prototype.next=function(t){return this._invoke("next",t)},e.prototype.throw=function(t){return this._invoke("throw",t)},e.prototype.return=function(t){return this._invoke("return",t)}}();!function(e){function n(){var n=e("#restaurant-name");if(!n.length)return!1;var r=[],o=null;n.autocomplete({length:2,select:function(t,n){e("#restaurant-id").val(n.item.id)},source:function(n,a){if(null!==o&&200!==o.status&&o.abort(),void 0!==r[n.term])return a(r[n.term]),!1;o=e.ajax({type:"POST",url:ajaxurl,minLength:2,data:{action:"wiloke_find_open_table_id",term:n.term},success:function(e){if(e.success){var o=[],u=jQuery.parseJSON(e.data.data);t(e.data.data.restaurants)&&(void 0!==u.restaurants&&u.restaurants.length>0&&u.restaurants.filter(function(t){o.push({label:t.name,value:t.name,id:t.id,address:t.address})}),r[n.term]=o,a(o))}else a([{label:e.data.msg,value:e.data.msg,id:""}])}})}})}e(document).ready(function(){n()})}(jQuery)}();

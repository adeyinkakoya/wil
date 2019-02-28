!function(){"use strict";var v={props:["postTypes","isMultiple","value","label","index"],computed:{hasValue:function(){return void 0!==this.value&&this.value.length}},mounted:function(){var n=this;jQuery(this.$el).find(".js-select-two-ajax").dropdown({apiSettings:{url:ajaxurl+"?postTypes="+this.postTypes+"&s={query}&action=get_posts_by_post_types",method:"get"},forceSelection:!1,saveRemoteData:!0,debug:!1,localSearch:!1,onChange:function(e,t,s){n.selected=e,n.updateValue()},clear:!0})},methods:{clearField:function(e){jQuery(this.$el).find(".js-select-two-ajax").dropdown("clear")},updateValue:function(){this.$emit("changed",this.selected,{index:this.index})}},data:function(){var e="";if(void 0!==this.value&&this.value.length){for(var t in this.value)e+=this.value[t].id+",";e=e.slice(0,-1)}return{selected:e}}},e=function(){var t=this,e=t.$createElement,s=t._self._c||e;return s("div",{staticClass:"two fields"},[s("div",{staticClass:"field"},[s("label",[t._v(t._s(t.label))]),t._v(" "),"yes"==t.isMultiple?s("div",{staticClass:"ui multiple search selection dropdown js-select-two-ajax no-js"},[s("input",{directives:[{name:"model",rawName:"v-model",value:t.selected,expression:"selected"}],staticClass:"search-val",attrs:{type:"hidden"},domProps:{value:t.selected},on:{input:function(e){e.target.composing||(t.selected=e.target.value)}}}),t._v(" "),s("i",{staticClass:"dropdown icon"}),t._v(" "),s("div",{staticClass:"default text"},[t._v("Select product...")]),t._v(" "),t.hasValue?s("div",{staticClass:"menu"},[s("div",{staticClass:"item",attrs:{"data-value":t.value.id}},[t._v(t._s(t.value.name))])]):t._e()]):s("div",{staticClass:"ui search selection dropdown js-select-two-ajax no-js"},[s("input",{directives:[{name:"model",rawName:"v-model",value:t.selected,expression:"selected"}],staticClass:"search-val",attrs:{type:"hidden"},domProps:{value:t.selected},on:{input:function(e){e.target.composing||(t.selected=e.target.value)}}}),t._v(" "),s("i",{staticClass:"dropdown icon"}),t._v(" "),s("div",{staticClass:"default text"},[t._v("Select product...")]),t._v(" "),t.hasValue?s("div",{staticClass:"menu"},[s("div",{staticClass:"item",attrs:{"data-value":t.value.id}},[t._v(t._s(t.value.name))])]):t._e()])]),t._v(" "),s("div",{staticClass:"field"},[s("label",[t._v("Clear")]),t._v(" "),s("button",{staticClass:"ui button red",on:{click:function(e){return e.preventDefault(),t.clearField(e)}}},[t._v("Execute")])])])};e._withStripped=!0;var t=function(e,t,s,n,i,a,l,o){var d,c=("function"==typeof v?v.options:v)||{};if(c.__file="/Volumes/Dev/wilcity.com/wp-content/plugins/wiloke-listing-tools/admin/source/dev/form-fields/SelectFromPostTypes.vue",c.render||(c.render=e.render,c.staticRenderFns=e.staticRenderFns,c._compiled=!0),(c._scopeId=void 0)!==(d=function(e){t.call(this,function e(){var c=document.head||document.getElementsByTagName("head")[0],r=e.styles||(e.styles={}),u="undefined"!=typeof navigator&&/msie [6-9]\\b/.test(navigator.userAgent.toLowerCase());return function(e,t){if(!document.querySelector('style[data-vue-ssr-id~="'+e+'"]')){var s=u?t.media||"default":e,n=r[s]||(r[s]={ids:[],parts:[],element:void 0});if(!n.ids.includes(e)){var i=t.source,a=n.ids.length;if(n.ids.push(e),u&&(n.element=n.element||document.querySelector("style[data-group="+s+"]")),!n.element){var l=n.element=document.createElement("style");l.type="text/css",t.media&&l.setAttribute("media",t.media),u&&(l.setAttribute("data-group",s),l.setAttribute("data-next-index","0")),c.appendChild(l)}if(u&&(a=parseInt(n.element.getAttribute("data-next-index")),n.element.setAttribute("data-next-index",a+1)),n.element.styleSheet)n.parts.push(i),n.element.styleSheet.cssText=n.parts.filter(Boolean).join("\n");else{var o=document.createTextNode(i),d=n.element.childNodes;d[a]&&n.element.removeChild(d[a]),d.length?n.element.insertBefore(o,d[a]):n.element.appendChild(o)}}}}}())}))if(c.functional){var r=c.render;c.render=function(e,t){return d.call(t),r(e,t)}}else{var u=c.beforeCreate;c.beforeCreate=u?[].concat(u,d):[d]}return c}({render:e,staticRenderFns:[]},function(e){e&&e("data-v-77887671_0",{source:"\n.ui.dropdown .remove.icon {\n  font-size: 0.857143em;\n  float:left;\n  margin:0;\n  padding:0;\n  left:-0.7em;\n  top:0;\n  position:relative;\n  opacity: 0.5;\n}\n",map:{version:3,sources:["/Volumes/Dev/wilcity.com/wp-content/plugins/wiloke-listing-tools/admin/source/dev/form-fields/SelectFromPostTypes.vue"],names:[],mappings:";AA4BA;EACA,sBAAA;EACA,WAAA;EACA,SAAA;EACA,UAAA;EACA,YAAA;EACA,MAAA;EACA,kBAAA;EACA,aAAA;CACA",file:"SelectFromPostTypes.vue",sourcesContent:['<template>\n    <div class="two fields">\n        <div class="field">\n            <label>{{label}}</label>\n            <div v-if="isMultiple==\'yes\'" class="ui multiple search selection dropdown js-select-two-ajax no-js">\n                <input v-model="selected" class="search-val" type="hidden">\n                <i class="dropdown icon"></i>\n                <div class="default text">Select product...</div>\n                <div v-if="hasValue" class="menu">\n                    <div class="item" :data-value="value.id">{{value.name}}</div>\n                </div>\n            </div>\n            <div v-else class="ui search selection dropdown js-select-two-ajax no-js">\n                <input v-model="selected" class="search-val" type="hidden">\n                <i class="dropdown icon"></i>\n                <div class="default text">Select product...</div>\n                <div v-if="hasValue" class="menu">\n                    <div class="item" :data-value="value.id">{{value.name}}</div>\n                </div>\n            </div>\n        </div>\n        <div class="field">\n            <label>Clear</label>\n            <button @click.prevent="clearField" class="ui button red">Execute</button>\n        </div>\n    </div>\n</template>\n<style>\n.ui.dropdown .remove.icon {\n  font-size: 0.857143em;\n  float:left;\n  margin:0;\n  padding:0;\n  left:-0.7em;\n  top:0;\n  position:relative;\n  opacity: 0.5;\n}\n</style>\n<script>\n    export default{\n        props: [\'postTypes\', \'isMultiple\', \'value\', \'label\', \'index\'],\n        computed:{\n            hasValue(){\n                return typeof this.value !== \'undefined\' && this.value.length;\n            }\n        },\n        mounted(){\n            let $select = jQuery(this.$el).find(\'.js-select-two-ajax\');\n            $select.dropdown({\n                apiSettings: {\n                    url: ajaxurl + \'?postTypes=\'+this.postTypes+\'&s={query}&action=get_posts_by_post_types\',\n                    method: \'get\'\n                },\n                forceSelection: false,\n                saveRemoteData: true,\n                debug: false,\n                localSearch: false,\n                onChange: (value, text, $choice) =>{\n                    this.selected = value;\n                    this.updateValue();\n                },\n                clear: true\n            });\n        },\n        methods: {\n            clearField(event){\n                jQuery(this.$el).find(\'.js-select-two-ajax\').dropdown(\'clear\');\n            },\n            updateValue(){\n                this.$emit(\'changed\', this.selected, {\n                    index: this.index\n                });\n            }\n        },\n        data(){\n            let std = \'\';\n            if ( typeof this.value !== \'undefined\' && this.value.length ){\n                for ( let i in this.value ){\n                    std += this.value[i].id + \',\';\n                }\n                std = std.slice(0, -1);\n            }\n\n            return {\n                selected: std\n            }\n        }\n    }\n<\/script>\n']},media:void 0})});new Vue({el:"#wiloke-promotion-settings",data:{oPositions:WILOKE_PROMOTIONS.positions,aPlans:WILOKE_PROMOTIONS.plans,toggle:WILOKE_PROMOTIONS.toggle,oAvailablePositions:WILOKE_PROMOTIONS.availablePositions,isLoading:!1,errorMsg:"",successMsg:""},components:{SelectFromPostTypes:t},methods:{updateWooCommerceAssociate:function(e,t){this.aPlans[t.index].productAssociation=e},saveChanges:function(e){var t=this;this.isLoading=!0,jQuery.ajax({type:"POST",url:ajaxurl,data:{action:"wiloke_save_promotion_settings",plans:this.aPlans,toggle:this.toggle},success:function(e){t.errMsg="",t.successMsg="",t.isLoading=!1,e.success?t.successMsg="The plans have been saved successfully":t.errMsg="Something went to error"}})},removePlan:function(e){if(!confirm("Do you want to remove this plan?"))return!1;this.aPlans.splice(e,1)},getAvailablePositions:function(){if(this.aPlans.length)for(var e in this.aPlans){var t=this.oAvailablePositions.indexOf(this.aPlans[e].position);this.oAvailablePositions.splice(t,1)}},addPlan:function(){this.aPlans.push({name:"",price:"",duration:"",position:""}),this.getAvailablePositions()},optionClass:function(e){return this.oAvailablePositions.length&&-1===this.oAvailablePositions.indexOf(e)?"selected":""}},computed:{formClass:function(){return{"ui form":!0,loading:this.isLoading}},isPositionsExist:function(){return this.oAvailablePositions.length}}})}();

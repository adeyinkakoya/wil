!function(){"use strict";null!==document.getElementById("wilcity-verify-purchase-code-form")&&new Vue({el:"#wilcity-verify-purchase-code-form",data:{savingXHR:null,aResponse:[],isLoading:!1,isRevoking:!1,purchasedCode:null},computed:{formClass:function(){return this.isLoading?"loading form ui":"form ui"},revokeBtnClass:function(){return this.isRevoking?"ui red button loading":"ui red button"}},methods:{verifyPurchaseCode:function(){var s=this;null!==this.savingXHR&&200!==this.savingXHR.status&&(this.isLoading=!1,this.savingXHR.abort()),this.isLoading=!0,this.savingXHR=jQuery.ajax({type:"POST",url:ajaxurl,data:{action:"wiloke_verify_purchase_code",purchasedCode:this.purchasedCode},success:function(i){i.data.success?location.reload():alert(i.data.msg),s.isLoading=!1}})},revokeLicense:function(){var s=this;null!==this.savingXHR&&200!==this.savingXHR.status&&(this.isRevoking=!1,this.savingXHR.abort()),this.isRevoking=!0,this.savingXHR=jQuery.ajax({type:"POST",url:ajaxurl,data:{action:"wiloke_revoke_purchase_code",purchasedCode:this.purchasedCode},success:function(i){i.data.success?location.reload():alert(i.data.msg),s.isRevoking=!1}})}}})}();
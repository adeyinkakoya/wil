<template>
    <div class="two fields">
        <div class="field">
            <label>{{label}}</label>
            <div v-if="isMultiple=='yes'" class="ui multiple search selection dropdown js-select-two-ajax no-js">
                <input v-model="selected" class="search-val" type="hidden">
                <i class="dropdown icon"></i>
                <div class="default text">Select product...</div>
                <div v-if="hasValue" class="menu">
                    <div class="item" :data-value="value.id">{{value.name}}</div>
                </div>
            </div>
            <div v-else class="ui search selection dropdown js-select-two-ajax no-js">
                <input v-model="selected" class="search-val" type="hidden">
                <i class="dropdown icon"></i>
                <div class="default text">Select product...</div>
                <div v-if="hasValue" class="menu">
                    <div class="item" :data-value="value.id">{{value.name}}</div>
                </div>
            </div>
        </div>
        <div class="field">
            <label>Clear</label>
            <button @click.prevent="clearField" class="ui button red">Execute</button>
        </div>
    </div>
</template>
<style>
.ui.dropdown .remove.icon {
  font-size: 0.857143em;
  float:left;
  margin:0;
  padding:0;
  left:-0.7em;
  top:0;
  position:relative;
  opacity: 0.5;
}
</style>
<script>
    export default{
        props: ['postTypes', 'isMultiple', 'value', 'label', 'index'],
        computed:{
            hasValue(){
                return typeof this.value !== 'undefined' && this.value.length;
            }
        },
        mounted(){
            let $select = jQuery(this.$el).find('.js-select-two-ajax');
            $select.dropdown({
                apiSettings: {
                    url: ajaxurl + '?postTypes='+this.postTypes+'&s={query}&action=get_posts_by_post_types',
                    method: 'get'
                },
                forceSelection: false,
                saveRemoteData: true,
                debug: false,
                localSearch: false,
                onChange: (value, text, $choice) =>{
                    this.selected = value;
                    this.updateValue();
                },
                clear: true
            });
        },
        methods: {
            clearField(event){
                jQuery(this.$el).find('.js-select-two-ajax').dropdown('clear');
            },
            updateValue(){
                this.$emit('changed', this.selected, {
                    index: this.index
                });
            }
        },
        data(){
            let std = '';
            if ( typeof this.value !== 'undefined' && this.value.length ){
                for ( let i in this.value ){
                    std += this.value[i].id + ',';
                }
                std = std.slice(0, -1);
            }

            return {
                selected: std
            }
        }
    }
</script>

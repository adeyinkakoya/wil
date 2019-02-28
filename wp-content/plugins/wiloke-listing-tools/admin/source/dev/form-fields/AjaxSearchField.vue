<template>
    <div class="ui fluid search selection dropdown no-js">
        <input type="hidden" placeholder="Search..." v-model="val">
        <i class="dropdown icon"></i>
        <div class="default text">{{label}}</div>
        <div class="menu">
            <div v-if="val.length" class="item selected" :data-value="val" :data-text="val">{{val}}</div>
        </div>
    </div>
</template>
<script>
    export default{
        data(){
            return{
                isLoading:false,
                xhr: null,
                val: this.value,
                aOptions: []
            }
        },
        props: ['value', 'action', 'label'],
        mounted(){
            jQuery(this.$el).dropdown({
                apiSettings: {
                    url: ajaxurl + '?action='+this.action+'&s={query}',
                    cache: false
                },
                onChange: (value)=>{
                    this.val = value;
                    this.$emit('input', value);
                },
                forceSelection: false
            });
        }
    }
</script>

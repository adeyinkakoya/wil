<template>
    <div class="field">
        <label class="default text">{{label}}</label>
        <div class="ui fluid search selection dropdown no-js">
            <input type="hidden" placeholder="Search..." v-model="value">
            <div class="default text">{{defaultTxt}}</div>
            <i class="dropdown icon"></i>
            <div class="menu">
            </div>
            <p v-if="desc"><i>{{desc}}</i></p>
        </div>
    </div>
</template>
<script>
    export default{
        data(){
            return{
                isLoading:false,
                xhr: null,
                value: this.std,
                aOptions: [],
                defaultTxt: this.std.length ? this.std : this.label
            }
        },
        props: ['std', 'action', 'label', 'desc'],
        methods: {
            init(){
                jQuery(this.$el).find('.dropdown').dropdown({
                    apiSettings: {
                        url: ajaxurl + '?action='+this.action+'&s={query}',
                        cache: false
                    },
                    onChange: (value)=>{
                        this.value = value;
                        this.$emit('input', value);
                    },
                    forceSelection: false
                });
            }
        },
        mounted(){
            this.init();
            console.log(this.std)
        }
    }
</script>

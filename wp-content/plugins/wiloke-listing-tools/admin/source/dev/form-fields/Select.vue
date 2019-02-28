<template>
    <div v-if="settings.type === 'select' || settings.type === 'wiloke-select'" class="field">
        <label>{{settings.heading}}</label>
        <select v-if="settings.isMultiple" v-model="placeVal" multiple="multiple" class="ui fluid dropdown">
            <option v-for="option in settings.options" :value="printValue(option)">{{printName(option)}}</option>
        </select>
        <select v-else v-model="placeVal" class="ui fluid dropdown">
            <option v-for="option in settings.options" :value="printValue(option)">{{printName(option)}}</option>
        </select>
    </div>
</template>
<script>
    export default{
        data(){
            return {
                placeVal: null
            }
        },
        props: ['settings', 'value', 'parentKey'],
        methods:{
            printName(option){
                return typeof option.name !== 'undefined' ? option.name : option;
            },
            printValue(option){
                return typeof option.value !== 'undefined' ? option.value : option;
            },
            init(){
                if ( typeof this.value.fields[this.parentKey][this.settings.key] === 'undefined' || this.value.fields[this.parentKey][this.settings.key] === '' || this.value.fields[this.parentKey][this.settings.key] === null ){
                    if ( this.settings.isMultiple ){
                        this.placeVal = [];
                    }else{
                        this.placeVal = '';
                    }
                }else{
                    this.placeVal = this.value.fields[this.parentKey][this.settings.key];
                }
            }
        },
        watch:{
            placeVal: function (val) {
                this.value.fields[this.parentKey][this.settings.key] = val;
            }
        },
        beforeMount(){
            if ( this.settings.type === 'select' ){
                this.init();
            }
        }
    }
</script>

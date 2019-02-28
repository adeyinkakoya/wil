<template>
    <div class="disabled field">
        <label>{{label}}</label>
        <input type="text" class="wiloke-icon" v-model="value" @change="onChangedValue">
        <p v-if="desc"><i>{{desc}}</i></p>
    </div>
</template>
<script>
    export default{
        data(){
            return {
                value: this.std
            }
        },
        watch: {
            updateDef: function(newVal){
                this.value = newVal;
            }
        },
        props: {
            label: {
                type: String,
                default: ''
            },
            std: {
                type: String,
                default: ''
            },
            desc: {
                type: String,
                default: ''
            },
            updateDef: {
                type: String,
                default: ''
            }
        },
        methods: {
            onChangedValue(){
                this.$emit('input', this.value);
            }
        }
    }
</script>

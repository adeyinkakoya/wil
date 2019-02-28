<template>
    <div class="field wil-icon-wrap">
        <label>{{label}}</label>
        <div class="wil-icon-box">
            <i :class="value"></i>
        </div>
        <div class="ui right icon input">
            <input type="text" v-model="value" class="wiloke-icon" @keyup="updateIconManually" v-on:update-icon="onChangedIcon($event.target.value)">
        </div>
    </div>
</template>
<script>
    export default{
        data(){
            return {
                value: this.std
            }
        },
        props: ['std', 'label'],
        methods: {
            updateIconManually(){
                this.onChangedIcon(this.value);
            },
            updateIcon(newIcon){
                this.value = newIcon;
            },
            onChangedIcon(newValue){
                this.value = newValue;
                this.$emit('input', newValue);
            }
        }
    }
</script>

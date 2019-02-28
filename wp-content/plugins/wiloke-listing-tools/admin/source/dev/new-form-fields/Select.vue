<template>
    <div class="field">
        <label>{{label}}</label>
        <select v-if="isMultiple && isMultiple=='yes'" v-model="value" multiple="multiple" class="ui fluid dropdown" @change="onChangedValue">
            <option v-for="oOption in aOptions" :value="oOption.value">{{oOption.name}}</option>
        </select>
        <select v-else v-model="value" class="ui fluid dropdown" @change="onChangedValue">
            <option v-for="oOption in aOptions" :value="oOption.value">{{oOption.name}}</option>
        </select>
    </div>
</template>
<script>
    export default{
        data(){
            return {
                value:this.std
            }
        },
        props: ['isMultiple', 'std', 'label', 'aOptions'],
        methods:{
            onChangedValue(){
                this.$emit('input', this.value);
            }
        }
    }
</script>

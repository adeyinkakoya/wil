<template>
    <div class="field">
        <label>{{label}}</label>
        <textarea v-model="value" @change="onChangedValue"></textarea>
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
        props: ['label', 'std', 'desc'],
        methods: {
            onChangedValue(){
                this.$emit('input', this.value);
            }
        }
    }
</script>

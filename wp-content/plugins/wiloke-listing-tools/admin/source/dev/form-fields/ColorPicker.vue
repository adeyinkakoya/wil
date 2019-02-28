<template>
    <div class="field">
        <input class="color-picker" type="text" v-model="color">
    </div>
</template>
<script>
    export default{
        data(){
            return {
                color: this.value
            }
        },
        props: ['value'],
        mounted(){
            let $target = jQuery(this.$el).find('.color-picker');
            $target.spectrum({
                showAlpha: true,
                showInput: true,
                allowEmpty:true,
                change: (color=>{
                    this.color = color.toRgbString();
                    this.$emit('input', this.color);
                })
            });
        }
    }
</script>

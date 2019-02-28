<template>
    <div class="ui bottom attached tab segment" :class="{active: isActive}" :data-tab="tabkey" :name="name"><slot></slot></div>
</template>
<script>
    export default{
        data(){
            return{
                msg:'hello vue'
            }
        },
        props: {
            name: {required: true},
            tabkey: {required: true},
            isActive: false
        }
    }
</script>

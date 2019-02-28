<template>
    <div>
        <div class="ui top attached tabular menu">
            <a class="item" v-for="tab in tabs" :class="{active: tab.isActive}" :data-tab="tab.tabkey">{{tab.name}}</a>
        </div>
        <slot></slot>
    </div>
</template>
<script>
    export default{
        data(){
            return {
                tabs: []
            };
        },
        mounted() {
            this.tabs = this.$children;
        }
    }
</script>

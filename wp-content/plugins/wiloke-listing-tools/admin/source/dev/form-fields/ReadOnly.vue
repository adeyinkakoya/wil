<template>
    <div class="field">
        <label>{{settings.heading}}</label>
        <input type="text" v-model='value.fields[parentKey][settings.key]' class="read-only">
    </div>
</template>
<script>
    export default{
        props: ['settings', 'parentKey', 'value']
    }
</script>

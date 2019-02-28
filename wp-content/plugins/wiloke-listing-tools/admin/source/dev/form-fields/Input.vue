<template>
    <div v-if="settings.type === 'text' || settings.type === 'wiloke-text'" class="field">
        <label>{{settings.heading}}</label>
        <input type="text" v-model='value.fields[parentKey][settings.key]'>
    </div>
</template>
<script>
    export default{
        props: ['settings', 'parentKey', 'value']
    }
</script>

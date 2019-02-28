<template>
    <div class="field" v-if="settings.type === 'hidden' || settings.type === 'wiloke-hidden'">
        <input type="hidden" v-model='value.fields[parentKey][settings.key]'>
    </div>
</template>
<script>
    export default{
        props: ['settings', 'value', 'parentKey']
    }
</script>

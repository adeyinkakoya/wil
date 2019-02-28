<template>
    <div class="field wil-icon-wrap">
        <div class="wil-icon-box">
            <i :class="value.fields[parentKey][settings.key]"></i>
        </div>
        <div class="ui right icon input">
            <input type="text" v-model='value.fields[parentKey][settings.key]' class="wiloke-icon" v-on:update-icon="oUsedSection.icon=$event.target.value">
        </div>
    </div>
</template>
<script>
    export default{
        props: ['settings', 'value', 'parentKey']
    }
</script>

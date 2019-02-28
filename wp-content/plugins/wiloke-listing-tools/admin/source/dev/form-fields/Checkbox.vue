<template>
    <div v-if="settings.type === 'checkbox' || settings.type === 'wiloke-checkbox'" class="ui toggle checkbox" :class="{'checked': value.fields[parentKey][settings.key]=='yes'}">
        <input type="checkbox" v-model='value.fields[parentKey][settings.key]' true-value="yes" false-value="no">
        <label>{{settings.heading}}</label>
    </div>
</template>
<script>
    export default{
        props: ['settings', 'parentKey', 'value']
    }
</script>

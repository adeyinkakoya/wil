<template>
    <div v-if="settings.type=='textarea' || settings.type=='wiloke-textarea'" class="field">
        <label class="settings__heading">{{settings.heading}}</label>
        <p class="settings__desc" v-if="settings.desc!=''"><i>{{settings.desc}}</i></p>
        <ul class="settings__list" v-if="settings.isOptionField" v-html="printPrettyOptions(value.fields[parentKey][settings.key])"></ul>
        <textarea v-if="settings.isOptionField" v-model='value.fields[parentKey][settings.key]' :data-vv-name="value.fields[parentKey][settings.key]"></textarea>
        <p v-if="settings.isRequired" v-show="errors.has(value.fields[parentKey][settings.key])" class="ui red message">{{ errors.first(value.fields[parentKey][settings.key]) }}</p>
        <textarea v-if="!settings.isOptionField" v-model='value.fields[parentKey][settings.key]'></textarea>
    </div>
</template>
<script>
    export default{
        props: {
            value: {

            },
            settings: {
                type: Object,
                default: {

                }
            },
            parentKey: {

            },
            index: {
                default: 0
            }
        },
        methods: {
            printPrettyOptions(options){
                options = options.trim(' ');
                if ( options==='' ){
                    return '';
                }else{
                    let aOptions = options.split(','), prettyOptions = '', order;
                    for ( let order in aOptions ){
                        prettyOptions += '<li>'+aOptions[order]+'</li>';
                    }

                    return prettyOptions;
                }
            },
        },
        mounted(){

        }
    }
</script>

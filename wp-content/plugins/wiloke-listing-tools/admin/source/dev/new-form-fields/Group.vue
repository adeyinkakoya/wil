<template>
    <div>
        <div v-for="(oSegment, order) in aFields" :class="wrapperClass">
            <component v-for="oField in oSegment" :is="oField.component" v-on:input="oField.value=$event" :std="oField.value" :label="oField.heading"></component>
            <a class="la la-times" style="position: absolute; top: 0; right:0" href="#" @click.prevent="deleteField(order)"></a>
        </div>
        <button @click.prevent="addField" class="ui positive basic button">Add Field</button>
    </div>
</template>
<script>
    import WilokeInput from './Input.vue'
    import WilokeIcon from './Icon.vue'
    import WilokeInputReadOnly from './InputReadOnly.vue'
    import WilokeSelect from './Select.vue'
    import WilokeSelectFieldType from './SelectFieldType.vue'

    export default{
        props: {
            aFields: {
                type: Array,
                default: {}
            },
            wrapperClass: {
                type: String,
                default: 'three fields ui segment'
            }
        },
        components:{
           WilokeInput,
           WilokeIcon,
           WilokeInputReadOnly,
           WilokeSelect,
           WilokeSelectFieldType
        },
        methods: {
            addField(){
                this.aFields.push([
                    {
						'heading': 'Label',
						'type':'label',
						'component':'wiloke-input',
						'key':'label'
					},
					{
						'heading':'Key',
						'type':'name',
						'component':'wiloke-input',
						'key':'name'
					},
					{
						'heading':'Field Type',
						'type':'customField',
						'value': {
						    'type': 'text',
						    'options': ''
						},
						'component':'wiloke-select-field-type'
					}
                ]);
            },
            deleteField(order){
                this.aFields.splice(order, 1);
            }
        }
    }
</script>

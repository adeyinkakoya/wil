<template>
    <div>
        <div class="field">
            <label>{{label}}</label>
            <select v-model="value" class="ui fluid dropdown" @change="onChangedValue">
                <option v-for="oOption in aFieldTypes" :value="oOption.value">{{oOption.name}}</option>
            </select>
        </div>
        <div v-show="value=='select2' || value=='select2-multiple'">
            <label>Enter in your custom Options</label>
            <textarea v-html="options" v-model="customOptions" @change="onChangedValue"></textarea>
            <p><i>Each options should be separated by a comma: Eg: Option a,Option B</i></p>
        </div>
    </div>
</template>
<script>
    export default{
        data(){
            return {
                value:this.std.type,
                customOptions: this.std.options,
                aFieldTypes: [
                    {
                        'name':'Text',
                        'value':'text'
                    },
                    {
                        'name':'Textarea',
                        'value':'textarea'
                    },
                    {
                        'name':'Select',
                        'value':'select2'
                    },
                    {
                        'name':'Select Multiple',
                        'value':'select2-multiple'
                    }
                ]
            }
        },
        props: ['std', 'label', 'options'],
        methods:{
            onChangedValue(){
                this.$emit('input',  {
                    type: this.value,
                    options: this.customOptions
                });
            }
        }
    }
</script>

<div data-tab="listing-card" class="ui bottom attached tab segment">
	<h2 class="wiloke-add-listing-fields__title">Design Listing Card</h2>

	<div id="wiloke-listing-cards" :class="wrapperClass">
		<div class="field wiloke-form-has-icon">
            <div class="info ui message">
                Adding Custom Field to Listing Card: Please do the same what you did on Single Navigation. Please read <a target="_blank" href="https://documentation.wilcity.com/knowledgebase/how-to-build-custom-field-block-on-single-listing-part-1/">How to build Custom Field Block on Single Listing</a> to know more.
            </div>

            <div class="field" style="margin-bottom: 20px; margin-top: 20px; min-height: 50px;">
                <div class="right">
                    <button class="ui button red" @click.prevent="resetSettings">Reset</button>
                    <button class="ui button violet" @click.prevent="addItem('top')">Add Item</button>
                    <button class="ui button green" @click.prevent="saveChanges">Save Changes</button>
                </div>
            </div>

            <div v-show="errorMsg!=''" class="ui negative message">
                <p>{{errorMsg}}</p>
            </div>

            <div v-show="successMsg!=''" class="ui positive message">
                <p>{{successMsg}}</p>
            </div>

			<draggable class="dragArea drag__used" v-model="aBodyUsedFields" :options="{handle: '.dragArea__form-title--icon'}">
				<div v-for="(oItem, index) in aBodyUsedFields" class="dragArea__block">
					<div class="dragArea__form ui form field-wrapper segment">
						<div class="dragArea__form-title">
	                        <span class="dragArea__form-title--icon">
	                            <i class="la la-arrows-v"></i>
	                        </span>
							<span class="dragArea__form-title--text">
	                            {{oItem.key}}
	                        </span>
							<span class="dragArea__form-title--remove" @click.prevent="removeItem(index, aBodyUsedFields)" title="Remove Item">
	                            <i class="la la-times"></i>
	                        </span>
						</div>
						<div class="dragArea__form-content">
							<div class="two fields">
								<wiloke-new-icon v-model="oItem.icon" :std="oItem.icon" label="Icon"></wiloke-new-icon>
                                <wiloke-new-select v-model="oItem.type" v-on:input="changeType(oItem.type, index)" :is-multiple="'no'" :std="oItem.type" label="Type" :a-options="aBodyTypeFields">
                                </wiloke-new-select>
								<wiloke-new-input v-model="oItem.key" v-if="oItem.type=='custom_field'" label="Field Key" :std="oItem.key" desc=""></wiloke-new-input>
								<wiloke-input-read-only v-model="aBodyUsedFields[index].key" v-else="oItem.type!='custom_field'" :std="oItem.key" label="Field Key" :update-def="aBodyUsedFields[index].key"></wiloke-input-read-only>
                                <wiloke-new-input v-model="oItem.content" v-if="oItem.type=='custom_field'" label="Content" :std="oItem.content" desc=""></wiloke-new-input>
							</div>
						</div>
					</div>
				</div>
			</draggable>

            <div v-show="errorMsg!=''" class="ui negative message">
                <p>{{errorMsg}}</p>
            </div>

            <div v-show="successMsg!=''" class="ui positive message">
                <p>{{successMsg}}</p>
            </div>

            <div class="field">
                <button class="ui button green" @click.prevent="saveChanges">Save Changes</button>
                <button class="ui button violet" @click.prevent="addItem('button')">Add Item</button>
                <button class="ui button red" @click.prevent="resetSettings">Reset</button>
            </div>
		</div>
	</div>
</div>
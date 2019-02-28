// rollup.config.js
import vue from 'rollup-plugin-vue';
import css from 'rollup-plugin-css-only';
import commonjs from 'rollup-plugin-commonjs';
import buble from 'rollup-plugin-buble';

import resolve from 'rollup-plugin-node-resolve';
import uglify from 'rollup-plugin-uglify';
import { minify } from 'uglify-es';

// import serve from 'rollup-plugin-serve'

const General = {
	name: 'General',
	input: 'admin/source/dev/general.js',
	output:{
		format: 'iife',
		file: 'admin/source/js/general.js',
	}
};

const Global = {
	name: 'Global',
	input: 'admin/source/dev/global.js',
	output:{
		format: 'iife',
		file: 'admin/source/js/global.js',
	}
};

const ListingPlan = {
	name: 'ListingPlan',
	input: 'admin/source/dev/listing-plan.js',
	output:{
		format: 'iife',
		file: 'admin/source/js/listing-plan.js',
	}
};


const Script = {
	name: 'Script',
	input: 'admin/source/dev/script.js',
	output:{
		format: 'iife',
		file: 'admin/source/js/script.js'
	}
};

const ClaimScript = {
	name: 'ClaimScript',
	input: 'admin/source/dev/claim-script.js',
	output:{
		format: 'iife',
		file: 'admin/source/js/claim-script.js'
	}
};

const DesignFields = {
	name: 'DesignFields',
	input: 'admin/source/dev/design-fields.js',
	output:{
		format: 'iife',
		file: 'admin/source/js/design-fields.js',
	}
};

const EventScript = {
	name: 'EventScript',
	input: 'admin/source/dev/event-script.js',
	output: {
		format: 'iife',
		file: 'admin/source/js/event-script.js',
	}
};

const SingleNav = {
	name: 'SingleNav',
	input: 'admin/source/dev/design-single-nav.js',
	output: {
		format: 'iife',
		file: 'admin/source/js/design-single-nav.js',
	}
};

const AddCustomPostTypes = {
	name: 'AddCustomPostTypes',
	input: 'admin/source/dev/add-custom-posttype.js',
	output: {
		format: 'iife',
		file: 'admin/source/js/add-custom-posttype.js',
	}
};

const DesignSingleSidebar = {
	name: 'DesignSingleSidebar',
	input: 'admin/source/dev/design-sidebar.js',
	output: {
		format: 'iife',
		file: 'admin/source/js/design-sidebar.js',
	}
};

const DesignHighlightBoxes = {
	name: 'DesignHighlightBoxes',
	input: 'admin/source/dev/design-highlight-boxes.js',
	output: {
		format: 'iife',
		file: 'admin/source/js/design-highlight-boxes.js',
	}
};

const PromotionSettings = {
	name: 'PromotionSettings',
	input: 'admin/source/dev/promotion-script.js',
	output: {
		format: 'iife',
		file: 'admin/source/js/promotion-script.js',
	}
};

const SearchFormSettings = {
	name: 'SearchFormSettings',
	input: 'admin/source/dev/design-search-form.js',
	output: {
		format: 'iife',
		file: 'admin/source/js/design-search-form.js',
	}
};

const HeroSearchFormSettings = {
	name: 'HeroSearchFormSettings',
	input: 'admin/source/dev/design-hero-search-form.js',
	output: {
		format: 'iife',
		file: 'admin/source/js/design-hero-search-form.js',
	}
};

const ReportSetings = {
	name: 'ReportSetings',
	input: 'admin/source/dev/report-script.js',
	output: {
		format: 'iife',
		file: 'admin/source/js/report-script.js',
	}
};

const QuickSearchForm = {
	name: 'QuickSearchForm',
	input: 'admin/source/dev/quick-search-form-script.js',
	output: {
		format: 'iife',
		file: 'admin/source/js/quick-search-form-script.js',
	}
};

const WilokeListingTools = {
	name: 'WilokeListingTools',
	input: 'admin/source/dev/listing-tools.js',
	output: {
		format: 'iife',
		file: 'admin/source/js/listing-tools.js',
	}
};

const WilokeMobileMenu = {
	name: 'MobileMenu',
	input: 'admin/source/dev/mobile-menu.js',
	output:{
		format: 'iife',
		file: 'admin/source/js/mobile-menu.js',
	}
};

const WilokeListingCard = {
	name: 'ListingCard',
	input: 'admin/source/dev/listing-card.js',
	output:{
		format: 'iife',
		file: 'admin/source/js/listing-card.js',
	}
};

const WilokeSchemaMarkup = {
	name: 'ListingCard',
	input: 'admin/source/dev/schema-markup.js',
	output:{
		format: 'iife',
		file: 'admin/source/js/schema-markup.js',
	}
};

const WilokeSelect2 = {
	name: 'WilokeSelect2',
	input: 'admin/source/dev/pw-select2.js',
	output:{
		format: 'iife',
		file: 'admin/source/js/pw-select2.js',
	}
};

const VerifyPurchaseCode = {
	name: 'VerifyPurchaseCode',
	input: 'admin/source/dev/verify-purchase-code.js',
	output:{
		format: 'iife',
		file: 'admin/source/js/verify-purchase-code.js',
	}
};


const Common = {
	sourcemaps: true,
	plugins: [
		vue(),
		css(),
		buble(),
		resolve({
			jsnext: true,
			main: true,
			browser: true,
		}),
		commonjs(),
		uglify({}, minify),
		uglify({})
	]
};

export default [
	Object.assign({}, Common, Global),
	Object.assign({}, Common, QuickSearchForm),
	Object.assign({}, Common, ReportSetings),
	Object.assign({}, Common, SearchFormSettings),
	Object.assign({}, Common, HeroSearchFormSettings),
	Object.assign({}, Common, PromotionSettings),
	Object.assign({}, Common, ClaimScript),
	Object.assign({}, Common, DesignHighlightBoxes),
	Object.assign({}, Common, DesignSingleSidebar),
	Object.assign({}, Common, SingleNav),
	Object.assign({}, Common, General),
	Object.assign({}, Common, DesignFields),
	Object.assign({}, Common, Script),
	Object.assign({}, Common, EventScript),
	Object.assign({}, Common, AddCustomPostTypes),
	Object.assign({}, Common, WilokeListingTools),
	Object.assign({}, Common, ListingPlan),
	Object.assign({}, Common, WilokeMobileMenu),
	Object.assign({}, Common, WilokeListingCard),
	Object.assign({}, Common, WilokeSchemaMarkup),
	Object.assign({}, Common, WilokeSelect2),
	Object.assign({}, Common, VerifyPurchaseCode)
]
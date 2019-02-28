// rollup.config.js
var min = '.min';
import vue from 'rollup-plugin-vue';
import css from 'rollup-plugin-css-only';
import commonjs from 'rollup-plugin-commonjs';
import buble from 'rollup-plugin-buble';

import resolve from 'rollup-plugin-node-resolve';
import uglify from 'rollup-plugin-uglify';
import { minify } from 'uglify-es';

// let feInput = './assets/dev/js/';
// let feOutput = './assets/production/js/';
// import livereload from 'rollup-plugin-livereload'
import serve from 'rollup-plugin-serve'

// var entryName = 'index.js', destName = 'bundle.js';
// var entryName = 'quick-search.js', destName = 'quick-search.js';
// var entryName = 'review.js', destName = 'review.js';
// var entryName = 'FavoriteStatistics.js', destName = 'FavoriteStatistics.js';
// var entryName = 'map.js', destName = 'map.js';
// var entryName = 'dashboard.js', destName = 'dashboard.js';
// var entryName = 'app.js', destName = 'app.js';
// var entryName = 'no-map-search.js', destName = 'no-map-search.js';
// var entryName = 'MagnificGalleryPopup.js', destName = 'MagnificGalleryPopup.js';
// var entryName = 'CustomMarker.js', destName = 'CustomMarker.js';

const aPlugins = [
	vue(),
	css(),
	buble(),
	resolve({
		jsnext: true,
		main: true,
		browser: true,
	}),
	commonjs(),
	// serve(),
	uglify({}, minify)
];

export default [
	{
		name: 'WilCity',
		input: 'assets/dev/js/index.js',
		output: {
			file: 'assets/production/js/bundle'+min+'.js',
			format: 'iife',
		},
		sourcemaps: true,
		plugins: aPlugins
	},
	{
		name: 'Dashboard',
		input: 'assets/dev/js/dashboard.js',
		output: {
			file: 'assets/production/js/dashboard'+min+'.js',
			format: 'iife',
		},
		sourcemaps: true,
		plugins: aPlugins
	},
	{
		name: 'FavoriteStatistics',
		input: 'assets/dev/js/FavoriteStatistics.js',
		output: {
			file: 'assets/production/js/FavoriteStatistics'+min+'.js',
			format: 'iife',
		},
		sourcemaps: true,
		plugins: aPlugins
	},
	{
		name: 'NoMapSearch',
		input: 'assets/dev/js/no-map-search.js',
		output: {
			file: 'assets/production/js/no-map-search'+min+'.js',
			format: 'iife',
		},
		sourcemaps: true,
		plugins: aPlugins
	},
	{
		name: 'WilcityMap',
		input: 'assets/dev/js/map.js',
		output: {
			file: 'assets/production/js/map'+min+'.js',
			format: 'iife',
		},
		sourcemaps: true,
		plugins: aPlugins
	},
	{
		name: 'WilcityQuickSearch',
		input: 'assets/dev/js/quick-search.js',
		output: {
			file: 'assets/production/js/quick-search'+min+'.js',
			format: 'iife',
		},
		sourcemaps: true,
		plugins: aPlugins
	},
	{
		name: 'WilcityReview',
		input: 'assets/dev/js/review.js',
		output: {
			file: 'assets/production/js/review'+min+'.js',
			format: 'iife',
		},
		sourcemaps: true,
		plugins: aPlugins
	},
	{
		name: 'WilcityShortcodes',
		input: 'assets/dev/js/shortcodes.js',
		output: {
			file: 'assets/production/js/shortcodes'+min+'.js',
			format: 'iife',
		},
		sourcemaps: true,
		plugins: aPlugins
	},
	{
		name: 'WilcityApp',
		input: 'assets/dev/js/app.js',
		output: {
			file: 'assets/production/js/app'+min+'.js',
			format: 'iife',
		},
		sourcemaps: true,
		plugins: aPlugins
	},
	{
		name: 'WilCityResetPassword',
		input: 'assets/dev/js/resetPassword.js',
		output: {
			file: 'assets/production/js/resetPassword'+min+'.js',
			format: 'iife',
		},
		sourcemaps: true,
		plugins: aPlugins
	}
]
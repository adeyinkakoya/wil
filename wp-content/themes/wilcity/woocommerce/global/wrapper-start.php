<?php
/**
 * Content wrappers
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/global/wrapper-start.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see 	    https://docs.woocommerce.com/document/template-structure/
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     3.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
global $wiloke, $wilcitySidebarWrapper;
$sidebarPosition = $wiloke->aThemeOptions['woocommerce_sidebar'];

switch ($sidebarPosition){
	case 'right':
		$wilcitySidebarWrapper = 'col-md-4';
		$contentWrapper = 'col-md-8';
		break;
	case 'left':
		$wilcitySidebarWrapper = 'col-md-4 col-md-pull-8';
		$contentWrapper = 'col-md-8 col-md-push-4';
		break;
	default:
		$wilcitySidebarWrapper = '';
		$contentWrapper = 'col-md-12';
		break;
}
?>
<div class="<?php echo esc_attr($contentWrapper); ?>">

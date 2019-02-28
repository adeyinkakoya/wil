<?php

namespace WilokeListingTools\Controllers;


use Stripe\Util\Set;
use WilokeListingTools\Framework\Helpers\General;
use WilokeListingTools\Framework\Helpers\GetSettings;
use WilokeListingTools\Framework\Helpers\SetSettings;
use WilokeListingTools\Framework\Routing\Controller;

class TaxonomiesControllers extends Controller {
	public function __construct() {
		add_action( 'edited_terms', array($this, 'hasChangedTerms'), 40, 2 );
		add_action( 'created_term', array($this, 'hasChangedTerms'), 10, 3 );
		add_action( 'delete_term', array($this, 'hasChangedTerms'), 10, 3 );
		add_action('wp_ajax_wilcity_get_tags_options', array($this, 'getTags'));
	}

	public function getTags(){
		global $wpdb;
		$s = trim($_POST['s']);
		$aResults = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM $wpdb->terms LEFT JOIN $wpdb->term_taxonomy ON ($wpdb->terms.term_id = $wpdb->term_taxonomy.term_id) WHERE $wpdb->term_taxonomy.taxonomy = 'listing_tag' AND  $wpdb->terms.name LIKE %s ORDER BY $wpdb->terms.term_id DESC LIMIT 20",
				'%'.esc_sql($s).'%'
			)
		);

		if ( empty($aResults) || is_wp_error($aResults) ){
			wp_send_json_error();
		}

		$aOptions = array();
		foreach ($aResults  as $key => $oResult){
			$aOptions[$key]['text'] = $oResult->name;
			$aOptions[$key]['id']   = $oResult->slug;
		}

		wp_send_json_success(array(
			'results'=>$aOptions
		));
	}

	public function hasChangedTerms($termID, $taxonomy){
		SetSettings::setOptions('get_taxonomy_saved_at', current_time('timestamp', 1));
		if ( $taxonomy == 'listing_tag' ){
			$aPostTypes = General::getPostTypeKeys(false, false);
			foreach ($aPostTypes as $postType){
				SetSettings::setOptions(General::mainSearchFormSavedAtKey($postType), current_time('timestamp', 1));
			}
		}
	}
}
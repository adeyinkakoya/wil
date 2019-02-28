<?php
namespace WilokeListingTools\Controllers;


use WilokeListingTools\AlterTable\AlterTablePlanRelationships;
use WilokeListingTools\Framework\Routing\Controller;

class PlanRelationshipController extends Controller {
	public function __construct() {
		add_action('wiloke-listing-tools/payment-succeeded/event_plan', array($this, 'update'), 5);
		add_action('wiloke-listing-tools/payment-pending/event_plan', array($this, 'update'), 5);
		add_action('wiloke-listing-tools/payment-failed/event_plan', array($this, 'delete'), 5);

		add_action('wiloke-listing-tools/payment-succeeded/listing_plan', array($this, 'update'), 5);
		add_action('wiloke-listing-tools/payment-pending/listing_plan', array($this, 'update'), 5);
		add_action('wiloke-listing-tools/payment-failed/listing_plan', array($this, 'delete'), 5);
	}

	/*
	 * If the session is failed, we will delete this field
	 */
	public function delete($aInfo){
		if ( $aInfo['status'] !== 'failed' ){
			return false;
		}

		if ( !isset($aInfo['planRelationshipID']) || empty($aInfo['planRelationshipID']) ){
			return false;
		}

		global $wpdb;
		$tbl = $wpdb->prefix . AlterTablePlanRelationships::$tblName;

		return $wpdb->delete(
			$tbl,
			array(
				'ID' => $aInfo['planRelationshipID']
			),
			array(
				'%d'
			)
		);
	}

	/**
	 * After Payment has been completed, We should update Plan Relationship
	 *
	 * @param $aInfo: status, gateway, billingType, paymentID, planID, isTrial, planRelationshipID
	 * @return bool
	 */
	public function update($aInfo){
		if ( !in_array($aInfo['status'], array('active', 'succeeded', 'pending')) ){
			return false;
		}

		if ( !isset($aInfo['planRelationshipID']) || empty($aInfo['planRelationshipID']) ){
			return false;
		}

		global $wpdb;
		$tbl = $wpdb->prefix . AlterTablePlanRelationships::$tblName;

		return $wpdb->update(
			$tbl,
			array(
				'paymentID' => $aInfo['paymentID']
			),
			array(
				'ID' => $aInfo['planRelationshipID']
			),
			array(
				'%d'
			),
			array(
				'%d'
			)
		);

	}
}
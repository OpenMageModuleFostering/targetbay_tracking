<?php
/*
 * @author Targetbay
 * @copyright 2016 Sathishkumar Mariappan <sathishkumar.m@innoppl.com>
 * @package Targetbay_Tracking
 */
class Targetbay_Tracking_Model_Api2_Totalcartiteminfo_Rest_Admin_V1 extends Targetbay_Tracking_Model_Api2_Totalcartiteminfo {
	
	/**
	 * Get the total count info of cartitem
	 *
	 * @see Mage_Api2_Model_Resource::_retrieveCollection()
	 */
	public function _retrieveCollection() {

		$quoteTable = Mage::getSingleton('core/resource')->getTableName('sales/quote_item');
		$collection = Mage::getResourceModel( 'sales/quote_collection' )
				->addFieldToSelect(array(
							'customer_id',
							'customer_firstname', 
							'customer_lastname', 
							'customer_email', 
							'updated_at'))
				->addFieldToFilter('customer_email', array('neq' => ''))
				->addFieldToFilter('customer_id', array('neq' => ''));
;
		$collection->getSelect()->join(array('Q2'=> $quoteTable), '`main_table`.`entity_id` = `Q2`.`quote_id`', array('*'))->group('Q2.quote_id');

		$totals = array('total_cartitems' => count($collection));
		
		return json_encode($totals);
	}
}

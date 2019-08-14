<?php
/*
 * @author Targetbay
 * @copyright 2016 Sathishkumar Mariappan <sathishkumar.m@innoppl.com>
 * @package Targetbay_Tracking
 */
class Targetbay_Tracking_Model_Api2_Stores_Rest_Guest_V1 extends Targetbay_Tracking_Model_Api2_Stores {
	
	/**
	 * Retrieve wishlist items
	 *
	 * @return array
	 */
	protected function _retrieveCollection() {
		$storeData = array();
		$storeVal = array();
		$storeCollection = Mage::getModel('core/store')->getCollection();
		foreach($storeCollection as $store) {
			$storeId = $store->getStoreId();
			$storeVal['store_id'] = Mage::app()->getStore($storeId)->getId();
			$storeVal['store_name'] = Mage::app()->getStore($storeId)->getName();
			$storeVal['website_id'] = Mage::getModel('core/store')->load($storeId)->getWebsiteId();
			$websiteId = Mage::getModel('core/store')->load($storeId)->getWebsiteId();
			$website = Mage::getModel('core/website')->load($websiteId);
			$storeVal['website_name'] = $website->getName();
			$storeData[] = $storeVal;
		}
		return $storeData;
	}
}

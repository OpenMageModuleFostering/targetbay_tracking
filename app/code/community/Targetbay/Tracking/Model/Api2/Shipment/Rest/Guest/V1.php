<?php
/*
 * @author Targetbay
 * @copyright 2016 Sathishkumar Mariappan <sathishkumar.m@innoppl.com>
 * @package Targetbay_Tracking
 */
class Targetbay_Tracking_Model_Api2_Shipment_Rest_Guest_V1 extends Targetbay_Tracking_Model_Api2_Shipment {
	
	/**
	 * Get orders list
	 *
	 * @return array
	 */
	protected function _retrieveCollection() {
		$collection = Mage::getModel('sales/order_shipment')->getCollection();
		$collection->addAttributeToFilter('created_at', array(
    					'gt' => new Zend_Db_Expr('SUBDATE(CURRENT_TIMESTAMP, INTERVAL 6 HOUR)')));

		$shipmentData = array ();
		$params['shipment']=true;
		foreach ($collection->getItems() as $order) {
			//$shipmentData [$order->getId()] = $order->toArray();
			$orderId = $order->getOrderId();
			$orderInfo = Mage::getModel('sales/order')->load($orderId);
			$storeId = $orderInfo->getStoreId();
			$shipmentData[$order->getId()] = Mage::helper('tracking')->getFullFillmentData($orderInfo, $params);
			$shipmentData[$order->getId()]['store_id'] = $storeId;
			$shipmentData[$order->getId()]['website_id'] = Mage::getModel('core/store')->load($storeId)->getWebsiteId();
		}
		return $shipmentData;
	}
}


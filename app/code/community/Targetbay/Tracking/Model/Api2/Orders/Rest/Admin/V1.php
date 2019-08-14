<?php
/*
 * @author Targetbay
 * @copyright 2016 Sathishkumar Mariappan <sathishkumar.m@innoppl.com>
 * @package Targetbay_Tracking
 */
class Targetbay_Tracking_Model_Api2_Orders_Rest_Admin_V1 extends Mage_Sales_Model_Api2_Order_Rest {
	
	/**
	 * Get orders list
	 *
	 * @return array
	 */
	protected function _retrieveCollection() {
		$collection = $this->_getCollectionForRetrieve ();
		
		if ($this->_isPaymentMethodAllowed ()) {
			$this->_addPaymentMethodInfo ( $collection );
		}
		if ($this->_isGiftMessageAllowed ()) {
			$this->_addGiftMessageInfo ( $collection );
		}
		$this->_addTaxInfo ( $collection );
		
		$ordersData = array ();
		
		foreach ( $collection->getItems () as $order ) {
			$ordersData [$order->getId ()] = $order->toArray ();
			$storeId = $order->getStoreId();
			$ordersData [$order->getId ()]['store_id'] = $storeId;
			$ordersData [$order->getId ()]['website_id'] = Mage::getModel('core/store')->load($storeId)->getWebsiteId();
			$ordersData [$order->getId ()] ['payment_method'] = Mage::helper ( 'tracking' )->getPaymentInfo ( $order->getId (), true );
			$ordersData [$order->getId ()] [Targetbay_Tracking_Helper_Data::BILLING] = Mage::helper ( 'tracking' )->getAddressData ( $order, Targetbay_Tracking_Helper_Data::BILLING );
			$ordersData [$order->getId ()] [Targetbay_Tracking_Helper_Data::SHIPPING] = Mage::helper ( 'tracking' )->getAddressData ( $order, Targetbay_Tracking_Helper_Data::SHIPPING );
			$ordersData [$order->getId ()] ['cart_items'] = Mage::helper ( 'tracking' )->getOrderItemsInfo ( $order, true );
		}
		return $ordersData;
	}
}


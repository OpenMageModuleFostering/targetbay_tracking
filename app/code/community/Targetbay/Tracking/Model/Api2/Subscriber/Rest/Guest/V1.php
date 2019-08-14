<?php
/*
 * @author Targetbay
 * @copyright 2016 Sathishkumar Mariappan <sathishkumar.m@innoppl.com>
 * @package Targetbay_Tracking
 */
class Targetbay_Tracking_Model_Api2_Subscriber_Rest_Guest_V1 extends Targetbay_Tracking_Model_Api2_Subscriber {
	
	/**
	 * Retrieve susbcriber list
	 *
	 * @return array
	 */
	public function _retrieveCollection() {
		$subscribers = array();
		$subscriberData = array();

		$page_num = Mage::app()->getRequest()->getParam('page');
		$limit = Mage::app()->getRequest()->getParam('limit');

		$subscriptionCollection = Mage::getModel('newsletter/subscriber')->getCollection();
		$subscriptionCollection->getSelect()->limit($limit, $page_num);

		foreach($subscriptionCollection as $subscriber) {
			$subscribers['subscriber_id'] = $subscriber->getSubscriberId();
			$subscribers['store_id'] = $subscriber->getStoreId();
			$subscribers['customer_id'] = $subscriber->getCustomerId();
			$subscribers['subscriber_email'] = $subscriber->getSubscriberEmail();
			$subscribers['subscriber_status'] = $subscriber->getSubscriberStatus();
			$subscriberData[] = $subscribers;
		}
		return $subscriberData;
	}
}

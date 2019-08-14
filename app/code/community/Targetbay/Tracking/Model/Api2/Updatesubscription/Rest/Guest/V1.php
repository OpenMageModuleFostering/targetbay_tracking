<?php
/*
 * @author Targetbay
 * @copyright 2016 Sathishkumar Mariappan <sathishkumar.m@innoppl.com>
 * @package Targetbay_Tracking
 */
class Targetbay_Tracking_Model_Api2_Updatesubscription_Rest_Guest_V1 extends Targetbay_Tracking_Model_Api2_Updatesubscription {
	
	/**
	 * Get the newsletter subscription
	 *
     	 * @param array $data
	 * @see Mage_Api2_Model_Resource::_create()
	 */
	public function _create(array $data) {
		$email = Mage::app()->getRequest()->getParam('email');
		$status = Mage::app()->getRequest()->getParam('status');

		if($email == '' || $status == '')
			return false;

		if ($email != '' && $status != '') {
			try {
				$subscriberData = Mage::getModel('newsletter/subscriber')->loadByEmail($email);
				if ($status == Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED) {
					$subscriberTable = $subscriberData->setSubscriberStatus($status)
								    	  ->save();
					Mage::helper('newsletter')->sendConfirmationSuccessEmail();
					return 'Thank you for your subscription.';
				} else {
					$subscriberTable = $subscriberData->setSubscriberStatus($status)
								    	  ->save();
					Mage::helper('newsletter')->sendUnsubscriptionEmail();
					return 'You have been unsubscribed';
				}
			} catch (Exception $e) {
			    	Mage::helper('tracking')->debug($e->getMessage());
			}
		}
	}

	/**
	 * Get the newsletter subscription
	 *
	 * @return array
	 */
	public function _retrieveCollection() {

		$email = Mage::app()->getRequest()->getParam('email');
		$status = Mage::app()->getRequest()->getParam('status');

		if($email == '' || $status == '')
			return false;

		if ($email != '' && $status != '') {
			try {
				$customerData = Mage::getModel('customer/customer')
						->setWebsiteId(1)
						->loadByEmail($email);

				$newsletterCustomer = Mage::getModel('newsletter/subscriber')->loadByCustomer($customerData);
				if($newsletterCustomer->getId()) {
					$newsletterCustomer->setCustomerId($newsletterCustomer->getId());
					$newsletterCustomer->setStatus($status);
					$newsletterCustomer->save();
				}

				$subscriberData = Mage::getModel('newsletter/subscriber')->loadByEmail($email);
				if ($status == Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED) {
					$subscriberTable = $subscriberData->setSubscriberStatus($status)
								    	  ->save();
					Mage::getModel('newsletter/subscriber')->sendConfirmationSuccessEmail();
					return 'Thank you for your subscription.';
				} else {
					$subscriberTable = $subscriberData->setSubscriberStatus($status)
								    	  ->save();	
					Mage::getModel('newsletter/subscriber')->sendUnsubscriptionEmail();
					return 'You have been unsubscribed';
				}
			} catch (Exception $e) {
			    	Mage::helper('tracking')->debug($e->getMessage());
			}
		}
	}
}

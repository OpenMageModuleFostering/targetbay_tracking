<?php
/*
 * @author Targetbay
 * @copyright 2016 Sathishkumar Mariappan <sathishkumar.m@innoppl.com>
 * @package Targetbay_Tracking
 */
class Targetbay_Tracking_Model_Api2_Createsubscription_Rest_Guest_V1 extends Targetbay_Tracking_Model_Api2_Createsubscription {
	
	/**
	 * Get the newsletter subscription
	 *
     	 * @param array $data
	 * @see Mage_Api2_Model_Resource::_create()
	 */
	public function _create(array $data) {
		$session            = Mage::getSingleton('core/session');
		$customerSession    = Mage::getSingleton('customer/session');

		if (Mage::app()->getRequest()->getParam('email')) {

			$email              = (string) Mage::app()->getRequest()->getParam('email');
			$message = array();
			try {
				if (!Zend_Validate::is($email, 'EmailAddress')) {
				    return 'Please enter a valid email address.';
				}

				if (Mage::getStoreConfig(Mage_Newsletter_Model_Subscriber::XML_PATH_ALLOW_GUEST_SUBSCRIBE_FLAG) != 1 && 
				    !$customerSession->isLoggedIn()) {
				    return 'Sorry, but administrator denied subscription for guests. Please register.';
				}

				$ownerId = Mage::getModel('newsletter/subscriber')
						->loadByEmail($email)
						->getId();
				if ($ownerId !== null && $ownerId != $customerSession->getId()) {
				    return 'This email address is already exists.';
				}

				$status = Mage::getModel('newsletter/subscriber')->subscribe($email);
				if ($status == Mage_Newsletter_Model_Subscriber::STATUS_NOT_ACTIVE) {
				     return 'Confirmation request has been sent.';
				}
				else {
				    return 'Thank you for your subscription.';
				}
			} catch (\Exception $e) {
			    	Mage::helper('tracking')->debug($e->getMessage());
			}
		} else {
			 return 'Please enter a valid email address.';
		}
	}

	/**
	 * Get the newsletter subscription
	 *
	 * @return array
	 */
	public function _retrieveCollection() {
		$session            = Mage::getSingleton('core/session');
		$customerSession    = Mage::getSingleton('customer/session');

		if (Mage::app()->getRequest()->getParam('email')) {

			$email              = (string) Mage::app()->getRequest()->getParam('email');
			$message = array();
			try {
				if (!Zend_Validate::is($email, 'EmailAddress')) {
				    return 'Please enter a valid email address.';
				}

				if (Mage::getStoreConfig(Mage_Newsletter_Model_Subscriber::XML_PATH_ALLOW_GUEST_SUBSCRIBE_FLAG) != 1 && 
				    !$customerSession->isLoggedIn()) {
				    return 'Sorry, but administrator denied subscription for guests. Please register.';
				}

				$ownerId = Mage::getModel('newsletter/subscriber')
						->loadByEmail($email)
						->getId();
				if ($ownerId !== null && $ownerId != $customerSession->getId()) {
				    return 'This email address is already exists.';
				}

				$status = Mage::getModel('newsletter/subscriber')->subscribe($email);
				if ($status == Mage_Newsletter_Model_Subscriber::STATUS_NOT_ACTIVE) {
				     return 'Confirmation request has been sent.';
				}
				else {
				    return 'Thank you for your subscription.';
				}
			} catch (\Exception $e) {
			    	Mage::helper('tracking')->debug($e->getMessage());
			}
		} else {
			 return 'Please enter a valid email address.';
		}
	}
}

<?php
/*
 * @author Targetbay
 * @copyright 2016 Sathishkumar Mariappan <sathishkumar.m@innoppl.com>
 * @package Targetbay_Tracking
 */
class Targetbay_Tracking_IndexController extends Mage_Core_Controller_Front_Action
{
    public function reloadAction()
    {
	if (Mage::getSingleton('customer/session')->isLoggedIn())
		return false;

        try {
		$quoteId = Mage::app()->getRequest()->getParam('quote_id');
		$store_id = Mage::app()->getStore()->getId();
		
		$checkout = Mage::getSingleton('checkout/session');
		$cust = Mage::getSingleton('customer/session');
		$chkQuote = Mage::helper('checkout/cart')->getQuote();
		$helper = Mage::helper('tracking');

	        if(empty($quoteId))
		   $this->_redirectAfterReload();
		$quote = Mage::getModel('sales/quote')->load($quoteId);
		if ($quote->getId()) {
			$quote->setIsActive(1)
				->save();
			Mage::getSingleton('checkout/session')->replaceQuote($quote);
		}
        } catch (Exception $e) {
		$helper->debug("ERROR: " . $e->getMessage());
	}

        return $this->_redirectAfterReload();
    }

    private function _redirectAfterReload()
    {
        $url = 'checkout/cart/';

        return $this->_redirect(
            $url,
            array('_secure' => Mage::app()->getStore()->isCurrentlySecure())
        );
    }
}

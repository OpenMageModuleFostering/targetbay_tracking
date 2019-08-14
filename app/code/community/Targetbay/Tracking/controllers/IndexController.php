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
        if (Mage::getSingleton('customer/session')->isLoggedIn()) {
            return false;
        }

        try {
            $quoteId = Mage::app()->getRequest()->getParam('quote_id');
            $guestUserId = Mage::app()->getRequest()->getParam('guest_user_id');

            // ToDo: Do we need?
            $store_id = Mage::app()->getStore()->getId();

            if ($guestUserId != '' && !Mage::getSingleton('customer/session')->isLoggedIn()) {
                Mage::getModel('core/cookie')->set('targetbay_session_id', $guestUserId, null, null, null, null, false);
            }

            $checkout = Mage::getSingleton('checkout/session');

            // ToDo: Do we need?
            $cust = Mage::getSingleton('customer/session');

            $coreSession = Mage::getSingleton('core/session');
            $coreSession->setRestoreQuoteId($quoteId);
            $coreSession->setAbandonedMail(true);
            $cart = Mage::getModel('checkout/cart');
            $helper = Mage::helper('tracking');

            if (empty($quoteId)) {
                $this->_redirectAfterReload();
            }

            if ($checkout->getQuoteMerged()) {
                $this->_redirectAfterReload();
            }

            $quote = Mage::getModel('sales/quote')->load($quoteId);
            if ($quote && $quote->getId() && $quote->getIsActive() && (($checkout->getQuoteMerged() == null) ||
                    $checkout->getQuoteMerged() != true)
            ) {
                $quoteItems = $quote->getAllVisibleItems();
                $i=0;
                foreach ($quoteItems as $key => $item) {
                    $product = Mage::getModel('catalog/product')->load($item->getProductId());
                    if ($item->getProductType() == 'configurable') {
                        $customOptions = $item->getProduct()->getTypeInstance(true)
                                                                ->getOrderOptions($item->getProduct());
                        $superAttributeInfo = $customOptions['info_buyRequest'];
                        $params = array('qty' => $quoteItems[$i]['qty'],
                                        'super_attribute' => $superAttributeInfo['super_attribute']);
                        $cart->addProduct($product, $params);
                    } else {
                        $params = array('qty' => $quoteItems[$i]['qty']);
                        $cart->addProduct($product, $params);
                    }
                    $i++;
                }
                $cart->save();
                $checkout->setQuoteMerged(true);
            }
        } catch (Exception $e) {
            $helper->debug("ERROR: " . $e->getMessage());
        }

        return $this->_redirectAfterReload();
    }

    private function _redirectAfterReload()
    {
        $url = 'checkout/cart/';
        $utmSource = Mage::app()->getRequest()->getParam('utm_source');
        $utmToken = Mage::app()->getRequest()->getParam('token');

        return $this->_redirect(
            $url,
            array(
                '_secure' => Mage::app()->getStore()->isCurrentlySecure(),
                'utm_source' => $utmSource,
                'token' => $utmToken
            )
        );
    }
}

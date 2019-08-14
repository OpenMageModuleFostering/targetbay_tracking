<?php

/*
 * @author Targetbay
 * @copyright 2016 Sathishkumar Mariappan <sathishkumar.m@innoppl.com>
 * @package Targetbay_Tracking
 */

class Targetbay_Tracking_Block_Tracking extends Mage_Core_Block_Template
{

    /**
     * Rendered the tracking template if tracking enabled
     *
     * @see Mage_Core_Block_Template::_construct()
     */
    public function _construct()
    {
        parent::_construct();
        if (Mage::helper('tracking')->trackingEnabled()) {
            $this->setTemplate('tracking/tracking.phtml');
        }
    }

    public function getStockAvaliability() {
        $_product = $this->getProduct();
        if(!$_product->isAvailable()) {
            return true;
        } else {
            return 0;
        }
    }

    /**
     * Get the current product
     * @return Mage_Catalog_Model_Product
     */
    public function getProduct()
    {
        return Mage::registry('current_product');
    }
}

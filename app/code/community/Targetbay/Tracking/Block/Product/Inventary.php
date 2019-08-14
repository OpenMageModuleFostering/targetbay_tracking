<?php

/*
 * @author Targetbay
 * @copyright 2016 Sathishkumar Mariappan <sathishkumar.m@innoppl.com>
 * @package Targetbay_Tracking
 */

class Targetbay_Tracking_Block_Product_Inventary extends Mage_Core_Block_Template
{
    /**
     * Rendered the tracking review template if tracking enabled
     *
     * @see Mage_Core_Block_Template::_construct()
     */
    public function _construct()
    {
        parent::_construct();
        if (Mage::helper('tracking')->trackingEnabled()) {
        $this->setTemplate('tracking/product/inventary.phtml');
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

    public function getBackOrderStatus() {
        $managekStatus = Mage::getStoreConfig('cataloginventory/item_options/manage_stock');
        $backOrderStatus = Mage::getStoreConfig('cataloginventory/item_options/backorders');
        $html = '';
        if($managekStatus == 0 || $backOrderStatus > 0) {
            $html .= '<div id="backorders"></div>'; 
        }
        return $html;
    }
}

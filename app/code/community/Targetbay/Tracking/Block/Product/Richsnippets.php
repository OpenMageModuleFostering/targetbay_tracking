<?php

/*
 * @author Targetbay
 * @copyright 2016 Sathishkumar Mariappan <sathishkumar.m@innoppl.com>
 * @package Targetbay_Tracking
 */

class Targetbay_Tracking_Block_Product_Richsnippets extends Mage_Core_Block_Template
{
    /**
     * Rendered the tracking review template if tracking enabled
     *
     * @see Mage_Core_Block_Template::_construct()
     */
    public function _construct()
    {
        parent::_construct();
        if((int)Mage::helper('tracking')->getRichsnippetType()) {
            $this->setTemplate('tracking/product/richsnippets.phtml');
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

    public function getEscapedProductName()
    {
        return $this->escapeHtml($this->getProduct()->getName());
    }

    public function getEscapedDescription()
    {
        $product = $this->getProduct();
        if (!$product) {
            return '';
        }

        return $this->escapeHtml($product->getDescription());
    }

    public function getSku()
    {
        $product = $this->getProduct();
        if (!$product) {
            return '';
        }

        return $product->getSku();
    }
}

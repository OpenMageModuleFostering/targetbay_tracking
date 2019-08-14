<?php

/*
 * @author Targetbay
 * @copyright 2016 Sathishkumar Mariappan <sathishkumar.m@innoppl.com>
 * @package Targetbay_Tracking
 */

class Targetbay_Tracking_Block_Product_Review extends Mage_Core_Block_Template
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
        $this->setTemplate('tracking/product/review.phtml');
        }
    }
}

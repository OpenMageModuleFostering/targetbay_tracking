<?php

/*
 * @author Targetbay
 * @copyright 2016 Sathishkumar Mariappan <sathishkumar.m@innoppl.com>
 * @package Targetbay_Tracking
 */

class Targetbay_Tracking_Model_Api2_Totalinfo_Rest_Admin_V1 extends Targetbay_Tracking_Model_Api2_Totalinfo
{

    /**
     * Get the total count info of orders,products and customers
     *
     * @see Mage_Api2_Model_Resource::_retrieveCollection()
     */
    public function _retrieveCollection()
    {
        $totals = array(
            'total_products' => Mage::getModel('catalog/product')->getCollection()->getSize(),
            'total_customers' => Mage::getModel('customer/customer')->getCollection()->getSize(),
            'total_orders' => Mage::getModel('sales/order')->getCollection()->getSize()
        );

        return json_encode($totals);
    }
}

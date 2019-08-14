<?php

/*
 * @author Targetbay
 * @copyright 2016 Sathishkumar Mariappan <sathishkumar.m@innoppl.com>
 * @package Targetbay_Tracking
 */

class Targetbay_Tracking_Model_Api2_Totalorderinfo_Rest_Guest_V1 extends Targetbay_Tracking_Model_Api2_Totalorderinfo
{

    /**
     * Get the total count info of orders
     *
     * @see Mage_Api2_Model_Resource::_retrieveCollection()
     */
    public function _retrieveCollection()
    {
        $totals = array(
            'total_orders' => Mage::getModel('sales/order')->getCollection()->getSize()
        );

        return json_encode($totals);
    }
}

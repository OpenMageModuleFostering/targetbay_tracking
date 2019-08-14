<?php

/*
 * @author Targetbay
 * @copyright 2016 Sathishkumar Mariappan <sathishkumar.m@innoppl.com>
 * @package Targetbay_Tracking
 */

class Targetbay_Tracking_Model_Api2_Totalshipmentinfo_Rest_Guest_V1 extends Targetbay_Tracking_Model_Api2_Totalshipmentinfo
{

    /**
     * Get the total count info of shipment orders
     *
     * @see Mage_Api2_Model_Resource::_retrieveCollection()
     */
    public function _retrieveCollection()
    {
        $shipmentCollection = Mage::getModel('sales/order_shipment')->getCollection();
        $shipmentCollection->addAttributeToFilter('created_at', array(
            'gt' => new Zend_Db_Expr('SUBDATE(CURRENT_TIMESTAMP, INTERVAL 6 HOUR)')));

        $totals = array('total_shipped_orders' => count($shipmentCollection));

        return json_encode($totals);
    }
}

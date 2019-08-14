<?php

/*
 * @author Targetbay
 * @copyright 2016 Sathishkumar Mariappan <sathishkumar.m@innoppl.com>
 * @package Targetbay_Tracking
 */

class Targetbay_Tracking_Model_Api2_Totalreviewinfo_Rest_Guest_V1 extends Targetbay_Tracking_Model_Api2_Totalreviewinfo
{

    /**
     * Get the total count of reviews
     *
     * @see Mage_Api2_Model_Resource::_retrieveCollection()
     */
    public function _retrieveCollection()
    {
        $totals = array(
            'total_reviews' => Mage::getModel('review/review')->getCollection()->getSize()
        );

        return json_encode($totals);
    }
}

<?php

/*
 * @author Targetbay
 * @copyright 2016 Sathishkumar Mariappan <sathishkumar.m@innoppl.com>
 * @package Targetbay_Tracking
 */

class Targetbay_Tracking_Model_System_Config_Source_Tracking
{
    /**
     * Page Options configurations
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array(
                'value' => '',
                'label' => Mage::helper('adminhtml')->__('Please Select')
            ),
            array(
                'value' => 0,
                'label' => Mage::helper('adminhtml')->__('Automatic')
            ),
            array(
                'value' => 1,
                'label' => Mage::helper('adminhtml')->__('Manual')
            )
        );
    }
}

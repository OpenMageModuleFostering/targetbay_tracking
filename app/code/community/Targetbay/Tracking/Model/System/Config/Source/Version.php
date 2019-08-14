<?php

/*
 * @author Targetbay
 * @copyright 2016 Sathishkumar Mariappan <sathishkumar.m@innoppl.com>
 * @package Targetbay_Tracking
 */

class Targetbay_Tracking_Model_System_Config_Source_Version
{
    /**
     * Magento version configurations
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array(
                'value' => 'v1',
                'label' => __('Magento1.x')
            ),
            array(
                'value' => 'v2',
                'label' => __('Magento2.x')
            )
        );
    }
}

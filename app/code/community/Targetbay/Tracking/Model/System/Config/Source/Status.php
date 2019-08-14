<?php

/*
 * @author Targetbay
 * @copyright 2016 Sathishkumar Mariappan <sathishkumar.m@innoppl.com>
 * @package Targetbay_Tracking
 */

class Targetbay_Tracking_Model_System_Config_Source_Status
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
                'value' => '',
                'label' => __('Please Select')
            ),
            array(
                'value' => 'dev',
                'label' => __('Development')
            ),
            array(
                'value' => 'stage',
                'label' => __('Stage')
            ),
            array(
                'value' => 'app',
                'label' => __('Production')
            )
        );
    }
}

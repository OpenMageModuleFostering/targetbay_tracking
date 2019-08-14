<?php
/*
 * @author Targetbay
 * @copyright 2016 Sathishkumar Mariappan <sathishkumar.m@innoppl.com>
 * @package Targetbay_Tracking
 */
class Targetbay_Tracking_Model_System_Config_Source_Yesno
{
    /**
     * Magento mail configurations
     *
     * @return array
     */
    public function toOptionArray()
    {
         return array(
            array('value' => 1, 'label'=>Mage::helper('adminhtml')->__('Yes')),
            array('value' => 2, 'label'=>Mage::helper('adminhtml')->__('No')),
        );
    }
}

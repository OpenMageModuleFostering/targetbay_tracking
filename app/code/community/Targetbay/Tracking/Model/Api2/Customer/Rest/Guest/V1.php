<?php

/*
 * @author Targetbay
 * @copyright 2016 Sathishkumar Mariappan <sathishkumar.m@innoppl.com>
 * @package Targetbay_Tracking
 */

class Targetbay_Tracking_Model_Api2_Customer_Rest_Guest_V1 extends Mage_Customer_Model_Api2_Customer
{

    /**
     * Retrieve list of categories
     *
     * @return array
     */
    protected function _retrieveCollection()
    {
        $page = Mage::app()->getRequest()->getParam('page');
        $limit = Mage::app()->getRequest()->getParam('limit');

        $pageSize = ($page) ? $page : '';
        $pageLimit = ($limit) ? $limit : '';

        $pageSize = (($page - 1) * $limit);

        $collection = Mage::getResourceModel('customer/customer_collection');
        $collection->addAttributeToSelect(array_keys(
            $this->getAvailableAttributes($this->getUserType(), Mage_Api2_Model_Resource::OPERATION_ATTRIBUTE_READ)
        ));
        $collection->getSelect()->limit($pageLimit, $pageSize);
        $customers = $collection->load()->toArray();
        return $customers;
    }
}

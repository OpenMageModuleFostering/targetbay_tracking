<?php

/*
 * @author Targetbay
 * @copyright 2016 Sathishkumar Mariappan <sathishkumar.m@innoppl.com>
 * @package Targetbay_Tracking
 */

class Targetbay_Tracking_Model_Api2_Cartitem_Rest_Admin_V1 extends Targetbay_Tracking_Model_Api2_Cartitem
{

    /**
     * Retrieve cart items
     *
     * @return array
     */
    protected function _retrieveCollection()
    {
        $cartItems = $cartItemData = array();
        $quoteCollection = $this->getQuoteCollectionQuery();
        $cartItems = array();
        foreach ($quoteCollection as $id => $quoteInfo) {
            $quoteId = $quoteInfo['entity_id'];
            $quote = Mage::getModel('sales/quote')->setId($quoteId);
            $items = $quote->getAllVisibleItems();
            if (count($items) > 0) {
                $cartItems[$id]['entity_id'] = $quoteInfo['entity_id'];
                $cartItems[$id]['customer_id'] = $quoteInfo['customer_id'];
                $cartItems[$id]['customer_email'] = $quoteInfo['customer_email'];
                $cartItems[$id]['abandonded_at'] = $quoteInfo['updated_at'];
                $cartItems[$id]['cart_items'] = Mage::helper('tracking')->getQuoteItems($items);
            }
        }

        return $cartItems;
    }

    public function getQuoteCollectionQuery()
    {
        $page_num = Mage::app()->getRequest()->getParam('page');
        $limit = Mage::app()->getRequest()->getParam('limit');
        $start = 1;
        $offset = 100;

        $page_num = ($page_num) ? $page_num : $start;
        $limit = ($limit) ? $limit : $offset;

        $quoteTable = Mage::getSingleton('core/resource')->getTableName('sales/quote_item');
        $collection = Mage::getResourceModel('sales/quote_collection')
            ->addFieldToSelect(array(
                'customer_id',
                'customer_firstname',
                'customer_lastname',
                'customer_email',
                'updated_at'))
            ->addFieldToFilter('customer_email', array('neq' => ''))
            ->addFieldToFilter('customer_id', array('neq' => ''));

        $collection->getSelect()->join(array('Q2' => $quoteTable), '`main_table`.`entity_id` = `Q2`.`quote_id`', array('*'))->group('Q2.quote_id');

        $collection->getSelect()->limit($limit, $page_num);
        return $collection;
    }
}

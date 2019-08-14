<?php

/*
 * @author Targetbay
 * @copyright 2016 Sathishkumar Mariappan <sathishkumar.m@innoppl.com>
 * @package Targetbay_Tracking
 */

class Targetbay_Tracking_Model_Api2_Wishlist_Rest_Admin_V1 extends Targetbay_Tracking_Model_Api2_Wishlist
{

    /**
     * Retrieve wishlist items
     *
     * @return array
     */
    protected function _retrieveCollection()
    {
        $customerId = '';
        $wishlistData = array();
        $wishlistCollection = Mage::getModel('wishlist/wishlist')->getCollection();
        foreach ($wishlistCollection as $id => $wishlist) {
            $wishlistInfo = Mage::getModel('wishlist/wishlist')->loadByCustomer($wishlist->getCustomerId());
            $wishlistItemCollection = $wishlistInfo->setStoreId(1)->getItemCollection();
            if ($wishlistItemCollection->getSize() > 0) {
                $wishlistData[$id]['wishlist_id'] = $wishlistInfo['wishlist_id'];
                $wishlistData[$id]['customer_id'] = $wishlistInfo['customer_id'];
                $wishlistData[$id]['updated_at'] = $wishlistInfo['updated_at'];
                $wishlistData[$id]['item_details'] = Mage::helper('tracking')->getWishlistItemsInfo($wishlistInfo);
            }
        }
        return $wishlistData;
    }
}

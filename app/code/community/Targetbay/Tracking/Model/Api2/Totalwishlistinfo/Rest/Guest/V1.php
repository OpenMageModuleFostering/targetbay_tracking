<?php
/*
 * @author Targetbay
 * @copyright 2016 Sathishkumar Mariappan <sathishkumar.m@innoppl.com>
 * @package Targetbay_Tracking
 */
class Targetbay_Tracking_Model_Api2_Totalwishlistinfo_Rest_Guest_V1 extends Targetbay_Tracking_Model_Api2_Totalwishlistinfo {
	
	/**
	 * Get the total count info of wishlist
	 *
	 * @see Mage_Api2_Model_Resource::_retrieveCollection()
	 */
	public function _retrieveCollection() {

		$wishlistCollection = Mage::getModel('wishlist/wishlist')->getCollection();
		$i = 0; $count = '';
			
		foreach($wishlistCollection as $id => $wishlist) {
			$wishlistInfo = Mage::getModel('wishlist/wishlist')->loadByCustomer($wishlist->getCustomerId());
			$wishlistItemCollection = $wishlistInfo->setStoreId(1)->getItemCollection();
			if ($wishlistItemCollection->getSize() > 0) {
				$count = $i+1;
				$i++;
			}
		}

		$totals = array (
				'total_wishlist' => $count
		);
		
		return json_encode($totals);
	}
}

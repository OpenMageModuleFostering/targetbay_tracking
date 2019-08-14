<?php
/*
 * @author Targetbay
 * @copyright 2016 Sathishkumar Mariappan <sathishkumar.m@innoppl.com>
 * @package Targetbay_Tracking
 */
class Targetbay_Tracking_Model_Api2_Review_Rest_Admin_V1 extends Mage_Api2_Model_Resource {	
	
	/**
	 * Retrieve review
	 *
	 * @return array
	 */
	protected function _retrieveCollection() {

		$page_num = Mage::app()->getRequest()->getParam('page');
		$limit = Mage::app()->getRequest()->getParam('limit');
		
		$reviewCollection = Mage::getModel('review/review')->getResourceCollection()
				    ->setDateOrder()
				    ->addRateVotes()
				    ->load();
		$reviewCollection->getSelect()->limit($limit, $page_num);

		$review_data = array();	
		$review_datas = array();
		$sku = '';
		foreach($reviewCollection as $review)
        	{			
			$storeId = Mage::getModel('review/review')->load($review->getId())->getStoreId();	
			$product = Mage::getModel ( 'catalog/product' )->load($review->getEntityPkValue());
             		$sku = $product->getSku();                        

			if($sku) {
				$ratingCollection = Mage::getModel('rating/rating_option_vote')
							->getResourceCollection()
							->setReviewFilter($review->getId());

		        
				$rating_val = "";
				$option = "";
				$option_value = '';
				foreach($ratingCollection as $rating)
				{
				                     
				    $option =  $rating->getOptionId();                        
				    $rating_val = $rating->getRatingId(); 
				    
				    if(!empty($option_value) && $option_value != '') 
				        $option_value = $option_value."@".$rating_val.":".$option; 
				     else
				        $option_value = $rating_val.":".$option;
				        
				}

				$review_data['entity_id'] = $review->getId();
				$review_data['product_id'] = $review->getEntityPkValue(); 
				$review_data['sku'] = $sku;
				$review_data['title'] = $review->getTitle();
				$review_data['detail'] = $review->getDetail();
				$review_data['nickname'] = $review->getNickname();
				$review_data['store_id'] = $storeId;
				$review_data['website_id'] = Mage::getModel('core/store')->load($storeId)->getWebsiteId();
				$review_data['customer_id'] = $review->getCustomer_id();
				$review_data['option_id'] = $option_value;         
				$review_data['created_at'] = Mage::helper('core')->formatDate($review->getCreated_at(), 'long');
				$review_data['status_id'] = $review->getStatus_id();
				$review_datas[$review->getId()][] =   $review_data;      
			} 
		}
		return $review_datas;
	}
}

<?php
/*
 * @author Targetbay
 * @copyright 2016 Sathishkumar Mariappan <sathishkumar.m@innoppl.com>
 * @package Targetbay_Tracking
 */
class Targetbay_Tracking_Model_Api2_Category_Rest_Guest_V1 extends Mage_Api2_Model_Resource {
	
	/**
	 * Retrieve list of categories
	 *
	 * @return array
	 */
	protected function _retrieveCollection() {
		$model = Mage::getModel('catalog/category');
		$model->setStoreId($this->_getStore()->getId());
		$collection = $model->getCollection()
		 	->addAttributeToSelect(array_keys(
		         $this->getAvailableAttributes($this->getUserType(), Mage_Api2_Model_Resource::OPERATION_ATTRIBUTE_READ)
	 	        ));
		$this->_applyCollectionModifiers($collection);
		$categoryData = array();
		
		foreach ( $collection as $category ) {
			$categoryData[$category->getId()] = $category->toArray();
			$categoryDetail = Mage::getModel('catalog/category')->load($category->getId());
			$storeIds = $categoryDetail->getStoreIds();
			$categoryData[$category->getId()]['store_id'] = $storeIds;
			$categoryIds = $categoryDetail->getPathIds();
			$storesDetail = Mage::getModel('core/store')->getCollection()->loadByCategoryIds($categoryIds);
			$websiteIds = array_unique($storesDetail->getColumnValues('website_id'));
			$categoryData[$category->getId()]['website_id'] = $websiteIds;
		}
		return $categoryData;
	}
}

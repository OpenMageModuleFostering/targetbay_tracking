<?php
/*
 * @author Targetbay
 * @copyright 2016 Sathishkumar Mariappan <sathishkumar.m@innoppl.com>
 * @package Targetbay_Tracking
 */
class Targetbay_Tracking_Model_Api2_Totalproductinfo_Rest_Admin_V1 extends Targetbay_Tracking_Model_Api2_Totalproductinfo {
	
	/**
	 * Get the total count info of Products
	 *
	 * @see Mage_Api2_Model_Resource::_retrieveCollection()
	 */
	public function _retrieveCollection() {

		$collection = Mage::getResourceModel('catalog/product_collection');
		$store = $this->_getStore();
		$entityOnlyAttributes = $this->getEntityOnlyAttributes($this->getUserType(), Mage_Api2_Model_Resource::OPERATION_ATTRIBUTE_READ);
		$availableAttributes = array_keys($this->getAvailableAttributes($this->getUserType(), Mage_Api2_Model_Resource::OPERATION_ATTRIBUTE_READ));
	
		// available attributes not contain image attribute, but it needed for get image_url
		$availableAttributes[] = 'image';
		$collection->addAttributeToSelect(array_diff($availableAttributes, $entityOnlyAttributes));

		$totals = array (
				'total_products' => $collection->getSize()
		);
		
		return json_encode ( $totals );
	}
}

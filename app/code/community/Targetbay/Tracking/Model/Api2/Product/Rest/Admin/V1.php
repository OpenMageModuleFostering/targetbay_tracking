<?php
/*
 * @author Targetbay
 * @copyright 2016 Sathishkumar Mariappan <sathishkumar.m@innoppl.com>
 * @package Targetbay_Tracking
 */
class Targetbay_Tracking_Model_Api2_Product_Rest_Admin_V1 extends Mage_Catalog_Model_Api2_Product_Rest {
	
	// Product type configurable.
	CONST CONFIGURABLE_PRODUCT = 'configurable';
	CONST BUNDLE_PRODUCT = 'bundle';
	
	/**
	 * Retrieve list of products
	 *
	 * @return array
	 */
	protected function _retrieveCollection() {
		/**
		 *
		 * @var $collection Mage_Catalog_Model_Resource_Product_Collection
		 */
		$collection = Mage::getResourceModel('catalog/product_collection');
		$store = $this->_getStore();
		$collection->setStoreId($store->getId());
		$collection->addAttributeToSelect(array_keys($this->getAvailableAttributes($this->getUserType(), Mage_Api2_Model_Resource::OPERATION_ATTRIBUTE_READ)));
		$collection->addStoreFilter($store->getId())->addPriceData($this->_getCustomerGroupId(), $store->getWebsiteId())->addAttributeToSelect(array_diff($availableAttributes, $entityOnlyAttributes))->addAttributeToFilter('visibility', array(
				'neq' => Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE 
		))->addAttributeToFilter('status', array(
				'eq' => Mage_Catalog_Model_Product_Status::STATUS_ENABLED 
		));
		$this->_applyCategoryFilter($collection);
		$this->_applyCollectionModifiers( $collection);
		$products = $collection->load()->toArray();
		
		foreach($products as $id => $data) {
			$product = Mage::getModel('catalog/product')->load($id);
			$stockItem = Mage::getModel('cataloginventory/stock_item');
			$stockItem->loadByProduct($product);
			$products[$id]['qty'] = $stockItem->getQty();

			/**
			 *
			 * @var $category Get product categories
			 */
			$products[$id]['meta_keyword'] = (string) Mage::helper('tracking')->getProductCategory($product);
			$products[$id]['category_id'] = (string) Mage::helper('tracking')->getProductCategory($product);
			$products[$id]['related_product_id'] = implode(',', $product->getRelatedProductIds());
			$products[$id]['upsell_product_id'] = implode(',', $product->getUpSellProductIds());
			$products[$id]['crosssell_product_id'] = implode(',', $product->getCrossSellProducts());
			$products[$id]['visibility'] = $product->getVisibility();
			$products[$id]['status'] = $product->getStatus();
			$products[$id]['website_id'] = $product->getWebsiteIds();
			$products[$id]['store_id'] = $product->getStoreIds();
			$products[$id]['price'] = Mage::helper('core')->currency($product->getPrice(), true, false);
			$products[$id]['special_price'] = Mage::helper('core')->currency($product->getSpecialPrice(), true, false);
			$products[$id]['image_url'] = (string) Mage::helper('catalog/image')->init($product, 'image');

			$configOptions = array();
			$customOptions = array();
			$childProductData = array();
			
			if($product->getTypeId() == self::CONFIGURABLE_PRODUCT) {
				if($productAttributeOptions = Mage::getModel('catalog/product')->load($product->getId())->getTypeInstance (true)->getConfigurableAttributesAsArray($product)) {
					$configOptions = Mage::helper('tracking')->productOptions($productAttributeOptions, 'label');
				}
			
				$childProducts = $product->getTypeInstance()->getUsedProductIds();
				foreach($childProducts as $childProductId) {
					$childProductDetails = Mage::getModel('catalog/product')->load($childProductId);
					$childProductData[$childProductId] = Mage::helper('tracking')->getProductData($childProductDetails);
					$childProductData[$childProductId]['parent_id'] = $product->getId();
				}
				$products[$id]['child_items'] = $childProductData;
				$products[$id]['parent_id'] = $product->getId();
			}

			if($product->getTypeId() == self::BUNDLE_PRODUCT) {
				$collection = $product->getTypeInstance(true)
						->getSelectionsCollection($product->getTypeInstance(true)->getOptionsIds($product), $product);

				foreach($collection as $item) {
					$childProductId = $item->getId();
					$childProductDetails = Mage::getModel('catalog/product')->load($item->getId());
					$childProductData[$childProductId] = Mage::helper('tracking')->getProductData($childProductDetails);
					$childProductData[$childProductId]['parent_id'] = $product->getId();
				}
				$products[$id]['child_items'] = $childProductData;
				$products[$id]['parent_id'] = $product->getId();
			}
s
			if($custOptions = Mage::getModel('catalog/product')->load($product->getId())->getOptions()) {
				$customOptions = Mage::helper('tracking')->productOptions($custOptions);
			}
			$options = array_merge($configOptions, $customOptions);
			
			if (!empty($options))
				$products[$id]['attributes'] = $options;
		}
		
		return $products;
	}
}

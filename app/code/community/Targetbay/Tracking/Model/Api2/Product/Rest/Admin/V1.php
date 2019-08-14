<?php
/*
 * @author Targetbay
 * @copyright 2016 Sathishkumar Mariappan <sathishkumar.m@innoppl.com>
 * @package Targetbay_Tracking
 */
class Targetbay_Tracking_Model_Api2_Product_Rest_Admin_V1 extends Mage_Catalog_Model_Api2_Product_Rest
{

    // Product type configurable.
    const CONFIGURABLE_PRODUCT = 'configurable';
    const BUNDLE_PRODUCT = 'bundle';
    const GROUPED_PRODUCT = 'grouped';

    /**
     * Retrieve list of products
     *
     * @return array
     */
    protected function _retrieveCollection()
    {
        /**
         *
         * @var $collection Mage_Catalog_Model_Resource_Product_Collection
         */
        $collection = Mage::getResourceModel('catalog/product_collection');
        $store = $this->_getStore();

        $entityOnlyAttributes = $this->getEntityOnlyAttributes($this->getUserType(),
            Mage_Api2_Model_Resource::OPERATION_ATTRIBUTE_READ);
        $availableAttributes = array_keys($this->getAvailableAttributes($this->getUserType(),
            Mage_Api2_Model_Resource::OPERATION_ATTRIBUTE_READ));
        // available attributes not contain image attribute, but it needed for get image_url
        $availableAttributes[] = 'image';
        $collection->addStoreFilter($store->getId())
                    ->addAttributeToSelect(array_diff($availableAttributes, $entityOnlyAttributes));

        $this->_applyCategoryFilter($collection);
        $this->_applyCollectionModifiers($collection);
        $products = $collection->load()->toArray();

        foreach ($products as $id => $data) {
            $product = Mage::getModel('catalog/product')->load($id);
            $stockItem = Mage::getModel('cataloginventory/stock_item');
            $stockItem->loadByProduct($product);
            $products[$id]['qty'] = $stockItem->getQty();

            // Get product url key
            if ($product->getUrlKey() != '') {
                $urlKey = $product->getUrlKey();
            } else {
                $urlKey = $product->getProductUrl();
            }

            /**
             *
             * @var $category Get product categories
             */
            $products[$id]['meta_keyword'] = (string) Mage::helper('tracking')->getProductCategory($product);
            $products[$id]['category_id'] = (string) Mage::helper('tracking')->getProductCategory($product);
            if(count($product->getRelatedProductIds()) > 0) {
            $products[$id]['related_product_id'] = implode(',', $product->getRelatedProductIds());
            }
            if(count($product->getUpSellProductIds()) > 0) {
            $products[$id]['upsell_product_id'] = implode(',', $product->getUpSellProductIds());
            }
            if(count($product->getCrossSellProductIds()) > 0) {
            $products[$id]['crosssell_product_id'] = implode(',', $product->getCrossSellProductIds());
            }
            $products[$id]['visibility'] = $product->getVisibility();
            $products[$id]['status'] = $product->getStatus();
            $products[$id]['website_id'] = $product->getWebsiteIds();
            $products[$id]['store_id'] = $product->getStoreIds();
            $products[$id]['url_key'] = $urlKey;
            $products[$id]['price'] = $product->getFinalPrice();
            $products[$id]['special_price'] = $product->getSpecialPrice();
            $products[$id]['image_url'] = (string) Mage::helper('catalog/image')->init($product, 'image');
            $products[$id]['stock_count'] = ($stockItem->getQty() > 0) ? $stockItem->getQty() : $stockItem->getMaxSaleQty();

            $configOptions = array();
            $customOptions = array();
            $childProductData = array();

            switch ($product->getTypeId()) {
                case self::CONFIGURABLE_PRODUCT:
                    if ($productAttributeOptions = Mage::getModel('catalog/product')->load($product->getId())->getTypeInstance(true)->getConfigurableAttributesAsArray($product)) {
                        $configOptions = Mage::helper('tracking')->productOptions($productAttributeOptions, 'label');
                    }

                    $childProducts = $product->getTypeInstance()->getUsedProductIds();
                    foreach ($childProducts as $childProductId) {
                        $childProductDetails = Mage::getModel('catalog/product')->load($childProductId);
                        $childProductData[$childProductId] = Mage::helper('tracking')->getProductData($childProductDetails);
                        $childProductData[$childProductId]['parent_id'] = $product->getId();
                    }
                    $products[$id]['child_items'] = $childProductData;
                    $products[$id]['parent_id'] = $product->getId();
                break;
                case self::BUNDLE_PRODUCT:
                    $collection = $product->getTypeInstance(true)
                            ->getSelectionsCollection($product->getTypeInstance(true)->getOptionsIds($product), $product);

                    foreach ($collection as $item) {
                        $childProductId = $item->getId();
                        $childProductDetails = Mage::getModel('catalog/product')->load($item->getId());
                        $childProductData[$childProductId] = Mage::helper('tracking')->getProductData($childProductDetails);
                        $childProductData[$childProductId]['parent_id'] = $product->getId();
                    }
                    $products[$id]['child_items'] = $childProductData;
                    $products[$id]['parent_id'] = $product->getId();
                break;
                case self::GROUPED_PRODUCT:
                    $collection = $product->getTypeInstance(true)->getAssociatedProducts($product);

                    foreach ($collection as $item) {
                        $childProductId = $item->getId();
                        $childProductDetails = Mage::getModel('catalog/product')->load($item->getId());
                        $childProductData[$childProductId] = Mage::helper('tracking')->getProductData($childProductDetails);
                        $childProductData[$childProductId]['parent_id'] = $product->getId();
                    }
                    $products[$id]['child_items'] = $childProductData;
                    $products[$id]['parent_id'] = $product->getId();
                break;
            }

            if ($custOptions = Mage::getModel('catalog/product')->load($product->getId())->getOptions()) {
                $customOptions = Mage::helper('tracking')->productOptions($custOptions);
            }
            $options = array_merge($configOptions, $customOptions);

            if (!empty($options)) {
                $products[$id]['attributes'] = $options;
            }
        }

        return $products;
    }
}

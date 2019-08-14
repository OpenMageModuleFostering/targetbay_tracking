<?php

/*
 * @author Targetbay
 * @copyright 2016 Sathishkumar Mariappan <sathishkumar.m@innoppl.com>
 * @package Targetbay_Tracking
 */

class Targetbay_Tracking_Model_Api2_Product_Rest_Guest_V1 extends Mage_Catalog_Model_Api2_Product_Rest
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
        $entityOnlyAttributes = $this->getEntityOnlyAttributes($this->getUserType(), Mage_Api2_Model_Resource::OPERATION_ATTRIBUTE_READ);
        $availableAttributes = array_keys($this->getAvailableAttributes($this->getUserType(), Mage_Api2_Model_Resource::OPERATION_ATTRIBUTE_READ));

        // available attributes not contain image attribute, but it needed for get image_url
        $availableAttributes[] = 'image';
        $collection->addStoreFilter($store->getId())->addAttributeToSelect(array_diff($availableAttributes, $entityOnlyAttributes));

        $this->_applyCategoryFilter($collection);
        $this->_applyCollectionModifiers($collection);
        $products = $collection->load();

        /**
         *
         * @var Mage_Catalog_Model_Product $product
         */
        foreach ($products as $product) {
            $this->_setProduct($product);
            $this->_prepareProductForResponse($product);
        }
        return $products->toArray();
    }

    /**
     * Add special fields to product get response
     *
     * @param Mage_Catalog_Model_Product $product
     */
    protected function _prepareProductForResponse(Mage_Catalog_Model_Product $product)
    {
        /**
         *
         * @var $productHelper Mage_Catalog_Helper_Product
         */
        $productHelper = Mage::helper('catalog/product');
        $productData = $product->getData();
        $product->setWebsiteId($this->_getStore()->getWebsiteId());
        // customer group is required in product for correct prices calculation
        $product->setCustomerGroupId($this->_getCustomerGroupId());

        // Get Stock product
        $stockItem = Mage::getModel('cataloginventory/stock_item');
        $stockItem->loadByProduct($product);

        // Get product url key
        if ($product->getUrlKey() != '') {
            $urlKey = $product->getUrlKey();
        } else {
            $urlKey = $product->getProductUrl();
        }

        // calculate prices
        $finalPrice = $product->getFinalPrice();
        $productData['regular_price_with_tax'] = $this->_applyTaxToPrice($product->getPrice(), true);
        $productData['regular_price_without_tax'] = $this->_applyTaxToPrice($product->getPrice(), false);
        $productData['final_price_with_tax'] = $this->_applyTaxToPrice($finalPrice, true);
        $productData['final_price_without_tax'] = $this->_applyTaxToPrice($finalPrice, false);

        $productData['is_saleable'] = $product->getIsSalable();
        $productData['image_url'] = (string) Mage::helper('catalog/image')->init($product, 'image');
        $productData['visibility'] = $product->getVisibility();
        $productData['status'] = $product->getStatus();
        $productData['website_id'] = $product->getWebsiteIds();
        $productData['store_id'] = $product->getStoreIds();
        $productData['url_key'] = $urlKey;
        $productData['price'] = $product->getFinalPrice();
        $productData['special_price'] = $product->getSpecialPrice();
        $productData['stock_count'] = ($stockItem->getQty() > 0) ? $stockItem->getQty() : $stockItem->getMaxSaleQty();

        /**
         *
         * @var $category Get product categories
         */
        $productData['meta_keyword'] = (string) Mage::helper('tracking')->getProductCategory($product);
        $productData['category_id'] = (string) Mage::helper('tracking')->getProductCategory($product);
        if(count($product->getRelatedProductIds()) > 0) {
        $productData['related_product_id'] = implode(',', $product->getRelatedProductIds());
        }
        if(count($product->getUpSellProductIds()) > 0) {
        $productData['upsell_product_id'] = implode(',', $product->getUpSellProductIds());
        }
        if(count($product->getCrossSellProductIds()) > 0) {
        $productData['crosssell_product_id'] = implode(',', $product->getCrossSellProductIds());
        }

        if ($this->getActionType() == self::ACTION_TYPE_ENTITY) {
            // define URLs
            $productData['url'] = $productHelper->getProductUrl($product->getId());

            /**
             *
             * @var $cartHelper Mage_Checkout_Helper_Cart
             */
            $cartHelper = Mage::helper('checkout/cart');
            $productData['buy_now_url'] = $cartHelper->getAddUrl($product);

            /**
             *
             * @var $stockItem Mage_CatalogInventory_Model_Stock_Item
             */
            $stockItem = $product->getStockItem();
            if (!$stockItem) {
                $stockItem = Mage::getModel('cataloginventory/stock_item');
                $stockItem->loadByProduct($product);
            }
            $productData['is_in_stock'] = $stockItem->getIsInStock();

            /**
             *
             * @var $reviewModel Mage_Review_Model_Review
             */
            $reviewModel = Mage::getModel('review/review');
            $productData['total_reviews_count'] = $reviewModel->getTotalReviews($product->getId(), true, $this->_getStore()->getId());
            $productData['tier_price'] = $this->_getTierPrice();
            $productData['has_custom_options'] = count($product->getOptions()) > 0;
        } else {
            // remove tier price from response
            $product->unsetData('tier_price');
            unset($productData['tier_price']);
        }

        // Added the custom options and configurable products options
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
                $productData['child_items'] = $childProductData;
                $productData['parent_id'] = $product->getId();
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
                $productData['child_items'] = $childProductData;
                $productData['parent_id'] = $product->getId();
                break;
            case self::GROUPED_PRODUCT:
                $collection = $product->getTypeInstance(true)->getAssociatedProducts($product);

                foreach ($collection as $item) {
                    $childProductId = $item->getId();
                    $childProductDetails = Mage::getModel('catalog/product')->load($item->getId());
                    $childProductData[$childProductId] = Mage::helper('tracking')->getProductData($childProductDetails);
                    $childProductData[$childProductId]['parent_id'] = $product->getId();
                }
                $productData['child_items'] = $childProductData;
                $productData['parent_id'] = $product->getId();
                break;
        }
        if ($custOptions = Mage::getModel('catalog/product')->load($product->getId())->getOptions()) {
            $customOptions = Mage::helper('tracking')->productOptions($custOptions);
        }
        $options = array_merge($configOptions, $customOptions);

        if (!empty($options)) {
            $productData['attributes'] = $options;
        }
        $product->addData($productData);
    }
}

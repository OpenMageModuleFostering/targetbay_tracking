<?php
/*
 * @author Targetbay
 * @copyright 2016 Sathishkumar Mariappan <sathishkumar.m@innoppl.com>
 * @package Targetbay_Tracking
 */
class Targetbay_Tracking_Model_Api2_Tbreviewcount_Rest_Guest_V1 extends Targetbay_Tracking_Model_Api2_Tbreviewcount
{

    /**
     * Get the Review count from Targetbay
     *
     * @return array
     */
    public function _retrieveCollection()
    {
        $pageReference = Mage::app()->getRequest()->getParam('page_identifier') ? Mage::app()->getRequest()->getParam('page_identifier') : '';

        $reviewCount = Mage::app()->getRequest()->getParam('review_count') ? Mage::app()->getRequest()->getParam('review_count') : '';

        $productId = Mage::app()->getRequest()->getParam('product_id') ? Mage::app()->getRequest()->getParam('product_id') : '';
        if (!empty($pageReference) && $reviewCount > 0) {
            try {
                if ($pageReference == Targetbay_Tracking_Helper_Data::RATINGS_STATS) {
                    Mage::getSingleton('core/session')->setProductReviewCacheCount($reviewCount);
                } elseif ($pageReference == Targetbay_Tracking_Helper_Data::QUESTION_STATS) {
                    Mage::getSingleton('core/session')->setQaReviewCacheCount($reviewCount);
                } elseif ($pageReference == Targetbay_Tracking_Helper_Data::RATINGS_STATS
                                 && $productId == '') {
                    Mage::getSingleton('core/session')->setSiteReviewCacheCount($reviewCount);
                }
            } catch (\Exception $e) {
                Mage::helper('tracking')->debug($e->getMessage());
            }
        }
    }
}

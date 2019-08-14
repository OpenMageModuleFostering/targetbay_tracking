<?php
/*
 * @author Targetbay
 * @copyright 2016 Sathishkumar Mariappan <sathishkumar.m@innoppl.com>
 * @package Targetbay_Tracking
 */
class Targetbay_Tracking_Model_System_Config_Source_Page
{
    /**
     * Page Options configurations
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array(
                'value' => Targetbay_Tracking_Helper_Data::ALL_PAGES,
                'label' => Mage::helper('adminhtml')->__('All Pages')
            ),
            array(
                'value' => Targetbay_Tracking_Helper_Data::ADD_PRODUCT,
                'label' => Mage::helper('adminhtml')->__('Add Product')
            ),
            array(
                'value' => Targetbay_Tracking_Helper_Data::DELETE_PRODUCT,
                'label' => Mage::helper('adminhtml')->__('Delete Product')
            ),
            array(
                'value' => Targetbay_Tracking_Helper_Data::UPDATE_PRODUCT,
                'label' => Mage::helper('adminhtml')->__('Update Product')
            ),
            array(
                'value' => Targetbay_Tracking_Helper_Data::PAGE_VISIT,
                'label' => Mage::helper('adminhtml')->__('Page Visit')
            ),
            array(
                'value' => Targetbay_Tracking_Helper_Data::CATEGORY_VIEW,
                'label' => Mage::helper('adminhtml')->__('Category View')
            ),
            array(
                'value' => Targetbay_Tracking_Helper_Data::PRODUCT_VIEW,
                'label' => Mage::helper('adminhtml')->__('Product View')
            ),
            array(
                'value' => Targetbay_Tracking_Helper_Data::CATALOG_SEARCH,
                'label' => Mage::helper('adminhtml')->__('Search Page')
            ),
            array(
                'value' => Targetbay_Tracking_Helper_Data::CREATE_ACCOUNT,
                'label' => Mage::helper('adminhtml')->__('Create Account')
            ),
            array(
                'value' => Targetbay_Tracking_Helper_Data::LOGIN,
                'label' => Mage::helper('adminhtml')->__('Login')
            ),
            array(
                'value' => Targetbay_Tracking_Helper_Data::LOGOUT,
                'label' => Mage::helper('adminhtml')->__('Logout')
            ),
            array(
                'value' => Targetbay_Tracking_Helper_Data::ADDTOCART,
                'label' => Mage::helper('adminhtml')->__('Add to cart')
            ),
            array(
                'value' => Targetbay_Tracking_Helper_Data::UPDATECART,
                'label' => Mage::helper('adminhtml')->__('Update cart')
            ),
            array(
                'value' => Targetbay_Tracking_Helper_Data::REMOVECART,
                'label' => Mage::helper('adminhtml')->__('Remove Cart')
            ),
            array(
                'value' => Targetbay_Tracking_Helper_Data::CHECKOUT,
                'label' => Mage::helper('adminhtml')->__('Checkout')
            ),
            array(
                'value' => Targetbay_Tracking_Helper_Data::BILLING,
                'label' => Mage::helper('adminhtml')->__('Billing page')
            ),
            array(
                'value' => Targetbay_Tracking_Helper_Data::SHIPPING,
                'label' => Mage::helper('adminhtml')->__('Shipping page')
            ),
            array(
                'value' => Targetbay_Tracking_Helper_Data::ORDER_ITEMS,
                'label' => Mage::helper('adminhtml')->__('Order page')
            ),
            array(
                'value' => Targetbay_Tracking_Helper_Data::PAGE_REFERRAL,
                'label' => Mage::helper('adminhtml')->__('Referrer page')
            ),
	    array (
		'value' => Targetbay_Tracking_Helper_Data::WISHLIST,
		'label' => Mage::helper ( 'adminhtml' )->__ ( 'Wishlist page' ) 
	    )
        );
    }
}

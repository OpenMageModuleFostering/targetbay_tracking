<?php
/*
 * @author Targetbay
 * @copyright 2016 Sathishkumar Mariappan <sathishkumar.m@innoppl.com>
 * @package Targetbay_Tracking
 */
class Targetbay_Tracking_Helper_Data extends Mage_Core_Helper_Abstract
{
    public $cookie;
    public $date;

    CONST ANONYMOUS_USER = 'anonymous';
    // page types
    CONST ALL_PAGES = 'all';
    CONST PAGE_VISIT = 'page-visit';
    CONST PRODUCT_VIEW = 'product-view';
    CONST CATEGORY_VIEW = 'category-view';
    CONST DELETE_PRODUCT = "delete-product";
    CONST UPDATE_PRODUCT = 'update-product';
    CONST ADD_PRODUCT = 'add-product';
    CONST CREATE_ACCOUNT = 'create-account';
    CONST ADMIN_ACTIVATE_ACCOUNT = 'admin-activate-customer-account';
    CONST LOGIN = 'login';
    CONST LOGOUT = 'logout';
    CONST ADDTOCART = 'add-to-cart';
    CONST REMOVECART = 'remove-to-cart';
    CONST UPDATECART = 'update-cart';
    CONST ORDER_ITEMS = 'ordered-items';
    CONST BILLING = 'billing';
    CONST SHIPPING = 'shipping';
    CONST PAGE_REFERRAL = 'referrer';
    CONST CHECKOUT = 'checkout';
    CONST CATALOG_SEARCH = 'searched';
    CONST WISHLIST = 'wishlist';
    CONST UPDATE_WISHLIST = 'update-wishlist';
    CONST REMOVE_WISHLIST = 'remove-wishlist';
    CONST ONESTEPCHECKOUT_ADDRESS = 'onestepcheckout';
    CONST CART_INDEX = 'checkout-cart';
    CONST SUBSCRIBE_CUSTOMER = 'user-subscribe';
    CONST CUSTOMER_ADDRESS = 'change-user-address';
    CONST CUSTOMER_ACCOUNT = 'change-user-account-info';
    CONST CREATE_ADDRESS = 'new';
    CONST UPDATE_ADDRESS = 'edit';
    CONST RATINGS_STATS = 'ratings-stats';
    CONST QUESTION_STATS = 'qa-stats';
    
    // subscription status	
    CONST STATUS_SUBSCRIBED = 1;
    CONST STATUS_NOT_ACTIVE = 2;
    CONST STATUS_UNSUBSCRIBED = 3;
    CONST STATUS_UNCONFIRMED = 4;
    
    // product stock status
    CONST IN_STOCK = 'in-stock';
    CONST OUT_OF_STOCK = 'out-stock';
    
    // order fullfillment process
    CONST ORDER_SHIPMENT = 'shipment';
    CONST ORDER_INVOICE = 'invoice';
    CONST ORDER_REFUND = 'creditmemo';
    CONST ORDER_STATUS = 'order-status';

    CONST HOST_STAGE = 'https://stage.targetbay.com/api/v1/webhooks/';
    CONST HOST_LIVE = 'https://app.targetbay.com/api/v1/webhooks/';
    CONST HOST_DEV = 'https://dev.targetbay.com/api/v1/webhooks/';

    CONST API_STAGE = 'stage';
    CONST API_LIVE = 'app';
    CONST API_DEV = 'dev';
    CONST DEFAULT_TIMEOUT = 30;
    
    /**
     * Initialize object
     */
    public function __construct()
    {
        $this->cookie = Mage::getModel('core/cookie');
        $this->date   = Mage::getModel('core/date');
    }
    
    /**
     * Check module is enabled or not
     *
     * @return boolean
     */
    public function trackingEnabled()
    {
        return (bool)Mage::getStoreConfig('targetbay_tracking/tracking_groups/enabled');
    }
    
    /**
     * Get Target bay Host
     *
     * @return mixed
     */
    public function getHostname()
    {
        return Mage::getStoreConfig('targetbay_tracking/tracking_groups/hostname');
    }
    
    /**
     * Get TargetbayToken
     *
     * @return mixed
     */
    public function getApiToken()
    {
        return Mage::getStoreConfig('targetbay_tracking/tracking_groups/api_token');
    }
    
    /**
     * Get TargetBayIndex
     *
     * @return mixed
     */
    public function getApiIndex()
    {
        return Mage::getStoreConfig('targetbay_tracking/tracking_groups/api_index');
    }
    
    /**
     * Get getApiStatus
     *
     * @return mixed
     */
    public function getApiStatus()
    {
        $apiStatus = Mage::getStoreConfig('targetbay_tracking/tracking_groups/api_status');
	if($apiStatus) {
		return $apiStatus;
	} else {
		return self::API_LIVE;
	}
    }
    
    /**
     * Get the Session Tracking Js
     *
     * @return mixed
     */
    public function getReviewPageSize()
    {        
	$reviewSize = Mage::getStoreConfig('targetbay_tracking/tracking_groups/reviews_per_page');
	if($reviewSize){
		$reviewSize = $reviewSize;
	} else {
		$reviewSize = 10;
	}
	return $reviewSize;
    }
    
    /**
     * Get the email status
     *
     * @return mixed
     */
    public function getEmailStatus()
    {        
	$emailStatus = Mage::getStoreConfig('targetbay_tracking/tracking_groups/disable_email');
	if($emailStatus){
		$emailStatus = $emailStatus;
	} else {
		$emailStatus = 1;
	}
	return $emailStatus;
    }
    
    /**
     * Get TargetBay tracking type
     *
     * @return mixed
     */
    public function getTrackingType()
    {
        return Mage::getStoreConfig('targetbay_tracking/tracking_groups/tracking_type');
    }
    
    /**
     * Print the log or not
     *
     * @return mixed
     */
    public function logEnabled()
    {
        return Mage::getStoreConfig('targetbay_tracking/tracking_groups/debug');
    }
    
    /**
     * Get the log file name from configurations
     */
    public function getLogFileName()
    {
        return Mage::getStoreConfig('targetbay_tracking/tracking_groups/debug_file');
    }
    
    /**
     * Get the availabe pages from configurations
     *
     * @return multitype:
     */
    public function availablePageTypes()
    {
        $types      = (string) Mage::getStoreConfig('targetbay_tracking/tracking_groups/page_types');
        $typesArray = explode(',', $types);
        return $typesArray;
    }
    
    /**
     * Get Tracking Code
     *
     * @return mixed
     */
    public function getTrackingScript()
    {
        return Mage::getStoreConfig('targetbay_tracking/tracking_groups/tracking_script');
    }
    
    /**
     * Check the page configurations
     *
     * @param unknown $pageType        	
     * @return boolean
     */
    public function canTrackPages($pageType)
    {
        if (!$this->trackingEnabled()) {
            $this->debug('Tracking Module is Not Enabled. Please enable a Module');
            return false;
        }
        $availabelPages = $this->availablePageTypes();
        if (in_array(self::ALL_PAGES, $availabelPages)) {
            return true;
        }
        if (!in_array($pageType, $availabelPages)) {
            $this->debug("'$pageType'" . 'page is not enabled');
            return false;
        }
        return true;
    }
    
    /**
     * Get the customer data based on the action
     *
     * @param unknown $customer        	
     * @param unknown $action        	
     * @return unknown
     */
    public function getCustomerData($customer, $action)
    {
	try {
		switch ($action) {
		    case self::LOGIN:
			$data               = $this->getCustomerSessionId($customer);
			$data['login_date'] = $this->date->date('Y-m-d');
			break;
		    case self::LOGOUT:
			$data['session_id']  = Mage::getSingleton('core/session')->getCustomerSessionId();
			$data['logout_date'] = $this->date->date('Y-m-d');
			Mage::getSingleton('core/session')->unsTrackingSessionId();
			Mage::getSingleton('core/session')->unsCustomerSessionId();
			break;
		    case self::CREATE_ACCOUNT:
			$data          = $this->getCustomerSessionId($customer);                
			$data['firstname']           = $customer->getFirstname();
			$data['lastname']            = $customer->getLastname();
			$data['subscription_status'] = $this->getSubscriptionStatus($customer->getId());
			$data['account_created']     = $this->date->date('Y-m-d');
			break;
		}
		$data['user_id']    = $customer->getId();
		$data['user_name']  = $customer->getName();
		$data['user_mail']  = $customer->getEmail();
		$data['timestamp']  = strtotime($this->date->date('Y-m-d'));
		$data['ip_address'] = Mage::helper('core/http')->getRemoteAddr();
		return $data;
	} catch (Exception $e) {
		$this->debug('Error message:'.$e->getMessage());
		return;
        }
    }
	
    /**
     * Get the customer subscription status
     *
     * @param unknown $customerId   	
     * @return string
     */
    public function getSubscriptionStatus($customerId)
    {
	try {
		$customerData = Mage::getModel('customer/customer')->load($customerId);
		$subscriber    = Mage::getModel('newsletter/subscriber')->loadByCustomer($customerData);
		if ($subscriber->getSubscriberStatus() == self::STATUS_UNSUBSCRIBED) {
		    $status = 'Unsubscribed';
		} elseif ($subscriber->getSubscriberStatus() == self::STATUS_SUBSCRIBED) {
		    $status = 'Subscribed';
		} elseif ($subscriber->getSubscriberStatus() == self::STATUS_UNCONFIRMED) {
		    $status = 'Unconfirmed';
		} elseif ($subscriber->getSubscriberStatus() == self::STATUS_NOT_ACTIVE) {
		    $status = 'Not Activated';
		} else {
		    $status = '';
		}
		return $status;
	} catch (Exception $e) {
		$this->debug('Error message:'.$e->getMessage());
		return;
        }
     }
    
    /**
     * Get the customer session info
     *
     * @param unknown $customerId        	
     * @return string
     */
    public function getCustomerSessionId($customer)
    {
	$data = array();
	try {
		$visitorData = Mage::getSingleton('core/session')->getVisitorData();
		$session = $visitorData['session_id'] . strtotime(date('Y-m-d H:i:s'));
		$data['session_id'] = $session;
		
		$trackingType = $this->getTrackingType();
		if ($trackingType == 1):
		    $data['previous_session_id'] = $this->cookie->get('targetbay_session_id');
		else:
		    $data['previous_session_id'] = $this->cookie->get('trackingsession');
		endif;
		
		Mage::getSingleton('core/session')->setCustomerSessionId($session);
		return $data;
	} catch (Exception $e) {
		$this->debug('Error message:'.$e->getMessage());
		return;
        }
    }
    
    /**
     * Check the order placed by registered user or not
     *
     * @param unknown $quoteId        	
     * @return boolean|Mage_Core_Model_Abstract
     */
    public function isRegisterCheckout($order)
    {
        $checkoutMethod = Mage::getModel('sales/quote')->load($order->getQuoteId())->getCheckoutMethod(true);
        if ($checkoutMethod != 'register')
            return false;
        return Mage::getModel('customer/customer')->load($order->getCustomerId());
    }
    
    /**
     * Basic visit info
     *
     * @return string
     */
    public function visitInfo()
    {
	$data = array();
	try {
		$customer          = Mage::getSingleton('customer/session');
		$data['user_name'] = $customer->isLoggedIn() ? $customer->getCustomer()->getName() : self::ANONYMOUS_USER;
		$data['user_email'] = $customer->isLoggedIn() ? $customer->getCustomer()->getEmail() : self::ANONYMOUS_USER;
		$trackingType      = $this->getTrackingType();
		$moduleName        = Mage::app()->getRequest()->getModuleName();	
		
		if (($moduleName == 'catalogsearch') && ($customer->isLoggedIn())) {
		    $user_id = $customer->getCustomer()->getId();
		} else {
		    $user_id = $customer->isLoggedIn() ? $customer->getCustomer()->getId() : $this->cookie->get('targetbay_session_id');
		}

		if ($trackingType == 1):
		    $data['already_tracked'] = true;
		    $data['user_id']         = $user_id;
		    $data['session_id']      = $customer->isLoggedIn() ? Mage::getSingleton('core/session')->getCustomerSessionId() : $this->cookie->get('targetbay_session_id');
		else:
		    if (!$this->cookie->get('trackingsession')) {
		    	$userId = Mage::getSingleton('core/session')->getTrackingSessionId();
		    } else {
		    	$userId = $this->cookie->get('trackingsession');
		    }
		    $data['user_id']    = $customer->isLoggedIn() ? $customer->getCustomer()->getId() : $userId;
		    $data['session_id'] = $customer->isLoggedIn() ? Mage::getSingleton('core/session')->getCustomerSessionId() : Mage::getSingleton('core/session')->getTrackingSessionId();
		endif;
		
		$data['page_url']    = Mage::helper('core/url')->getCurrentUrl();
		$data['ip_address']  = Mage::helper('core/http')->getRemoteAddr();
		$data['user_agent']  = Mage::helper('core/http')->getHttpUserAgent();
		$data['utm_sources'] = $this->cookie->get('utm_source') ? $this->cookie->get('utm_source') : '';
		$data['utm_token']   = $this->cookie->get('utm_token') ? $this->cookie->get('utm_token') : '';
		$pageTitle = Mage::app()->getLayout()->getBlock('head') ? Mage::app()->getLayout()->getBlock('head')->getTitle() : Mage::getSingleton('checkout/session')->getTitle();
		$data['page_title']  = str_replace("'", " ", $pageTitle);
		return $data;
	} catch (Exception $e) {
		$this->debug('Error message:'.$e->getMessage());
		return;
        }
    }
    
    /**
     * Push the referer url
     *
     * @return boolean
     */
    public function getRefererData()
    {
	try {
		$isSecure = Mage::app()->getFrontController()->getRequest()->isSecure();
		if($isSecure) {
			$currentUrl = Mage::getUrl('', array('_secure'=>true));
		} else {
			$currentUrl = Mage::getUrl('');
		}
		$referer    = Mage::helper('core/http')->getHttpReferer() ? Mage::helper('core/http')->getHttpReferer() : $currentUrl;
		if (strpos($referer, $currentUrl) !== false) {
		    return false; // base url and referer url matches.
		}
		$data                 = $this->visitInfo();
		$data['referrer_url'] = $referer;
		return $data;
	} catch (Exception $e) {
		$this->debug('Error message:'.$e->getMessage());
		return;
        }
    }
    
    /**
     * Push the page info using Varien_Http_Client
     *
     * @return string
     */
    public function postPageInfo($url, $jsondata)
    {
        $client = new Varien_Http_Client();
        $client->setUri($url);
        $client->setConfig(array(
            'maxredirects' => 0,
            'timeout' => 1
        ));
        $client->setRawData(utf8_encode($jsondata));
        
        try {
            $this->debug("Endpoint Url =>$url");
            $this->debug("Request =>");
            $this->debug($jsondata);
            $response = $client->request(Varien_Http_Client::POST)->getBody();
            $this->debug("Response =>");
            $this->debug ( print_r($response, true) );
            return $response;
        } catch (Exception $e) {
	    $this->debug('Error message '.$e->getMessage());
            return;
        }
    }
    
    /**
     * Remove the cookies
     */
    public function removeCookies()
    {
        $this->cookie->delete('trackingsession');
        $this->cookie->delete('trackingid');
        $this->cookie->delete('trackingemail');
        $this->cookie->delete('trackingname');
        $this->cookie->delete('trackingorderid');
        $this->cookie->delete('utm_source');
        $this->cookie->delete('utm_token');
        $this->cookie->delete('targetbay_session_id');
        $this->cookie->delete('user_loggedin');
        $this->cookie->delete('afterlogin_session_id');
    }
    
    /**
     * Get the cart info
     *
     * @return string
     */
    public function getCartInfo()
    {
	$data = array();
	try {
		$customer          = Mage::getSingleton('customer/session');
		$checkout          = Mage::getSingleton('checkout/session')->getQuote();		
		$trackingType = $this->getTrackingType();
		if ($trackingType == 1):
		    $data['user_id']    = $customer->isLoggedIn() ? $customer->getCustomer()->getId() : $this->cookie->get('targetbay_session_id');
		    $data['session_id'] = $customer->isLoggedIn() ? Mage::getSingleton('core/session')->getCustomerSessionId() : $this->cookie->get('targetbay_session_id');
		else:
		    $data['user_id']    = $customer->isLoggedIn() ? $customer->getId() : Mage::getSingleton('core/session')->getTrackingSessionId();
		    $data['session_id'] = $customer->isLoggedIn() ? Mage::getSingleton('core/session')->getCustomerSessionId() : Mage::getSingleton('core/session')->getTrackingSessionId();
		endif;

		$data['user_name'] = $customer->isLoggedIn() ? $customer->getCustomer()->getName() : Targetbay_Tracking_Helper_Data::ANONYMOUS_USER;
		$data['user_mail'] = $customer->isLoggedIn() ? $customer->getCustomer()->getEmail() : Targetbay_Tracking_Helper_Data::ANONYMOUS_USER;

		$data['order_id']    = Mage::getSingleton('checkout/session')->getQuoteId();
		$data['utm_sources'] = $this->cookie->get('utm_source') ? $this->cookie->get('utm_source') : '';
		$data['utm_token']   = $this->cookie->get('utm_token') ? $this->cookie->get('utm_token') : '';
		$data['timestamp']   = strtotime($this->date->date('Y-m-d'));
		$pageTitle = Mage::app()->getLayout()->getBlock('head') ? Mage::app()->getLayout()->getBlock('head')->getTitle() : Mage::getSingleton('checkout/session')->getTitle();
		$data['page_title']  = addslashes($pageTitle);		
		return $data;
        } catch (Exception $e) {
	    $this->debug('Error message '.$e->getMessage());
            return;
        }
    }
    
    /**
     * Get the common info for quote or order, billing and shipping
     *
     * @param unknown $order        	
     * @return string
     */
    public function getSessionInfo($object)
    {
	$data = array();
	try {      
		$trackingType = $this->getTrackingType();
		if ($trackingType == 1):
		    $data['user_id']    = $object->getCustomerId() ? $object->getCustomerId() : $this->cookie->get('targetbay_session_id');
		    $data['session_id'] = Mage::getSingleton('customer/session')->isLoggedIn() ? Mage::getSingleton('core/session')->getCustomerSessionId() : $this->cookie->get('targetbay_session_id');
		else:
		    $data['user_id']    = $object->getCustomerId() ? $object->getCustomerId() : Mage::getSingleton('core/session')->getTrackingSessionId();
		    $data['session_id'] = Mage::getSingleton('customer/session')->isLoggedIn() ? Mage::getSingleton('core/session')->getCustomerSessionId() : Mage::getSingleton('core/session')->getTrackingSessionId();
		endif;

		$data['utm_sources'] = $this->cookie->get('utm_source') ? $this->cookie->get('utm_source') : '';
		$data['utm_token']   = $this->cookie->get('utm_token') ? $this->cookie->get('utm_token') : '';
		$data['timestamp']   = strtotime($this->date->date('Y-m-d'));
		$pageTitle = Mage::app()->getLayout()->getBlock('head') ? Mage::app()->getLayout()->getBlock('head')->getTitle() : Mage::getSingleton('checkout/session')->getTitle();
		$data['page_title']  = addslashes($pageTitle);
		return $data;
        } catch (Exception $e) {
	    $this->debug('Error message '.$e->getMessage());
            return;
        }
    }
    
    /**
     * Get the Order data
     *
     * @param unknown $object        	
     * @return string
     */
    public function getInfo($object)
    {  
    	try {
    		$customer                = $object->getCustomer();
    		$items                   = $object->getAllVisibleItems();
    		$data                    = $this->getSessionInfo($object);
    		$data['first_name']      = $object->getCustomerFirstname();
    		$data['last_name']       = $object->getCustomerLastname();
    		$guestUsername = $object->getCustomerFirstname().' '.$object->getCustomerLastname();
    		$gName = !empty($guestUsername) ? $guestUsername : self::ANONYMOUS_USER;
    		$data['user_name']       = $object->getCustomerIsGuest() ? $gName : $data['first_name'] . ' ' . $data['last_name'];
    		$data['user_mail']       = $object->getCustomerEmail();
    		$data['order_id']        = $object->getId();
    		$data['order_price']     = $object->getSubtotal();
    		$data['order_quantity']  = $object->getData('total_qty_ordered');
    		$data['shipping_method'] = $object->getData('shipping_description');
    		$data['shipping_price']  = $object->getData('shipping_amount');
    		$data['tax_amount']      = $object->getData('tax_amount');
    		$data['payment_method']  = $object->getPayment()->getMethodInstance()->getTitle();
    		$data['cart_items']      = $this->getOrderItemsInfo($object);
    		return $data;
        } catch (Exception $e) {
            $this->debug('Error message '.$e->getMessage());
            return;
        }
    }
    
    /**
     * Get the Order data
     *
     * @param unknown $object           
     * @return string
     */
    public function getOrderInfo($object)
    {  
        try {
            $customer                = $object->getCustomer();
            $items                   = $object->getAllVisibleItems();
            $data['user_id']         = $object->getCustomerIsGuest() ? self::ANONYMOUS_USER : $object->getCustomerId();
            $data['first_name']      = $object->getCustomerFirstname();
            $data['last_name']       = $object->getCustomerLastname();
            $guestUsername = $object->getCustomerFirstname().' '.$object->getCustomerLastname();
            $gName = !empty($guestUsername) ? $guestUsername : self::ANONYMOUS_USER;
            $data['user_name']       = $object->getCustomerIsGuest() ? $gName : $data['first_name'] . ' ' . $data['last_name'];
            $data['user_mail']       = $object->getCustomerEmail();
            $data['order_id']        = $object->getId();
            $data['order_status']        = $object->getStatus();
            $data['order_price']     = $object->getSubtotal();
            $data['order_quantity']  = $object->getData('total_qty_ordered');
            $data['shipping_method'] = $object->getData('shipping_description');
            $data['shipping_price']  = $object->getData('shipping_amount');
            $data['tax_amount']      = $object->getData('tax_amount');
            $data['payment_method']  = $object->getPayment()->getMethodInstance()->getTitle();
            $data['cart_items']      = $this->getOrderItemsInfo($object);
            return $data;
        } catch (Exception $e) {
            $this->debug('Error message '.$e->getMessage());
            return;
        }
    }
    
    /**
     * Collect the order items info
     *
     * @param unknown $order        	
     * @return multitype:unknown
     */
    public function getOrderItemsInfo($order, $orderExportApi = false)
    { 
        $dataItems = array(); 
	try {
		$items     = $order->getAllVisibleItems();
		foreach ($items as $orderItem) {
		    $dataItem = $this->getItemInfo($orderItem);
		    if ($customOptions = $this->getCustomOptionsInfo($orderItem, $orderExportApi))
		        $dataItem['attributes'] = $customOptions;
		    $dataItems[] = $dataItem;
		}		
		return $dataItems;
        } catch (Exception $e) {
            $this->debug('Error message '.$e->getMessage());
            return;
        }
    }
    
    /**
     * Collect the order payment info
     *
     * @param unknown $order        	
     * @return multitype:unknown
     */
    public function getPaymentInfo($orderId, $orderExportApi = false)
    {
    	$paymentTitle      = '';$paymentMethod     = '';
    	try {
    		$paymentCollection = Mage::getModel('sales/order_payment')->getCollection()->addFieldToFilter('parent_id', $orderId);
    		foreach ($paymentCollection as $paymentDetails) {
    		    $paymentMethod = $paymentDetails->getMethod();
    		}
    		
    		$store        = Mage::app()->getStore();
    		$path         = 'payment/' . $paymentMethod . '/title';
    		$paymentTitle = Mage::getStoreConfig($path, $store);
    		return $paymentTitle;
        } catch (Exception $e) {
            $this->debug('Error message '.$e->getMessage());
            return;
        }
    }
    
    /**
     * Get the item info
     *
     * @param unknown $item        	
     * @return unknown
     */
    public function getItemInfo($item, $actionType = false)
    {
    	$dataItem = array();
    	try {
    		$product                  = Mage::getModel('catalog/product')->load($item->getData('product_id'));
    		$dataItem['type']         = $item->getProductType();
    		$dataItem['product_id']   = $item->getProductId();
    		$dataItem['product_sku']  = $item->getSku();
    		$dataItem['product_name'] = addslashes($item->getName());
    		$dataItem['price']        = $actionType ? $item->getProduct()->getPrice() : $item->getPrice();
    		$dataItem['special_price'] = $product->getSpecialPrice();
    		$qty                      = $actionType ? $item->getProduct()->getQty() : $item->getQty();
    		$dataItem['productimg']   = $this->getImageUrl($product, 'image');
    		
    		$dataItem['category']      = $this->getProductCategory($product);
    		$dataItem['category_name'] = $this->getProductCategoryName($product);
    		$dataItem['quantity']      = ($item->getData('qty_ordered')) ? $item->getData('qty_ordered') : $qty;
    		$dataItem['page_url']      = Mage::getUrl($product->getUrlPath());
    		return $dataItem;
        } catch (Exception $e) {
            $this->debug('Error message '.$e->getMessage());
            return;
        }
    }
    
    /**
     * Get the product categories
     *
     * @param unknown $product       	
     * @return unknown
     */    
    public function getProductCategoryName($product)
    {
        $categoryIds = $product->getCategoryIds();
        
        $productCategories = array();
        $categoryName      = '';
        
        if (count($categoryIds)) {
            foreach ($categoryIds as $categoryId) {
                $_category           = Mage::getModel('catalog/category')->load($categoryId);
                $productCategories[] = str_replace("'", " ", $_category->getName());
            }
            
            $categoryName = implode(',', $productCategories);
        }
        
        return $categoryName;
    }
    
    /**
     * Get the product categories
     *
     * @param unknown $product       	
     * @return unknown
     */    
    public function getProductCategory($product)
    {
        $categoryIds = $product->getCategoryIds();        
        $productCategories = array();
        $categoryId        = '';
        if (count($categoryIds)) {
            
            foreach ($categoryIds as $categoryId) {
                $_category           = Mage::getModel('catalog/category')->load($categoryId);
                $productCategories[] = $_category->getName();
            }

            $categoryId = implode(',', $categoryIds);
        }
        
        return $categoryId;
    }
    
    /**
     * Get the item custom options
     *
     * @param unknown $item        	
     * @return boolean|unknown|number
     */
    public function getCustomOptionsInfo($item, $orderExportApi)
    {
	try {
		$customOptions = $orderExportApi ? $item->getProductOptions() : $item->getProduct()->getTypeInstance(true)->getOrderOptions($item->getProduct());

		if (empty($customOptions['options']) && empty($customOptions['attributes_info']))
		    return false;
		$superAttributeInfo = isset($customOptions['attributes_info']) ? $this->getOptionValues($customOptions['attributes_info']) : array();
		$customOptionInfo   = isset($customOptions['options']) ? $this->getOptionValues($customOptions['options']) : array();
		$optionsData        = array_merge($superAttributeInfo, $customOptionInfo);
		return $optionsData;
        } catch (Exception $e) {
            $this->debug('Error message '.$e->getMessage());
            return;
        }
    }
    
    /**
     * Get the options values
     *
     * @param unknown $options        	
     * @return multitype:unknown
     */
    public function getOptionValues($options)
    {
        $optionData = array();
    	try {
    		foreach ($options as $option) {
    		    $data['label'] = $option['label'];
    		    $data['value'] = $option['value'];
    		    $optionData[]  = $data;
    		}
    		return $optionData;
        } catch (Exception $e) {
            $this->debug('Error message '.$e->getMessage());
            return;
        }
    }
    
    /**
     * Get the order or quote address by address type.
     *
     * @param unknown $object        	
     * @param unknown $type        	
     * @return string
     */
    public function getAddressData($object, $type)
    {
    	try {
    		$address                   = ($type == self::SHIPPING) ? $object->getShippingAddress() : $object->getBillingAddress();
    		$addressData               = $this->getSessionInfo($object);
    		$addressData['first_name'] = $address->getFirstname();
    		$addressData['last_name']  = $address->getLastname();
    		$guestUsername = $address->getFirstname().' '.$address->getLastname();
    		$gName = !empty($guestUsername) ? $guestUsername : self::ANONYMOUS_USER;
    		$addressData['user_name']  = $object->getCustomerIsGuest() ? $gName : $addressData['first_name'] . ' ' . $addressData['last_name'];
    		$addressData['order_id']   = $object->getId();
    		$addressData['user_mail']  = $object->getCustomerEmail();
    		$addressData['address1']   = $address->getStreet(1);
    		$addressData['address2']   = $address->getStreet(2);
    		$addressData['city']       = $address->getCity();
    		$addressData['state']      = $address->getRegion();
    		$addressData['zipcode']    = $address->getPostcode();
    		if($address->getCountryId()) {
    			$countryName = Mage::getModel('directory/country')->loadByCode($address->getCountryId())->getName();
    		} else {
    			$countryName = '';
    		}
    		$addressData['country'] = isset($countryName) ? $countryName : $address->getCountryId();
    		$addressData['phone']      = $address->getTelephone();
    		return $addressData;
        } catch (Exception $e) {
            $this->debug('Error message '.$e->getMessage());
            return;
        }
    }
    
    /**
     * Get the product info
     *
     * @param unknown $product        	
     * @return string
     */
    public function getProductData($product)
    {		
    	$configOptions = array();$customOptions = array();$data = array();
    	try {
    		$data['image_url'][]      = $this->getProductImages($product);
    		$data['entity_id']        = $product->getId();
    		$data['attribute_set_id'] = $product->getEntityTypeId();
    		$data['type_id']          = $product->getTypeId();
    		$data['sku']              = $product->getSku();
    		$data['product_status']   = $product->getStatus();
    		$data['currency_type']    = Mage::app()->getStore()->getCurrentCurrencyCode();
    		
    		$data['stock_count'] = -1;
    		
    		if($stock = $product->getData('stock_data')) {
    		    $data['stock_count'] = !empty($stock['is_in_stock']) ? $stock['qty'] : -1;
    		}
    		
    		$data['visibility']  = $product->getVisibility();
    		$data['description'] = $product->getDescription();
    		$data['price']       = $product->getPrice();
    		$data['special_price']       = $product->getSpecialPrice();
    		$data['weight']      = $product->getWeight();
    		$data['name']        = addslashes($product->getName());
    		
    		$data['category']     = $this->getProductCategory($product);
    		$data['url_key']      = $product->getUrlKey();
    		$data['full_url_key'] = $product->getProductUrl();
    		
    		if($configData = $product->getData('configurable_attributes_data')) {
    		    $configOptions = $this->productOptions($configData, 'label');
    		}
    		if($custOptions = $product->getData('product_options')) {
    		    $customOptions = $this->productOptions($custOptions);
    		}
    		$options = array_merge($configOptions, $customOptions);
    		if(!empty($options)) {
    		    $data['attributes'] = $options;
            }
    		return $data;
        } catch (Exception $e) {
            $this->debug('Error message '.$e->getMessage());
            return;
        }
    }
    
    /**
     * Get the product options when saving product
     *
     * @param unknown $configData        	
     * @param string $customOption        	
     * @return multitype:multitype:unknown
     */
    public function productOptions($configData, $customOption = 'title')
    {
        $options = array();
    	try {
    		foreach($configData as $cdata) {
    		    $attrLabel = $cdata[$customOption];
    		    if(!isset($cdata['values'])) {
    		        $options[] = array(
    		            'label' => $attrLabel,
    		            'value' => $attrLabel
    		        );
    		        continue;
    		    }
    		    foreach($cdata['values'] as $val) {
    		        $attrVal   = $val[$customOption];
    		        $options[] = array(
    		            'label' => $attrLabel,
    		            'value' => $attrVal
    		        );
    		    }
    		}
    		return $options;
        } catch (Exception $e) {
            $this->debug('Error message '.$e->getMessage());
            return;
        }
    }
    
    /**
     * Get the product images
     *
     * @param unknown $product        	
     * @return string
     */
    public function getProductImages($product)
    {
        $images = array();
        try {
        	$images['url']             = $this->getImageUrl($product, 'image');
        	$images['position']        = 1;
        	$images['thumbnail_image'] = $this->getImageUrl($product, 'small_image');
        	$images['medium_image']    = $this->getImageUrl($product, 'thumbnail');
        	return $images;
        } catch (Exception $e) {
            $this->debug('Error message '.$e->getMessage());
            return;
        }
    }
    
    /**
     * Get the Product image url
     *
     * @param unknown $product        	
     * @param unknown $imageType        	
     * @return string
     */
    public function getImageUrl($product, $imageType)
    {
        if($product->getData($imageType)) {
        	$imgPath = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'catalog/product' . $product->getData($imageType);
        } else {
        	$imgPath = '';
        }

        return $imgPath;
    }
    
    /**
     * Check the order full fillment
     *
     * @return boolean
     */
    public function isFullFillmentProcess($params)
    {
        if(isset($params[self::ORDER_SHIPMENT]) || 
            isset($params[self::ORDER_INVOICE]) || 
            isset($params[self::ORDER_REFUND])
        ) {
            return true;
        }
        return false;
    }
    
    /**
     * Get the wishlist product info
     *
     * @param unknown $productId        	
     * @return string
     */
    public function getWishlistProductInfo($productId)
    {
    	$data = array();
    	try {
    		$product = Mage::getModel('catalog/product')->load($productId);
    		$qty = 1;

    	 	$data['type']         = $product->getTypeId();
    		$data['product_id']   = $product->getId();
    		$data['product_sku']  = $product->getSku();
    		$data['name']  = addslashes($product->getName());
    		$data['price']        = $product->getPrice();
    		$data['special_price'] = $product->getSpecialPrice();
    		$data['productimg']   = $this->getImageUrl($product, 'image');
    		
    		$data['category']      = $this->getProductCategory($product);
    		$data['category_name'] = $this->getProductCategoryName($product);
    		$data['quantity']      = (Mage::app()->getRequest()->getParam('qty')) ? Mage::app()->getRequest()->getParam('qty') : $qty;
    		$data['page_url']      = Mage::getUrl($product->getUrlPath());
    		
    		return $data;
        } catch (Exception $e) {
            $this->debug('Error message '.$e->getMessage());
            return;
        }
    }
    
    /**
     * Collect the wishlist items info
     *
     * @param unknown $productIds        	
     * @return multitype:unknown
     */
    public function getWishlistItemsInfo($wishlistInfo)
    {        
        $dataItems              = array();
    	try {
    		$wishlistItemCollection = $wishlistInfo->setStoreId(1)->getItemCollection();
    		
    		foreach ($wishlistItemCollection as $id => $wishlistItem) {            
    		    $product        = $wishlistItem->getProduct();
    		    $dataItem       = $this->wishlistProductInfo($product);
    		    $dataItems[$id] = $dataItem;
    		}
        	return $dataItems;
        } catch (Exception $e) {
            $this->debug('Error message '.$e->getMessage());
            return;
        }
    }
    
    /**
     * Collect the wishlist items product info
     *
     * @param unknown $productIds        	
     * @return multitype:unknown
     */    
    public function wishlistProductInfo($product)
    {
    	$dataItem = array();
    	try {
    		$product = Mage::getModel('catalog/product')->load($product->getData('entity_id'));
    		$dataItem['type']         = $product->getTypeId();
    		$dataItem['product_id']   = $product->getId();
    		$dataItem['product_name'] = addslashes($product->getName());
    		$dataItem['price']        = $product->getPrice();
    		$dataItem['special_price'] = $product->getSpecialPrice();
    		$dataItem['productimg']   = $this->getImageUrl($product, 'image');        
    		$dataItem['category']      = $this->getProductCategory($product);
    		$dataItem['category_name'] = $this->getProductCategoryName($product);
    		$dataItem['page_url']      = Mage::getUrl($product->getUrlPath());
    		return $dataItem;
        } catch (Exception $e) {
            $this->debug('Error message '.$e->getMessage());
            return;
        }
        
    }
    
    /**
     *
     * @param unknown $order        	
     * @param unknown $params        	
     * @return unknown|boolean
     */
    public function getFullFillmentData($order, $params)
    {
    	$shipmetItems = array();
    	$shipmentsInfo = array();
    	try {
    		if (isset($params[self::ORDER_SHIPMENT])) {
    		    $shipmentsInfo['order_id']          = $order->getId();
    		    $shipmentsInfo['order_status']      = $order->getStatus();
    		    $shipmentsInfo['total_ordered_qty'] = (int)$order->getData('total_qty_ordered');
    		    $shipmentsInfo['user_id']           = $order->getData('customer_is_guest') ? self::ANONYMOUS_USER : $order->getData('customer_id');
    		    $shipmentsInfo['user_mail']         = $order->getData('customer_is_guest') ? $order->getData('customer_email') : $order->getData('customer_email');
    		    $shipmentsInfo['created_at'] = $order->getData('updated_at');

    		    foreach ($order->getAllVisibleItems() as $item) {
    		        if ($item->getQtyShipped() == '')
    		            continue;
    		        $shipmentItemInfo['product_id']  = $item->getProductId();
    		        $shipmentItemInfo['name']        = addslashes($item->getName());
    		        $shipmentItemInfo['sku']         = $item->getSku();
    		        $shipmentItemInfo['qty_ordered'] = (int)$item->getQtyOrdered();
    		        $shipmentItemInfo['qty_shipped'] = (int)$item->getQtyShipped();
    		        $shipmetItems[]                  = $shipmentItemInfo;
    		    }
    		    $shipmentsInfo['shipment_items'] = $shipmetItems;
    		    return $shipmentsInfo;
    		}
        } catch (Exception $e) {
            $this->debug('Error message '.$e->getMessage());
            return;
        }
    }
    
    /**
     * Stop the page visit for already tracked events
     *
     * @return multitype:string
     */
    public function eventAlreadyTracked($eventName)
    {
        $stopAction = array(
	    'cms_index_index',
            'catalog_category_view',
            'catalog_product_view',
            'catalogsearch_result_index',
            'catalogsearch_ajax_suggest',
            'checkout_cart_add',
            'checkout_cart_index',
            'checkout_cart_updatePost',
            'checkout_cart_remove',
            'customer_account_create',
            'customer_account_loginPost',
            'customer_account_logout',
            'customer_account_logoutSuccess',
            'customer_account_createPost',
            'checkout_onepage_index',
            'onestepcheckout_index_index',
            'firecheckout_index_index',
            'checkout_onepage_saveBilling',
            'checkout_onepage_saveShipping',
            'checkout_onepage_saveShippingMethod',
            'checkout_onepage_savePayment',
            'checkout_onepage_getAdditional',
            'checkout_onepage_progress',
            'checkout_onepage_saveOrder'
        );
        return in_array($eventName, $stopAction);
    }
    
    /**
     * debugging
     *
     * @param unknown $mess        	
     */
    public function debug($mess, $logFile = null)
    {
        if ($this->logEnabled())
            Mage::log($mess, null, $logFile ? $logFile : $this->getLogFileName());
    }
    
    /**
     * debugging
     *
     * @param unknown $mess        	
     */
    public function getProductVersion()
    {
        return (string)Mage::getConfig()->getModuleConfig('Targetbay_Tracking')->version;
    }
    
    /**
     * Visiting page info
     *
     * @param Varien_Event_Observer $observer        	
     *
     * @return void
     */
    public function getPageVisitData()
    {        
        Mage::getModel('tracking/observer')->setCookieValues();
        
        // Set Token Values
        if (isset($_GET['utm_source']) && !$this->cookie->get('utm_source')) {
            $this->cookie->set('utm_source', $_GET['utm_source'], null, null, null, null, false);
        }
        
        if (isset($_GET['token']) && !$this->cookie->get('utm_token')) {
            $this->cookie->set('utm_token', $_GET['token'], null, null, null, null, false);
        }
        
        // Page Visit Tracking
        $data = $this->visitInfo();

        return $data;
    }
    
    /**
     * Category view page
     *
     * @param Varien_Event_Observer $observer        	
     *
     * @return void
     */
    public function getCategoryViewData()
    {        
        $category              = Mage::registry('current_category');
        $data                  = $this->visitInfo();
        $data['category_id']   = $category->getId();
        $data['category_url']  = $category->getUrl();
        $data['category_name'] = $category->getName();

        return $data;
    }
    
    /**
     * Product view page
     *
     * @param Varien_Event_Observer $observer        	
     *
     * @return void
     */
    public function getProductViewData()
    {
    	$data = array();      
            // Get the base visit info
    	try {
    		$data        = $this->visitInfo();
    		$product     = Mage::registry('product');
    		$categoryIds = $product->getCategoryIds();
    		if (count($categoryIds)) {
    		    $firstCategoryId  = $categoryIds[0];
    		    $_category        = Mage::getModel('catalog/category')->load($firstCategoryId);
    		    $data['category'] = $_category->getName();
    		}
    		$data['product_id']   = $product->getId();
    		$data['product_name'] = addslashes($product->getName());
    		$data['price']        = $product->getPrice();
    		$data['special_price'] = $product->getSpecialPrice();
    		$data['productimg']   = $product->getImageUrl();
    		$data['stock']        = self::OUT_OF_STOCK;
    		$stock                = $product->getStockItem();
    		if ($stock->getIsInStock()) {
    		    $data['stock'] = self::IN_STOCK;
    		}
    		return $data;
        } catch (Exception $e) {
            $this->debug('Error message '.$e->getMessage());
            return;
        }
    }
    
    /**
     * Catalog search result page
     *
     * @param Varien_Event_Observer $observer           
     *
     * @return void
     */
    public function getCatalogSearchResultData()
    {
        $data = array(); 

        try {
            $keyword = Mage::app()->getRequest()->getParam('q');
            if (empty($keyword)) {
                return false;
            }
            $data            = $this->visitInfo();
            $data['keyword'] = $keyword;
            return $data;
        } catch (Exception $e) {
            $this->debug('Error message '.$e->getMessage());
            return;
        }
    }
    
    /**
     * Get the page info for static page
     *    	
     * @return string
     */ 
    public function getPageInfo()
    {        
        $controllername = Mage::app()->getRequest()->getControllerName();
	    $moduleName     = Mage::app()->getRequest()->getModuleName();

        if ($moduleName == 'cms') {
            $data = $this->getPageVisitData();
        } elseif ($controllername == 'category') {
            $data = $this->getCategoryViewData();
        } elseif ($controllername == 'product') {
            $data = $this->getProductViewData();
        } elseif ($moduleName == 'catalogsearch') {
            $data = $this->getCatalogSearchResultData();
        }
        $data['index_name'] = $this->getApiIndex();
        
        return $data;
    }
    
    /**
     * Get the api url for static pages
     *    	
     * @return string
     */ 
    public function getApiUrl()
    {        
        $controllername = Mage::app()->getRequest()->getControllerName();
	    $moduleName     = Mage::app()->getRequest()->getModuleName(); 
        $endPointUrl    = '';
        
        if ($moduleName == 'cms') {
            $type        = 'page-visit';
            $endPointUrl = $this->getHostname() . $type . '?api_token=' . $this->getApiToken();
        } elseif ($controllername == 'category') {
            $type        = 'category-view';
            $endPointUrl = $this->getHostname() . $type . '?api_token=' . $this->getApiToken();
        } elseif ($controllername == 'product') {
            $type        = 'product-view';
            $endPointUrl = $this->getHostname() . $type . '?api_token=' . $this->getApiToken();
        } elseif ($moduleName == 'catalogsearch') {
            $type        = 'searched';
            $endPointUrl = $this->getHostname() . $type . '?api_token=' . $this->getApiToken();
        }
        
        return $endPointUrl;
    }
    
    /**
     * Get the quote items
     *
     * @param $items        	
     * @param $orderExportApi        	
     * @return unknown
     */    
    public function getQuoteItems($items, $orderExportApi = false)
    {
        $dataItems = array();
	try {
		foreach ($items as $item) {
		    $dataItem = $this->getItemInfo($item);
		    if ($customOptions = $this->getCustomOptionsInfo($item, $orderExportApi))
		        $dataItem['attributes'] = $customOptions;
		    $dataItems[$item->getId()] = $dataItem;
		}
		return $dataItems;
        } catch (Exception $e) {
	    $this->debug('Error message '.$e->getMessage());
            return;
        }
    }
    
    /**
     * Get the customer address data based on the action
     *
     * @param unknown $addressId 	
     * @return unknown
     */
    public function getCustomerAddressData($addressId)
    {
	$data = array();
	try {
		$address           = Mage::getModel('customer/address');
		$customer          = Mage::getSingleton('customer/session')->getCustomer();
		$data['user_id']   = $customer->getId();
		$data['user_name'] = $customer->getName();
		$data['user_mail'] = $customer->getEmail();
		if ($addressId) {
		    $existsAddress       = $address->load($addressId);
		    $data['address_id']  = $addressId;
		    $data['old_address'] = $this->getExistingAddressData($existsAddress);
		}
		$data['new_address'] = $this->getNewAddressData();
		$data['timestamp']   = strtotime($this->date->date('Y-m-d'));
		$data['ip_address']  = Mage::helper('core/http')->getRemoteAddr();
		$data['user_agent']  = Mage::helper('core/http')->getHttpUserAgent();
		return $data;
        } catch (Exception $e) {
	    $this->debug('Error message '.$e->getMessage());
            return;
        }
    }
    
    /**
     * Get the customer address data based on the action
     *      	
     * @return multitype:unknown
     */    
    public function getNewAddressData()
    {
        $request                   = Mage::app()->getRequest();
        $addressData               = array();
	try {
		$addressData['firstname']  = $request->getParam('firstname');
		$addressData['lastname']   = $request->getParam('lastname');
		$addressData['company']    = $request->getParam('company');
		$addressData['street']     = implode('', $request->getParam('street'));
		$addressData['city']       = $request->getParam('city');
		$addressData['region_id']  = $request->getParam('region_id');
		$addressData['region']     = $request->getParam('region');
		$addressData['postcode']   = $request->getParam('postcode');
		$addressData['country_id'] = $request->getParam('country_id');
		$addressData['telephone']  = $request->getParam('telephone');
		return $addressData;
        } catch (Exception $e) {
	    $this->debug('Error message '.$e->getMessage());
            return;
        }
    }
    
    /**
     * Get the customer existing address data
     *      	
     * @return multitype:unknown
     */ 
    public function getExistingAddressData($existsAddress)
    {
        $addressData               = array();
	try {
		$addressData['firstname']  = $existsAddress->getFirstname();
		$addressData['lastname']   = $existsAddress->getLastname();
		$addressData['company']    = $existsAddress->getCompany();
		$addressData['street']     = $existsAddress->getStreet();
		$addressData['city']       = $existsAddress->getCity();
		$addressData['region_id']  = $existsAddress->getRegionId();
		$addressData['region']     = $existsAddress->getRegion();
		$addressData['postcode']   = $existsAddress->getPostcode();
		$addressData['country_id'] = $existsAddress->getCountryId();
		$addressData['telephone']  = $existsAddress->getTelephone();
		return $addressData;
        } catch (Exception $e) {
	    $this->debug('Error message '.$e->getMessage());
            return;
        }
    }
    
    /**
     * Get the targetbay review count and rating for product
     *      	
     * @return array
     */ 
    public function getRichSnippets()
    { 
        $data = array();
        $responseData = '';       
        $controllername = Mage::app()->getRequest()->getControllerName();
        $moduleName     = Mage::app()->getRequest()->getModuleName();
        $productReviewCount = Mage::getSingleton('core/session')->getProductReview();
        try {
    		$type = self::RATINGS_STATS;
    		$apiToken = '?api_token=' . $this->getApiToken();
    		$feedUrl = $this->getHostname() . $type . $apiToken;
    		$data['index_name'] = $this->getApiIndex();
    		if($moduleName == 'catalog' && $controllername == 'product') {
        		$productId = Mage::registry('product')->getId();
        		$data['product_id'] = $productId;
    		}

            if($productReviewCount < 1 || $productReviewCount == '') {
        		$jsondata = json_encode($data);	
        		$response = $this->postPageInfo($feedUrl, $jsondata);
                $body = json_decode($response);

                if($body->reviews_count > 1) {
                    $_SESSION['last_session'] = time();
                    Mage::getSingleton('core/session')->setProductReview($body->reviews_count);
                    Mage::getSingleton('core/session')->setProductReviewResponse($body);
                }
            }

            $responseBody = Mage::getSingleton('core/session')->getProductReviewResponse();
    		if(!empty($responseBody)) {
    		    $averageScore = $responseBody->reviews_average;
    		    $reviewsCount = $responseBody->reviews_count;
    		    $reviewsDetails = $responseBody->reviews;
    			$responseData = array( "average_score" => $averageScore, "reviews_count" => $reviewsCount, "reviews" => $reviewsDetails);
    			return $responseData;
    		}
        } catch(Exception $e) {
    	    $this->debug('Error message '.$e->getMessage());
    	    return;
        }
    }
    
    /**
     * Get the targetbay review count and dynamic ids
     *      	
     * @return array
     */ 
    public function getTargetbayReviewId()
    {  
        $itemRefData = array();
        $itemRef = '';
        try {
    		$trackingSnippet = $this->getRichSnippets();
    		if($trackingSnippet['reviews_count'] > 0) {
    			foreach($trackingSnippet['reviews'] as $key => $aggregateReviewDetails) {
    				$itemRefData[]= 'tb-review-'.$key;
    			}
    			$itemRef = implode(' ', $itemRefData);
    		}
    		return $itemRef;
        } catch(Exception $e) {
    	    $this->debug('Error message '.$e->getMessage());
    	    return;
        }
    }
    
    /**
     * Get the targetbay review count and rating for product
     *      	
     * @return array
     */ 
    public function getSiteReviewSnippets()
    { 
    	$data = array();
    	$responseData = '';
        $siteReviewCount = Mage::getSingleton('core/session')->getSiteReview();
        try {
    		$type = self::RATINGS_STATS;
    		$apiToken = '?api_token=' . $this->getApiToken();
    		$feedUrl = $this->getHostname() . $type . $apiToken;
    		$data['index_name'] = $this->getApiIndex();

            if($siteReviewCount < 1 || $siteReviewCount == '') {
        		$jsondata = json_encode($data);	
        		$response = $this->postPageInfo($feedUrl, $jsondata);
                $body = json_decode($response);

                if($body->reviews_count > 1) {
                    Mage::getSingleton('core/session')->setSiteReview($body->reviews_count);
                    Mage::getSingleton('core/session')->setSiteReviewResponse($body);
                    $_SESSION['last_session'] = time();
                }
            }

            $responseBody = Mage::getSingleton('core/session')->getSiteReviewResponse();
    		if(!empty($responseBody)) {
    		        $averageScore = $responseBody->reviews_average;
    		        $reviewsCount = $responseBody->reviews_count;
    		        $reviewsDetails = $responseBody->reviews;
    		$responseData = array( "average_score" => $averageScore, "reviews_count" => $reviewsCount, "reviews" => $reviewsDetails);
    			return $responseData;
    		}
        } catch(Exception $e) {
    	    $this->debug('Error message '.$e->getMessage());
    	    return;
        }
    }

    /**
     * Get the targetbay review count and rating for product
     *          
     * @return array
     */ 
    public function getQuestionSnippets()
    { 
        $data = array();
        $responseData = '';        
        $controllername = Mage::app()->getRequest()->getControllerName();
        $moduleName     = Mage::app()->getRequest()->getModuleName();
        $qaReviewCount = Mage::getSingleton('core/session')->getQaReview();
        try {
            $type = self::QUESTION_STATS;
            $apiToken = '?api_token=' . $this->getApiToken();
            $feedUrl = $this->getHostname() . $type . $apiToken;
            $data['index_name'] = $this->getApiIndex();
            if($moduleName == 'catalog' && $controllername == 'product') {
                $productId = Mage::registry('product')->getId();
                $data['product_id'] = $productId;
            }

            if($qaReviewCount < 1 || $qaReviewCount == '') {
                $jsondata = json_encode($data); 
                $response = $this->postPageInfo($feedUrl, $jsondata);
                $body = json_decode($response);

                if($body->qa_count > 1) {
                    Mage::getSingleton('core/session')->setQaReview($body->qa_count);
                    Mage::getSingleton('core/session')->setQaReviewResponse($body);
                    $_SESSION['last_session'] = time();
                }
            }

            $responseBody = Mage::getSingleton('core/session')->getQaReviewResponse();
            if(!empty($responseBody)) {
                $qaCount = $responseBody->qa_count;
                $qaDetails = $responseBody->qas;
                $qaAuthor = $responseBody->client;
                $responseData = array("qa_count" => $qaCount, "qa_details" => $qaDetails, "qa_author" => $qaAuthor);
                return $responseData;
            }
        } catch(Exception $e) {
            $this->debug('Error message '.$e->getMessage());
            return;
        }
    }
}
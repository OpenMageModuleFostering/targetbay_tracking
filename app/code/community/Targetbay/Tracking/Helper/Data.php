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

    const ANONYMOUS_USER = 'anonymous';
    // page types
    const ALL_PAGES = 'all';
    const PAGE_VISIT = 'page-visit';
    const PRODUCT_VIEW = 'product-view';
    const CATEGORY_VIEW = 'category-view';
    const DELETE_PRODUCT = "delete-product";
    const UPDATE_PRODUCT = 'update-product';
    const ADD_PRODUCT = 'add-product';
    const CREATE_ACCOUNT = 'create-account';
    const ADMIN_ACTIVATE_ACCOUNT = 'admin-activate-customer-account';
    const LOGIN = 'login';
    const LOGOUT = 'logout';
    const ADDTOCART = 'add-to-cart';
    const REMOVECART = 'remove-to-cart';
    const UPDATECART = 'update-cart';
    const ORDER_ITEMS = 'ordered-items';
    const BILLING = 'billing';
    const SHIPPING = 'shipping';
    const PAGE_REFERRAL = 'referrer';
    const CHECKOUT = 'checkout';
    const CATALOG_SEARCH = 'searched';
    const WISHLIST = 'wishlist';
    const UPDATE_WISHLIST = 'update-wishlist';
    const REMOVE_WISHLIST = 'remove-wishlist';
    const ONESTEPCHECKOUT_ADDRESS = 'onestepcheckout';
    const CART_INDEX = 'checkout-cart';
    const SUBSCRIBE_CUSTOMER = 'user-subscribe';
    const CUSTOMER_ADDRESS = 'change-user-address';
    const CUSTOMER_ACCOUNT = 'change-user-account-info';
    const CREATE_ADDRESS = 'new';
    const UPDATE_ADDRESS = 'edit';
    const RATINGS_STATS = 'ratings-stats';
    const QUESTION_STATS = 'qa-stats';

    // subscription status
    const STATUS_SUBSCRIBED = 1;
    const STATUS_NOT_ACTIVE = 2;
    const STATUS_UNSUBSCRIBED = 3;
    const STATUS_UNCONFIRMED = 4;

    // product stock status
    const IN_STOCK = 'in-stock';
    const OUT_OF_STOCK = 'out-stock';

    // order fullfillment process
    const ORDER_SHIPMENT = 'shipment';
    const ORDER_INVOICE = 'invoice';
    const ORDER_REFUND = 'creditmemo';
    const ORDER_STATUS = 'order-status';

    const HOST_STAGE = 'https://stage.targetbay.com/api/v1/webhooks/';
    const HOST_LIVE = 'https://app.targetbay.com/api/v1/webhooks/';
    const HOST_DEV = 'https://dev.targetbay.com/api/v1/webhooks/';

    const API_STAGE = 'stage';
    const API_LIVE = 'app';
    const API_DEV = 'dev';
    const DEFAULT_TIMEOUT = 30;

    /**
     * Initialize object
     */
    public function __construct()
    {
        $this->cookie = Mage::getModel('core/cookie');
        $this->date = Mage::getModel('core/date');
    }

    /**
     * Check module is enabled or not
     *
     * @return boolean
     */
    public function trackingEnabled()
    {
        return (bool) Mage::getStoreConfig('targetbay_tracking/tracking_groups/enabled');
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
        return Mage::getStoreConfig('targetbay_tracking/tracking_groups/api_status');
    }

    /**
     * Get the Session Tracking Js
     *
     * @return mixed
     */
    public function getReviewPageSize()
    {
        $reviewSize = Mage::getStoreConfig('targetbay_tracking/tracking_groups/reviews_per_page');
        if ($reviewSize) {
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
        if ($emailStatus) {
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
     * Get TargetBay richsnippet type
     *
     * @return mixed
     */
    public function getRichsnippetType()
    {
        return Mage::getStoreConfig('targetbay_tracking/tracking_groups/richsnippets_type');
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
     * Get the available pages from configurations
     *
     * @return array
     */
    public function availablePageTypes()
    {
        $types = (string) Mage::getStoreConfig('targetbay_tracking/tracking_groups/page_types');
        //$typesArray = explode(',', $types);

        return explode(',', $types);
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
     * @param $pageType
     * @return boolean
     */
    public function canTrackPages($pageType)
    {
        if (!$this->trackingEnabled()) {
            $this->debug('Tracking Module is not enabled. Please enable a Module');

            return false;
        }
        $availablePages = $this->availablePageTypes();
        if (in_array(self::ALL_PAGES, $availablePages, true)) {
            return true;
        }
        if (!in_array($pageType, $availablePages, true)) {
            $this->debug("'$pageType'" . 'page is not enabled.');

            return false;
        }

        return true;
    }

    /**
     * Get the customer data based on the action
     *
     * @param $customer
     * @param $action
     * @return array
     */
    public function getCustomerData($customer, $action)
    {
        try {
            switch ($action) {
                case self::LOGIN:
                    $data = $this->getCustomerSessionId($customer);
                    $data['login_date'] = $this->date->date('Y-m-d');
                    break;
                case self::LOGOUT:
                    $data['session_id'] = Mage::getSingleton('core/session')->getCustomerSessionId();
                    $data['logout_date'] = $this->date->date('Y-m-d');
                    Mage::getSingleton('core/session')->unsTrackingSessionId();
                    Mage::getSingleton('core/session')->unsCustomerSessionId();
                    break;
                case self::CREATE_ACCOUNT:
                    $data = $this->getCustomerSessionId($customer);
                    $data['firstname'] = $customer->getFirstname();
                    $data['lastname'] = $customer->getLastname();
                    $data['subscription_status'] = $this->getSubscriptionStatus($customer->getId());
                    $data['account_created'] = $this->date->date('Y-m-d');
                    break;
            }
            $data['user_id'] = $customer->getId();
            $data['user_name'] = $customer->getName();
            $data['user_mail'] = $customer->getEmail();
            $data['timestamp'] = strtotime($this->date->date('Y-m-d'));
            $data['ip_address'] = Mage::helper('core/http')->getRemoteAddr();

            return $data;
        } catch (Exception $e) {
            $this->debug('Error message:' . $e->getMessage());

            return;
        }
    }

    /**
     * Get the customer subscription status
     *
     * @param $customerId
     * @return string
     */
    public function getSubscriptionStatus($customerId)
    {
        try {
            $customerData = Mage::getModel('customer/customer')->load($customerId);
            $subscriber = Mage::getModel('newsletter/subscriber')->loadByCustomer($customerData);
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
            $this->debug('Error message:' . $e->getMessage());
            return;
        }
    }

    /**
     * Get the customer session info
     *
     * @param $customer
     * @return array
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
                $data['previous_session_id'] = $this->cookie->get('targetbay_session_id'); else:
                $data['previous_session_id'] = $this->cookie->get('trackingsession');
            endif;

            Mage::getSingleton('core/session')->setCustomerSessionId($session);
        } catch (Exception $e) {
            $this->debug('Error message:' . $e->getMessage());
        }

        return $data;
    }

    /**
     * Check the order placed by registered user or not
     *
     * @param $quoteId
     * @return boolean|Mage_Core_Model_Abstract
     */
    public function isRegisterCheckout($order)
    {
        $checkoutMethod = Mage::getModel('sales/quote')->load($order->getQuoteId())->getCheckoutMethod(true);
        if ($checkoutMethod !== 'register') {
            return false;
        }
        return Mage::getModel('customer/customer')->load($order->getCustomerId());
    }

    /**
     * Basic visit info
     *
     * @return array
     */
    public function visitInfo()
    {
        $data = array();
        try {
            $customer = Mage::getSingleton('customer/session');
            $data['user_name'] = $customer->isLoggedIn() ? $customer->getCustomer()->getName() : self::ANONYMOUS_USER;
            $data['user_email'] = $customer->isLoggedIn() ? $customer->getCustomer()->getEmail() : self::ANONYMOUS_USER;
            $trackingType = $this->getTrackingType();
            $moduleName = Mage::app()->getRequest()->getModuleName();

            if (($moduleName === 'catalogsearch') && ($customer->isLoggedIn())) {
                $user_id = $customer->getCustomer()->getId();
            } else {
                $user_id = $customer->isLoggedIn() ? $customer->getCustomer()->getId() : $this->cookie->get('targetbay_session_id');
            }

            if ($trackingType == 1) {
                $data['already_tracked'] = true;
                $data['user_id'] = $user_id;
                $data['session_id'] = $customer->isLoggedIn() ? Mage::getSingleton('core/session')->getCustomerSessionId() : $this->cookie->get('targetbay_session_id');
            } else {
                if (!$this->cookie->get('trackingsession')) {
                    $userId = Mage::getSingleton('core/session')->getTrackingSessionId();
                } else {
                    $userId = $this->cookie->get('trackingsession');
                }
                $data['user_id'] = $customer->isLoggedIn() ? $customer->getCustomer()->getId() : $userId;
                $data['session_id'] = $customer->isLoggedIn() ? Mage::getSingleton('core/session')->getCustomerSessionId() : Mage::getSingleton('core/session')->getTrackingSessionId();
            }

            $data['page_url'] = Mage::helper('core/url')->getCurrentUrl();
            $data['ip_address'] = Mage::helper('core/http')->getRemoteAddr();
            $data['user_agent'] = Mage::helper('core/http')->getHttpUserAgent();
            $data['utm_sources'] = $this->cookie->get('utm_source') ? $this->cookie->get('utm_source') : '';
            $data['utm_token'] = $this->cookie->get('utm_token') ? $this->cookie->get('utm_token') : '';
            $pageTitle = Mage::app()->getLayout()->getBlock('head') ? Mage::app()->getLayout()->getBlock('head')->getTitle() : Mage::getSingleton('checkout/session')->getTitle();
            $quotes = array("'", '"');
            $replace = array('', '');
            $data['page_title'] = str_replace($quotes, $replace, $pageTitle);
        } catch (Exception $e) {
            $this->debug('Error message:' . $e->getMessage());
        }

        return $data;
    }

    /**
     * Push the referer url
     *
     * @return boolean
     */
    public function getRefererData()
    {
        try {
            $domainName = $_SERVER['SERVER_NAME'];
            $referer = Mage::helper('core/http')->getHttpReferer();

            if ($referer == '' || strpos($referer, $domainName) !== false) {
                return false; // base url and referer url matches.
            }

            $data = $this->visitInfo();
            $data['referrer_url'] = $referer;

            return $data;
        } catch (Exception $e) {
            $this->debug('Error message:' . $e->getMessage());

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
            $response = $client->request(Varien_Http_Client::POST)->getBody();
            return $response;
        } catch (Exception $e) {
            $this->debug('Error message ' . $e->getMessage());

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
     * @return array
     */
    public function getCartInfo()
    {
        $data = array();
        try {
            $customer = Mage::getSingleton('customer/session');
            //$checkout = Mage::getSingleton('checkout/session')->getQuote();
            $trackingType = $this->getTrackingType();
            if ($trackingType == 1):
                $data['user_id'] = $customer->isLoggedIn() ? $customer->getCustomer()->getId() : $this->cookie->get('targetbay_session_id');
            $data['session_id'] = $customer->isLoggedIn() ? Mage::getSingleton('core/session')->getCustomerSessionId() : $this->cookie->get('targetbay_session_id'); else:
                $data['user_id'] = $customer->isLoggedIn() ? $customer->getId() : Mage::getSingleton('core/session')->getTrackingSessionId();
            $data['session_id'] = $customer->isLoggedIn() ? Mage::getSingleton('core/session')->getCustomerSessionId() : Mage::getSingleton('core/session')->getTrackingSessionId();
            endif;

            $data['user_name'] = $customer->isLoggedIn() ? $customer->getCustomer()->getName() : Targetbay_Tracking_Helper_Data::ANONYMOUS_USER;
            $data['user_mail'] = $customer->isLoggedIn() ? $customer->getCustomer()->getEmail() : Targetbay_Tracking_Helper_Data::ANONYMOUS_USER;

            $data['order_id'] = Mage::getSingleton('checkout/session')->getQuoteId();
            $data['utm_sources'] = $this->cookie->get('utm_source') ? $this->cookie->get('utm_source') : '';
            $data['utm_token'] = $this->cookie->get('utm_token') ? $this->cookie->get('utm_token') : '';
            $data['timestamp'] = strtotime($this->date->date('Y-m-d'));
            $pageTitle = Mage::app()->getLayout()->getBlock('head') ? Mage::app()->getLayout()->getBlock('head')->getTitle() : Mage::getSingleton('checkout/session')->getTitle();
            $data['page_title'] = addslashes($pageTitle);
        } catch (Exception $e) {
            $this->debug('Error message ' . $e->getMessage());
        }

        return $data;
    }

    /**
     * Get the common info for quote or order, billing and shipping
     *
     * @param $object
     * @return array
     */
    public function getSessionInfo($object)
    {
        $data = array();
        try {
            $trackingType = $this->getTrackingType();
            if ($trackingType == 1):
                $data['user_id'] = $object->getCustomerId() ? $object->getCustomerId() : $this->cookie->get('targetbay_session_id');
            $data['session_id'] = Mage::getSingleton('customer/session')->isLoggedIn() ? Mage::getSingleton('core/session')->getCustomerSessionId() : $this->cookie->get('targetbay_session_id'); else:
                $data['user_id'] = $object->getCustomerId() ? $object->getCustomerId() : Mage::getSingleton('core/session')->getTrackingSessionId();
            $data['session_id'] = Mage::getSingleton('customer/session')->isLoggedIn() ? Mage::getSingleton('core/session')->getCustomerSessionId() : Mage::getSingleton('core/session')->getTrackingSessionId();
            endif;

            $data['utm_sources'] = $this->cookie->get('utm_source') ? $this->cookie->get('utm_source') : '';
            $data['utm_token'] = $this->cookie->get('utm_token') ? $this->cookie->get('utm_token') : '';
            $data['timestamp'] = strtotime($this->date->date('Y-m-d'));
            $pageTitle = Mage::app()->getLayout()->getBlock('head') ? Mage::app()->getLayout()->getBlock('head')->getTitle() : Mage::getSingleton('checkout/session')->getTitle();
            $data['page_title'] = addslashes($pageTitle);
        } catch (Exception $e) {
            $this->debug('Error message ' . $e->getMessage());
        }

        return $data;
    }

    /**
     * Get the Order data
     *
     * @param $object
     * @return array
     */
    public function getInfo($object)
    {
        try {
            //$customer = $object->getCustomer();
            //$items = $object->getAllVisibleItems();

            $data = $this->getSessionInfo($object);
            $data['first_name'] = $object->getCustomerFirstname();
            $data['last_name'] = $object->getCustomerLastname();
            $guestUsername = $object->getCustomerFirstname() . ' ' . $object->getCustomerLastname();
            $gName = !empty($guestUsername) ? $guestUsername : self::ANONYMOUS_USER;
            $data['user_name'] = $object->getCustomerIsGuest() ? $gName : $data['first_name'] . ' ' . $data['last_name'];
            $data['user_mail'] = $object->getCustomerEmail();
            $data['order_id'] = $object->getId();
            $data['order_price'] = $object->getSubtotal();
            $data['order_quantity'] = $object->getData('total_qty_ordered');
            $data['shipping_method'] = $object->getData('shipping_description');
            $data['shipping_price'] = $object->getData('shipping_amount');
            $data['tax_amount'] = $object->getData('tax_amount');
            $data['payment_method'] = $object->getPayment()->getMethodInstance()->getTitle();
            $data['cart_items'] = $this->getOrderItemsInfo($object);
        } catch (Exception $e) {
            $this->debug('Error message ' . $e->getMessage());
        }

        return $data;
    }

    /**
     * Get the Order data
     *
     * @param $object
     * @return array
     */
    public function getOrderInfo($object)
    {
        $data = array();
        try {
            //$customer = $object->getCustomer();
            //$items = $object->getAllVisibleItems();

            $data['user_id'] = $object->getCustomerIsGuest() ? self::ANONYMOUS_USER : $object->getCustomerId();
            $data['first_name'] = $object->getCustomerFirstname();
            $data['last_name'] = $object->getCustomerLastname();
            $guestUsername = $object->getCustomerFirstname() . ' ' . $object->getCustomerLastname();
            $gName = !empty($guestUsername) ? $guestUsername : self::ANONYMOUS_USER;
            $data['user_name'] = $object->getCustomerIsGuest() ? $gName : $data['first_name'] . ' ' . $data['last_name'];
            $data['user_mail'] = $object->getCustomerEmail();
            $data['order_id'] = $object->getId();
            $data['order_status'] = $object->getStatus();
            $data['order_price'] = $object->getSubtotal();
            $data['order_quantity'] = $object->getData('total_qty_ordered');
            $data['shipping_method'] = $object->getData('shipping_description');
            $data['shipping_price'] = $object->getData('shipping_amount');
            $data['tax_amount'] = $object->getData('tax_amount');
            $data['payment_method'] = $object->getPayment()->getMethodInstance()->getTitle();
            $data['cart_items'] = $this->getOrderItemsInfo($object);
        } catch (Exception $e) {
            $this->debug('Error message ' . $e->getMessage());
        }

        return $data;
    }

    /**
     * Collect the order items info
     *
     * @param $order
     * @param $orderExportApi
     * @return array
     */
    public function getOrderItemsInfo($order, $orderExportApi = false)
    {
        $dataItems = array();
        try {
            $items = $order->getAllVisibleItems();
            foreach ($items as $orderItem) {
                $dataItem = $this->getItemInfo($orderItem);
                if ($customOptions = $this->getCustomOptionsInfo($orderItem, $orderExportApi)) {
                    $dataItem['attributes'] = $customOptions;
                }
                $dataItems[] = $dataItem;
            }
        } catch (Exception $e) {
            $this->debug('Error message ' . $e->getMessage());
        }

        return $dataItems;
    }

    /**
     * Collect the order payment info.
     *
     * @param $orderId
     * @param $orderExportApi
     * @return string
     */
    public function getPaymentInfo($orderId, $orderExportApi = false)
    {
        $paymentTitle = '';
        $paymentMethod = '';
        try {
            $paymentCollection = Mage::getModel('sales/order_payment')->getCollection()->addFieldToFilter('parent_id', $orderId);
            foreach ($paymentCollection as $paymentDetails) {
                $paymentMethod = $paymentDetails->getMethod();
            }

            $store = Mage::app()->getStore();
            $path = 'payment/' . $paymentMethod . '/title';
            $paymentTitle = Mage::getStoreConfig($path, $store);
        } catch (Exception $e) {
            $this->debug('Error message ' . $e->getMessage());
        }

        return $paymentTitle;
    }

    /**
     * Get the item info
     *
     * @param $item
     * @param $actionType
     * @return array
     */
    public function getItemInfo($item, $actionType = false)
    {
        $dataItem = array();
        try {
            $product = Mage::getModel('catalog/product')->load($item->getData('product_id'));
            $dataItem['type'] = $item->getProductType();
            $dataItem['product_id'] = $item->getProductId();
            $dataItem['product_sku'] = $item->getSku();
            $dataItem['product_name'] = addslashes($item->getName());
            $dataItem['price'] = $actionType ? $item->getProduct()->getPrice() : $item->getPrice();
            $dataItem['special_price'] = $product->getSpecialPrice();
            $qty = $actionType ? $item->getProduct()->getQty() : $item->getQty();
            $dataItem['productimg'] = $this->getImageUrl($product, 'image');

            $dataItem['category'] = $this->getProductCategory($product);
            $dataItem['category_name'] = $this->getProductCategoryName($product);
            $dataItem['quantity'] = ($item->getData('qty_ordered')) ? $item->getData('qty_ordered') : $qty;
            $dataItem['page_url'] = Mage::getUrl($product->getUrlPath());
        } catch (Exception $e) {
            $this->debug('Error message ' . $e->getMessage());
        }

        return $dataItem;
    }

    /**
     * Get the product categories
     *
     * @param $product
     * @return string
     */
    public function getProductCategoryName($product)
    {
        $categoryIds = $product->getCategoryIds();
        $productCategories = array();
        $categoryName = '';
        $quotes = array("'", '"');
        $replace = array('', '');

        if (count($categoryIds)) {
            foreach ($categoryIds as $categoryId) {
                $_category = Mage::getModel('catalog/category')->load($categoryId);
                $productCategories[] = str_replace($quotes, $replace, $_category->getName());
            }

            $categoryName = implode(',', $productCategories);
        }

        return $categoryName;
    }

    /**
     * Get the product categories
     *
     * @param $product
     * @return string
     */
    public function getProductCategory($product)
    {
        $categoryIds = $product->getCategoryIds();
        //$productCategories = [];
        $categoryId = '';
        if (count($categoryIds)) {
            //foreach ($categoryIds as $categoryId) {
            //    $_category = Mage::getModel('catalog/category')->load($categoryId);
            //    $productCategories[] = $_category->getName();
            //}

            $categoryId = implode(',', $categoryIds);
        }

        return $categoryId;
    }

    /**
     * Get the item custom options
     *
     * @param $item
     * @param $orderExportApi
     * @return array|bool
     */
    public function getCustomOptionsInfo($item, $orderExportApi)
    {
        $optionsData = array();
        try {
            $customOptions = $orderExportApi ? $item->getProductOptions() : $item->getProduct()->getTypeInstance(true)->getOrderOptions($item->getProduct());

            if (empty($customOptions['options']) && empty($customOptions['attributes_info'])) {
                return false;
            }
            $superAttributeInfo = isset($customOptions['attributes_info']) ? $this->getOptionValues($customOptions['attributes_info']) : array();
            $customOptionInfo = isset($customOptions['options']) ? $this->getOptionValues($customOptions['options']) : array();
            $optionsData = array_merge($superAttributeInfo, $customOptionInfo);
        } catch (Exception $e) {
            $this->debug('Error message ' . $e->getMessage());
        }

        return $optionsData;
    }

    /**
     * Get the options values
     *
     * @param $options
     * @return array
     */
    public function getOptionValues($options)
    {
        $optionData = array();
        try {
            foreach ($options as $option) {
                $data['label'] = $option['label'];
                $data['value'] = $option['value'];
                $optionData[] = $data;
            }
        } catch (Exception $e) {
            $this->debug('Error message ' . $e->getMessage());
        }

        return $optionData;
    }

    /**
     * Get the order or quote address by address type.
     *
     * @param $object
     * @param $type
     * @return array
     */
    public function getAddressData($object, $type)
    {
        try {
            $address = ($type == self::SHIPPING) ? $object->getShippingAddress() : $object->getBillingAddress();
            $addressData = $this->getSessionInfo($object);
            $addressData['first_name'] = $address->getFirstname();
            $addressData['last_name'] = $address->getLastname();
            $guestUsername = $address->getFirstname() . ' ' . $address->getLastname();
            $gName = !empty($guestUsername) ? $guestUsername : self::ANONYMOUS_USER;
            $addressData['user_name'] = $object->getCustomerIsGuest() ? $gName : $addressData['first_name'] . ' ' . $addressData['last_name'];
            $addressData['order_id'] = $object->getId();
            $addressData['user_mail'] = $object->getCustomerEmail();
            $addressData['address1'] = $address->getStreet(1);
            $addressData['address2'] = $address->getStreet(2);
            $addressData['city'] = $address->getCity();
            $addressData['state'] = $address->getRegion();
            $addressData['zipcode'] = $address->getPostcode();

            $countryName = '';
            if ($address->getCountryId()) {
                $countryName = Mage::getModel('directory/country')->loadByCode($address->getCountryId())->getName();
            }

            $addressData['country'] = $countryName !== '' ? $countryName : $address->getCountryId();
            $addressData['phone'] = $address->getTelephone();
        } catch (Exception $e) {
            $this->debug('Error message ' . $e->getMessage());
        }

        return $addressData;
    }

    /**
     * Get the product info
     *
     * @param $product
     * @return array
     */
    public function getProductData($product)
    {
        $configOptions = array();
        $customOptions = array();
        $data = array();
        try {
            $data['image_url'][] = $this->getProductImages($product);
            $data['entity_id'] = $product->getId();
            $data['attribute_set_id'] = $product->getEntityTypeId();
            $data['type_id'] = $product->getTypeId();
            $data['sku'] = $product->getSku();
            $data['product_status'] = $product->getStatus();
            $data['currency_type'] = Mage::app()->getStore()->getCurrentCurrencyCode();

            $stockItem = Mage::getModel('cataloginventory/stock_item');
            $stockItem->loadByProduct($product);
            $data['stock_count'] = ($stockItem->getQty() > 0) ? $stockItem->getQty() : $stockItem->getMaxSaleQty();

            $data['visibility'] = $product->getVisibility();
            $data['description'] = $product->getDescription();
            $data['price'] = $product->getPrice();
            $data['special_price'] = $product->getSpecialPrice();
            $data['weight'] = $product->getWeight();
            $data['name'] = addslashes($product->getName());

            $data['category'] = $this->getProductCategory($product);

            // Get product url key
            if($product->getUrlKey() != '') {
                $urlKey = $product->getUrlKey();
            } else {
                $urlKey = $product->getProductUrl();
            }
            $data['url_key'] = $urlKey;
            $data['full_url_key'] = $product->getProductUrl();

            if ($configData = $product->getData('configurable_attributes_data')) {
                $configOptions = $this->productOptions($configData, 'label');
            }
            if ($custOptions = $product->getData('product_options')) {
                $customOptions = $this->productOptions($custOptions);
            }
            $options = array_merge($configOptions, $customOptions);
            if (!empty($options)) {
                $data['attributes'] = $options;
            }
        } catch (Exception $e) {
            $this->debug('Error message ' . $e->getMessage());
        }

        return $data;
    }

    /**
     * Get the product options when saving product
     *
     * @param $configData
     * @param string $customOption
     * @return array
     */
    public function productOptions($configData, $customOption = 'title')
    {
        $options = array();
        try {
            foreach ($configData as $cdata) {
                $attrLabel = $cdata[$customOption];
                if (!isset($cdata['values'])) {
                    $options[] = array(
                        'label' => $attrLabel,
                        'value' => $attrLabel
                    );
                    continue;
                }
                foreach ($cdata['values'] as $val) {
                    $attrVal = $val[$customOption];
                    $options[] = array(
                        'label' => $attrLabel,
                        'value' => $attrVal
                    );
                }
            }
        } catch (Exception $e) {
            $this->debug('Error message ' . $e->getMessage());
        }

        return $options;
    }

    /**
     * Get the product images
     *
     * @param $product
     * @return string
     */
    public function getProductImages($product)
    {
        $images = array();
        try {
            $largeImage = $this->getImageUrl($product, 'image');
            $smallImage = $this->getImageUrl($product, 'small_image');
            $thumbImage = $this->getImageUrl($product, 'thumbnail');
            if (!empty($largeImage)) {
                $images['url'] = $this->getImageUrl($product, 'image');
            }
            if (!empty($smallImage)) {
                $images['thumbnail_image'] = $this->getImageUrl($product, 'small_image');
            }
            if (!empty($smallImage)) {
                $images['medium_image'] = $this->getImageUrl($product, 'thumbnail');
            }
            $images['position'] = 1;
            return $images;
        } catch (Exception $e) {
            $this->debug('Error message ' . $e->getMessage());
            return;
        }
    }

    /**
     * Get the Product image url
     *
     * @param $product
     * @param $imageType
     * @return string
     */
    public function getImageUrl($product, $imageType)
    {
        $imgPath = '';
        if ($product->getData($imageType)) {
            $productImage = $product->getData($imageType);
            if (!empty($productImage) && $productImage != 'no_selection') {
                $imgPath = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'catalog/product' . $product->getData($imageType);
            }
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
        if (isset($params[self::ORDER_SHIPMENT]) ||
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
     * @param $productId
     * @return string
     */
    public function getWishlistProductInfo($productId)
    {
        $data = array();
        try {
            $product = Mage::getModel('catalog/product')->load($productId);
            $qty = 1;

            $data['type'] = $product->getTypeId();
            $data['product_id'] = $product->getId();
            $data['product_sku'] = $product->getSku();
            $data['name'] = addslashes($product->getName());
            $data['price'] = $product->getPrice();
            $data['special_price'] = $product->getSpecialPrice();
            $data['productimg'] = $this->getImageUrl($product, 'image');

            $data['category'] = $this->getProductCategory($product);
            $data['category_name'] = $this->getProductCategoryName($product);
            $data['quantity'] = (Mage::app()->getRequest()->getParam('qty')) ? Mage::app()->getRequest()->getParam('qty') : $qty;
            $data['page_url'] = Mage::getUrl($product->getUrlPath());
        } catch (Exception $e) {
            $this->debug('Error message ' . $e->getMessage());
        }

        return $data;
    }

    /**
     * Collect the wishlist items info
     *
     * @param $productIds
     * @return multitype:unknown
     */
    public function getWishlistItemsInfo($wishlistInfo)
    {
        $dataItems = array();
        try {
            $wishlistItemCollection = $wishlistInfo->setStoreId(1)->getItemCollection();

            foreach ($wishlistItemCollection as $id => $wishlistItem) {
                $product = $wishlistItem->getProduct();
                $dataItem = $this->wishlistProductInfo($product);
                $dataItems[$id] = $dataItem;
            }
        } catch (Exception $e) {
            $this->debug('Error message ' . $e->getMessage());
        }

        return $dataItems;
    }

    /**
     * Collect the wishlist items product info
     *
     * @param $product
     * @return array
     */
    public function wishlistProductInfo($product)
    {
        $dataItem = array();
        try {
            $product = Mage::getModel('catalog/product')->load($product->getData('entity_id'));
            $dataItem['type'] = $product->getTypeId();
            $dataItem['product_id'] = $product->getId();
            $dataItem['product_name'] = addslashes($product->getName());
            $dataItem['price'] = $product->getPrice();
            $dataItem['special_price'] = $product->getSpecialPrice();
            $dataItem['productimg'] = $this->getImageUrl($product, 'image');
            $dataItem['category'] = $this->getProductCategory($product);
            $dataItem['category_name'] = $this->getProductCategoryName($product);
            $dataItem['page_url'] = Mage::getUrl($product->getUrlPath());
        } catch (Exception $e) {
            $this->debug('Error message ' . $e->getMessage());
        }

        return $dataItem;
    }

    /**
     *
     * @param $order
     * @param $params
     * @return array
     */
    public function getFullFillmentData($order, $params)
    {
        $shipmentItems = array();
        $shipmentsInfo = array();
        try {
            if (isset($params[self::ORDER_SHIPMENT])) {
                $shipmentsInfo['order_id'] = $order->getId();
                $shipmentsInfo['order_status'] = $order->getStatus();
                $shipmentsInfo['total_ordered_qty'] = (int) $order->getData('total_qty_ordered');
                $shipmentsInfo['user_id'] = $order->getData('customer_is_guest') ? self::ANONYMOUS_USER : $order->getData('customer_id');
                $shipmentsInfo['user_mail'] = $order->getData('customer_is_guest') ? $order->getData('customer_email') : $order->getData('customer_email');
                $shipmentsInfo['created_at'] = $order->getData('updated_at');

                foreach ($order->getAllVisibleItems() as $item) {
                    if ($item->getQtyShipped() == '') {
                        continue;
                    }
                    $shipmentItemInfo['product_id'] = $item->getProductId();
                    $shipmentItemInfo['name'] = addslashes($item->getName());
                    $shipmentItemInfo['sku'] = $item->getSku();
                    $shipmentItemInfo['qty_ordered'] = (int) $item->getQtyOrdered();
                    $shipmentItemInfo['qty_shipped'] = (int) $item->getQtyShipped();
                    $shipmentItems[] = $shipmentItemInfo;
                }
                $shipmentsInfo['shipment_items'] = $shipmentItems;
            }
        } catch (Exception $e) {
            $this->debug('Error message ' . $e->getMessage());
        }

        return $shipmentsInfo;
    }

    /**
     * Stop the page visit for already tracked events.
     *
     * @param $eventName
     * @return bool
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

        return in_array($eventName, $stopAction, true);
    }

    /**
     * debugging
     *
     * @param $mess
     */
    public function debug($mess, $logFile = null)
    {
        if ($this->logEnabled()) {
            Mage::log($mess, null, $logFile ? $logFile : $this->getLogFileName());
        }
    }

    /**
     * debugging
     *
     * @return string
     */
    public function getProductVersion()
    {
        return (string) Mage::getConfig()->getModuleConfig('Targetbay_Tracking')->version;
    }

    /**
     * Visiting page info
     *
     * @return array
     */
    public function getPageVisitData()
    {
        Mage::getModel('tracking/observer')->setCookieValues();

        // Set Token Values.
        if (isset($_GET['utm_source']) && !$this->cookie->get('utm_source')) {
            $this->cookie->set('utm_source', $_GET['utm_source'], null, null, null, null, false);
        }

        if (isset($_GET['token']) && !$this->cookie->get('utm_token')) {
            $this->cookie->set('utm_token', $_GET['token'], null, null, null, null, false);
        }

        // Page Visit Tracking.
        return $this->visitInfo();
    }

    /**
     * Category view page
     *
     * @return array
     */
    public function getCategoryViewData()
    {
        $category = Mage::registry('current_category');
        $data = $this->visitInfo();
        $quotes = array("'", '"');
        $replace = array('', '');
        $data['category_id'] = $category->getId();
        $data['category_url'] = $category->getUrl();
        $data['category_name'] = str_replace($quotes, $replace, $category->getName());

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

        // Get the base visit info.
        try {
            $data = $this->visitInfo();
            $product = Mage::registry('product');
            $categoryIds = $product->getCategoryIds();
            $quotes = array("'", '"');
            $replace = array('', '');
            if (count($categoryIds)) {
                $firstCategoryId = $categoryIds[0];
                $_category = Mage::getModel('catalog/category')->load($firstCategoryId);
                $data['category'] = str_replace($quotes, $replace, $_category->getName());
            }
            $data['product_id'] = $product->getId();
            $data['product_name'] = str_replace($quotes, $replace, $product->getName());
            $data['price'] = $product->getPrice();
            $data['special_price'] = $product->getSpecialPrice();
            $data['productimg'] = $product->getImageUrl();
            $data['stock'] = self::OUT_OF_STOCK;
            $stock = $product->getStockItem();
            if ($stock->getIsInStock()) {
                $data['stock'] = self::IN_STOCK;
            }
        } catch (Exception $e) {
            $this->debug('Error message ' . $e->getMessage());
        }

        return $data;
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
            $quotes = array("'", '"');
            $replace = array('', '');
            $keyword = Mage::app()->getRequest()->getParam('q');
            if (empty($keyword)) {
                return false;
            }
            $data = $this->visitInfo();
            $data['keyword'] = str_replace($quotes, $replace, $keyword);
        } catch (Exception $e) {
            $this->debug('Error message ' . $e->getMessage());
        }

        return $data;
    }

    /**
     * Get the page info for static page
     *
     * @return string
     */
    public function getPageInfo()
    {
        $controllerName = Mage::app()->getRequest()->getControllerName();
        $moduleName = Mage::app()->getRequest()->getModuleName();

        if ($moduleName === 'cms') {
            $data = $this->getPageVisitData();
        } elseif ($controllerName === 'category') {
            $data = $this->getCategoryViewData();
        } elseif ($controllerName === 'product') {
            $data = $this->getProductViewData();
        } elseif ($moduleName === 'catalogsearch') {
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
        $controllerName = Mage::app()->getRequest()->getControllerName();
        $moduleName = Mage::app()->getRequest()->getModuleName();

        $type = '';
        if ($moduleName === 'cms') {
            $type = 'page-visit';
        } elseif ($controllerName === 'category') {
            $type = 'category-view';
        } elseif ($controllerName === 'product') {
            $type = 'product-view';
        } elseif ($moduleName === 'catalogsearch') {
            $type = 'searched';
        }

        return $type !== '' ? $this->getHostname() . $type . '?api_token=' . $this->getApiToken() : '';
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
                if ($customOptions = $this->getCustomOptionsInfo($item, $orderExportApi)) {
                    $dataItem['attributes'] = $customOptions;
                }
                $dataItems[$item->getId()] = $dataItem;
            }
        } catch (Exception $e) {
            $this->debug('Error message ' . $e->getMessage());
        }

        return $dataItems;
    }

    /**
     * Get the customer address data based on the action
     *
     * @param $addressId
     * @return array
     */
    public function getCustomerAddressData($addressId)
    {
        $data = array();
        try {
            $address = Mage::getModel('customer/address');
            $customer = Mage::getSingleton('customer/session')->getCustomer();
            $data['user_id'] = $customer->getId();
            $data['user_name'] = $customer->getName();
            $data['user_mail'] = $customer->getEmail();
            if ($addressId) {
                $existsAddress = $address->load($addressId);
                $data['address_id'] = $addressId;
                $data['old_address'] = $this->getExistingAddressData($existsAddress);
            }
            $data['new_address'] = $this->getNewAddressData();
            $data['timestamp'] = strtotime($this->date->date('Y-m-d'));
            $data['ip_address'] = Mage::helper('core/http')->getRemoteAddr();
            $data['user_agent'] = Mage::helper('core/http')->getHttpUserAgent();
        } catch (Exception $e) {
            $this->debug('Error message ' . $e->getMessage());
        }

        return $data;
    }

    /**
     * Get the customer address data based on the action
     *
     * @return array
     */
    public function getNewAddressData()
    {
        $request = Mage::app()->getRequest();
        $addressData = array();
        try {
            $addressData['firstname'] = $request->getParam('firstname');
            $addressData['lastname'] = $request->getParam('lastname');
            $addressData['company'] = $request->getParam('company');
            $addressData['street'] = implode('', $request->getParam('street'));
            $addressData['city'] = $request->getParam('city');
            $addressData['region_id'] = $request->getParam('region_id');
            $addressData['region'] = $request->getParam('region');
            $addressData['postcode'] = $request->getParam('postcode');
            $addressData['country_id'] = $request->getParam('country_id');
            $addressData['telephone'] = $request->getParam('telephone');
        } catch (Exception $e) {
            $this->debug('Error message ' . $e->getMessage());
        }

        return $addressData;
    }

    /**
     * Get the customer existing address data
     *
     * @return multitype:unknown
     */
    public function getExistingAddressData($existsAddress)
    {
        $addressData = array();
        try {
            $addressData['firstname'] = $existsAddress->getFirstname();
            $addressData['lastname'] = $existsAddress->getLastname();
            $addressData['company'] = $existsAddress->getCompany();
            $addressData['street'] = $existsAddress->getStreet();
            $addressData['city'] = $existsAddress->getCity();
            $addressData['region_id'] = $existsAddress->getRegionId();
            $addressData['region'] = $existsAddress->getRegion();
            $addressData['postcode'] = $existsAddress->getPostcode();
            $addressData['country_id'] = $existsAddress->getCountryId();
            $addressData['telephone'] = $existsAddress->getTelephone();
        } catch (Exception $e) {
            $this->debug('Error message ' . $e->getMessage());
        }

        return $addressData;
    }

    /**
     * Get the targetbay review count and rating for product
     *
     * @return array
     */
    public function getRichSnippets()
    {
        if (!$this->trackingEnabled()) {
            return false;
        }

        $data = array();
        $responseData = '';
        $controllerName = Mage::app()->getRequest()->getControllerName();
        $moduleName = Mage::app()->getRequest()->getModuleName();
        $reviewProductId = Mage::getSingleton('core/session')->getProductReviewId();
        $tbReviewCount = Mage::getSingleton('core/session')->getProductReviewCacheCount();

        try {
            $type = self::RATINGS_STATS;
            $apiToken = '?api_token=' . $this->getApiToken();
            $feedUrl = $this->getHostname() . $type . $apiToken;
            $data['index_name'] = $this->getApiIndex();
            $productId = '';
            if ($moduleName === 'catalog' && $controllerName === 'product') {
                $productId = Mage::registry('product')->getId();
                $data['product_id'] = $productId;
                if ($reviewProductId != $productId) {
                    Mage::getSingleton('core/session')->unsProductReviewCount();
                    Mage::getSingleton('core/session')->unsProductReviewResponse();
                    $productReviewCount = '';
                } else {
                    $productReviewCount = Mage::getSingleton('core/session')->getProductReviewCount();
                }
            }

            if ($productReviewCount != $tbReviewCount  || $productReviewCount == '') {
                $jsondata = json_encode($data);
                $response = $this->postPageInfo($feedUrl, $jsondata);
                $responseBody = json_decode($response);

                if (!empty($responseBody) && $responseBody->reviews_count > 0) {
                    $_SESSION['last_session'] = time();
                    Mage::getSingleton('core/session')->setProductReviewCount($responseBody->reviews_count);
                    Mage::getSingleton('core/session')->setProductReviewResponse($responseBody);
                    if (!empty($productId)) {
                        Mage::getSingleton('core/session')->setProductReviewId($productId);
                    }
                    Mage::getSingleton('core/session')->unsProductReviewCacheCount();
                }
            } else {
                $responseBody = Mage::getSingleton('core/session')->getProductReviewResponse();
            }
            if (!empty($responseBody)) {
                $averageScore = $responseBody->reviews_average;
                $reviewsCount = $responseBody->reviews_count;
                $reviewsDetails = $responseBody->reviews;
                $responseData = array(
                    'average_score' => $averageScore,
                    'reviews_count' => $reviewsCount,
                    'reviews' => $reviewsDetails
                );
                return $responseData;
            }
        } catch (Exception $e) {
            $this->debug('Error message ' . $e->getMessage());
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
            if ($trackingSnippet['reviews_count'] > 0) {
                foreach ($trackingSnippet['reviews'] as $key => $aggregateReviewDetails) {
                    $itemRefData[] = 'tb-review-' . $key;
                }
                $itemRef = implode(' ', $itemRefData);
            }
            return $itemRef;
        } catch (Exception $e) {
            $this->debug('Error message ' . $e->getMessage());
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
        if (!$this->trackingEnabled()) {
            return false;
        }
        $data = array();
        $responseData = '';
        $siteReviewCount = Mage::getSingleton('core/session')->getSiteReviewCount();
        $tbSiteReviewCount = Mage::getSingleton('core/session')->getSiteReviewCacheCount();
        try {
            $type = self::RATINGS_STATS;
            $apiToken = '?api_token=' . $this->getApiToken();
            $feedUrl = $this->getHostname() . $type . $apiToken;
            $data['index_name'] = $this->getApiIndex();

            if ($siteReviewCount != $tbSiteReviewCount || $siteReviewCount == '') {
                $jsondata = json_encode($data);
                $response = $this->postPageInfo($feedUrl, $jsondata);
                $responseBody = json_decode($response);

                if (!empty($responseBody) && $responseBody->reviews_count > 0) {
                    Mage::getSingleton('core/session')->setSiteReviewCount($responseBody->reviews_count);
                    Mage::getSingleton('core/session')->setSiteReviewResponse($responseBody);
                    $_SESSION['last_session'] = time();
                    Mage::getSingleton('core/session')->unsSiteReviewCacheCount();
                }
            } else {
                $responseBody = Mage::getSingleton('core/session')->getSiteReviewResponse();
            }
            if (!empty($responseBody)) {
                $averageScore = $responseBody->reviews_average;
                $reviewsCount = $responseBody->reviews_count;
                $reviewsDetails = $responseBody->reviews;
                $responseData = array(
                    'average_score' => $averageScore,
                    'reviews_count' => $reviewsCount,
                    'reviews' => $reviewsDetails
                );
                return $responseData;
            }
        } catch (Exception $e) {
            $this->debug('Error message ' . $e->getMessage());
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
        if (!$this->trackingEnabled()) {
            return false;
        }
        $data = array();
        //$responseData = '';
        $controllerName = Mage::app()->getRequest()->getControllerName();
        $moduleName = Mage::app()->getRequest()->getModuleName();
        $reviewProductId = Mage::getSingleton('core/session')->getProductReviewId();
        $tbQaReviewCount = Mage::getSingleton('core/session')->getQaReviewCacheCount();

        try {
            $type = self::QUESTION_STATS;
            $apiToken = '?api_token=' . $this->getApiToken();
            $feedUrl = $this->getHostname() . $type . $apiToken;
            $data['index_name'] = $this->getApiIndex();
            if ($moduleName === 'catalog' && $controllerName === 'product') {
                $productId = Mage::registry('product')->getId();
                $data['product_id'] = $productId;
                if ($reviewProductId != $productId) {
                    Mage::getSingleton('core/session')->unsQaReviewCount();
                    Mage::getSingleton('core/session')->unsQaReviewResponse();
                    $qaReviewCount = '';
                } else {
                    $qaReviewCount = Mage::getSingleton('core/session')->getQaReviewCount();
                }
            }
            if ($qaReviewCount != $tbQaReviewCount || $qaReviewCount == '') {
                $jsonData = json_encode($data);
                $response = $this->postPageInfo($feedUrl, $jsonData);
                $responseBody = json_decode($response);

                if (!empty($responseBody) && $responseBody->qa_count > 0) {
                    $_SESSION['last_session'] = time();
                    Mage::getSingleton('core/session')->setQaReview($responseBody->qa_count);
                    Mage::getSingleton('core/session')->setQaReviewResponse($responseBody);
                    if (!empty($productId)) {
                        Mage::getSingleton('core/session')->setProductReviewId($productId);
                    }
                    Mage::getSingleton('core/session')->unsQaReviewCacheCount();
                }
            } else {
                $responseBody = Mage::getSingleton('core/session')->getQaReviewResponse();
            }
            if (!empty($responseBody)) {
                $qaCount = $responseBody->qa_count;
                $qaDetails = $responseBody->qas;
                $qaAuthor = $responseBody->client;
                $responseData = array(
                    'qa_count' => $qaCount,
                    'qa_details' => $qaDetails,
                    'qa_author' => $qaAuthor
                );
                return $responseData;
            }
        } catch (Exception $e) {
            $this->debug('Error message ' . $e->getMessage());
            return;
        }
    }
}

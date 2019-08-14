<?php

/*
 * @author Targetbay
 * @copyright 2016 Sathishkumar Mariappan <sathishkumar.m@innoppl.com>
 * @package Targetbay_Tracking
 */

class Targetbay_Tracking_Model_Observer
{
    private $apiToken;
    private $indexName;
    private $targetBayHost;
    private $helper;

    /**
     * Get the credentials from store configurations
     */
    public function __construct()
    {
        //$expireAfter = 1;
        $expireAfter = 1380;

        $this->helper = Mage::helper('tracking');
        $this->apiToken = '?api_token=' . $this->helper->getApiToken();
        $this->indexName = $this->helper->getApiIndex();
        $this->targetBayHost = $this->helper->getHostname();

        // ToDo: Fix this unused variable.
        $sess = Mage::getSingleton('core/session')->getTrackingSessionId();
        if (!$this->helper->cookie->get('trackingsession')) {
            $visitor_data = Mage::getSingleton('core/session')->getVisitorData();
            $trackingSession = $visitor_data['visitor_id'] . strtotime(date('Y-m-d H:i:s'));
            Mage::getSingleton('core/session')->setTrackingSessionId($trackingSession);
        }

        if (Mage::getSingleton('customer/session')->isLoggedIn()) {
            Mage::getModel('core/cookie')->set('user_loggedin', true, null, null, null, null, false);
            Mage::getModel('core/cookie')->set('afterlogin_session_id', Mage::getSingleton('core/session')->getCustomerSessionId(), null, null, null, null, false);
        }

        if (isset($_SESSION['last_session'])) {
            $secondsInactive = time() - $_SESSION['last_session'];
            $expireAfterSeconds = $expireAfter * 60;
            if ($secondsInactive > $expireAfterSeconds) {
                Mage::getSingleton('core/session')->unsProductReviewCount();
                Mage::getSingleton('core/session')->unsProductReviewResponse();
                Mage::getSingleton('core/session')->unsSiteReviewCount();
                Mage::getSingleton('core/session')->unsSiteReviewResponse();
                Mage::getSingleton('core/session')->unsQaReviewCount();
                Mage::getSingleton('core/session')->unsQaReviewResponse();
                Mage::getSingleton('core/session')->unsProductReviewId();
            }
        }
    }

    /**
     * Set the cookie values for user differentiate.
     */
    public function setCookieValues()
    {
        // For anonymous user
        $customerName = Targetbay_Tracking_Helper_Data::ANONYMOUS_USER;
        $customerEmail = Targetbay_Tracking_Helper_Data::ANONYMOUS_USER;
        if (!$this->helper->cookie->get('trackingid')) {
            $customerId = Mage::getSingleton('core/session')->getTrackingSessionId();
        }

        // for logged user
        if (Mage::app()->isInstalled() && Mage::getSingleton('customer/session')->isLoggedIn()) {
            $customer = Mage::getSingleton('customer/session')->getCustomer();
            $customerName = $customer->getName();
            $customerId = $customer->getId();
            $customerEmail = $customer->getEmail();
        }

        // ToDo: Do we need this?
        $trackingId = !empty($customerId) ? $this->helper->cookie->set('trackingid', $customerId, null, null, null, null, false) : '';

        $this->helper->cookie->set('trackingemail', $customerEmail, null, null, null, null, false);
        $this->helper->cookie->set('trackingname', $customerName, null, null, null, null, false);

        $quoteId = Mage::getSingleton('checkout/session')->getQuoteId() ? Mage::getSingleton('checkout/session')->getQuoteId() : '';
        $this->helper->cookie->set('trackingorderid', $quoteId, null, null, null, null, false);

        if (!$this->helper->cookie->get('trackingsession')) {
            $this->helper->cookie->set('trackingsession', Mage::getSingleton('core/session')->getTrackingSessionId(), null, null, null, null, false);
        }
    }

    /**
     * Visiting page info
     *
     * @param Varien_Event_Observer $observer
     *
     * @return void
     */
    public function pushPageVisitData(Varien_Event_Observer $observer)
    {
        $request = Mage::app()->getRequest();

        $currentUri = sprintf("%s/%s/%s", $request->getModuleName(), $request->getControllerName(), $request->getActionName());

        $triggers = array(
            'checkout/onepage/savePayment',
            'checkout/onepage/saveOrder',
            'checkout/onepage/success',
            'onestepcheckout/index/saveAddressOnestepcheckout',
            'onestepcheckout/index/save_shipping',
            'onestepcheckout/index/is_valid_email'
        );

        if (in_array($currentUri, $triggers, true)) {
            return false;
        }

        $this->setCookieValues();

        // Page referrer Tracking
        $this->pushReferralData();

        if ($this->helper->eventAlreadyTracked($observer->getEvent()->getControllerAction()->getFullActionName())) {
            return false;
        }

        // Set Token Values
        if (isset($_GET['utm_source']) && !$this->helper->cookie->get('utm_source')) {
            $this->helper->cookie->set('utm_source', $_GET['utm_source'], null, null, null, null, false);
        }

        if (isset($_GET['token']) && !$this->helper->cookie->get('utm_token')) {
            $this->helper->cookie->set('utm_token', $_GET['token'], null, null, null, null, false);
        }
    }

    /**
     * Visiting page info
     *
     * @param Varien_Event_Observer $observer
     *
     * @return void
     */
    public function cmsPageVisit(Varien_Event_Observer $observer)
    {
        if (!$this->helper->canTrackPages(Targetbay_Tracking_Helper_Data::PAGE_VISIT)) {
            return false;
        }

        if ($this->helper->eventAlreadyTracked($observer->getEvent()->getControllerAction()->getFullActionName())) {
            return false;
        }

        // Set Token Values
        if (isset($_GET['utm_source']) && !$this->helper->cookie->get('utm_source')) {
            $this->helper->cookie->set('utm_source', $_GET['utm_source'], null, null, null, null, false);
        }

        if (isset($_GET['token']) && !$this->helper->cookie->get('utm_token')) {
            $this->helper->cookie->set('utm_token', $_GET['token'], null, null, null, null, false);
        }

        $request = Mage::app()->getRequest();
        $trackingType = $this->helper->getTrackingType();
        // ToDo: Do we need this?
        $identifier = Mage::getSingleton('cms/page')->getIdentifier();
        $moduleName = $request->getModuleName();

        // Page Visit Tracking
        $data = $this->helper->visitInfo();
        if ($trackingType == 1 && (
                $request->getControllerName() === 'product' ||
                $request->getControllerName() === 'category' ||
                $moduleName === 'cms'
            )
        ) {
            return false;
        }

        $this->pushPages($data, Targetbay_Tracking_Helper_Data::PAGE_VISIT);
        return;
    }

    /**
     * Add to cart
     *
     * @param Varien_Event_Observer $observer
     *
     * @return void
     */
    public function pushAddToCart(Varien_Event_Observer $observer)
    {
        if (!$this->helper->canTrackPages(Targetbay_Tracking_Helper_Data::ADDTOCART)) {
            return false;
        }
        Mage::getSingleton('checkout/session')->setTitle('Shopping cart');

        //$item = $observer->getEvent()->getQuoteItem();
        //if ($item->getParentItem())
        //$item = $item->getParentItem();

        $productInfo = $observer->getProduct();

        $productEventInfo = $observer->getEvent()->getProduct();
        $quote = Mage::getModel('checkout/cart')->getQuote();
        $item = $quote->getItemByProduct($productEventInfo);

        $itemCollection = $quote->getItemsCollection();

        if ($productInfo->getData('type_id') === 'grouped') {
            $productIds = Mage::app()->getRequest()->getParam('super_group');
            $dataItem = array();
            $productData = array();
            foreach ($productIds as $id => $qty) {
                if ($qty < 1) {
                    continue;
                }
                $product = Mage::getModel('catalog/product')->load($id);
                $productData['type'] = $product->getTypeId();
                $productData['product_id'] = $id;
                $productData['product_sku'] = $product->getSku();
                $productData['product_name'] = addslashes($product->getName());
                $productData['price'] = $product->getFinalPrice() * $qty;
                $productData['special_price'] = $product->getSpecialPrice();
                $productData['productimg'] = $this->helper->getImageUrl($product, 'image');
                $productData['category'] = $this->helper->getProductCategory($product);
                $productData['category_name'] = $this->helper->getProductCategoryName($product);
                $productData['quantity'] = $qty;
                $productData['page_url'] = Mage::getUrl($product->getUrlPath());
                $productData['attributes'] = '';
                $dataItem[] = $productData;
            }

            /*foreach($itemCollection as $_item) {
                $itemPrice = $_item->getCalculationPrice();
                $productData['price'] = $itemPrice * $qty;
            }*/

            $data = $this->helper->getCartInfo();
            $data['product_type'] = $productInfo->getData('type_id');
            $data['cart_item'] = $dataItem;
        } else {
            $data = array_merge($this->helper->getCartInfo(), $this->helper->getItemInfo($item, Targetbay_Tracking_Helper_Data::ADDTOCART));
            $data['product_type'] = $item->getProductType();
            $data['price'] = $item->getProduct()->getFinalPrice();
            /*foreach($itemCollection as $_item) {
                $data['price'] = $_item->getCalculationPrice();
            }*/
            if ($customOptions = $this->helper->getCustomOptionsInfo($item, null)) {
                $data['attributes'] = $customOptions;
            }
        }

        $this->pushPages($data, Targetbay_Tracking_Helper_Data::ADDTOCART);

        return;
    }

    /**
     * Capture the Update cart event
     *
     * @param Varien_Event_Observer $observer
     *
     * @return void
     */
    public function pushUpdateCartData(Varien_Event_Observer $observer)
    {
        if (!$this->helper->canTrackPages(Targetbay_Tracking_Helper_Data::UPDATECART)) {
            return false;
        }
        $items = $observer->getEvent()->getCart()->getQuote()->getAllVisibleItems();
        $requestInfo = $observer->getEvent()->getInfo();
        $data = $this->helper->getCartInfo();

        foreach ($items as $item) {
            $newQty = $requestInfo[$item->getId()]['qty'];
            $oldQty = $item->getQty();
            if ($newQty == 0 || ($newQty == $oldQty)) {
                continue;
            }
            $itemData = $this->helper->getItemInfo($item);

            unset($itemData['quantity']);
            $newitemData = $itemData;
            $newitemData['old_quantity'] = $oldQty;
            $newitemData['new_quantity'] = $newQty;
            $data['cart_items'][] = $newitemData;
        }

        if (isset($data['cart_items'])) {
            $this->pushPages($data, Targetbay_Tracking_Helper_Data::UPDATECART);
        }

        return;
    }

    /**
     * Push the empty cart actions data
     *
     * @param Varien_Event_Observer $observer
     *
     * @return void|boolean
     */
    public function pushEmptyCartData(Varien_Event_Observer $observer)
    {
        $updateAction = (string) Mage::app()->getRequest()->getParam('update_cart_action');
        if ($updateAction != 'empty_cart') {
            return false;
        }
        $items = Mage::getSingleton('checkout/session')->getQuote()->getAllVisibleItems();
        foreach ($items as $item) {
            $this->removeCartItem($item);
        }

        return;
    }

    /**
     * Push the remove cart item data
     *
     * @param Varien_Event_Observer $observer
     */
    public function pushRemoveCartItemData(Varien_Event_Observer $observer)
    {
        $this->removeCartItem($observer->getEvent()->getQuoteItem());
    }

    /**
     * Remove the cart item
     *
     * @param unknown $item
     *
     * @return void|boolean
     */
    public function removeCartItem($item)
    {
        if (!$this->helper->canTrackPages(Targetbay_Tracking_Helper_Data::REMOVECART)) {
            return false;
        }
        $data = array_merge($this->helper->getCartInfo(), $this->helper->getItemInfo($item));
        $this->pushPages($data, Targetbay_Tracking_Helper_Data::REMOVECART);

        return;
    }

    /**
     * Observe the checkout page
     *
     * @param Varien_Event_Observer $observer
     *
     * @return void|boolean
     */
    public function pushCheckoutPageData(Varien_Event_Observer $observer)
    {
        if (!$this->helper->canTrackPages(Targetbay_Tracking_Helper_Data::CHECKOUT)) {
            return false;
        }

        // Set Token Values
        if (isset($_GET['utm_source']) && !$this->helper->cookie->get('utm_source')) {
            $this->helper->cookie->set('utm_source', $_GET['utm_source'], null, null, null, null, false);
        }

        if (isset($_GET['token']) && !$this->helper->cookie->get('utm_token')) {
            $this->helper->cookie->set('utm_token', $_GET['token'], null, null, null, null, false);
        }

        $quote = Mage::getSingleton('checkout/session')->getQuote();

        $data = $this->helper->getCartInfo();
        $data['cart_items'] = $this->helper->getOrderItemsInfo($quote);
        $data['total_qty'] = Mage::getModel('checkout/cart')->getQuote()->getItemsQty();
        $data['total_amount'] = Mage::getModel('checkout/cart')->getQuote()->getGrandTotal();
        $this->pushPages($data, Targetbay_Tracking_Helper_Data::CHECKOUT);

        return;
    }

    /**
     * Push billing info when user enter the billing in checkout
     *
     * @param Varien_Event_Observer $observer
     *
     * @return void|boolean
     */
    public function pushBillingAddressData(Varien_Event_Observer $observer)
    {
        try {
            if (!$this->helper->canTrackPages(Targetbay_Tracking_Helper_Data::BILLING)) {
                return false;
            }
            Mage::getSingleton('checkout/session')->setTitle('Checkout Billing');
            $quote = Mage::getSingleton('checkout/session')->getQuote();
            $billingInfo = $this->helper->getAddressData($quote, Targetbay_Tracking_Helper_Data::BILLING);
            $this->pushPages($billingInfo, Targetbay_Tracking_Helper_Data::BILLING);
        } catch (Exception $e) {
            $this->helper->debug("ERROR: " . $e->getMessage());
        }

        return;
    }

    /**
     * Push shipping info when user enter the shipping in checkout
     *
     * @param Varien_Event_Observer $observer
     *
     * @return void|boolean
     */
    public function pushShippingAddressData(Varien_Event_Observer $observer)
    {
        try {
            if (!$this->helper->canTrackPages(Targetbay_Tracking_Helper_Data::SHIPPING)) {
                return false;
            }
            Mage::getSingleton('checkout/session')->setTitle('Checkout Shipping');
            $quote = Mage::getSingleton('checkout/session')->getQuote();
            $shippingInfo = $this->helper->getAddressData($quote, Targetbay_Tracking_Helper_Data::SHIPPING);
            $this->pushPages($shippingInfo, Targetbay_Tracking_Helper_Data::SHIPPING);
        } catch (Exception $e) {
            $this->helper->debug("ERROR: " . $e->getMessage());
        }

        return;
    }

    /**
     * Order data
     *
     * @param Varien_Event_Observer $observer
     * @return Targetbay_Tracking_Model_Observer
     *
     * @return void|boolean
     */
    public function pushOrderData(Varien_Event_Observer $observer)
    {
        try {
            if (!Mage::registry('order_pushed')) {
                Mage::register('order_pushed', true);
                if (!$this->helper->canTrackPages(Targetbay_Tracking_Helper_Data::ORDER_ITEMS)) {
                    return false;
                }
                $order = $observer->getEvent()->getOrder();
                $params = Mage::app()->getFrontController()->getRequest()->getParams();

                if ($this->pushShipmentData($order, $params)) {
                    return false;
                } // order shipment process so no need to make order submit api.

                // Capture the customer registration.
                if ($customer = $this->helper->isRegisterCheckout($order)) {
                    $this->pushRegisterData($customer);
                }

                // Order Data Push to the Tag Manager
                $orderInfo = $this->helper->getInfo($order);
                $this->pushPages($orderInfo, Targetbay_Tracking_Helper_Data::ORDER_ITEMS);
                Mage::getSingleton('checkout/session')->unsQuoteMerged();
            }
        } catch (Exception $e) {
            $this->helper->debug("ERROR: " . $e->getMessage());
        }

        return;
    }

    /**
     * Order status change data
     *
     * @param Varien_Event_Observer $observer
     * @return Targetbay_Tracking_Model_Observer
     *
     * @return void|boolean
     */
    public function pushOrderStatusData(Varien_Event_Observer $observer)
    {
        try {
            if (!$this->helper->canTrackPages(Targetbay_Tracking_Helper_Data::ORDER_STATUS)) {
                return false;
            }
            $order = $observer->getEvent()->getOrder();

            $data = $this->helper->getSessionInfo($order);
            $data['first_name'] = $order->getCustomerFirstname();
            $data['last_name'] = $order->getCustomerLastname();
            $guestUsername = $order->getCustomerFirstname() . ' ' . $order->getCustomerLastname();
            $gName = !empty($guestUsername) ? $guestUsername : self::ANONYMOUS_USER;
            $data['user_name'] = $order->getCustomerIsGuest() ? $gName : $data['first_name'] . ' ' . $data['last_name'];
            $data['user_mail'] = $order->getCustomerEmail();
            $data['order_id'] = $order->getId();
            $data['status'] = $order->getStatus();
            if ($data['status'] === null) {
                return false;
            }
            $this->pushPages($data, Targetbay_Tracking_Helper_Data::ORDER_STATUS);
        } catch (Exception $e) {
            $this->helper->debug("ERROR: " . $e->getMessage());
        }

        return;
    }

    /**
     * Abandoned cart merge for logged in user
     *
     * @param Varien_Event_Observer $observer
     *
     * @return void
     */
    public function quoteMergeData(Varien_Event_Observer $observer)
    {
        $abandonedMail = Mage::getSingleton('core/session')->getAbandonedMail();
        $cart = Mage::getModel('checkout/cart');
        if ($abandonedMail) {
            try {
                $this->helper->debug('MergeObs');
                $cart->truncate();
            } catch (\Exception $e) {
                $this->helper->debug('Error:' . $e->getMessage());
            }
        }
        return;
    }

    /**
     * Push the shipment data
     *
     * @param unknown $order
     * @param unknown $params
     * @return boolean
     */
    public function pushShipmentData($order, $params)
    {
        try {
            if ($this->helper->isFullFillmentProcess($params)) {
                $data = $this->helper->getFullFillmentData($order, $params);
                $this->pushPages($data, Targetbay_Tracking_Helper_Data::ORDER_SHIPMENT);
                return true;
            }
        } catch (Exception $e) {
            $this->helper->debug("ERROR: " . $e->getMessage());
        }

        return false;
    }

    /**
     * Push the order shipment data
     *
     * @param Varien_Event_Observer $observer
     * @return Targetbay_Tracking_Model_Observer
     * @return boolean
     */
    public function pushOrderShipmentData(Varien_Event_Observer $observer)
    {
        try {
            if (!$this->helper->canTrackPages(Targetbay_Tracking_Helper_Data::ORDER_SHIPMENT)) {
                return false;
            }

            $shipment = $observer->getEvent()->getShipment();
            $order = $shipment->getOrder();
            $params[Targetbay_Tracking_Helper_Data::ORDER_SHIPMENT] = true;
            if ($this->helper->isFullFillmentProcess($params)) {
                $data = $this->helper->getFullFillmentData($order, $params);
                $this->pushPages($data, Targetbay_Tracking_Helper_Data::ORDER_SHIPMENT);
            }
        } catch (Exception $e) {
            $this->helper->debug("ERROR: " . $e->getMessage());
        }
        return;
    }

    /**
     * Push billing and shipping info when user enter into onestepcheckout
     *
     * @param Varien_Event_Observer $observer
     *
     * @return void|boolean
     */
    public function pushCheckoutAddressData(Varien_Event_Observer $observer)
    {
        if (!$this->helper->canTrackPages(Targetbay_Tracking_Helper_Data::ONESTEPCHECKOUT_ADDRESS)) {
            return false;
        }
        Mage::getSingleton('checkout/session')->setTitle('OneStepCheckout');

        $quote = Mage::getSingleton('checkout/session')->getQuote();
        $billingInfo = $this->helper->getAddressData($quote, Targetbay_Tracking_Helper_Data::BILLING);
        $this->pushPages($billingInfo, Targetbay_Tracking_Helper_Data::BILLING);

        $shippingInfo = $this->helper->getAddressData($quote, Targetbay_Tracking_Helper_Data::SHIPPING);
        $this->pushPages($shippingInfo, Targetbay_Tracking_Helper_Data::SHIPPING);

        return;
    }

    /**
     * Push the data when customer loggedin
     *
     * @param Varien_Event_Observer $observer
     *
     * @return void|boolean
     */
    public function customerLoginPushData(Varien_Event_Observer $observer)
    {
        if (!$this->helper->canTrackPages(Targetbay_Tracking_Helper_Data::LOGIN)) {
            return false;
        }
        if (!$observer->getCustomer()) {
            return false;
        }
        $data = $this->helper->getCustomerData($observer->getCustomer(), Targetbay_Tracking_Helper_Data::LOGIN);
        $this->pushPages($data, Targetbay_Tracking_Helper_Data::LOGIN);

        return;
    }

    /**
     * Push the data when customer logout
     *
     * @param Varien_Event_Observer $observer
     *
     * @return void|boolean
     */
    public function customerLogoutPushData(Varien_Event_Observer $observer)
    {
        if (!$this->helper->canTrackPages(Targetbay_Tracking_Helper_Data::LOGOUT)) {
            return false;
        }
        $data = $this->helper->getCustomerData($observer->getCustomer(), Targetbay_Tracking_Helper_Data::LOGOUT);
        $this->pushPages($data, Targetbay_Tracking_Helper_Data::LOGOUT);

        // Remove all Cookies
        $this->helper->removeCookies();

        return;
    }

    /**
     * Registration observer
     *
     * @param Varien_Event_Observer $observer
     */
    public function customerRegisterPushData(Varien_Event_Observer $observer)
    {
        $this->pushRegisterData($observer->getCustomer());
    }

    /**
     * Push the registration data
     *
     * @param unknown $customer
     *
     * @return void|boolean
     */
    public function pushRegisterData($customer)
    {
        if (!$this->helper->canTrackPages(Targetbay_Tracking_Helper_Data::CREATE_ACCOUNT)) {
            return false;
        }
        $data = $this->helper->getCustomerData($customer, Targetbay_Tracking_Helper_Data::CREATE_ACCOUNT);
        $this->pushPages($data, Targetbay_Tracking_Helper_Data::CREATE_ACCOUNT);

        return;
    }

    /**
     * Push the newslettter subscription data
     *
     * @param Varien_Event_Observer $observer
     *
     * @return void|boolean
     */
    public function pushSubscriptionData(Varien_Event_Observer $observer)
    {
        if (!$this->helper->canTrackPages(Targetbay_Tracking_Helper_Data::SUBSCRIBE_CUSTOMER)) {
            return false;
        }

        $webstieId = Mage::app()->getStore()->getWebsiteId();
        $customerModel = Mage::getModel('customer/customer');

        if (Mage::app()->getRequest()->getParam('email')) {
            $email = Mage::app()->getRequest()->getParam('email');
            $customerData = $customerModel->setWebsiteId($webstieId)->loadByEmail($email);
            // ToDo: Do we need this?
            $customerId = $customerData->getEntityId();
        } else {
            $customerId = Mage::getSingleton('customer/session')->getCustomer()->getId();
            $customerData = $customerModel->load($customerId);
            $email = '';
        }

        $data = $this->helper->visitInfo();

        if (empty($email)) {
            $subscriberFactory = Mage::getModel('newsletter/subscriber')->loadByCustomer($customerData);

            if ($subscriberFactory->getSubscriberStatus() == Targetbay_Tracking_Helper_Data::STATUS_UNSUBSCRIBED) {
                $status = 'Unsubscribed';
            } elseif ($subscriberFactory->getSubscriberStatus() == Targetbay_Tracking_Helper_Data::STATUS_SUBSCRIBED) {
                $status = 'Subscribed';
            } elseif ($subscriberFactory->getSubscriberStatus() == Targetbay_Tracking_Helper_Data::STATUS_UNCONFIRMED) {
                $status = 'Unconfirmed';
            } elseif ($subscriberFactory->getSubscriberStatus() == Targetbay_Tracking_Helper_Data::STATUS_NOT_ACTIVE) {
                $status = 'Not Activated';
            } else {
                $status = Mage::app()->getRequest()->getParam('status');
            }
        } else {
            $status = '';
        }

        $status = !empty($email) ? 'Subscribed' : $status;
        $data['user_mail'] = Mage::getSingleton('customer/session')->isLoggedIn() ? Mage::getSingleton('customer/session')->getCustomer()->getEmail() : $email;
        $data['subscription_status'] = $status;
        $this->pushPages($data, Targetbay_Tracking_Helper_Data::SUBSCRIBE_CUSTOMER);

        return;
    }

    /**
     * Admin Account Activation observer
     *
     * @param Varien_Event_Observer $observer
     */
    public function customerAdminConfirmData(Varien_Event_Observer $observer)
    {
        $customer_info = $observer->getCustomer()->getData();
        $data = array_merge($this->helper->visitInfo(), $customer_info);
        $this->pushPages($data, Targetbay_Tracking_Helper_Data::ADMIN_ACTIVATE_ACCOUNT);

        return;
    }

    /**
     * Add and update product
     *
     * @param Varien_Event_Observer $observer
     *
     * @return void
     */
    public function pushProductData(Varien_Event_Observer $observer)
    {
        try {
            if (!$this->helper->canTrackPages(Targetbay_Tracking_Helper_Data::ADD_PRODUCT)) {
                return false;
            }
            $param = Mage::app()->getRequest()->getParams();
            $product = $observer->getEvent()->getProduct();
            if ($product->getId()) {
                $type = Targetbay_Tracking_Helper_Data::ADD_PRODUCT;
                if ($param['id']) {
                    if (!$this->helper->canTrackPages(Targetbay_Tracking_Helper_Data::UPDATE_PRODUCT)) {
                        return false;
                    }
                    $type = Targetbay_Tracking_Helper_Data::UPDATE_PRODUCT;
                }
                $data = $this->helper->getProductData($product);
                $this->pushPages($data, $type);
            }
        } catch (Exception $e) {
            $this->helper->debug("ERROR: " . $e->getMessage());
        }

        return;
    }

    /**
     * Delete product
     *
     * @param Varien_Event_Observer $observer
     *
     * @return void
     */
    public function pushDeleteProductData(Varien_Event_Observer $observer)
    {
        try {
            if (!$this->helper->canTrackPages(Targetbay_Tracking_Helper_Data::DELETE_PRODUCT)) {
                return false;
            }
            $params = Mage::app()->getRequest()->getParams();

            if ($params) {
                $data['entity_id'] = Mage::app()->getRequest()->getParam('id');
                $data['user_name'] = Targetbay_Tracking_Helper_Data::ANONYMOUS_USER;
                $data['user_id'] = strtotime(date('Y-m-d H:i:s'));
                $data['session_id'] = strtotime(date('Y-m-d H:i:s'));
                if ($this->helper->cookie->get('trackingsession')) {
                    $data['user_id'] = $this->helper->cookie->get('trackingsession');
                    $data['session_id'] = $this->helper->cookie->get('trackingsession');
                }
                $data['date'] = $this->helper->date->date('Y-m-d');
                $data['timestamp'] = strtotime($this->helper->date->date('Y-m-d'));
                $data['time'] = $this->helper->date->date('H:i');
                $data['user_mail'] = Targetbay_Tracking_Helper_Data::ANONYMOUS_USER;
                $this->pushPages($data, Targetbay_Tracking_Helper_Data::DELETE_PRODUCT);
            }
        } catch (Exception $e) {
            $this->helper->debug("ERROR: " . $e->getMessage());
        }

        return;
    }

    /**
     * Category view page
     *
     * @param Varien_Event_Observer $observer
     *
     * @return void
     */
    public function pushCategoryViewData(Varien_Event_Observer $observer)
    {
        if (!$this->helper->canTrackPages(Targetbay_Tracking_Helper_Data::CATEGORY_VIEW)) {
            return false;
        }
        $category = Mage::registry('current_category');
        $data = $this->helper->visitInfo();
        $data['category_id'] = $category->getId();
        $data['category_url'] = $category->getUrl();
        $data['category_name'] = $category->getName();

        $trackingType = $this->helper->getTrackingType();
        if ($trackingType != 1):
            $this->pushPages($data, Targetbay_Tracking_Helper_Data::CATEGORY_VIEW);
        endif;

        return;
    }

    /**
     * Product view page
     *
     * @param Varien_Event_Observer $observer
     *
     * @return void
     */
    public function pushProductViewData(Varien_Event_Observer $observer)
    {
        if (!$this->helper->canTrackPages(Targetbay_Tracking_Helper_Data::PRODUCT_VIEW)) {
            return false;
        }

        // Get the base visit info
        $data = $this->helper->visitInfo();
        $product = Mage::registry('product');
        $categoryIds = $product->getCategoryIds();
        if (count($categoryIds)) {
            $firstCategoryId = $categoryIds[0];
            $_category = Mage::getModel('catalog/category')->load($firstCategoryId);
            $data['category'] = $_category->getName();
        }
        $data['product_id'] = $product->getId();
        $data['product_name'] = $product->getName();
        $data['price'] = $product->getPrice();
        $data['productimg'] = $product->getImageUrl();
        $data['stock'] = Targetbay_Tracking_Helper_Data::OUT_OF_STOCK;
        $stock = $product->getStockItem();
        if ($stock->getIsInStock()) {
            $data['stock'] = Targetbay_Tracking_Helper_Data::IN_STOCK;
        }

        $trackingType = $this->helper->getTrackingType();
        if ($trackingType != 1):
            $this->pushPages($data, Targetbay_Tracking_Helper_Data::PRODUCT_VIEW);
        endif;

        return;
    }

    /**
     * Push the searhced query string
     *
     * @param Varien_Event_Observer $observer
     *
     * @return void
     */
    public function pushSearchQueryData(Varien_Event_Observer $observer)
    {
        if (!$this->helper->canTrackPages(Targetbay_Tracking_Helper_Data::CATALOG_SEARCH)) {
            return false;
        }
        $keyword = Mage::app()->getRequest()->getParam('q');
        if (empty($keyword)) {
            return false;
        }

        $data = $this->helper->visitInfo();
        $data['keyword'] = $keyword;

        $trackingType = $this->helper->getTrackingType();
        if ($trackingType != 1):
            $this->pushPages($data, Targetbay_Tracking_Helper_Data::CATALOG_SEARCH);
        endif;

        return;
    }

    /**
     * Add the Wishlist
     *
     * @param Varien_Event_Observer $observer
     *
     * @return void
     */
    public function pushAddWishlistData($observer)
    {
        if (!$this->helper->canTrackPages(Targetbay_Tracking_Helper_Data::WISHLIST)) {
            return false;
        }
        $wishlistItems = $observer->getEvent()->getItems();
        //$item_info = [];

        foreach ($wishlistItems as $item) {
            if ($item->getParentItem()) {
                $item = $item->getParentItem();
            }

            $item_info = $this->helper->getWishlistProductInfo($item->getData('product_id'));

            $data = array_merge($this->helper->visitInfo(), $item_info);
            $data['item_id'] = $item->getWishlistItemId();
            if ($customOptions = $this->helper->getCustomOptionsInfo($item, null)) {
                $data['attributes'] = $customOptions;
            }
            $this->pushPages($data, Targetbay_Tracking_Helper_Data::WISHLIST);
        }

        return;
    }

    /**
     * Update the Wishlist items
     *
     * @param Varien_Event_Observer $observer
     */
    public function pushUpdateWishlistData(Varien_Event_Observer $observer)
    {
        if (!$this->helper->canTrackPages(Targetbay_Tracking_Helper_Data::UPDATE_WISHLIST)) {
            return false;
        }

        $controller = $observer->getEvent()->getControllerAction();
        $request = $controller->getRequest();
        $wishlistId = Mage::app()->getRequest()->getParam('wishlist_id');
        $wishlistDesc = Mage::app()->getRequest()->getParam('description');
        $wishlistQty = Mage::app()->getRequest()->getParam('qty');

        $data = $this->helper->visitInfo();
        $items = array();
        $data['wishlist_id'] = $wishlistId;

        foreach ($wishlistDesc as $id => $item) {
            $wishlistItem = Mage::getModel('wishlist/item')->load($id);
            $items[$id]['item_id'] = $id;
            $items[$id]['product_id'] = $wishlistItem->getProductId();
            $items[$id]['store_id'] = $wishlistItem->getStoreId();
            $items[$id]['description'] = $item;
            $items[$id]['qty'] = $wishlistQty[$id];
        }

        $data['wishlist_items'] = $items;
        $this->pushPages($data, Targetbay_Tracking_Helper_Data::UPDATE_WISHLIST);

        return;
    }

    /**
     * Remove the Wishlist items
     *
     * @param Varien_Event_Observer $observer
     */
    public function removeWhislistItem(Varien_Event_Observer $observer)
    {
        if (!$this->helper->canTrackPages(Targetbay_Tracking_Helper_Data::REMOVE_WISHLIST)) {
            return false;
        }

        $controller = $observer->getEvent()->getControllerAction();

        // ToDo: Do we need this?
        $request = $controller->getRequest();

        // ToDo: Do we need this?
        $user = Mage::getSingleton('admin/session');

        $id = (int) Mage::app()->getRequest()->getParam('item');
        $item = Mage::getModel('wishlist/item')->load($id);

        if (!$item->getId()) {
            return false;
        }

        $wishlist = Mage::getModel('wishlist/wishlist')->load($item->getWishlistId());

        if (!$wishlist) {
            return false;
        } else {
            $data = $this->helper->visitInfo();
            $data['item_id'] = $id;
            $data['product_id'] = $item->getProductId();
            $data['store_id'] = $item->getStoreId();
            $data['wishlist_id'] = $item->getWishlistId();
            $this->pushPages($data, Targetbay_Tracking_Helper_Data::REMOVE_WISHLIST);
        }

        return;
    }

    /**
     * Visiting page info
     *
     * @param Varien_Event_Observer $observer
     *
     * @return void
     */
    public function pushCartData(Varien_Event_Observer $observer)
    {
        if (!$this->helper->canTrackPages(Targetbay_Tracking_Helper_Data::CART_INDEX)) {
            return false;
        }

        // Set Token Values
        if (isset($_GET['utm_source']) && !$this->helper->cookie->get('utm_source')) {
            $this->helper->cookie->set('utm_source', $_GET['utm_source'], null, null, null, null, false);
        }

        if (isset($_GET['token']) && !$this->helper->cookie->get('utm_token')) {
            $this->helper->cookie->set('utm_token', $_GET['token'], null, null, null, null, false);
        }

        // Page Visit Tracking
        $data = $this->helper->visitInfo();
        $this->pushPages($data, Targetbay_Tracking_Helper_Data::CART_INDEX);

        return;
    }

    /**
     * Push customer address data
     *
     * @param Varien_Event_Observer $observer
     *
     * @return void
     */
    public function pushCustomerAddressData(Varien_Event_Observer $observer)
    {
        if (!$this->helper->canTrackPages(Targetbay_Tracking_Helper_Data::CUSTOMER_ADDRESS)) {
            return false;
        }
        $addressId = Mage::app()->getRequest()->getParam('id');
        $data = $this->helper->getCustomerAddressData($addressId);
        $this->pushPages($data, Targetbay_Tracking_Helper_Data::CUSTOMER_ADDRESS);
        return;
    }

    /**
     * Push customer account data
     *
     * @param Varien_Event_Observer $observer
     *
     * @return void
     */
    public function pushCustomerAccountData(Varien_Event_Observer $observer)
    {
        if (!$this->helper->canTrackPages(Targetbay_Tracking_Helper_Data::CUSTOMER_ACCOUNT)) {
            return false;
        }
        Mage::getSingleton('customer/session')->setTitle('Account Information');
        $data = $this->helper->visitInfo();
        $data['customer_id'] = Mage::getSingleton('customer/session')->getCustomer()->getId();
        $data['firstname'] = Mage::getSingleton('customer/session')->getCustomer()->getFirstname();
        $data['lastname'] = Mage::getSingleton('customer/session')->getCustomer()->getLastname();
        $data['email'] = Mage::getSingleton('customer/session')->getCustomer()->getEmail();
        $data['account_updated'] = Mage::getModel('core/date')->date('Y-m-d');
        $this->pushPages($data, Targetbay_Tracking_Helper_Data::CUSTOMER_ACCOUNT);
        return;
    }

    /**
     * Push the referrer data.
     *
     * @return boolean
     */
    public function pushReferralData()
    {
        if (!$this->helper->canTrackPages(Targetbay_Tracking_Helper_Data::PAGE_REFERRAL)) {
            return false;
        }
        try {
            $request = Mage::app()->getRequest();
            $trackingType = $this->helper->getTrackingType();
            // ToDo: Do we need this?
            $identifier = Mage::getSingleton('cms/page')->getIdentifier();
            $moduleName = $request->getModuleName();

            if ($trackingType == 1 && ($request->getControllerName() == 'product'
                    || $request->getControllerName() == 'category'
                    || $moduleName == 'cms')
            ) {
                return false;
            }

            if ($referrerData = $this->helper->getRefererData()) {
                $this->pushPages($referrerData, Targetbay_Tracking_Helper_Data::PAGE_REFERRAL);
            }
        } catch (Exception $e) {
            $this->debug('Error message ' . $e->getMessage());
            return;
        }
    }

    /**
     * API Calls
     *
     * @param unknown $data
     * @param unknown $type
     */
    public function pushPages($data, $type)
    {
        $endPointUrl = $this->targetBayHost . $type . $this->apiToken;
        $data['index_name'] = $this->indexName;
        try {
            $this->helper->postPageInfo($endPointUrl, json_encode($data));
        } catch (Exception $e) {
            $this->helper->debug($type . "ERROR:" . $e->getMessage());
        }
    }
}

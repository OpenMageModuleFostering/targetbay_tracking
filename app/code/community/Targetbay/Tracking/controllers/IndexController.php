<?php
/*
 * @author Targetbay
 * @copyright 2016 Sathishkumar Mariappan <sathishkumar.m@innoppl.com>
 * @package Targetbay_Tracking
 */
class Targetbay_Tracking_IndexController extends Mage_Core_Controller_Front_Action
{
    public function reloadAction()
    {
		if (Mage::getSingleton('customer/session')->isLoggedIn())
			return false;

        try {
			$quoteId = Mage::app()->getRequest()->getParam('quote_id');
			$guestUserId = Mage::app()->getRequest()->getParam('guest_user_id');
			//$guestUserId = 591861368;
			$store_id = Mage::app()->getStore()->getId();

			if($guestUserId != '' && !Mage::getSingleton('customer/session')->isLoggedIn()) {
				Mage::getModel('core/cookie')->set('targetbay_session_id', $guestUserId, null, null, null, null, false);
			}
			
			$checkout = Mage::getSingleton('checkout/session');
			$cust = Mage::getSingleton('customer/session');
			$coreSession = Mage::getSingleton('core/session');
			$coreSession->setRestoreQuoteId($quoteId);
			$coreSession->setAbandonedMail(true);
			$chkQuote = Mage::helper('checkout/cart')->getQuote();
			$helper = Mage::helper('tracking');			

			if(empty($quoteId)) {
				$this->_redirectAfterReload();
			}

			if($checkout->getQuoteMerged()) {
				$this->_redirectAfterReload();
			}

			$quote = Mage::getModel('sales/quote')->load($quoteId);
			if($quote && $quote->getId() && $quote->getIsActive() &&  (($checkout->getQuoteMerged() == null) || 
																		$checkout->getQuoteMerged() != true)) {
				if (!$chkQuote) {
				    $chkQuote = Mage::getModel('sales/quote');
				}

				$chkQuote->merge($quote)
					     ->collectTotals()
					     ->save();
				$checkout->setQuoteId($chkQuote->getId());
	            $checkout->setQuoteMerged(true);
            }
	    } catch (Exception $e) {
			$helper->debug("ERROR: " . $e->getMessage());
		}

        return $this->_redirectAfterReload();
    }

    private function _redirectAfterReload()
    {        
        $url = 'checkout/cart/';
		$utmSource = Mage::app()->getRequest()->getParam('utm_source');
		$utmToken = Mage::app()->getRequest()->getParam('token');

        return $this->_redirect(
            $url,
            array('_secure' => Mage::app()->getStore()->isCurrentlySecure(), 'utm_source' => $utmSource, 'token' => $utmToken)
        );
    }

    public function tokenAction() {
    
        $params = array(
            'siteUrl' => 'http://dev-goodstate.enterpriseapplicationdevelopers.com/oauth',
            'requestTokenUrl' => 'http://dev-goodstate.enterpriseapplicationdevelopers.com/oauth/initiate',
            'accessTokenUrl' => 'http://dev-goodstate.enterpriseapplicationdevelopers.com/oauth/token',
            'authorizeUrl' => 'http://dev-goodstate.enterpriseapplicationdevelopers.com/admin/oauth_authorize',//This URL is used only if we authenticate as Admin user type
            'consumerKey' => 'fe2975957a5699225d7cbad7873e6d7a',//Consumer key registered in server administration
            'consumerSecret' => '0b59f92cfd9bcfa982aa95d3dc0513c5',//Consumer secret registered in server administration
            'callbackUrl' => 'http://localhost/magento_19/index.php/tracking/index/callback',//Url of callback action below
        );
 
        // Initiate oAuth consumer with above parameters
        $consumer = new Zend_Oauth_Consumer($params);

        // Get request token
        $requestToken = $consumer->getRequestToken();
        Mage::helper('tracking')->debug('requestToken====');
        Mage::helper('tracking')->debug(print_r($requestToken, true));
        // Get session
        $session = Mage::getSingleton('core/session');
        // Save serialized request token object in session for later use
        $session->setRequestToken(serialize($requestToken));
        // Redirect to authorize URL
        $consumer->redirect();
 
        return;
    }
 
    public function callbackAction() {

        $params = array(
            'siteUrl' => 'http://dev-goodstate.enterpriseapplicationdevelopers.com/oauth',
            'requestTokenUrl' => 'http://dev-goodstate.enterpriseapplicationdevelopers.com/oauth/initiate',
            'accessTokenUrl' => 'http://dev-goodstate.enterpriseapplicationdevelopers.com/oauth/token',
            'consumerKey' => 'fe2975957a5699225d7cbad7873e6d7a',
            'consumerSecret' => '0b59f92cfd9bcfa982aa95d3dc0513c5'
        );
 
        // Get session
        $session = Mage::getSingleton('core/session');
        // Read and unserialize request token from session
        $requestToken = unserialize($session->getRequestToken());
        // Initiate oAuth consumer
        $consumer = new Zend_Oauth_Consumer($params);
        Mage::log('Get Array:' . print_r($_GET, true) );
        // Using oAuth parameters and request Token we got, get access token
        $acessToken = $consumer->getAccessToken($_GET, $requestToken);
        Mage::helper('tracking')->debug('acessToken====');
        Mage::helper('tracking')->debug(print_r($acessToken, true));
        // Get HTTP client from access token object
        $restClient = $acessToken->getHttpClient($params);
        Mage::helper('tracking')->debug('restClient====');
        Mage::helper('tracking')->debug(print_r($restClient, true));
        // Set REST resource URL
        //$restClient->setUri('http://dev.getbitoutdoor.com/api/rest/targetbay/totalproductcounts');
        //$restClient->setUri('http://developer:heymagento@getbit-live.enterpriseapplicationdevelopers.com/api/rest/targetbay/totalproductcounts');
        $restClient->setUri('http://developer:heymagento@dev-goodstate.enterpriseapplicationdevelopers.com/api/rest/targetbay/totalcategorycounts');
        // In Magento it is neccesary to set json or xml headers in order to work
        $restClient->setHeaders('Accept', 'application/json');
        // Get method
        $restClient->setMethod(Zend_Http_Client::GET);
        //Make REST request
        $response = $restClient->request();
        // Here we can see that response body contains json list of products
        echo '<pre>';
        Zend_Debug::dump($response);
 
        return;
    }
}

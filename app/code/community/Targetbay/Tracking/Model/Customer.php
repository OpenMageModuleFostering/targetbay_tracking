<?php

/*
 * @author Targetbay
 * @copyright 2016 Sathishkumar Mariappan <sathishkumar.m@innoppl.com>
 * @package Targetbay_Tracking
 */

class Targetbay_Tracking_Model_Customer extends Mage_Customer_Model_Customer
{
    /**#@+
     * Configuration pathes for email templates and identities
     */
    const XML_PATH_REGISTER_EMAIL_TEMPLATE = 'customer/create_account/email_template';
    const XML_PATH_CONFIRM_EMAIL_TEMPLATE = 'customer/create_account/email_confirmation_template';
    const XML_PATH_CONFIRMED_EMAIL_TEMPLATE = 'customer/create_account/email_confirmed_template';
    /**#@-*/

    /**
     * Send email with new account related information
     *
     * @param string $type
     * @param string $backUrl
     * @param string $storeId
     * @throws Mage_Core_Exception
     * @return Mage_Customer_Model_Customer
     */
    public function sendNewAccountEmail($type = 'registered', $backUrl = '', $storeId = '0', $password = null)
    {
        $emailStatus = Mage::helper('tracking')->getEmailStatus();
        if ($emailStatus == 1) {
            return false;
        }

        $types = array(
            'registered' => self::XML_PATH_REGISTER_EMAIL_TEMPLATE, // welcome email, when confirmation is disabled
            'confirmed' => self::XML_PATH_CONFIRMED_EMAIL_TEMPLATE, // welcome email, when confirmation is enabled
            'confirmation' => self::XML_PATH_CONFIRM_EMAIL_TEMPLATE, // email with confirmation link
        );
        if (!isset($types[$type])) {
            Mage::throwException(Mage::helper('customer')->__('Wrong transactional account email type'));
        }

        if (!$storeId) {
            $storeId = $this->_getWebsiteStoreId($this->getSendemailStoreId());
        }

        $this->_sendEmailTemplate($types[$type], self::XML_PATH_REGISTER_EMAIL_IDENTITY,
            array('customer' => $this, 'back_url' => $backUrl), $storeId);

        return $this;
    }
}

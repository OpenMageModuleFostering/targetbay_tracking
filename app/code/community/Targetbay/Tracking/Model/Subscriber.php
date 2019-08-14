<?php

/*
 * @author Targetbay
 * @copyright 2016 Sathishkumar Mariappan <sathishkumar.m@innoppl.com>
 * @package Targetbay_Tracking
 */

class Targetbay_Tracking_Model_Subscriber extends Mage_Newsletter_Model_Subscriber
{
    const XML_PATH_CONFIRM_EMAIL_TEMPLATE = 'newsletter/subscription/confirm_email_template';
    const XML_PATH_CONFIRM_EMAIL_IDENTITY = 'newsletter/subscription/confirm_email_identity';
    const XML_PATH_SUCCESS_EMAIL_TEMPLATE = 'newsletter/subscription/success_email_template';
    const XML_PATH_SUCCESS_EMAIL_IDENTITY = 'newsletter/subscription/success_email_identity';
    const XML_PATH_UNSUBSCRIBE_EMAIL_TEMPLATE = 'newsletter/subscription/un_email_template';
    const XML_PATH_UNSUBSCRIBE_EMAIL_IDENTITY = 'newsletter/subscription/un_email_identity';

    /**
     * Sends out confirmation success email
     *
     * @return Mage_Newsletter_Model_Subscriber
     */
    public function sendConfirmationSuccessEmail()
    {
        $emailStatus = Mage::helper('tracking')->getEmailStatus();

        if ($emailStatus == 1) {
            return false;
        }

        if ($this->getImportMode()) {
            return $this;
        }

        if (!Mage::getStoreConfig(self::XML_PATH_SUCCESS_EMAIL_TEMPLATE)
            || !Mage::getStoreConfig(self::XML_PATH_SUCCESS_EMAIL_IDENTITY)
        ) {
            return $this;
        }

        $translate = Mage::getSingleton('core/translate');
        /* @var $translate Mage_Core_Model_Translate */
        $translate->setTranslateInline(false);

        $email = Mage::getModel('core/email_template');

        $email->sendTransactional(
            Mage::getStoreConfig(self::XML_PATH_SUCCESS_EMAIL_TEMPLATE),
            Mage::getStoreConfig(self::XML_PATH_SUCCESS_EMAIL_IDENTITY),
            $this->getEmail(),
            $this->getName(),
            array('subscriber' => $this)
        );

        $translate->setTranslateInline(true);

        return $this;
    }

    /**
     * Sends out unsubsciption email
     *
     * @return Mage_Newsletter_Model_Subscriber
     */
    public function sendUnsubscriptionEmail()
    {
        $emailStatus = Mage::helper('tracking')->getEmailStatus();

        if ($emailStatus == 1) {
            return false;
        }

        if ($this->getImportMode()) {
            return $this;
        }
        if (!Mage::getStoreConfig(self::XML_PATH_UNSUBSCRIBE_EMAIL_TEMPLATE)
            || !Mage::getStoreConfig(self::XML_PATH_UNSUBSCRIBE_EMAIL_IDENTITY)
        ) {
            return $this;
        }

        $translate = Mage::getSingleton('core/translate');
        /* @var $translate Mage_Core_Model_Translate */
        $translate->setTranslateInline(false);

        $email = Mage::getModel('core/email_template');

        $email->sendTransactional(
            Mage::getStoreConfig(self::XML_PATH_UNSUBSCRIBE_EMAIL_TEMPLATE),
            Mage::getStoreConfig(self::XML_PATH_UNSUBSCRIBE_EMAIL_IDENTITY),
            $this->getEmail(),
            $this->getName(),
            array('subscriber' => $this)
        );

        $translate->setTranslateInline(true);

        return $this;
    }
}

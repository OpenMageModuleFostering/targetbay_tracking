<?php

/*
 * @author Targetbay
 * @copyright 2016 Sathishkumar Mariappan <sathishkumar.m@innoppl.com>
 * @package Targetbay_Tracking
 */

class Targetbay_Tracking_Model_Api2_Orders_Rest_Admin_V1 extends Mage_Sales_Model_Api2_Order_Rest
{
    const ORDER_COMPLETE = 'complete';

    /**
     * Get orders list
     *
     * @return array
     */
    public function _retrieveCollection()
    {
        $collection = $this->_getCollectionForRetrieve();

        if ($this->_isPaymentMethodAllowed()) {
            $this->_addPaymentMethodInfo($collection);
        }
        if ($this->_isGiftMessageAllowed()) {
            $this->_addGiftMessageInfo($collection);
        }
        $this->_addTaxInfo($collection);

        $ordersData = array();

        foreach ($collection->getItems() as $order) {
            $ordersData [$order->getId()] = $order->toArray();
            $storeId = $order->getStoreId();
            $ordersData [$order->getId()]['store_id'] = $storeId;
            $ordersData [$order->getId()]['website_id'] = Mage::getModel('core/store')->load($storeId)->getWebsiteId();
            $ordersData [$order->getId()] ['payment_method'] = Mage::helper('tracking')->getPaymentInfo($order->getId(), true);
            $ordersData [$order->getId()] [Targetbay_Tracking_Helper_Data::BILLING] = $this->getUserAddressData($order, Targetbay_Tracking_Helper_Data::BILLING);
            if ($order->getShippingAddress()) {
                $ordersData [$order->getId()] [Targetbay_Tracking_Helper_Data::SHIPPING] = $this->getUserAddressData($order, Targetbay_Tracking_Helper_Data::SHIPPING);
                if ($order->getStatus() == self::ORDER_COMPLETE) {
                    //order shipped date
                    foreach ($order->getShipmentsCollection() as $shipment) {
                        /** @var $shipment Mage_Sales_Model_Order_Shipment */
                        //$shipmentDate = Mage::getModel('core/date')->timestamp(strtotime($shipment->getCreatedAt()));
                        //$ordersData[$order->getId()]['shipped_at'] = date('F j, Y g:i a', strtotime($actualDate . " UTC"));
						$ordersData[$order->getId()]['shipped_at']=$shipment->getCreatedAt();
                        $ordersData[$order->getId()]['timezone'] = Mage::getStoreConfig('general/locale/timezone');
                    }
                }
            } else {
                $ordersData [$order->getId()] [Targetbay_Tracking_Helper_Data::SHIPPING] = '';
            }
            $ordersData [$order->getId()] ['cart_items'] = Mage::helper('tracking')->getOrderItemsInfo($order, true);
        }
        return $ordersData;
    }

    public function getUserAddressData($object, $type)
    {
        $address = ($type == Targetbay_Tracking_Helper_Data::SHIPPING) ? $object->getShippingAddress() : $object->getBillingAddress();
        $addressData['first_name'] = $address->getFirstname();
        $addressData['last_name'] = $address->getLastname();
        $guestUsername = $address->getFirstname() . ' ' . $address->getLastname();
        $gName = !empty($guestUsername) ? $guestUsername : Targetbay_Tracking_Helper_Data::ANONYMOUS_USER;
        $addressData['user_name'] = $object->getCustomerIsGuest() ? $gName : $addressData['first_name'] . ' ' . $addressData['last_name'];
        $addressData['order_id'] = $object->getId();
        $addressData['user_mail'] = $object->getCustomerEmail();
        $addressData['address1'] = $address->getStreet(1);
        $addressData['address2'] = $address->getStreet(2);
        $addressData['city'] = $address->getCity();
        $addressData['state'] = $address->getRegion();
        $addressData['zipcode'] = $address->getPostcode();
        if ($address->getCountryId()) {
            $countryName = Mage::getModel('directory/country')->loadByCode($address->getCountryId())->getName();
        } else {
            $countryName = '';
        }
        $addressData['country'] = isset($countryName) ? $countryName : $address->getCountryId();
        $addressData['phone'] = $address->getTelephone();
        if (!empty($addressData)) {
            return $addressData;
        }
    }
}

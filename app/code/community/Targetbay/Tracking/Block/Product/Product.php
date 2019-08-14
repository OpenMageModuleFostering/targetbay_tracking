<?php
/*
 * @author Targetbay
 * @copyright 2016 Sathishkumar Mariappan <sathishkumar.m@innoppl.com>
 * @package Targetbay_Tracking
 */
if(class_exists("TM_RichSnippets_Block_Product", true)) {
class Targetbay_Tracking_Block_Product_Product extends TM_RichSnippets_Block_Product
{
    /*JSON code*/

    public function getJsonSnippetsProduct()
    {
	$data = array(
	    '@context'              => 'http://schema.org',
	    '@type'                 => 'Product',
	    'name'                  => $this->getProduct()->getName(),
	    'image'                 => (string)Mage::helper('catalog/image')->init($this->getProduct(), 'image'),
	    'description'           => $this->getProduct()->getShortDescription(),
	    'sku'                   => $this->getProduct()->getSku(),
	    'offers'                => array(
	        '@type'             => 'Offer',
	        'availability'      => $this->getStockStatusUrl(),
	        'price'             => $this->getPriceValues(),
	        'priceCurrency'     => Mage::app()->getStore()->getCurrentCurrency()->getCode()
	    )
	);

	$aggregateRating = Mage::helper('tracking')->getRichSnippets();
	if($aggregateRating['average_score'] != 0) {
	    $data['aggregateRating']['@type'] = 'AggregateRating';
	    $data['aggregateRating']['bestRating'] = '100';
	    $data['aggregateRating']['ratingValue'] = $aggregateRating['average_score'];
	    $data['aggregateRating']['reviewCount'] = $aggregateRating['reviews_count'];
	    $data['aggregateRating']['ratingCount'] = $aggregateRating['reviews_count'];
	}

	if (is_array($this->getPriceValues())) {
	    unset($data['offers']['price']);

	    $getPriceValues = $this->getPriceValues();
	    $data['offers']['@type'] = 'AggregateOffer';
	    $data['offers']['lowPrice'] = $getPriceValues[0];
	    $data['offers']['highPrice'] = $getPriceValues[1];
	    
	}

	return Mage::helper('core')->jsonEncode($data);
    }
}
}

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

		$richSnippets = Mage::helper('tracking')->getRichSnippets();
		if($richSnippets['average_score'] != 0) {
		    $data['aggregateRating']['@type'] = 'AggregateRating';
		    $data['aggregateRating']['bestRating'] = 5;
		    $data['aggregateRating']['worstRating'] = 1;
		    $data['aggregateRating']['ratingValue'] = $richSnippets['average_score'];
		    $data['aggregateRating']['reviewCount'] = $richSnippets['reviews_count'];
		    $data['aggregateRating']['ratingCount'] = $richSnippets['reviews_count'];
		}

		if($richSnippets['reviews_count'] > 0)	{
			foreach($richSnippets['reviews']  as $key => $aggregateReviewDetails) {
			    $reviewId = $aggregateReviewDetails->_id;
			    $reviewTitle = $aggregateReviewDetails->_source->review_title;
			    $review = $aggregateReviewDetails->_source->review;
			    $timestamp = $aggregateReviewDetails->_source->timestamp;
			    $reviewRating = $aggregateReviewDetails->_source->review_rating;
			    $userName = $aggregateReviewDetails->_source->user_name;
				
			    $reviewData['@type'] = 'Review';
			    $reviewData['name'] = $reviewTitle;
			    $reviewData['description'] = $review;
			    $reviewData['datePublished'] = date('m/d/Y', $timestamp);
			    $reviewData['ratingCount'] = $aggregateRating['reviews_count'];
			    $reviewData['author'] = $userName;
			    $revRating['@type'] = 'Rating';
			    $revRating['ratingValue'] = $reviewRating;
			    $revRating['worstRating'] = 1;
			    $revRating['bestRating'] = 5;
			    $reviewData['reviewRating'] = $revRating;
			    $data['review'][] = $reviewData;
			}
		}

		/*$questionSnippets = Mage::helper('tracking')->getQuestionSnippets();

		if(!empty($questionSnippets) && $questionSnippets['qa_count'] > 0)	{
			$authorName = $questionSnippets['qa_author'];
			$i = 1;
			foreach($questionSnippets['qa_details']  as $key => $qasDetails) {
			    $qasId = $qasDetails->_id;
				$productName = $qasDetails->_source->product_name;
				$questionName = $qasDetails->_source->questions;
				$qasCreatedDate = $qasDetails->_source->timestamp;
				$qasUsername = $qasDetails->_source->user_name;
				$answerArray = $qasDetails->_source->question_answers;
				
			    $data['@type'] = 'Question';
			    $data['name'] = $productName;
			    $data['text'] = $questionName;
			    $data['dateCreated'] = date('m/d/Y', $qasCreatedDate);
			    $data['author']['@type'] = 'Person';
			    $data['author']['name'] = $qasUsername;
			    $data['answerCount'] = count($answerArray);
			    if(count($answerArray) > 0) {
				    $acceptedAnswer['@type'] = 'Answer';
				    $acceptedAnswer['upvoteCount'] = $answerArray[0]->upvotes;
				    $acceptedAnswer['text'] = $answerArray[0]->answers;
				    $acceptedAnswer['dateCreated'] = date('m/d/Y', $answerArray[0]->answer_timestamp);
				    $acceptedAnswer['author']['@type'] = 'Person';
				    $acceptedAnswer['author']['name'] = $authorName;
				    $data['acceptedAnswer'] = $acceptedAnswer;
				    foreach($answerArray  as $key => $answers) {				    	
					    $suggestedAnswer[$i]['@type'] = 'Answer';
					    $suggestedAnswer[$i]['upvoteCount'] = $answerArray->upvotes;
					    $suggestedAnswer[$i]['text'] = $answerArray->answers;
					    $suggestedAnswer[$i]['dateCreated'] = date('m/d/Y', $answerArray->answer_timestamp);
					    $suggestedAnswer[$i]['author']['@type'] = 'Person';
					    $suggestedAnswer[$i]['author']['name'] = $authorName;
					    $data['suggestedAnswer'] = $suggestedAnswer;
				    }
				}
			}
		}*/

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

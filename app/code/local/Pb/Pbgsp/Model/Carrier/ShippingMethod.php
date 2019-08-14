<?php

/**
 * Product:       Pb_Pbgsp (1.3.0)
 * Packaged:      2015-11-12T06:33:00+00:00
 * Last Modified: 2015-11-04T12:13:20+00:00





 * File:          app/code/local/Pb/Pbgsp/Model/Carrier/ShippingMethod.php
 * Copyright:     Copyright (c) 2015 Pitney Bowes <info@pb.com> / All rights reserved.
 */
class Pb_Pbgsp_Model_Carrier_ShippingMethod extends Mage_Shipping_Model_Carrier_Abstract
{
  /**
   * unique internal shipping method identifier
   *
   * @var string [a-z0-9_]
   */
  protected $_code = 'pbgsp';
  
  public function getConfigData($field) {
  	  /* BigPixel 3/25/2012. hide errors for not applicable countries */

	 return parent::getConfigData($field);
  }

    /**
     * @param $result
     * @param $errors
     * Adds custom error messages to $result
     */
    public function displayErrors($result,$quote) {
        $processor = new Pb_Pbgsp_Model_Messages();
        $isUnitErrorAdded = false;

        if(array_key_exists('quoteLines',$quote)) {
            foreach($quote['quoteLines'] as $quoteLine) {
                if(array_key_exists('unitErrors',$quoteLine)) {
                    foreach($quoteLine['unitErrors'] as $error) {
                        $message = '';
                        if(array_key_exists('message',$error))
                            $message = $error['message'];
                        $message = $processor->getDisplayMessage($error["error"],$message);//.$sku;
                        $sku = $quoteLine['merchantComRefId'];
                        $product = Mage::getModel('catalog/product')->loadByAttribute('sku',$sku);
                        $message = $message . " Please remove ". $product->getName() . " from cart.";
                        $this->_addError($result,$message);
                        $isUnitErrorAdded = true;
                    }

                }

            }
        }

        if(!$isUnitErrorAdded) {
            foreach($quote['errors'] as $error) {
                $message = '';
                if(array_key_exists('message',$error))
                    $message = $error['message'];
                $message = $processor->getDisplayMessage($error["error"],$message);//.$sku;
                $this->_addError($result,$message);
            }
        }

  }

    private function _addError($result,$message) {
        $error = Mage::getModel('shipping/rate_result_error');
        $error->setCarrier('pbgsp');
        $error->setCarrierTitle("Pitney Bowes");
        $error->setErrorMessage($message);
        $result->append($error);
        Pb_Pbgsp_Model_Util::log("Added error $message");
    }

    /**
     * @param $index
     * @return string
     */
    private function getSku($index){
      $items = Mage::getSingleton('checkout/cart')->getItems();
      $i = 0;
      foreach ($items as $item):
          if($i++==$index):
              return $item->getSku();
          endif;
      endforeach;
      return '';
  }

    /**
     * @param $index
     * @return mixed
     */
    public function getRestrictId($index){
      $sku = $this->getSku($index);
      $id =  Mage::getModel('catalog/product')->getIdBySku($sku);
      return $id;
  }

    private function _getShipMethodFromQuote($quote,$items) {
        $handlingFee = Pb_Pbgsp_Model_Credentials::getHandlingFee();

        $handlingoption = Pb_Pbgsp_Model_Credentials::getHandlingOption();
        $domesticShippingFee = Pb_Pbgsp_Model_Credentials::getDomesticShippingFee();
        $domesticShippingOption = Pb_Pbgsp_Model_Credentials::getDomesticShippingOption();

        $deliveryAdjMinDays = Pb_Pbgsp_Model_Credentials::getDeliveryAdjustmentMinDays();
        $deliveryAdjMixDays = Pb_Pbgsp_Model_Credentials::getDeliveryAdjustmentMaxDays();
        $totalQty = 0;
        foreach($items as $item) {
            $totalQty += $item->getQty();
        }
        if(isset($handlingoption) && $handlingoption == "2") { //apply as per item
            $handlingFee = $handlingFee * $totalQty;
        }
        if(isset($domesticShippingOption) && $domesticShippingOption == "2") { //apply as per item
            $domesticShippingFee = $domesticShippingFee * $totalQty;
        }
        $transportation = $quote['totalTransportation'];
        // create new instance of method rate
        $method = Mage::getModel('shipping/rate_result_method');

        // record carrier information
        $method->setCarrier($this->_code);

        // get config info
        $vv = Mage::getStoreConfig('carriers/pbgsp/title');
        $method->setCarrierTitle($vv);

        // record method information
        $title = $transportation['speed']." - ".(intval($transportation['minDays']) + $deliveryAdjMinDays)."-".(intval($transportation['maxDays']) + $deliveryAdjMixDays )." days";
        $method->setMethod($transportation['speed']);
        $method->setMethodTitle($title);

        // rate cost is optional property to record how much it costs to vendor to ship
        $method->setCost($transportation['shipping']['value']);
        $method->setPrice((floatval($transportation['total']['value']) + $handlingFee + $domesticShippingFee));

        if(Pb_Pbgsp_Model_Credentials::isFreeTaxEnabled()) {
            Pb_Pbgsp_Model_Util::log('Free tax enabled');
            $method->setTax(0);
        }

        else {
            Pb_Pbgsp_Model_Util::log('Free tax disabled');
            $tax = $quote['totalImportation']['total']['value'];
            Mage::getSingleton("customer/session")->setPbDutyAndTaxUSD($tax);
            if(Mage::app()->getStore()->getCurrentCurrencyCode() != 'USD') {

                $tax = Mage::app()->getStore()->convertPrice($tax);

            }
            Mage::getSingleton("customer/session")->setPbDutyAndTax($tax);
            $method->setTax($tax);
        }
        Pb_Pbgsp_Model_Util::log($method);
        return $method;
    }
    private function _getCheapestShipMethod($quoteSet,$items)
    {
        $minPrice = -1;
        $minQuote = null;
        foreach ($quoteSet['quote'] as $quote) {

            if (array_key_exists('totalTransportation', $quote)) {
                $transportation = $quote['totalTransportation'];
                $cost = floatval($transportation['total']['value']);
                if($minPrice == -1 || $minPrice > $cost) {
                    $minPrice = $cost;
                    $minQuote = $quote;

                }
            }

        }
        return $this->_getShipMethodFromQuote($minQuote,$items);
    }
    /**
     * Collect rates for this shipping method based on information in $request
     *
     * @param Mage_Shipping_Model_Rate_Request $data
     * @return Mage_Shipping_Model_Rate_Result
     */
  public function collectRates(Mage_Shipping_Model_Rate_Request $request)
  {
      Mage::getSingleton("customer/session")->setPbDutyAndTax(false);

      Mage::getSingleton("customer/session")->setPbDutyAndTaxUSD(false);
  	//Pb_Pbgsp_Model_Util::log('Pb_Pbgsp_Model_Carrier_ShippingMethod.collectRates ' . Mage::getStoreConfig('carriers/'.$this->_code.'/active'));
    // skip if not enabled
    if (!Mage::getStoreConfig('carriers/'.$this->_code.'/active')) {
        return false;
    }
        
    // skip if the store is not located in the US
      Pb_Pbgsp_Model_Util::log($request->country_id);
    if ($request->country_id != "US") {
	    return false;
    }
 
    // get necessary configuration values

 
    // this object will be returned as result of this method
    // containing all the shipping rates of this method
    $result = Mage::getModel('shipping/rate_result');
    
    $items = Mage::getSingleton('checkout/cart')->getItems();    
    $address = Mage::getSingleton('checkout/cart')->getQuote()->getShippingAddress();

    if(!$items || count($items) == 0)
        $items = $request->getAllItems();
    if(!$address->getCountryId()) {
        $address->setCountryId($request->getDestCountryId());
        $address->setCity($request->getDestCity());
        $address->setStreet($request->getDestStreet());
        $address->setRegion($request->getDestRegionCode());
        $address->setPostcode($request->getDestPostcode());
        $address->setEmail('test@bigpixelstudio.com');
        $address->setFirstname('firstname');
        $address->setLastname('lastname');
        $address->setTelephone('1231231234');

    }

	// Clear all restrictions from previous calls
	Mage::getSingleton("customer/session")->setPbRestrictions(null);
      $allowSpecific = Mage::getStoreConfig('carriers/'.$this->_code .'/sallowspecific');
      if($allowSpecific) {
          $activeCountries = Mage::getStoreConfig('carriers/'.$this->_code .'/specificcountry');

          if(!(strpos($activeCountries,$address->getCountryId()) !== false)) {
              Pb_Pbgsp_Model_Util::log($address->getCountryId().' not found');
              return false;
          }
      }


	// NOTE: Insert calls to Magento Domestic Shipping costs here. Pass the newly calculated
	// domestic shipping costs to the API calls to get a proper full quote.

    
    if (!$address->getStreet1() || !$address->getCity()) {
            $address->setFirstname('firstname');
            $address->setLastname('lastname');
            $address->setCity('city');
            $address->setTelephone('phone');
            $address->setEmail('email@email.com');
            $address->setStreet(array('street'));
    }

      try {

          $shipMethodAvailable = false;
          $quoteSet = Pb_Pbgsp_Model_Api::getQuote($items,  $address);
          $freeShippingEnabled = Mage::getStoreConfig('carriers/pbgsp/free_shipping_enable');
          $freeShippingSubTotal = Mage::getStoreConfig('carriers/pbgsp/free_shipping_subtotal');

          $totalOrderPrice = $this->getTotalOrderPrice($items);
          $shipMethods = array();
          foreach($quoteSet['quote'] as $quote) {
              if(array_key_exists('errors',$quote) && count($quote['errors']) > 0) {
                  $this->displayErrors($result,$quote);
              }
              else if(array_key_exists('totalTransportation',$quote)) {
                  if($freeShippingEnabled && $totalOrderPrice >= $freeShippingSubTotal) {
                      //get cheapest shipping method from quoteSet
                      $method = $this->_getCheapestShipMethod($quoteSet,$items);
                      $method->setCost(0);
                      $method->setPrice(0);
                      //$method->setTax(0); //tax should be included in free shipping
                      $result->append($method);
                      array_push($shipMethods,$method);
                      break;
                  }
                  else {
                      $method = $this->_getShipMethodFromQuote($quote,$items);

                      // add this rate to the result
                      $result->append($method);
                      array_push($shipMethods,$method);
                  }


                  $shipMethodAvailable = true;
              }
          }
          if($shipMethodAvailable) {
              Mage::getSingleton('checkout/session')->setPbMethods($shipMethods);
          }
      }
      catch(Exception $e) {
          Pb_Pbgsp_Model_Util::log("Error getting quotes");
          Pb_Pbgsp_Model_Util::logException($e);
          //$message = $e->getMessage();
          //if($e->getMessage() == '')
              $message = "We've received an unexpected error while getting your quote. Please try again. If the error persists contact magentosupport@pb.com.";
          $this->_addError($result,$message);
          Mage::getSingleton("customer/session")->setPbDutyAndTax(false);

          Mage::getSingleton("customer/session")->setPbDutyAndTaxUSD(false);

      }
      return $result;


  }

    private function getTotalOrderPrice($products)
    {
        $totalOrderPrice = 0;
        foreach ($products as $product) {
            if ((!$product->getParentItem() && $product->getRealProductType() != Mage_Catalog_Model_Product_Type::TYPE_BUNDLE) || ($product->getParentItem() && $product->isChildrenCalculated())) {
                $totalOrderPrice += $product->getPrice() * $product->getQty();

            }
        }
        return $totalOrderPrice;
    }
}
?>

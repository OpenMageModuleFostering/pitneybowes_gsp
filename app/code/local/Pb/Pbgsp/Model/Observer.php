<?php
/**
 * Product:       Pb_Pbgsp (1.3.7)
 * Packaged:      2016-06-01T14:02:28+00:00
 * Last Modified: 2016-04-14T14:05:10+00:00
 * File:          app/code/local/Pb/Pbgsp/Model/Observer.php
 * Copyright:     Copyright (c) 2016 Pitney Bowes <info@pb.com> / All rights reserved.
 */
class Pb_Pbgsp_Model_Observer {
 const MODULE_NAME = 'Pb_Pbgsp';
	public function __construct() {
	}
	
	public function isPbOrder($address) {
		$shipMethod = $address->getShippingMethod();
        Pb_Pbgsp_Model_Util::log("Shipping method". $shipMethod);
		$len = strlen("pbgsp_");
		if (strlen($shipMethod) > $len && substr($shipMethod,0,$len) == "pbgsp_") {
			return true;
		}

		return false;

	}

    public function productLoadAfter($observer) {
        $event = $observer->getEvent();
        $product = $event->getProduct();
        $product->lockAttribute('pb_pbgsp_upload');
    }
    public function categoryLoadAfter($observer) {
        $event = $observer->getEvent();
        $category = $event->getCategory();
        $category->lockAttribute('pb_pbgsp_upload');
    }
	public function getShipMethod($observer) {
        Pb_Pbgsp_Model_Util::log('Pb_Pbgsp_Model_Observer.getShipMethod');
		$shipMethod = $observer->getQuote()->getShippingAddress()->getShippingMethod();

		return substr($shipMethod,strlen("pbgsp_"));
	}
    public function salesOrderShipmentTrackSaveAfter($observer) {

        if(Pb_Pbgsp_Model_Credentials::isASNGenerationEnabled() == '1' && Pb_Pbgsp_Model_Credentials::isGenerateAsnOnAddTrack() == '1') {
            Pb_Pbgsp_Model_Util::log('salesOrderShipmentTrackSaveAfter');
            $track = $observer->getTrack();
            Pb_Pbgsp_Model_Util::log($track->getNumber());
            $shipment = $track->getShipment();
            $this->generateASN($shipment);
        }


    }
    private function generateASN($shipment) {
        try {

            $order = $shipment->getOrder();
            $shipId = $shipment['entity_id'];

            /* @var $order Mage_Sales_Model_Order */

            if(!$this->isPbOrder($order))
                return;
            //check if ASN already generated or not
            $parcel = Mage::getModel("pb_pbgsp/inboundparcel")-> getCollection();
            $parcel -> addFieldToFilter('mage_order_number', $order -> getRealOrderId());
            $parcel->addFieldToFilter('mage_order_shipment_number',$shipId);

            if(count($parcel) > 0)
                return;
            Pb_Pbgsp_Model_Util::log("Generting ASN.");
            $clearPathOrders = Mage::getModel("pb_pbgsp/ordernumber")-> getCollection();
            $clearPathOrders -> addFieldToFilter('mage_order_number', $order -> getRealOrderId());
            foreach ($clearPathOrders as $cpOrder) {
                $cpOrderNumber = $cpOrder -> getCpOrderNumber();
                $tracks = array();
                $items = array();
                foreach($shipment->getTracksCollection() as $track) {
                    $tracks[] = $track;
                }
                foreach($shipment->getItemsCollection() as $item) {

                    $items[] = $item;

                }

                $parcelResponse = Pb_Pbgsp_Model_Api::generateInboundParcelNumber($shipment,$items,$order,$cpOrderNumber);

                if(array_key_exists('errors',$parcelResponse)) {
                    Pb_Pbgsp_Model_Util::log("Error generating inbound parcel");
                    Pb_Pbgsp_Model_Util::log($parcelResponse);
                }
                else {

                    //Save entry in table
                    $cpParcel = Mage::getModel('pb_pbgsp/inboundparcel');
                    $cpParcel->setInboundParcel($parcelResponse['parcelIdentifier']);
                    $cpParcel->setMageOrderNumber( $order->getRealOrderId());
                    $cpParcel->setPbOrderNumber( $cpOrderNumber);
                    $cpParcel->setMageOrderShipmentNumber( $shipId);
                    $cpParcel->save();

                    // add the tracking info magento
                    $track = Mage::getModel('sales/order_shipment_track')
                        ->setShipment($shipment)
                        ->setData('title', 'PB')
                        ->setData('number',$parcelResponse['parcelIdentifier'])
                        ->setData('carrier_code', 'pbgsp')
                        ->setData('description',$cpOrderNumber)
                        ->setData('order_id', $shipment->getData('order_id'))
                        ->save();

                    Pb_Pbgsp_Model_Util::log('Inbound Parcel Number Saved');
                }

            }


        }
        catch(Exception $e) {
            Pb_Pbgsp_Model_Util::log("Error creating inbound parcel. ");
            Pb_Pbgsp_Model_Util::logException($e);
        }
    }
    public function generateInboundParcelPreAdvice($observer) {

        if(Pb_Pbgsp_Model_Credentials::isASNGenerationEnabled() == '1' && Pb_Pbgsp_Model_Credentials::isGenerateAsnOnAddTrack() != '1') {
            $shipment = $observer->getEvent()->getShipment();
            $this->generateASN($shipment);
        }

    }
	public function createPbOrder($observer) {
        Pb_Pbgsp_Model_Util::log('Pb_Pbgsp_Model_Observer.createPbOrder');
		Mage::getSingleton("customer/session")->setPbDutyAndTax(0);
		$mageOrderNumber = Mage::getSingleton('checkout/session')->getLastRealOrderId();
		$order = Mage::getModel('sales/order')->loadByIncrementId($mageOrderNumber);
        Pb_Pbgsp_Model_Util::log(" createPbOrder");
		if ($this->isPbOrder($order)) {
//            $clearPathOrders = Mage::getModel("pb_pbgsp/ordernumber")-> getCollection();
//            $clearPathOrders -> addFieldToFilter('mage_order_number', $order -> getRealOrderId());
//            $orderNumber = null;
//            foreach ($clearPathOrders as $cpOrder) {
//                $orderNumber = $cpOrder;
//
//            }
//            if(!$orderNumber) {
//                Pb_Pbgsp_Model_Util::log("$mageOrderNumber not PB order");
//                return;
//            }

            $orderNumber = Mage::getSingleton("customer/session")->getPbOrderNumber();
            //$orderNumber = Mage::getSingleton("customer/session")->getPbOrderNumber();
			
			// Save in DB

			$orderNumber->setMageOrderNumber($mageOrderNumber);
            //$orderNumber->setCpOrderNumber($cpOrderNumber);
            Pb_Pbgsp_Model_Util::log($orderNumber->getHubId());
            Pb_Pbgsp_Model_Util::log($orderNumber->getHubCountry());
			$orderNumber->setConfirmed(false);
			$orderNumber->setReferenced(false);
			$orderNumber->save();
			Pb_Pbgsp_Model_Util::log("calling PB confirm order for $mageOrderNumber");
			if (Pb_Pbgsp_Model_Api::confirmOrder($orderNumber->getCpOrderNumber(),$order)) {
				$orderNumber->setConfirmed(true);
                $orderNumber->setReferenced(true);
				$orderNumber->save();
				
				/* Update order shipping address in Magento 
				   Added by: Sudarshan
				   Date: 25/09/2015
				
				*/
				if(Pb_Pbgsp_Model_Credentials::isOverrideShippingAddressEnabled() == '1') {	
					try{
						$shippingAddress = Mage::getModel('sales/order_address')->load($order->getShippingAddress()->getId());
						
						$shippingAddress
						->setStreet(array($orderNumber->getHubStreet1(),$orderNumber->getHubStreet2()))
						->setCity($orderNumber->getHubCity())
						->setCountry_id($orderNumber->getHubCountry())
						->setRegion($orderNumber->getHubProvinceOrState())
						->setPostcode($orderNumber->getHubPostalCode())->save();
					}
					catch(Exception $e) {
						Pb_Pbgsp_Model_Util::log("Error updating shipping address in magento");
						Pb_Pbgsp_Model_Util::logException($e);
					}
					
				}
				
                Pb_Pbgsp_Model_Util::log(" $mageOrderNumber order is confirmed in PB");
			}
//			if (Pb_Pbgsp_Model_Api::setOrderReference($cpOrderNumber,$mageOrderNumber)) {
//				$orderNumber->setReferenced(true);
//				$orderNumber->save();
//			}
		}
        else {
            Pb_Pbgsp_Model_Util::log("$mageOrderNumber not PB order");
        }

	}
	
	/* This observer method get called on cancel order event in Magento
	   Created by: Sudarshan
	   Date: 02/09/2015
	  
	*/
	public function cancelPbOrder($observer) {
      
		$mageOrderNumber = $observer->getEvent()->getOrder()->getId();
		$order = $observer->getEvent()->getOrder();
				
		if ($this->isPbOrder($order)) {
          
			$clearPathOrders = Mage::getModel("pb_pbgsp/ordernumber")-> getCollection();
            $clearPathOrders -> addFieldToFilter('mage_order_number', $order -> getRealOrderId());
            foreach ($clearPathOrders as $cpOrder) {
				$cpOrderNumber = $cpOrder -> getCpOrderNumber();
				if (Pb_Pbgsp_Model_Api::cancelOrder($cpOrderNumber)) {
					
						Pb_Pbgsp_Model_Util::log(" $mageOrderNumber order is cancel in PB");
						Mage::getSingleton('core/session')->addSuccess('PB order cancel successfully');
				}  
			}
		}
        else {
            Pb_Pbgsp_Model_Util::log("$mageOrderNumber not PB order");
        }

	}
	
	public function saveShippingMethod($observer) {
        Pb_Pbgsp_Model_Util::log('Pb_Pbgsp_Model_Observer.saveShippingMethod');

		//TODO: If anything fails here I need to fail the checkout process.
		$address = $observer->getQuote()->getShippingAddress();
        Pb_Pbgsp_Model_Util::log($address->getShippingMethod());
        Pb_Pbgsp_Model_Util::log($this->getShipMethod($observer));
		$domesticShippingAdress = $address->getName()."</br>".$address->getStreetFull().", ".$address->getCity()."</br> ".$address->getRegion().", ".$address->getCountry().",".$address->getPostcode()."</br> T:".$address->getTelephone();
		if ($this->isPbOrder($address)) {
            Pb_Pbgsp_Model_Util::log(" PB order");
			$items = Mage::getSingleton('checkout/cart')->getItems(); 
			$shipMethod = $this->getShipMethod($observer);
			$order = Pb_Pbgsp_Model_Api::createOrder($items,$shipMethod,$address);
			if (!$order) {
				Mage::throwException("Unable to create Pb order.");
			}
            Mage::getSingleton("customer/session")->setPbOrder($order);
            $tax = $order['order']['totalImportation']['total']['value'];

            if(Pb_Pbgsp_Model_Credentials::isFreeTaxEnabled())
                $tax = 0;
            else {
                //set the tax for each item in quote
//                foreach($address->getAllItems() as $item) {
//                    /* @var Mage_Sales_Model_Quote_Item $item */
//                    foreach($order['order']['quoteLines'] as $quoteLine) {
//                        $sku = $quoteLine['merchantComRefId'];
//                        if($item->getSku() == $sku) {
//                            $itemBaseTax = $quoteLine['unitImportation']['total']['value'];
//                            $item->setBaseTaxAmount(floatval($itemBaseTax));
//                            $itemTax = $itemBaseTax;
//                            if(Mage::app()->getStore()->getCurrentCurrencyCode() != 'USD') {
//
//                                $itemTax = Mage::app()->getStore()->convertPrice($itemBaseTax);
//
//                            }
//                            $item->setTaxAmount($itemTax);
//                            $item->save();
//                        }
//                    }
//                    $address->save();
//                }
            }
            Mage::getSingleton("customer/session")->setPbDutyAndTaxUSD($tax);
            if(Mage::app()->getStore()->getCurrentCurrencyCode() != 'USD') {

                $tax = Mage::app()->getStore()->convertPrice($tax);

            }
            Mage::getSingleton("customer/session")->setPbDutyAndTax($tax);

            $orderNumber = Mage::getModel("pb_pbgsp/ordernumber");

            $orderNumber->setCpOrderNumber($order["orderId"]);
            $orderNumber->setHubId($order["shipToHub"]['hubId']);
            $orderNumber->setHubStreet1($order["shipToHub"]['hubAddress']['street1']);
            $orderNumber->setHubStreet2($order["shipToHub"]['hubAddress']['street2']);

            $orderNumber->setHubProvinceOrState($order["shipToHub"]['hubAddress']['provinceOrState']);
            $orderNumber->setHubCountry($order["shipToHub"]['hubAddress']['country']);
            $orderNumber->setHubPostalCode($order["shipToHub"]['hubAddress']['postalOrZipCode']);
            $orderNumber->setHubCity($order["shipToHub"]['hubAddress']['city']);
			$orderNumber->setOriginalShippingAddress($domesticShippingAdress);
            $orderNumber->save();
			Mage::getSingleton("customer/session")->setPbOrderNumber($orderNumber);
		} else {
            Pb_Pbgsp_Model_Util::log(" not clearpath order");
			Mage::getSingleton("customer/session")->setPbDutyAndTax(0);
			Mage::getSingleton("customer/session")->setPbOrderNumber(0);
            Mage::getSingleton("customer/session")->setPbDutyAndTaxUSD(0);
            Mage::getSingleton("customer/session")->setPbOrder(0);
		}
	}


    public function addDutiesOnEstimation($observer){
        Pb_Pbgsp_Model_Util::log('Pb_Pbgsp_Model_Observer.addDutiesOnEstimation');
        Mage::getSingleton("customer/session")->setPbDutyAndTax(0);//Added by BigPixel to clear previous values, 10/20/2013
        $clearPathMethods = Mage::getSingleton('checkout/session')->getPbMethods();
        $quote = Mage::getSingleton('checkout/session')->getQuote();
        $selectedMethod = null;
        if($quote->getShippingAddress()){
            $selectedMethod = $quote->getShippingAddress()->getShippingMethod();
        }

        if($selectedMethod){
            $selectedMethod = preg_replace('/pbgsp_/', '',$selectedMethod);
            foreach($clearPathMethods as $clearPathMethod){
                Pb_Pbgsp_Model_Util::log('addDutiesOnEstimation method'.$clearPathMethod->getMethod().' and tax'.$clearPathMethod->getTax());
               if($clearPathMethod->getMethod() == $selectedMethod){
                 Mage::getSingleton("customer/session")->setPbDutyAndTax($clearPathMethod->getTax());
               }
            }
        }
    }

    public function modifyOrderView($observer = NULL) {

        //return;
        if (!$observer) {
            return;
        }
        if (Mage::getStoreConfig('advanced/modules_disable_output/'.self::MODULE_NAME))
            return;
        $transport = $observer->getEvent()->getTransport();
        $layoutName = $observer->getEvent()->getBlock()->getNameInLayout();
        if ('order_info' == $layoutName) {

            if (!Mage::getStoreConfig('advanced/modules_disable_output/'.self::MODULE_NAME)) {


                $cpOrderNumber = $this->_getCpOrderNumber($observer->getEvent()->getBlock()->getOrder());
                if($cpOrderNumber) {
                    $html = "<div class='entry-edit'>
                        <div class='entry-edit-head'>
                            <h4 class='icon-head '>Pitney Bowes Shipments</h4>
                        </div>
                        <fieldset>
                           <strong>PB Order Number</strong>
                             <span>".$cpOrderNumber->getCpOrderNumber()."</span><br/>
                              <strong>Hub ID</strong>
                              <span>".$cpOrderNumber->getHubId()."</span><br/>
                              <strong>Hub Street 1</strong>
                              <span>".$cpOrderNumber->getHubStreet1()."</span><br/>
                              <strong>Hub Street 2</strong>
                              <span>".$cpOrderNumber->getHubStreet2()."</span><br/>

                              <strong>Postal Code</strong>
                              <span>".$cpOrderNumber->getHubPostalCode()."</span><br/>
                               <strong>Hub Province/State</strong>
                              <span>".$cpOrderNumber->getHubProvinceOrState()."</span><br/>
                              <strong>Hub City</strong>
                              <span>".$cpOrderNumber->getHubCity()."</span><br/>
                              <strong>Hub Country</strong>
                              <span>".$cpOrderNumber->getHubCountry()."</span><br/>
							  <strong>Original Shipping Address</strong>
                              <span>".$cpOrderNumber->getOriginalShippingAddress()."</span><br/>
                            </fieldset>
                        </div>";
                    $transport['html'] = $transport['html'] . $html;
                }


            }
        }
        else if('email/order/shipment/track.phtml' == $observer->getEvent()->getBlock()->getTemplate()) {
            if(!Mage::getStoreConfig('carriers/pbgsp/trackinglink'))
                return;

            $cpord = $this->_getCPORD($observer->getEvent()->getBlock()->getOrder());
            if($cpord) {
                $staging = 0;
                if(strpos(Pb_Pbgsp_Model_Credentials::getCheckoutUrl(),'cpsandbox'))
                    $staging = 1;
				
				
                $transport['html'] = "<a href='http://tracking.ecommerce.pb.com/track/$cpord?staging=$staging'>Track your order</a>";
            }

        }
//        else if('shipping.tracking.popup' == $layoutName) {
//            if(!Mage::getStoreConfig('carriers/pbgsp/trackinglink'))
//                return;
//
//            $helper = Mage::helper('shipping');
//            $data = $helper->decodeTrackingHash($observer->getEvent()->getBlock()->getRequest()->getParam('hash'));
//
//            $orderId = null;
//            if($data['key'] == 'order_id')
//                $orderId = $data['id'];
//            else if($data['key'] == 'ship_id') {
//                /* @var $model Mage_Sales_Model_Order_Shipment */
//                $model = Mage::getModel('sales/order_shipment');
//                $ship = $model->load($data['id']);
//                $orderId = $model->getOrderId();
//            }
//            else if($data['key'] == 'track_id') {
//                $track = Mage::getModel('sales/order_shipment_track')->load($data['id']);
//                $orderId = $track->getOrderId();
//            }
//            if(!$orderId)
//                return;
//            $cpord = $this->_getCPORD(Mage::getModel('sales/order')->load($orderId));
//            if($cpord) {
//                if(Mage::getStoreConfig('carriers/pbgsp/suppress_domestic_tracking') == '1') {
//                    $staging = 0;
//                    if(strpos(Pb_Pbgsp_Model_Credentials::getCheckoutUrl(),'cpsandbox'))
//                        $staging = 1;
//
//                    $script = "<script lang='javascript'>
//                                window.location = 'http://tracking.ecommerce.pb.com/track/$cpord?staging=$staging';
//                               </script>
//                            ";
//                    $transport['html'] = $script;
//                }
//
//            }
//
//        }
        else
//            if( 'checkout.onepage.review' == $layoutName
//            || 'checkout.onepage.review.info.totals' == $layoutName
//            || 'checkout.cart.totals' == $layoutName)
        {
            //replace "Shipping & Handling" to "Transportation Charges" and "Duty & Taxes" to "Importation Charges"
            $html = $transport['html'];
            $pbTitle = Mage::getStoreConfig('carriers/pbgsp/title');
            if(strpos($html,$pbTitle) !== false) {
                $html = str_replace("Shipping &amp; Handling","Transportation Charges",$html);
                $html = str_replace("Shipping & Handling","Transportation Charges",$html);
                $html = str_replace("Duty & Taxes","Importation Charges",$html);
                //$html = str_replace("Duty &amp; Taxes","Importation Charges",$html);
                $transport['html'] = $html;
            }

        }
//        else  {
//
//        }
//        else {
//            $html = $transport['html'];
//            if(strpos('Shipping & Handling',$html) >= 0)
//                Pb_Pbgsp_Model_Util::log("Found in $layoutName");
//        }

        return $this;
    }
    private function _getCPORD($order)
    {
        if($order) {
            $cpOrder = $this->_getCpOrderNumber($order);
            if($cpOrder) {
                return $cpOrder -> getCpOrderNumber();
            }

        }

        return false;
    }
    private function _getCpOrderNumber($order)
    {
        if($order) {
//            $clearPathOrders = Mage::getModel("pb_pbgsp/ordernumber")-> getCollection();
//
//            $clearPathOrders -> addFieldToFilter('mage_order_number', $order -> getRealOrderId());
//            foreach ($clearPathOrders as $cpOrder) {
//                return $cpOrder ;
//
//            }
            $cpOrder = Mage::getModel("pb_pbgsp/ordernumber")->load($order -> getRealOrderId(),'mage_order_number');
            return $cpOrder;
        }

        return false;
    }
}
?>

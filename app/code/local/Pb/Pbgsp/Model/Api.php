<?php
/**
 * Product:       Pb_Pbgsp (1.4.3)
 * Packaged:      2016-12-06T09:30:00+00:00
 * Last Modified: 2016-09-21T11:45:00+00:00





 * File:          app/code/local/Pb/Pbgsp/Model/Api.php
 * Copyright:     Copyright (c) 2015 Pitney Bowes <info@pb.com> / All rights reserved.
 */
class Pb_Pbgsp_Model_Api
{

    static $STORE_TOKENS = array();

    public static function CallAPI($method, $url, $data = false,$attempts=0)
    {
        $curl = curl_init();
        $headers = array();
        $dataString = '';
        switch ($method)
        {
            case "POST":
                curl_setopt($curl, CURLOPT_POST, 1);

                if ($data) {
                    $dataString = json_encode($data,JSON_UNESCAPED_SLASHES);
                    Pb_Pbgsp_Model_Util::log("Data to pass to get quote $dataString");
                    $headers[] = 'Content-Type: application/json';
                    $headers[] = 'Content-Length: ' . strlen($dataString);
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $dataString);
                }

                break;
            case "PUT":
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PUT');
                if ($data) {
                    $dataString = json_encode($data,JSON_UNESCAPED_SLASHES);
                    Pb_Pbgsp_Model_Util::log("Data to pass to PUT $dataString");
                    $headers[] = 'Content-Type: application/json';
                    $headers[] = 'Content-Length: ' . strlen($dataString);
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $dataString);
                }
                break;
            default:
                if ($data)
                    $url = sprintf("%s?%s", $url, http_build_query($data));
        }
        if($dataString != '')
            Pb_Pbgsp_Model_Util::log("Passing $dataString to $url");
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $token = self::getToken();
        if(!$token)
            throw new Exception("Could not get authentication token");
        $headers[] = 'Cookie: '. $token['cookie'][0];
        $headers[] = 'Authorization: '.$token['token_type'].' '.$token['access_token'];
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers );
        curl_setopt($curl, CURLOPT_VERBOSE, 1); // turn verbose on
        curl_setopt($curl, CURLINFO_HEADER_OUT, true);
        //curl_setopt($curl, CURLOPT_SSL_CIPHER_LIST, 'TLSv1');
        $result = curl_exec($curl);
        $info = curl_getinfo($curl);
        //Pb_Pbgsp_Model_Util::log($info);
        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if($status != 200 && $status != 201 && $status != 400)
        {
            Pb_Pbgsp_Model_Util::log("Http Status: $status");
            Pb_Pbgsp_Model_Util::log("body : $result");
            Pb_Pbgsp_Model_Util::log("token_type". $token['token_type']);
            Pb_Pbgsp_Model_Util::log("access_token". $token['access_token']);
            //if($status == 401 && !$isSecondCall) {
            if($status == 401 && $attempts < 5) {
                //token expired regenerat it

                Pb_Pbgsp_Model_Util::log("Regenerating token");
                //Mage::getSingleton("customer/session")->setPbToken(false);
                Mage::app()->getCache()->remove(trim(Pb_Pbgsp_Model_Credentials::getUsername()));
                $attempts++;
                curl_close($curl);
                return self::CallAPI($method,$url,$data,$attempts);
            }
			if($status == 404){
				return $result;
			}
            else {
                throw new Exception($result,$status);
            }
        }
        curl_close($curl);
        return $result;
    }

    public static function getToken() {
        $token = null;
        $username = trim(Pb_Pbgsp_Model_Credentials::getUsername());
        //$token = Mage::getSingleton("customer/session")->getPbToken();
        if (false !== ($data = Mage::app()->getCache()->load($username))) {
            $token = unserialize($data);
        }
        if(!$token) {
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_POST, 1);

            $password = trim(Pb_Pbgsp_Model_Credentials::getPassword());
            curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($curl, CURLOPT_USERPWD, "$username:$password");
            curl_setopt($curl, CURLINFO_HEADER_OUT, 1); // capture the header info
            curl_setopt($curl, CURLOPT_VERBOSE, 1); // turn verbose on
            // get headers too with this line
            curl_setopt($curl, CURLOPT_HEADER, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, 'grant_type=client_credentials');
            // Will return the response, if false it print the response
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            $url = Pb_Pbgsp_Model_Credentials::getAuthorizationUrl();
            curl_setopt($curl, CURLOPT_URL, $url);
            $response = curl_exec($curl);
            $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
            $header = substr($response, 0, $header_size);
            // get cookies
            $cookies = array();
            preg_match_all('/Set-Cookie:(?<cookie>\s{0,}.*)$/im', $header, $cookies);
             $body = substr($response, $header_size);
            $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $info = curl_getinfo($curl);
            if($status != 200) {
                Pb_Pbgsp_Model_Util::log("Error getting authentication token.". $response);
                throw new Exception("Could not get authentication token",$status);
            }
            curl_close($curl);
            $token = json_decode($body,true);
            $token['cookie'] = $cookies['cookie'];
            Pb_Pbgsp_Model_Util::log("Token regenerated:". $body);
            //Mage::getSingleton("customer/session")->setPbToken($token);

            Mage::app()->getCache()->save(serialize($token), $username);
        }
        return $token;
    }

    protected static function  getClientIP() {
        $ipaddress = '';
        if (array_key_exists('HTTP_CLIENT_IP',$_SERVER))
            $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
        else if (array_key_exists('HTTP_X_FORWARDED_FOR',$_SERVER))
            $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        else if (array_key_exists('HTTP_X_FORWARDED',$_SERVER))
            $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
        else if (array_key_exists('HTTP_FORWARDED_FOR',$_SERVER))
            $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
        else if (array_key_exists('HTTP_FORWARDED',$_SERVER))
            $ipaddress = $_SERVER['HTTP_FORWARDED'];
        else if (array_key_exists('REMOTE_ADDR',$_SERVER))
            $ipaddress = $_SERVER['REMOTE_ADDR'];
        else
            $ipaddress = '0.0.0.0';
        return $ipaddress;
    }
    protected static function makeBasket($products, $address,$method='STANDARD',$forCreateOrder=false)
    {
        $basketLines = array();
        $totalProducts = 0;
        /* BigPixel code for adding handling fee, 6/6/2012*/


        $handlingFee = Pb_Pbgsp_Model_Credentials::getHandlingFee();

        $handlingoption = Pb_Pbgsp_Model_Credentials::getHandlingOption();
        $domesticShippingFee = Pb_Pbgsp_Model_Credentials::getDomesticShippingFee();
        $domesticShippingOption = Pb_Pbgsp_Model_Credentials::getDomesticShippingOption();

        $deliveryAdjMinDays = Pb_Pbgsp_Model_Credentials::getDeliveryAdjustmentMinDays();
        $deliveryAdjMixDays = Pb_Pbgsp_Model_Credentials::getDeliveryAdjustmentMaxDays();

        foreach ($products as $product) {
            if ((!$product->getParentItem() && $product->getRealProductType()
                    != Mage_Catalog_Model_Product_Type::TYPE_BUNDLE) ||
                ($product->getParentItem() && $product->isChildrenCalculated())) {
                /* @var Mage_Sales_Model_Order_Item $product */

            	$totalProducts += $product->getQty();

//                Pb_Pbgsp_Model_Util::log('discount amount '. $product->getDiscountAmount());
//                Pb_Pbgsp_Model_Util::log('Price '. $product->getPrice());

                $price = $product->getPrice() - $product->getDiscountAmount();//$price - ($product->getDiscountAmount() * $product->getQty());
                if($price == 0)
                    $price = 0.01; //set this price so that quote could be retrieved.
                $actualProduct = Mage::getModel('catalog/product') -> load($product->getProductId());
                /* @var Mage_Catalog_Model_Product $actualProduct */

				$cats = $actualProduct->getCategoryIds();
				$category_name= array();
					foreach ($cats as $category_id) {
						$_cat = Mage::getModel('catalog/category')->load($category_id) ;
						$category_name[]= array("merchantCatRefId" =>$_cat->getId());
					}
				$category_name_rev = array_reverse ($category_name); 	
                $shortDescription = Pb_Pbgsp_Model_Util::stripHtml($actualProduct -> getShortDescription());
                $shortDescription = preg_replace("/[^A-Za-z0-9 .\-\+=;:\\(){}\[\]@?%$#]/",'',$shortDescription);
                if (strlen($shortDescription) >= 2000) {

                    $shortDescription = Pb_Pbgsp_Model_Util::chopString($shortDescription, 1999);
                }
                $arDescriptions = array(array(
                    "locale" => "en_CA",
                    "name" => $product->getName(),
                    "shortDescription" => $shortDescription
                ));
                $commodity = array(
                    'merchantComRefId' => $product->getSku()

                );
                if(Mage::getStoreConfig('carriers/pbgsp/seller_active')) {
                    $commodity['descriptions'] = $arDescriptions;
                    $commodity['commodityUrl'] = $actualProduct->getProductUrl();
                    $commodity['categories'] = $category_name_rev;
                }
                if($actualProduct->getCountryOfManufacture()) {
                    $commodity['coo'] = $actualProduct->getCountryOfManufacture();
                }
                if($actualProduct->getWeight() > 0) {
                    $commoditySize = array(
                        'weightUnit' => 'lb',
                        'distanceUnit'=>'in',
                        'weight'=> $actualProduct->getWeight(),

                    );
                    if($actualProduct->getPbPbgspCommodityHeight() && $actualProduct->getPbPbgspCommodityHeight() > 0) {
                        $commoditySize['height'] = $actualProduct->getPbPbgspCommodityHeight();
                    }
                    if($actualProduct->getPbPbgspCommodityWidth() && $actualProduct->getPbPbgspCommodityWidth() > 0) {
                        $commoditySize['width'] = $actualProduct->getPbPbgspCommodityWidth();
                    }
                    if($actualProduct->getPbPbgspCommodityLength() && $actualProduct->getPbPbgspCommodityLength() > 0) {
                        $commoditySize['length'] = $actualProduct->getPbPbgspCommodityLength();
                    }
                    $commodity['commoditySize'] = $commoditySize;
                }


                if($actualProduct->getPbPbgspPackageWeight() && $actualProduct->getPbPbgspPackageWeight() > 0 ) {
                    $shippingSize = array(
                        'weightUnit' => 'lb',
                        'distanceUnit'=>'in',
                        'weight'=> $actualProduct->getPbPbgspPackageWeight(),

                    );
                    if($actualProduct->getPbPbgspPackageHeight() && $actualProduct->getPbPbgspPackageHeight() > 0) {
                        $shippingSize['height'] = $actualProduct->getPbPbgspPackageHeight();
                    }
                    if($actualProduct->getPbPbgspPackageWidth() && $actualProduct->getPbPbgspPackageWidth() > 0) {
                        $shippingSize['width'] = $actualProduct->getPbPbgspPackageWidth();
                    }
                    if($actualProduct->getPbPbgspPackageLength() && $actualProduct->getPbPbgspPackageLength() > 0) {
                        $shippingSize['length'] = $actualProduct->getPbPbgspPackageLength();
                    }
                    $commodity['shippingSize'] = $shippingSize;
                }
                array_push($basketLines, array(
                                              "lineId" => $product->getSku(),
                                              "commodity" => $commodity,
                                              "unitPrice" => array('price' => array('value' => $price)),
                                              "quantity" => intval($product->getQty())

                                              )
                                         );
            }

        }

        if(isset($handlingoption) && $handlingoption == "2") { //apply as per item
            $handlingFee = $handlingFee * $totalProducts;
        }
        if(isset($domesticShippingOption) && $domesticShippingOption == "2") { //apply as per item
            $domesticShippingFee = $domesticShippingFee * $totalProducts;
        }
        $toHubTransportations = array(
            array(
                'merchantShippingIdentifier' => 'PB',
                'speed' => $method,
                'shipping' =>  array('value' => $handlingFee),
                'handling' =>  array('value' => $domesticShippingFee),
                'total' => array('value' => $handlingFee + $domesticShippingFee),
                'minDays' => $deliveryAdjMinDays,
                'maxDays' => $deliveryAdjMixDays
            )
        );

        if (strlen($address->getEmail()) > 0) {
            $email = $address->getEmail();
        }
        elseif (strlen(Mage::getSingleton('checkout/cart')->getQuote()->getBillingAddress()->getEmail()) > 0) {
            $email = Mage::getSingleton('checkout/cart')->getQuote()->getBillingAddress()->getEmail();
        }
        else {
            $email = Mage::getSingleton('customer/session')->getCustomer()->getEmail();
        }
        if(!$email)
            $email = "kamranattari@gmail.com";

        if($email == 'email@email.com' && $forCreateOrder)
            $email = Mage::getSingleton('customer/session')->getCustomer()->getEmail();//set correct email address of customer

        $familyName = $address->getLastname(); //when it comes from paypal express, lastname is null, Kamran, Bigpixel Studio,
        if(!$familyName)
            $familyName = $address->getFirstname();
        $consignee = array(
            'familyName' => $familyName,
            'givenName' => $address->getFirstname(),
            'email' => $email,
            'phoneNumbers' => array(
                array(
                    'number' => $address->getTelephone(),
                    'type' => 'other'
                )
            )
        );
        $shippingAddress = array(
            'street1' => $address->getStreet1(),
            'street2' => $address->getStreet2(),

            'city' => $address->getCity(),
            'countyOrRegion' => '',
            'provinceOrState' => $address->getRegionCode(),
            'country' => $address->getCountryId(),
            'postalOrZipCode' => $address->getPostcode()
        );
        $currency = 'USD';//Mage::app()->getStore()->getCurrentCurrencyCode();
		
		
		// added seller information 30/06/2016
		$contactdetail=array(
		'familyName' => Mage::getStoreConfig('carriers/pbgsp/seller_family_name'),
		'givenName' => Mage::getStoreConfig('carriers/pbgsp/seller_given_name'),
		'email' => Mage::getStoreConfig('carriers/pbgsp/seller_email'),
		'phoneNumbers'=>  array(
                array(
                    'number' => Mage::getStoreConfig('carriers/pbgsp/seller_phone'),
                    'type' => Mage::getStoreConfig('carriers/pbgsp/seller_phone_type')
                )
            )
		);
		
		$sellerAddress= array(
		'street1' => Mage::getStoreConfig('carriers/pbgsp/seller_street_address'),
		'city' => Mage::getStoreConfig('carriers/pbgsp/seller_city'),
		'provinceOrState' => Mage::getStoreConfig('carriers/pbgsp/seller_province_state'),
		'postalOrZipCode' => Mage::getStoreConfig('carriers/pbgsp/seller_zip'),
		'country' => Mage::getStoreConfig('carriers/pbgsp/seller_country'),
		);
		
		$seller= array(
		'sellerId'=> Mage::getStoreConfig('carriers/pbgsp/seller_id'),
		'sellerType'=> Mage::getStoreConfig('carriers/pbgsp/seller_type'),
		'contactDetails'=>$contactdetail,
		'address'=>$sellerAddress	
		);
		
		// end seller code
        $basket = array(
            'merchantId' => Pb_Pbgsp_Model_Credentials::getMerchantCode(),
            'purchaserIPAddress' => self::getClientIP(),
            'transactionId' => "".mt_rand(100000, 999999)."",
            'quoteCurrency' => $currency,
            'basketCurrency' => $currency,
            'basketLines' => $basketLines,
            'toHubTransportations' => $toHubTransportations,
            'consignee' => $consignee,
            'shippingAddress' => $shippingAddress

        );
        if(Mage::getStoreConfig('carriers/pbgsp/seller_active')) {
            $basket['seller'] = $seller;
        }

        return $basket;

    }

    public static function generateInboundParcelNumber($shipment,$items,$order,$cpOrderNumber) {
        $inboundParcelCommodities = array();
        /* @var Mage_Sales_Model_Order_Shipment  $shipment*/
        /* @var Mage_Sales_Model_Order $order */
        $address = $order->getShippingAddress();
        $totalWeight = 0;
        foreach($shipment->getItemsCollection() as $item) {
            $qty =  intval($item->getQty());
            if($qty ==0)
                continue;
            $product = Mage::getModel('catalog/product')->loadByAttribute('sku',$item->getSku());
            $commodity = array(
                'merchantComRefId' => $item->getSku(),
                
                'quantity' => intval($item->getQty()),
                'coo' => $product->getCountryOfManufacture(),
                'size' => array(
                    'weight' => $product->getWeight(),
                    'weightUnit' => 'lb'
                )

            );
            array_push($inboundParcelCommodities,$commodity);
            $totalWeight += $product->getWeight();
        }
        $tracks = array();

        foreach($shipment->getTracksCollection() as $track) {
            $tracks[] = $track;
        }
		
		if(isset($tracks[0])){
			$number=$tracks[0]->getNumber();
			$title = $tracks[0]->getTitle();
		}else{
			$number = $cpOrderNumber;
			$title = 'PB';
			
			// add the tracking info magento
//			$track = Mage::getModel('sales/order_shipment_track')
//							 ->setShipment($shipment)
//							 ->setData('title', 'PB')
//							 ->setData('number',$cpOrderNumber)
//							 ->setData('carrier_code', 'custom')
//							 ->setData('order_id', $shipment->getData('order_id'))
//							 ->save();
		}
		
		
		$returnAddress = array(
            'street1' => Pb_Pbgsp_Model_Credentials::getReturnAddressStreet1(),
            'city' => Pb_Pbgsp_Model_Credentials::getReturnAddressCity(),
            'provinceOrState' => Pb_Pbgsp_Model_Credentials::getReturnAddressState(),
            'country' => Pb_Pbgsp_Model_Credentials::getReturnAddressCountry(),
            'postalOrZipCode' => Pb_Pbgsp_Model_Credentials::getReturnAddressZip()
        );

//        if(Mage::getStoreConfig('carriers/pbgsp/seller_active')) {
//            $returnAddress['street1'] = Mage::getStoreConfig('carriers/pbgsp/seller_street_address');
//            $returnAddress['city'] = Mage::getStoreConfig('carriers/pbgsp/seller_city');
//            $returnAddress['provinceOrState'] = Mage::getStoreConfig('carriers/pbgsp/seller_province_state');
//            $returnAddress['postalOrZipCode'] = Mage::getStoreConfig('carriers/pbgsp/seller_zip');
//            $returnAddress['country'] = Mage::getStoreConfig('carriers/pbgsp/seller_country');
//        }
        $requestBody = array(
            'merchantOrderNumber' => $cpOrderNumber,
            'parcelIdentificationNumber' => $number,
            'inboundParcelCommodities' => $inboundParcelCommodities,
            'shipper' => $title,
            'shipperService' => 'EXPRESS',
            'shipperTrackingNumber'=> $number,
            'dcId' => '211113442',
            'dcAddress' => array(
                'street1' => '200 Main Street',
                'city' => 'PHOENIX',
                'provinceOrState' => 'AZ',
                'country' => 'US',
                'postalOrZipCode' => '85123'
            ),
            'returnDetails' => array(
                'returnAddress' => $returnAddress,
                'contactInformation' => array(
                    'familyName' => $address->getLastname(),
                    'givenName' => $address->getFirstname(),
                    'email' => $address->getEmail(),
                    'phoneNumbers' => array(
                        array(
                            'number' => $address->getTelephone(),
                            'type' => 'home'
                        )
                    )
                )
            ),
            'size' => array(
                'weight' => $totalWeight,
                'weightUnit' => 'lb'
            )
        );
        $url = Pb_Pbgsp_Model_Credentials::getOrderMgmtAPIUrl().'/orders/'.
            $cpOrderNumber.'/inbound-parcels';
		
		 $tryCnt = 1;
	  	 do
		 {
		    $response = self::CallAPI('POST',$url,$requestBody);
		    $parcelResponse = json_decode($response,true);
		   
  		    Pb_Pbgsp_Model_Util::log('Response of inbound-parcels try'.$tryCnt);
			Pb_Pbgsp_Model_Util::log($parcelResponse);
			$tryCnt ++;
			
		} while(($tryCnt <= 10) && (array_key_exists('errors',$parcelResponse)));
        
        return $parcelResponse;

    }
    public static function getCheckoutUrl($method) {
        return Pb_Pbgsp_Model_Credentials::getCheckoutUrl().'/'.$method;
    }
    public static function getQuote($products,$address)
    {

        Pb_Pbgsp_Model_Util::log('Getting quote from Pitney Bowes');
        $basket = Pb_Pbgsp_Model_Api::makeBasket($products, $address);

            $url = self::getCheckoutUrl('quotes');
            $response = self::CallAPI('POST',$url,$basket);


        $quoteSet = json_decode($response,true);
        Pb_Pbgsp_Model_Util::log('response of quotes ');
        Pb_Pbgsp_Model_Util::log($quoteSet);
        return $quoteSet;


	}


    public static function createOrder($products,$method,$address) {


        try {
            Pb_Pbgsp_Model_Util::log('Creating order in clearpath');
            $basket = Pb_Pbgsp_Model_Api::makeBasket($products, $address,$method,true);

               $url = self::getCheckoutUrl('orders');
            $response = self::CallAPI('POST',$url,$basket);
            Pb_Pbgsp_Model_Util::log("response of create order ". $response );

            $orderSet = json_decode($response,true);
            Pb_Pbgsp_Model_Util::log('response of orders ');
            Pb_Pbgsp_Model_Util::log($orderSet);
            foreach($orderSet['order'] as $order) {
                $tax = 0;
                if(!self::logIfError($order,'createOrder')) {
//                    if(array_key_exists('order',$order)) {
//                        $quote = $order['order'];
//                        $tax = $quote['totalImportation']['total']['value'];
//                    }

                    return $order;//array("orderNumber" => $order['orderId'], "tax" => $tax);
                }
                else {
                    return false;
                }

            }
            return $orderSet;
        }
        catch(Exception $e) {
            Pb_Pbgsp_Model_Util::log("Received unexpected exception from Pb while calling createOrder.");
            Pb_Pbgsp_Model_Util::logException($e);


        }

    }
	
	/* This is API method for cancel order in PB
	   Created by: Sudarshan
	   Date: 02/09/2015
	  
	*/
	public static function cancelOrder($cpOrderNumber) {

        try {
            Pb_Pbgsp_Model_Util::log('cancelling order in clearpath ',$cpOrderNumber);
			$requestBody = array();
            $url = Pb_Pbgsp_Model_Credentials::getOrderMgmtAPIUrl().'/orders/'.$cpOrderNumber.'/cancel';
			$response = self::CallAPI('POST',$url,$requestBody);
			$cancelOrderResponse = json_decode($response,true);
			Pb_Pbgsp_Model_Util::log('Response of cancel order');
			Pb_Pbgsp_Model_Util::log($cancelOrderResponse);
            $errorMessage = '';
			if(array_key_exists('errors',$cancelOrderResponse)) {
				Pb_Pbgsp_Model_Util::log("Error generating cancelling order");
				Pb_Pbgsp_Model_Util::log($cancelOrderResponse);
				foreach($cancelOrderResponse['errors'] as $error){
					$errorCode= $error['error'];
					$errorMessage .=$error['message']."</br>";
				}
				Mage::getSingleton('core/session')->addError('PB Error - '.$errorMessage);
			}else{
				return $cancelOrderResponse;
			}
			

        }
        catch(Exception $e) {
            Pb_Pbgsp_Model_Util::log("Received unexpected exception from Pb while calling cancel Order.");
            Pb_Pbgsp_Model_Util::logException($e);
			Mage::getSingleton('core/session')->addError('Unexpected exception from PB while calling cancel Order. '.$e->getMessage());

        }

    }

    private static function logIfError($object,$action=null) {
        if(array_key_exists('errors',$object) && count($object['errors']) > 0) {
            $processor = new Pb_Pbgsp_Model_Messages();
            foreach($object['errors'] as $error) {
                $message = '';
                if(array_key_exists('message',$error))
                    $message = $error['message'];
                $message = $processor->getDisplayMessage($error["error"],$message);//.$sku;
                if($action)
                    Pb_Pbgsp_Model_Util::log("Error received in action $action");
                Pb_Pbgsp_Model_Util::log($message);
            }
            return true;
        }
        return false;
    }
    public static function confirmOrder($orderNumber,$order) {
        /* @var Mage_Sales_Model_Order $order */



        $billingAddress = $order->getBillingAddress();
        $confirm = array(
            'transactionId' => $order->getRealOrderId(),
            'merchantOrderNumber' => $order->getRealOrderId(),
            'purchaser' => array(
                'familyName' => $order->getCustomerLastname(),
                'givenName' => $order->getCustomerFirstname(),
                'email' => $order->getCustomerEmail(),
                'phoneNumbers' => array(
                    array(
                        'number' => $billingAddress->getTelephone(),
                        'type' => 'other'
                    )
                 )),
            'purchaserBillingAddress'  => array(
                'street1' => $billingAddress->getStreet1(),
                'street2' => '',
                'city' => $billingAddress->getCity(),
                'countyOrRegion' => '',
            'provinceOrState' => $billingAddress->getRegionCode(),
                'country' => $billingAddress->getCountryId(),
                'postalOrZipCode' => $billingAddress->getPostcode(),
             )

        );
        try {
            $url = self::getCheckoutUrl("orders/$orderNumber/confirm");
            $response = self::CallAPI('PUT',$url,$confirm);
            Pb_Pbgsp_Model_Util::log("Passing $url  ");
            Pb_Pbgsp_Model_Util::log($confirm);
            $confirmResponse = json_decode($response,true);
            if(self::logIfError($confirmResponse,'confirmOrder'))//there is an error
                return false;
            return true;
        }
        catch(Exception $e) {
            Pb_Pbgsp_Model_Util::log("Received unexpected exception from Pb while calling confirming order.");
            Pb_Pbgsp_Model_Util::log($e->getMessage(). '   '. $e->getTraceAsString());
            return false;
        }
        return true;
    }

}

?>

<?php
/**
 * Product:       Pb_Pbgsp (1.0.0)
 * Packaged:      2015-06-04T15:09:31+00:00
 * Last Modified: 2015-06-04T15:00:31+00:00
 * File:          app/code/local/Pb/Pbgsp/Model/Api.php
 * Copyright:     Copyright (c) 2015 Pitney Bowes <info@pb.com> / All rights reserved.
 */
class Pb_Pbgsp_Model_Api
{


    public static function CallAPI($method, $url, $data = false,$isSecondCall=false)
    {
        $curl = curl_init();
        $headers = array();
        $dataString = '';
        switch ($method)
        {
            case "POST":
                curl_setopt($curl, CURLOPT_POST, 1);

                if ($data) {
                    $dataString = json_encode($data);
                    Pb_Pbgsp_Model_Util::log("Data to pass to get quote $dataString");
                    $headers[] = 'Content-Type: application/json';
                    $headers[] = 'Content-Length: ' . strlen($dataString);
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $dataString);
                }

                break;
            case "PUT":
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PUT');
                if ($data) {
                    $dataString = json_encode($data);
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
        $result = curl_exec($curl);
        $info = curl_getinfo($curl);
        //Pb_Pbgsp_Model_Util::log($info);
        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if($status != 200 && $status != 201 && $status != 400)
        {
            Pb_Pbgsp_Model_Util::log("Http Status: $status");
            Pb_Pbgsp_Model_Util::log("body : $result");
            if($status == 401 && !$isSecondCall) {
                //token expired regenerat it
                Pb_Pbgsp_Model_Util::log("Regenerating token");
                Mage::getSingleton("customer/session")->setPbToken(false);
                self::CallAPI($method,$url,$data,true);
            }
            else {
                throw new Exception($result,$status);
            }
        }
        curl_close($curl);
        return $result;
    }

    public static function getToken() {
        $token = Mage::getSingleton("customer/session")->getPbToken();
        if(!$token) {
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_POST, 1);
            $username = trim(Pb_Pbgsp_Model_Credentials::getUsername());
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
            Mage::getSingleton("customer/session")->setPbToken($token);
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
    protected static function makeBasket($products, $address,$method='STANDARD')
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
                array_push($basketLines, array(
                                              "lineId" => $product->getSku(),
                                              "commodity" => array('merchantComRefId' => $product->getSku()),
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

        $consignee = array(
            'familyName' => $address->getLastname(),
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
        $currency = Mage::app()->getStore()->getCurrentCurrencyCode();
        $basket = array(
            'merchantId' => Pb_Pbgsp_Model_Credentials::getMerchantCode(),
            'purchaserIPAddress' => self::getClientIP(),
            'transactionId' => mt_rand(100000, 999999),
            'quoteCurrency' => $currency,
            'basketLines' => $basketLines
            ,
            'toHubTransportations' => $toHubTransportations,
            'consignee' => $consignee
            ,
            'shippingAddress' => $shippingAddress

        );


        return $basket;

    }

    public static function generateInboundParcelNumber($shipment,$items,$mageOrderNumber,$cpOrderNumber) {
        $inboundParcelCommodities = array();
        /* @var Mage_Sales_Model_Order_Shipment  $shipment*/
        $totalWeight = 0;
        foreach($shipment->getItemsCollection() as $item) {
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
        $requestBody = array(
            'merchantOrderNumber' => $cpOrderNumber,
            'parcelIdentificationNumber' => $tracks[0]->getNumber(),
            'inboundParcelCommodities' => $inboundParcelCommodities,
            'shipper' => $tracks[0]->getTitle(),
            'shipperService' => 'EXPRESS',
            'shipperTrackingNumber'=> $tracks[0]->getNumber(),
            'dcId' => '211113442',
            'dcAddress' => array(
                'street1' => '200 Main Street',
                'city' => 'PHOENIX',
                'provinceOrState' => 'AZ',
                'country' => 'US',
                'postalOrZipCode' => '85123'
            ),
            'returnDetails' => array(
                'returnAddress' => array(
                    'street1' => '200 Main Street',
                    'city' => 'PHOENIX',
                    'provinceOrState' => 'AZ',
                    'country' => 'US',
                    'postalOrZipCode' => '85123'
                ),
                'contactInformation' => array(
                    'familyName' => 'Smith',
                    'givenName' => 'Mary',
                    'email' => 'retailer_email@test.com',
                    'phoneNumbers' => array(
                        array(
                            'number' => '12123232',
                            'type' => 'home'
                        )
                    )
                )
            ),
            'size' => array(
                'weight' => $totalWeight,
                'weightUnit' => 'LB'
            )
        );
        $url = Pb_Pbgsp_Model_Credentials::getOrderMgmtAPIUrl().'/orders/'.
            $cpOrderNumber.'/inbound-parcels';
        $response = self::CallAPI('POST',$url,$requestBody);
        $parcelResponse = json_decode($response,true);
        Pb_Pbgsp_Model_Util::log('Response of inbound-parcels');
        Pb_Pbgsp_Model_Util::log($parcelResponse);
        return $parcelResponse;

    }
    public static function getCheckoutUrl($method) {
        return Pb_Pbgsp_Model_Credentials::getCheckoutUrl().'/'.$method;
    }
    public static function getQuote($products,$address)
    {

        Pb_Pbgsp_Model_Util::log('Getting quote from clearpath');
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
            $basket = Pb_Pbgsp_Model_Api::makeBasket($products, $address,$method);

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

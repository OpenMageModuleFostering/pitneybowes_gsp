<?php
/**
 * Product:       Pb_Pbgsp (1.2.0)
 * Packaged:      2015-10-01T12:11:15+00:00
 * Last Modified: 2015-09-14T12:11:20+00:00




 * File:          app/code/local/Pb/Pbgsp/Model/Inboundparcel.php
 * Copyright:     Copyright (c) 2015 Pitney Bowes <info@pb.com> / All rights reserved.
 */
class Pb_Pbgsp_Model_Inboundparcel extends Mage_Core_Model_Abstract {
    public function _construct()
    {
        parent::_construct();
        $this->_init('pb_pbgsp/inboundparcel');
    }
	
	/* Method call hourly and try to generate ASN for fullfilled orders.
	   Created by: Sudarshan
	   Date: 07/09/2015
	  
	*/
	public function generateInboundParcelPreAdviceCron(){
		
		if(Pb_Pbgsp_Model_Credentials::isASNGenerationEnabled() == '1') {
            try {
				
				// Get PB orders
				$clearPathOrders = Mage::getModel("pb_pbgsp/ordernumber")-> getCollection();

				// Get Inbound parcels	
			//	$parcels = Mage::getModel("pb_pbgsp/inboundparcel")-> getCollection();
              //  $parcels->addFieldToSelect('mage_order_number');
				
				
				echo "</br>Trying to generate ASN PBorders which are fullfilled but doesn't have ASN </br>  ";
				// find order that doesn't have ASN entry.
				
				foreach($clearPathOrders as $clearPathOrder)
				{
					$orderID = $clearPathOrder['mage_order_number'];
					
					$cpOrderNumber = $clearPathOrder['cp_order_number'];
					$order = Mage::getModel('sales/order')->loadByIncrementId($orderID);
					
					if(($order->hasShipments()) && ($order['status'] != 'canceled')){
						
						$shipmentCollection = Mage::getResourceModel('sales/order_shipment_collection')->setOrderFilter($order)->load();
						
						foreach ($shipmentCollection as $shipment){
							
							//Get shipment id
							$shipId = $shipment['entity_id'];
							
							//check shipment entry of order in table (partially shipped order)
							$parcels = Mage::getModel("pb_pbgsp/inboundparcel")-> getCollection();
							$parcels->addFieldToFilter('mage_order_number',$orderID);
							$parcels->addFieldToFilter('mage_order_shipment_number',$shipId);
							
							if(count($parcels) == 0){
								echo "</br>$orderID  Ship- $shipId ";
								Pb_Pbgsp_Model_Util::log('Generate ASN for Order-'.$orderID.'  Ship- '.$shipId );
								$items = array();
								
								$parcelResponse = Pb_Pbgsp_Model_Api::generateInboundParcelNumber($shipment,$items,$order,$cpOrderNumber);
								
								if(array_key_exists('errors',$parcelResponse)) {
									echo " - Error \"".$parcelResponse['errors'][0]['message']."\"";
									Pb_Pbgsp_Model_Util::log("Error generating inbound parcel");
									Pb_Pbgsp_Model_Util::log($parcelResponse);
								}
								else if(isset($parcelResponse['parcelIdentifier'])) 
								{
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
										 ->setData('carrier_code', 'custom')
										 ->setData('order_id', $shipment->getData('order_id'))
										 ->save();
									
									Pb_Pbgsp_Model_Util::log($parcelResponse['parcelIdentifier']."Inbound Parcel Number Saved");
								}
								
							}
							
						}
												
						
					}else{
						//echo " - Not fullfilled yet";
					}
					
					unset($order);
					unset($shipmentCollection);
				}
				
				
			}
			catch(Exception $e) {
                Pb_Pbgsp_Model_Util::log("Error creating inbound parcel. ");
                Pb_Pbgsp_Model_Util::logException($e);
            }
		}	
	}
}

?>

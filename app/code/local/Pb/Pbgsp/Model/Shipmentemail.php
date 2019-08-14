<?php
/**
 * Product:       Pb_Pbgsp (1.4.3)
 * Packaged:      2016-12-06T09:30:00+00:00
 * Last Modified: 2016-09-21T11:45:00+00:00
 * File:          app/code/local/Pb/Pbgsp/Model/Variable.php
 * Copyright:     Copyright (c) 2016 Pitney Bowes <info@pb.com> / All rights reserved.
 */
class Pb_Pbgsp_Model_Shipmentemail extends Mage_Core_Model_Abstract {
    public function _construct()
    {
        parent::_construct();
        $this->_init('pb_pbgsp/shipmentemail');
    }

    public function sendShipmentEmails() {
        $shipmentemailColl = Mage::getModel("pb_pbgsp/shipmentemail")-> getCollection();
        $shipmentemailColl -> addFieldToFilter('email_sent',array('null' => true));
        foreach($shipmentemailColl as $shipEmail) {
            $shipment = Mage::getModel("sales/order_shipment");
            /* @var Mage_Sales_Model_Order_Shipment $shipment */
            $shipment->loadByIncrementId($shipEmail->getShipmentId());
            $shipment->sendEmail();
        }
    }
}

?>

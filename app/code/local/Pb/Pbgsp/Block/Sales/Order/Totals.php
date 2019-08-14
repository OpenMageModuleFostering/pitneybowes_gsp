<?php
/**
 * Product:       Pb_Pbgsp (1.4.3)
 * Packaged:      2016-12-06T09:30:00+00:00
 * Last Modified: 2016-09-21T11:45:00+00:00
 * File:          app/code/local/Pb/Pbgsp/Block/Sales/Order/Totals.php
 * Copyright:     Copyright (c) 2016 Pitney Bowes <info@pb.com> / All rights reserved.
 */
class Pb_Pbgsp_Block_Sales_Order_Totals extends Mage_Sales_Block_Order_Totals {

	public static function addDuties(Varien_Object $total, $after, $shipMethod, $taxAmount,$taxBaseAmount) {
		

				
		$len = strlen("pbgsp_");
		if (strlen($shipMethod) > $len && substr($shipMethod,0,$len) == "pbgsp_") {
			if ($total->getCode() == "tax") {
				$total = new Varien_Object(array(
							 'code'			=> 'tax',
							 'value'		=> $taxAmount,
							 'base_value' => $taxBaseAmount,
							 'label'		=> 'Importation Charges'
							 ));
			}
		}
		
		return $total;
	}
	
	public function addTotal(Varien_Object $total, $after=null) {
		
		$shipMethod = $this->getOrder()->getShippingMethod();
		$taxAmount = $this->getOrder()->getTaxAmount();
		$taxBaseAmount = $this->getOrder()->getBaseTaxAmount();
			Pb_Pbgsp_Model_Util::log("Add Duty & Taxes at Order");
		$total = self::addDuties($total,$after,$shipMethod,$taxAmount,$taxBaseAmount);
		return parent::addTotal($total,$after);

	}
    public function getTotals($area=null)
    {
        $totals = parent::getTotals($area);
        $shipMethod = $this->getOrder()->getShippingMethod();
        $len = strlen("pbgsp_");
        if (strlen($shipMethod) > $len && substr($shipMethod,0,$len) == "pbgsp_") {
            Pb_Pbgsp_Model_Util::log("getTotals PB shipmethod");
            if(array_key_exists("shipping",$totals)) {
                $shipping = $totals['shipping'];
                $shipping->setLabel('Transportation Charges');
        	}
        }

        return $totals;
    }
}
?>

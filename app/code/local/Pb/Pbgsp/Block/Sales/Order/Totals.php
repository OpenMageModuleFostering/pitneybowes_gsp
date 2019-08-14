<?php
/**
 * Product:       Pb_Pbgsp (1.3.2)
 * Packaged:      2016-01-11T11:12:49+00:00
 * Last Modified: 2015-12-18T11:00:00+00:00





 * File:          app/code/local/Pb/Pbgsp/Block/Sales/Order/Totals.php
 * Copyright:     Copyright (c) 2015 Pitney Bowes <info@pb.com> / All rights reserved.
 */
class Pb_Pbgsp_Block_Sales_Order_Totals extends Mage_Sales_Block_Order_Totals {

	public static function addDuties(Varien_Object $total, $after, $shipMethod, $taxAmount) {
		
		// TODO: This is kind of ugly. I am replacing the tax template with a tax entry... 
		// By default this sets the block_name without parameters which loads the appropriate
		// template. Need to see if this breaks anything.
				
		$len = strlen("pbgsp_");
		if (strlen($shipMethod) > $len && substr($shipMethod,0,$len) == "pbgsp_") {
			if ($total->getCode() == "tax") {
				$total = new Varien_Object(array(
							 'code'			=> 'tax',
							 'value'		=> $taxAmount,
							 'label'		=> 'Importation Charges'
							 ));
			}
		}
		
		return $total;
	}
	
	public function addTotal(Varien_Object $total, $after=null) {
		
		$shipMethod = $this->getOrder()->getShippingMethod();
		$taxAmount = $this->getOrder()->getTaxAmount();
			Pb_Pbgsp_Model_Util::log("Add Duty & Taxes at Order");
		$total = self::addDuties($total,$after,$shipMethod,$taxAmount);
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

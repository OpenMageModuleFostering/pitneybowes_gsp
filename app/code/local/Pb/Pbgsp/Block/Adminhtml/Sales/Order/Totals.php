<?php
/**
 * Product:       Pb_Pbgsp (1.4.0)
 * Packaged:      2016-07-28T17:25:00+00:00
 * Last Modified: 2016-07-26T14:17:00+00:00
 * File:          app/code/local/Pb/Pbgsp/Block/Adminhtml/Sales/Order/Totals.php
 * Copyright:     Copyright (c) 2016 Pitney Bowes <info@pb.com> / All rights reserved.
 */

class Pb_Pbgsp_Block_Adminhtml_Sales_Order_Totals extends Mage_Adminhtml_Block_Sales_Order_Totals {
	
	
	
	public function addTotal(Varien_Object $total, $after=null) {
		
		$shipMethod = $this->getOrder()->getShippingMethod();
		$taxAmount = $this->getOrder()->getTaxAmount();
		$taxBaseAmount = $this->getOrder()->getBaseTaxAmount();
		$total = Pb_Pbgsp_Block_Sales_Order_Totals::addDuties($total,$after,$shipMethod,$taxAmount,$taxBaseAmount);
		return parent::addTotal($total,$after);
		
	}
	
}

?>

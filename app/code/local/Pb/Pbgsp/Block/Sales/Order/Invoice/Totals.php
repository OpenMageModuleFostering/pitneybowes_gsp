<?php
/**
 * Product:       Pb_Pbgsp (1.3.8)
 * Packaged:      2016-06-23T10:40:00+00:00
 * Last Modified: 2016-06-01T14:02:28+00:00
 * File:          app/code/local/Pb/Pbgsp/Block/Sales/Order/Invoice/Totals.php
 * Copyright:     Copyright (c) 2016 Pitney Bowes <info@pb.com> / All rights reserved.
 */

class Pb_Pbgsp_Block_Sales_Order_Invoice_Totals extends Mage_Sales_Block_Order_Invoice_Totals {

	public function addTotal(Varien_Object $total, $after=null) {
		
		$shipMethod = $this->getOrder()->getShippingMethod();
		$taxAmount = $this->getOrder()->getTaxAmount();
		$total = Pb_Pbgsp_Block_Sales_Order_Totals::addDuties($total,$after,$shipMethod,$taxAmount);
		return parent::addTotal($total,$after);

	}
	
}
?>

<?php

/**
 * Product:       Pb_Pbgsp (1.1.2)
 * Packaged:      2015-09-23T12:09:53+00:00
 * Last Modified: 2015-09-14T12:11:20+00:00




 * File:          app/code/local/Pb/Pbgsp/Block/Adminhtml/Sales/Order/Invoice/Totals.php
 * Copyright:     Copyright (c) 2015 Pitney Bowes <info@pb.com> / All rights reserved.
 */
class Pb_Pbgsp_Block_Adminhtml_Sales_Order_Invoice_Totals extends Mage_Adminhtml_Block_Sales_Order_Totals {
	
	
	
	public function addTotal(Varien_Object $total, $after=null) {
		
		$shipMethod = $this->getOrder()->getShippingMethod();
		$taxAmount = $this->getOrder()->getTaxAmount();
		$total = Pb_Pbgsp_Block_Sales_Order_Totals::addDuties($total,$after,$shipMethod,$taxAmount);
		return parent::addTotal($total,$after);
		
	}
	
}

?>

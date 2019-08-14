<?php
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
							 'label'		=> 'Duty & Taxes'
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
	
}
?>

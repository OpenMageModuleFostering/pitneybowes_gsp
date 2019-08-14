<?php
	
	class Pb_Pbgsp_Model_Pdf_Tax extends Mage_Tax_Model_Sales_Pdf_Tax {
		public function getTitle() {
			$shipMethod = $this->getOrder()->getShippingMethod();
			$len = strlen("pbgsp_");
			if (strlen($shipMethod) > $len && substr($shipMethod,0,$len) == "pbgsp_") {
				return "Importation Charges";
			}
			return parent::getTitle();
		}		
	}
	
?>

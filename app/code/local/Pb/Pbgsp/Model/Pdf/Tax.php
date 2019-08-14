<?php
/**
 * Product:       Pb_Pbgsp (1.1.1)
 * Packaged:      2015-09-14T12:11:20+00:00
 * Last Modified: 2015-09-9T12:10:00+00:00




 * File:          app/code/local/Pb/Pbgsp/Model/Pdf/Tax.php
 * Copyright:     Copyright (c) 2015 Pitney Bowes <info@pb.com> / All rights reserved.
 */
	
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

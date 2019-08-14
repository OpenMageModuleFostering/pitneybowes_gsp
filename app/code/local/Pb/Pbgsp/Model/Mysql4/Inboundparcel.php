<?php
/**
 * Product:       Pb_Pbgsp (1.1.2)
 * Packaged:      2015-09-23T12:09:53+00:00
 * Last Modified: 2015-09-14T12:11:20+00:00




 * File:          app/code/local/Pb/Pbgsp/Model/Mysql4/Inboundparcel.php
 * Copyright:     Copyright (c) 2015 Pitney Bowes <info@pb.com> / All rights reserved.
 */
	class Pb_Pbgsp_Model_Mysql4_Inboundparcel extends Mage_Core_Model_Mysql4_Abstract {
		public function _construct()
		{
			$this->_init('pb_pbgsp/inboundparcel', 'inbound_parcel_id');
		}
	}
	
?>

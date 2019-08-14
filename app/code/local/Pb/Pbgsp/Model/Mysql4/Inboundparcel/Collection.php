<?php
/**
 * Product:       Pb_Pbgsp (1.3.0)
 * Packaged:      2015-11-12T06:33:00+00:00
 * Last Modified: 2015-11-04T12:13:20+00:00





 * File:          app/code/local/Pb/Pbgsp/Model/Mysql4/Inboundparcel/Collection.php
 * Copyright:     Copyright (c) 2015 Pitney Bowes <info@pb.com> / All rights reserved.
 */

	class Pb_Pbgsp_Model_Mysql4_Inboundparcel_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {
		public function _construct()
		{
			$this->_init('pb_pbgsp/inboundparcel');
		}
	}
?>

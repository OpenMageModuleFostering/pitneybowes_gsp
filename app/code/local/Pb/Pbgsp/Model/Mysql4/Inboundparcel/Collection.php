<?php
/**
 * Product:       Pb_Pbgsp (1.4.2)
 * Packaged:      2016-09-21T11:45:00+00:00
 * Last Modified: 2016-09-13T10:50:00+00:00
 * File:          app/code/local/Pb/Pbgsp/Model/Mysql4/Inboundparcel/Collection.php
 * Copyright:     Copyright (c) 2016 Pitney Bowes <info@pb.com> / All rights reserved.
 */

	class Pb_Pbgsp_Model_Mysql4_Inboundparcel_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {
		public function _construct()
		{
			$this->_init('pb_pbgsp/inboundparcel');
		}
	}
?>

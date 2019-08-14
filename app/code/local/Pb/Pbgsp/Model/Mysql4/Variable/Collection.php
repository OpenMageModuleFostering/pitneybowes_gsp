<?php
/**
 * Product:       Pb_Pbgsp (1.1.0)
 * Packaged:      2015-09-9T12:10:00+00:00
 * Last Modified: 2015-09-1T15:12:28+00:00




 * File:          app/code/local/Pb/Pbgsp/Model/Mysql4/Variable/Collection.php
 * Copyright:     Copyright (c) 2015 Pitney Bowes <info@pb.com> / All rights reserved.
 */
	class Pb_Pbgsp_Model_Mysql4_Variable_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {
		public function _construct()
		{
			$this->_init('pb_pbgsp/variable');
		}
	}
?>

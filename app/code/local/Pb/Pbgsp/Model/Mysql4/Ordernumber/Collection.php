<?php
/**
 * Product:       Pb_Pbgsp (1.1.1)
 * Packaged:      2015-09-14T12:11:20+00:00
 * Last Modified: 2015-09-9T12:10:00+00:00




 * File:          app/code/local/Pb/Pbgsp/Model/Mysql4/Ordernumber/Collection.php
 * Copyright:     Copyright (c) 2015 Pitney Bowes <info@pb.com> / All rights reserved.
 */
	class Pb_Pbgsp_Model_Mysql4_Ordernumber_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {
		public function _construct()
		{
			$this->_init('pb_pbgsp/ordernumber');
		}
	}
?>

<?php
/**
 * Product:       Pb_Pbgsp (1.0.0)
 * Packaged:      2015-06-04T15:09:31+00:00
 * Last Modified: 2015-06-04T15:00:31+00:00
 * File:          app/code/local/Pb/Pbgsp/Model/Mysql4/Variable.php
 * Copyright:     Copyright (c) 2015 Pitney Bowes <info@pb.com> / All rights reserved.
 */
	class Pb_Pbgsp_Model_Mysql4_Variable extends Mage_Core_Model_Mysql4_Abstract {
		public function _construct()
		{
			$this->_init('pb_pbgsp/variable', 'variable_id');
		}
	}
	
?>

<?php
/**
 * Product:       Pb_Pbgsp (1.3.9)
 * Packaged:      2016-07-26T14:17:00+00:00
 * Last Modified: 2016-06-23T10:40:00+00:00
 * File:          app/code/local/Pb/Pbgsp/Model/Mysql4/Variable.php
 * Copyright:     Copyright (c) 2016 Pitney Bowes <info@pb.com> / All rights reserved.
 */
	class Pb_Pbgsp_Model_Mysql4_Variable extends Mage_Core_Model_Mysql4_Abstract {
		public function _construct()
		{
			$this->_init('pb_pbgsp/variable', 'variable_id');
		}
	}
	
?>

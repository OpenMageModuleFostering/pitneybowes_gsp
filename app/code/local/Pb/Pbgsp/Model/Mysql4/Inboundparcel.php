<?php
	class Pb_Pbgsp_Model_Mysql4_Inboundparcel extends Mage_Core_Model_Mysql4_Abstract {
		public function _construct()
		{
			$this->_init('pb_pbgsp/inboundparcel', 'inbound_parcel_id');
		}
	}
	
?>

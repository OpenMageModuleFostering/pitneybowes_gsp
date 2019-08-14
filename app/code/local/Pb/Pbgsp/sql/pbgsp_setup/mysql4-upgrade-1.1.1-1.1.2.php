<?php
/**
 * Product:       Pb_Pbgsp (1.2.1)
 * Packaged:      2015-10-07T12:08:45+00:00
 * Last Modified: 2015-10-01T12:11:15+00:00





 * File:          app/code/local/Pb/Pbgsp/sql/pbgsp_setup/mysql4-install-1.0.0.php
 * Copyright:     Copyright (c) 2015 Pitney Bowes <info@pb.com> / All rights reserved.
 */


	$installer = $this;

	$installer->startSetup();

	$installer->run("

				Alter TABLE {$this->getTable('pb_pbgsp/inboundparcel')} add `mage_order_shipment_number` varchar(50) null;

				
				Alter TABLE {$this->getTable('pb_pbgsp/ordernumber')} add `original_shipping_address` varchar(150) null;
				");

	$installer->endSetup();
?>

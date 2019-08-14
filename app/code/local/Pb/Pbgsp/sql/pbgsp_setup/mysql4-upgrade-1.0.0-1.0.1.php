<?php
/**
 * Product:       Pb_Pbgsp (1.0.1)
 * Packaged:      2015-09-21T15:12:31+00:00
 * Last Modified: 2015-06-04T15:00:31+00:00

 * File:          app/code/local/Pb/Pbgsp/sql/pbgsp_setup/mysql4-install-1.0.0.php
 * Copyright:     Copyright (c) 2015 Pitney Bowes <info@pb.com> / All rights reserved.
 */


	$installer = $this;

	$installer->startSetup();

	$installer->run("


					Alter TABLE {$this->getTable('pb_pbgsp/ordernumber')} add `hub_city` varchar(50) null;



					");

	$installer->endSetup();
?>

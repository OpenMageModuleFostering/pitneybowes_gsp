<?php
/**
 * Product:       Pb_Pbgsp (1.4.3)
 * Packaged:      2016-12-06T09:30:00+00:00
 * Last Modified: 2016-09-21T11:45:00+00:00
 * File:          app/code/local/Pb/Pbgsp/sql/pbgsp_setup/mysql4-install-1.0.0.php
 * Copyright:     Copyright (c) 2016 Pitney Bowes <info@pb.com> / All rights reserved.
 */


$installer = $this;

$installer->startSetup();

$installer->run("

					DROP TABLE IF EXISTS {$this->getTable('pb_pbgsp/shipmentemail')};
					CREATE TABLE {$this->getTable('pb_pbgsp/shipmentemail')} (
					`shipmentemail_id` int(11) unsigned NOT NULL auto_increment,
					`shipment_id` varchar(255) NOT NULL default '',
					`created_date` int NOT NULL ,
					`email_sent` int NULL ,
					PRIMARY KEY (`shipmentemail_id`)
					) ENGINE=InnoDB DEFAULT CHARSET=utf8;



					");

$installer->endSetup();
?>

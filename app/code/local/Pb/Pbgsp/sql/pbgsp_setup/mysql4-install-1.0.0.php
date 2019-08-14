<?php



	$installer = $this;

	$installer->startSetup();

	$installer->run("

					DROP TABLE IF EXISTS {$this->getTable('pb_pbgsp/variable')};
					CREATE TABLE {$this->getTable('pb_pbgsp/variable')} (
					`variable_id` int(11) unsigned NOT NULL auto_increment,
					`name` varchar(255) NOT NULL default '',
					`value` varchar(255) NOT NULL default '',
					PRIMARY KEY (`variable_id`)
					) ENGINE=InnoDB DEFAULT CHARSET=utf8;

					DROP TABLE IF EXISTS {$this->getTable('pb_pbgsp/ordernumber')};
					CREATE TABLE {$this->getTable('pb_pbgsp/ordernumber')} (
					`ordernumber_id` int(11) unsigned NOT NULL auto_increment,
					`cp_order_number` varchar(255) NOT NULL default '',
					`mage_order_number` varchar(255) NOT NULL default '',
					`confirmed` tinyint NOT NULL default 0,
					`referenced` tinyint NOT NULL default 0,
					`hub_id` varchar(50) null,
					`hub_street1` varchar(50) null,
					`hub_street2` varchar(50) null,
					`hub_province_or_state` varchar(50) null,
					`hub_country` varchar(50) null,
					PRIMARY KEY (`ordernumber_id`)
					) ENGINE=InnoDB DEFAULT CHARSET=utf8;

					DROP TABLE IF EXISTS {$this->getTable('pb_pbgsp/inboundparcel')};
					CREATE TABLE {$this->getTable('pb_pbgsp/inboundparcel')} (
					`inbound_parcel_id` int(11) unsigned NOT NULL auto_increment,
					`inbound_parcel` varchar(255) NOT NULL default '',
					`mage_order_number` varchar(255) NOT NULL default '',
					`pb_order_number` varchar(255) NOT NULL default '',
					PRIMARY KEY (`inbound_parcel_id`)
					) ENGINE=InnoDB DEFAULT CHARSET=utf8;

					");

$eav = Mage::getResourceModel('catalog/setup', 'catalog_setup');//new Mage_Eav_Model_Entity_Setup('catalog_setup');
//$eav->removeAttribute('catalog_product','pb_pbgsp_upload');
$eav->addAttribute('catalog_product', 'pb_pbgsp_upload', array(
    'type' => 'int',
    'input' => 'text',
    'label' => 'Last PBGSP upload timestampt',
    'global' => 2,
    'user_defined' => 0,
    'required' => 0,
    'visible' => 1,
    'default' => 0

));

	$installer->endSetup();
?>

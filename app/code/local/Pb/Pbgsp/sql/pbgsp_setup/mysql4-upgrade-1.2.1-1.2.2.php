<?php
/**
 * Product:       Pb_Pbgsp (1.2.2)
 * Packaged:      2015-10-21T12:09:20+00:00
 * Last Modified: 2015-10-07T12:08:45+00:00





 * File:          app/code/local/Pb/Pbgsp/sql/pbgsp_setup/mysql4-install-1.0.0.php
 * Copyright:     Copyright (c) 2015 Pitney Bowes <info@pb.com> / All rights reserved.
 */


	$installer = $this;

	$installer->startSetup();


$eav = Mage::getResourceModel('catalog/setup', 'catalog_setup');

$eav->removeAttribute('catalog_product','pb_pbgsp_upload_active');
$eav->addAttribute('catalog_product', 'pb_pbgsp_upload_active', array(
    'type' => 'int',
    'input' => 'select',
    'label' => 'Export to PBGSP',
    'source' => 'eav/entity_attribute_source_boolean',
    'global' => 2,
    'user_defined' => 0,
    'required' => 0,
    'visible' => 1,
    'default' => 1,
    'group' => 'PBGSP'

));

$eav->removeAttribute('catalog_product','pb_pbgsp_upload_delete');
$eav->addAttribute('catalog_product', 'pb_pbgsp_upload_delete', array(
    'type' => 'int',
    'input' => 'select',
    'label' => 'Delete from PBGSP',
    'source' => 'eav/entity_attribute_source_boolean',
    'global' => 2,
    'user_defined' => 0,
    'required' => 0,
    'visible' => 1,
    'default' => 1,
    'group' => 'PBGSP'

));


	$installer->endSetup();
?>

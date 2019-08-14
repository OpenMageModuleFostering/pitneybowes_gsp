<?php
/**
 * Product:       Pb_Pbgsp (1.4.2)
 * Packaged:      2016-09-21T11:45:00+00:00
 * Last Modified: 2016-09-13T10:50:00+00:00
 * File:          app/code/local/Pb/Pbgsp/sql/pbgsp_setup/mysql4-install-1.0.0.php
 * Copyright:     Copyright (c) 2016 Pitney Bowes <info@pb.com> / All rights reserved.
 */


$installer = $this;

$installer->startSetup();

$eav = Mage::getResourceModel('catalog/setup', 'catalog_setup');

	// reverse version 1.3.2
	$eav->removeAttribute('catalog_product','pb_pbgsp_commodity_height');
	$eav->removeAttribute('catalog_product','pb_pbgsp_commodity_width');
    $eav->removeAttribute('catalog_product','pb_pbgsp_commodity_length');
    $eav->removeAttribute('catalog_product','pb_pbgsp_package_weight');
	$eav->removeAttribute('catalog_product','pb_pbgsp_package_height');
    $eav->removeAttribute('catalog_product','pb_pbgsp_package_width');
    $eav->removeAttribute('catalog_product','pb_pbgsp_package_length');

    $eav->addAttribute('catalog_product', 'pb_pbgsp_commodity_height', array(
        'type' => 'decimal',
        'input' => 'text',
        'label' => 'Commodity Height',
        'global' => 2,
        'user_defined' => 0,
        'required' => 0,
        'visible' => 1,
        'default' => 0,
        'group' => 'PBGSP'

    ));

    $eav->addAttribute('catalog_product', 'pb_pbgsp_commodity_width', array(
        'type' => 'decimal',
        'input' => 'text',
        'label' => 'Commodity Width',
        'global' => 2,
        'user_defined' => 0,
        'required' => 0,
        'visible' => 1,
        'default' => 0,
        'group' => 'PBGSP'

    ));

    $eav->addAttribute('catalog_product', 'pb_pbgsp_commodity_length', array(
        'type' => 'decimal',
        'input' => 'text',
        'label' => 'Commodity Length',
        'global' => 2,
        'user_defined' => 0,
        'required' => 0,
        'visible' => 1,
        'default' => 0,
        'group' => 'PBGSP'

    ));

    $eav->addAttribute('catalog_product', 'pb_pbgsp_package_weight', array(
        'type' => 'decimal',
        'input' => 'text',
        'label' => 'Package Weight',
        'global' => 2,
        'user_defined' => 0,
        'required' => 0,
        'visible' => 1,
        'default' => 0,
        'group' => 'PBGSP'

    ));
    $eav->addAttribute('catalog_product', 'pb_pbgsp_package_height', array(
        'type' => 'decimal',
        'input' => 'text',
        'label' => 'Package Height',
        'global' => 2,
        'user_defined' => 0,
        'required' => 0,
        'visible' => 1,
        'default' => 0,
        'group' => 'PBGSP'

    ));
$eav->addAttribute('catalog_product', 'pb_pbgsp_package_width', array(
    'type' => 'decimal',
    'input' => 'text',
    'label' => 'Package Width',
    'global' => 2,
    'user_defined' => 0,
    'required' => 0,
    'visible' => 1,
    'default' => 0,
    'group' => 'PBGSP'

));
$eav->addAttribute('catalog_product', 'pb_pbgsp_package_length', array(
    'type' => 'decimal',
    'input' => 'text',
    'label' => 'Package Length',
    'global' => 2,
    'user_defined' => 0,
    'required' => 0,
    'visible' => 1,
    'default' => 0,
    'group' => 'PBGSP'

));


	$installer->endSetup();
?>

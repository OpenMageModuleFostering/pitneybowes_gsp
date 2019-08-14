<?php
/**
 * Product:       Pb_Pbgsp (1.3.0)
 * Packaged:      2015-11-12T06:33:00+00:00
 * Last Modified: 2015-11-04T12:13:20+00:00





 * File:          app/code/local/Pb/Pbgsp/sql/pbgsp_setup/mysql4-install-1.0.0.php
 * Copyright:     Copyright (c) 2015 Pitney Bowes <info@pb.com> / All rights reserved.
 */


	$installer = $this;

	$installer->startSetup();
$versionInfo =  Mage::getVersionInfo();
$version = floatval($versionInfo['major'].'.'.$versionInfo['minor']);
if(Mage::getEdition() == Mage::EDITION_COMMUNITY && $version <= 1.7) {
    $eav = Mage::getResourceModel('catalog/setup', 'catalog_setup');//new Mage_Eav_Model_Entity_Setup('catalog_setup');
    $eav->removeAttribute('catalog_product','pb_pbgsp_upload');
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
    $eav->removeAttribute('catalog_category','pb_pbgsp_upload');
    $eav->addAttribute('catalog_category', 'pb_pbgsp_upload', array(
        'type' => 'int',
        'input' => 'text',
        'label' => 'Last PBGSP upload timestampt',
        'global' => 2,
        'user_defined' => 0,
        'required' => 0,
        'visible' => 1,
        'default' => 0,
        'group' => 'PBGSP'

    ));


}


	$installer->endSetup();
?>

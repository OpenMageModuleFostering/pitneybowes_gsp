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

$eavAttribute = new Mage_Eav_Model_Mysql4_Entity_Attribute();
$attrributeId = $eavAttribute->getIdByCode('catalog_product', 'pb_pbgsp_package_weight');
$attribute = Mage::getModel('catalog/resource_eav_attribute')->load($attrributeId);
$attribute->setFrontendLabel('Package Weight (lbs.)')->save();

$attrributeId = $eavAttribute->getIdByCode('catalog_product', 'pb_pbgsp_commodity_height');
$attribute = Mage::getModel('catalog/resource_eav_attribute')->load($attrributeId);
$attribute->setFrontendLabel('Commodity Height (in.)')->save();

$attrributeId = $eavAttribute->getIdByCode('catalog_product', 'pb_pbgsp_commodity_width');
$attribute = Mage::getModel('catalog/resource_eav_attribute')->load($attrributeId);
$attribute->setFrontendLabel('Commodity Width (in.)')->save();

$attrributeId = $eavAttribute->getIdByCode('catalog_product', 'pb_pbgsp_commodity_length');
$attribute = Mage::getModel('catalog/resource_eav_attribute')->load($attrributeId);
$attribute->setFrontendLabel('Commodity Length (in.)')->save();



$attrributeId = $eavAttribute->getIdByCode('catalog_product', 'pb_pbgsp_package_height');
$attribute = Mage::getModel('catalog/resource_eav_attribute')->load($attrributeId);
$attribute->setFrontendLabel('Package Height (in.)')->save();

$attrributeId = $eavAttribute->getIdByCode('catalog_product', 'pb_pbgsp_package_width');
$attribute = Mage::getModel('catalog/resource_eav_attribute')->load($attrributeId);
$attribute->setFrontendLabel('Package Width (in.)')->save();

$attrributeId = $eavAttribute->getIdByCode('catalog_product', 'pb_pbgsp_package_length');
$attribute = Mage::getModel('catalog/resource_eav_attribute')->load($attrributeId);
$attribute->setFrontendLabel('Package Length (in.)')->save();

$installer->endSetup();
?>

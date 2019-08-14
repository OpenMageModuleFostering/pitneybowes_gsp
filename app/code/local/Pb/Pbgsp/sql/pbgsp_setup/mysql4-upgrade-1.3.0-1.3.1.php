<?php
/**
 * Product:       Pb_Pbgsp (1.3.7)
 * Packaged:      2016-06-01T14:02:28+00:00
 * Last Modified: 2016-04-14T14:05:10+00:00
 * File:          app/code/local/Pb/Pbgsp/sql/pbgsp_setup/mysql4-install-1.0.0.php
 * Copyright:     Copyright (c) 2016 Pitney Bowes <info@pb.com> / All rights reserved.
 */


$installer = $this;

$installer->startSetup();
$versionInfo =  Mage::getVersionInfo();
$version = floatval($versionInfo['major'].'.'.$versionInfo['minor']);
$eav = Mage::getResourceModel('catalog/setup', 'catalog_setup');
if(Mage::getEdition() == Mage::EDITION_ENTERPRISE && $version <= 1.12) {
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
	// reverse version 1.3.2
	$eav->removeAttribute('catalog_product','pb_pbgsp_product_condition');
	$eav->removeAttribute('catalog_product','pb_pbgsp_upload_active');
    $eav->removeAttribute('catalog_product','pb_pbgsp_upload_delete');
    $eav->removeAttribute('catalog_product','pb_pbgsp_upload_deleted_on');
	
	$eav->addAttribute('catalog_product', 'pb_pbgsp_product_condition', array(
        'type' => 'varchar',
        'input' => 'select',
        'source' => 'pb_pbgsp/productattributesource_productconditions',
        'label' => 'Product Condition',
        'global' => 2,
        'user_defined' => 0,
        'required' => 0,
        'visible' => 1,
        'default' => 0

    ));
	
	
	// reverse version 1.3.3
	
	
	
	$type = 'datetime';
    $input = 'datetime';
	if( (Mage::getEdition() == Mage::EDITION_ENTERPRISE && $version <= 1.12) ||
		 Mage::getEdition() == Mage::EDITION_COMMUNITY && $version <= 1.7) {
		$type = 'int';
		$input = 'text';
	}
	$eav->addAttribute('catalog_product', 'pb_pbgsp_upload_delete', array(
		'type' => 'int',
		'input' => 'select',
		'source' => 'eav/entity_attribute_source_boolean',
		'label' => 'Delete from PBGSP',
		'global' => 2,
		'user_defined' => 0,
		'required' => 0,
		'visible' => 1,
		'default' => 0

	));

	$eav->addAttribute('catalog_product', 'pb_pbgsp_upload_deleted_on', array(
		'type' => $type,
		'input' => $input,
		'label' => 'Deleted from PBGSP on',
		'global' => 2,
		'user_defined' => 0,
		'required' => 0,
		'visible' => 1,
		'default' => 0

	));

	// reverse version 1.3.4
	
	$entityTypeId = Mage::getModel('catalog/product')->getResource()->getTypeId();
	$sets = Mage::getModel('eav/entity_attribute_set')
			->getResourceCollection()
			->addFilter('entity_type_id', $entityTypeId);
	
	//loop through all the attribute sets
	foreach ($sets as $set)
	{
		$attributeSetId = $set->getId();
		$group = Mage::getModel('eav/entity_attribute_group')
				->getResourceCollection()
				->setAttributeSetFilter($attributeSetId)
				->addFieldToFilter('attribute_group_name','PBGSP')
				->setSortOrder()
				->getFirstItem();
			 
		$groupId = $group->getId();
		//if group doesn't present , create it.
		if(empty($groupId)){
			try{
				$modelGroup = Mage::getModel('eav/entity_attribute_group');
				$modelGroup->setAttributeGroupName('PBGSP')
					->setAttributeSetId($attributeSetId)
					->setSortOrder(100);
				$modelGroup->save();
				
			//$attributeGroupObject = new Varien_Object($installer->getAttributeGroup($entityTypeId ,$attributeSetId,'PBGSP'));
			
				$groups = Mage::getModel('eav/entity_attribute_group')
						->getResourceCollection()
						->setAttributeSetFilter($attributeSetId)
						->addFieldToFilter('attribute_group_name','PBGSP')
						->setSortOrder()
						->getFirstItem();

				$groupId = $groups->getAttributeGroupId();
			
			}catch(Exception $e){
				 Mage::printException($e);
			}
			 
		}
	 
		$codes = array('pb_pbgsp_upload_delete','pb_pbgsp_upload_deleted_on','pb_pbgsp_product_condition','pb_pbgsp_upload');
	 
		foreach ($codes as $code)
		{
			$attributeInfo = Mage::getResourceModel('eav/entity_attribute_collection')
				->setCodeFilter($code)
				->getFirstItem();
			//$attCode = $attributeInfo->getAttributeCode();
			$attId = $attributeInfo->getId();
			if(!empty($attId) && !empty($groupId)){
				$newItem = Mage::getModel('eav/entity_attribute');
				$newItem->setEntityTypeId($entityTypeId) 
					->setAttributeSetId($attributeSetId) // Attribute Set ID
					->setAttributeGroupId($groupId) // Group Id
					->setAttributeId($attId) 
					->setSortOrder(10) 
					->save();
				
			}
			
		}
	}

	$installer->endSetup();
?>

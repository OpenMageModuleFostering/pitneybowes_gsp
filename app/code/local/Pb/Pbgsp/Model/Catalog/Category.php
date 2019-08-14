<?php
/**
 * Product:       Pb_Pbgsp (1.4.3)
 * Packaged:      2016-12-06T09:30:00+00:00
 * Last Modified: 2016-09-21T11:45:00+00:00
 * File:          app/code/local/Pb/Pbgsp/Model/Catalog/Category.php
 * Copyright:     Copyright (c) 2016 Pitney Bowes <info@pb.com> / All rights reserved.
 */
class Pb_Pbgsp_Model_Catalog_Category {
	protected $category;
	private static $roots;
	protected $url;
	public function __construct($id,$url=null) {
		if (gettype($id) == "object" && get_class($id) == "Mage_Catalog_Model_Category") {
			$this->category = $id;
		} else {
			$this->category = Mage::getModel('catalog/category')->load($id);
		}
        $this->url = $url;
	}
	public function getCategory() {
        return $this->category;
    }
	public function getName() {
		return $this->category->getName();
	}
	
	public function getCode() {
		return $this->category->getId();
	}
	
	public function getUrl() {
		//Pb_Pbgsp_Model_Util::log($this->category->getUrl());
        if($this->url)
            return $this->url;
		return $this->category->getUrl();
        //return Mage::app()->getStore($this->category->getStoreId())->getBaseUrl();
	}
	
	public function isRoot() {
		if (!$this->category->getParentId()) {
			return true;
		}
		if (!self::$roots) {
			$categoryOp = new Mage_Adminhtml_Block_Catalog_Category_Abstract();
			self::$roots = $categoryOp->getRootIds();
		}
		foreach (self::$roots as $rootId) {
			if ($this->getCode() == $rootId) {
				return true;
			}
		}
		return false;
	}
	
	public function getParentCode() {
		return $this->category->getParentId();
	}
    private function _shouldUpload($lastDiff) {

        if(!$lastDiff)
            return true; //full catalog upload

        //Pb_Pbgsp_Model_Util::log($this->category->getPbPbgspUploadActive());
        if(!$this->category->getPbPbgspUploadActive()) return false;
        $lastUpload = $this -> category -> getPbPbgspUpload();
        $updatedAt = $this->category->getUpdatedAt();
        //Pb_Pbgsp_Model_Util::log($this->getName()." lastUpload:$lastUpload  UpdatedAt:$updatedAt");
        if (!$lastUpload) {
            // First upload.
            return true;
        } else if ($lastUpload < $updatedAt) {
            // Added after the last diff
            return true;
        } else {
            return false;
        }
    }
	public function writeToFile($file,$lastDiff) {
        if($this->_shouldUpload($lastDiff)) {
            $name = Pb_Pbgsp_Model_Util::stripHtml($this->getName());
            $name = preg_replace("/[^A-Za-z0-9 ,.\-\+=;:\\(){}\[\]@?%$#]/", '', $name);

            $parentCateID = '';
            if($this->getParentCode() != 1 && !$this->isRoot()){
                $parentCateID =   $this->getParentCode();
            }
            fputcsv($file,array($this->getCode(),$parentCateID,$name,'',$this->getUrl()));
			fseek($file, -1, SEEK_CUR);
			fwrite($file, "\r\n");
            return true;
        }
        return false;

	}



	
	// This fuction starts at the Magento root and its not very efficient... see isRoot for proper implementation. 
	// This function is only used by the API upload.
	public static function getAllRootCategories() {
		$roots = array();
				
		$categories = Mage::getModel('catalog/category')->getCollection();//->getSelect()->order(array('IF(`id`>0, `id`, 9999) ASC'));
		$categories = $categories->getIterator();
		
		foreach ($categories as $category) {
			$parents = $category->getParentId();
			if (!$parents) {
				$rootCategory = new Pb_Pbgsp_Model_Catalog_Category($category->getId());
				array_push($roots,$rootCategory);
			}
		}
		return $roots;
	}
	
}
?>

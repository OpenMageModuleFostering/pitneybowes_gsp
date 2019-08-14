<?php
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
	
	public function writeToFile($file) {
        $name = Pb_Pbgsp_Model_Catalog_File::stripHtml($this->getName());
        $name = preg_replace("/[^A-Za-z0-9 ,.\-\+=;:\\(){}\[\]@?%$#]/", '', $name);
//		$string = "<Category>\n";
//		$string .= "<CategoryCode>".htmlentities($this->getCode())."</CategoryCode>\n";
//		$string .= "<Name>".htmlentities($name)."</Name>\n";
//		//$string .= "<URL><![CDATA[".htmlentities($this->getUrl())."]]></URL>\n";
//        $string .= "<URL><![CDATA[".htmlentities($this->getUrl())."]]></URL>\n";
//		if($this->getParentCode() != 1 && !$this->isRoot()){
//			$string .= "<ParentCategoryCode>".htmlentities($this->getParentCode())."</ParentCategoryCode>\n";
//		}
//		$string .= "</Category>\n";
        //fputcsv($this->file,array('CATEGORY_ID','PARENT_CATEGORY_ID','NAME','ID_PATH','URL'));
        $parentCateID = '';
        if($this->getParentCode() != 1 && !$this->isRoot()){
            $parentCateID =   $this->getParentCode();
        }
        fputcsv($file,array($this->getCode(),$parentCateID,$name,'',$this->getUrl()));
        //fwrite($file,$string);
	}

	// This is done via API
	public function upload() {
		echo "Uploading category: ".$this->category->getName()."... ";
		if (Pb_Pbgsp_Model_Api::addCategory($this)) {
			echo "ok";
		} else {
			echo "failed!";
		}
		echo "<br/>";
		
		$products = $this->category->getProductCollection();
		// TODO: Products that are part of multiple categories will be uploaded 
		// multiple times.
		foreach ($products as $product) {
			$clearPathProduct = new Pb_Pbgsp_Model_Catalog_Product($product);
			echo "Uploading product: ".$clearPathProduct->getName()."... ";
			if (Pb_Pbgsp_Model_Api::addCommodity($clearPathProduct)) {
				echo "ok";
			} else {
				echo "failed!";
			}
			echo "<br/>";
		}
		
		
		$children = $this->category->getChildrenCategories();
		
		// TODO: this code will run out of memory if there are a lot of categories.... 
		// Need to convert the recursive call to a loop at some point.
		foreach ($children as $child) {
			$childCategory = new Pb_Pbgsp_Model_Catalog_Category($child);
			$childCategory->upload();
		}
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

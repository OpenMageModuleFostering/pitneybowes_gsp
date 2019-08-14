<?php

/**
 * Product:       Pb_Pbgsp (1.1.0)
 * Packaged:      2015-09-9T12:10:00+00:00
 * Last Modified: 2015-09-1T15:12:28+00:00


 * File:          app/code/local/Pb/Pbgsp/Model/Catalog/File.php
 * Copyright:     Copyright (c) 2015 Pitney Bowes <info@pb.com> / All rights reserved.
 */

class Pb_Pbgsp_Model_Catalog_File {


    private $file;
    private $filename;
    private $lastDiff;
    private $productIds;
    private $lastFileName;
    private $uploadedCategories;
    public function __construct($lastDiff = false) {
        $this->lastDiff = $lastDiff;
        $this->productIds = array();
        $this->uploadedCategories = array();
    }

    private function _getTempDir() {
        $appRoot = Mage::getRoot();
        $mageRoot = dirname($appRoot);
        $configOptions = Mage::getModel('core/config_options');
        $tmpDir = $mageRoot . '/var/pbgsp/tmp/';
        $configOptions->createDirIfNotExists( $tmpDir);
        chmod($tmpDir, 0777);
        return $tmpDir;
    }


    private function _getDataFileName($dataFeedName,$part=null) {
        $partName = '';
        if($part)
            $partName = '_part'.$part;
        $fileName = Pb_Pbgsp_Model_Credentials::getCatalogSenderID() . "_".$dataFeedName."_update_". Pb_Pbgsp_Model_Credentials::getPBID().'_'.date('Ymd_His').'_'.mt_rand(100000, 999999);
        if($part == 1)
            $this->lastFileName = $fileName;
        else
            $fileName = $this->lastFileName;
        return $fileName.$partName.'.csv';
    }
    private function _createNewCommoditiyFile($part=null) {
        $fileName = $this->_getDataFileName('catalog',$part);


        $this->filename =  $this->_getTempDir().$fileName;
        $this->file = fopen($this->filename,"w+");
        chmod($this->filename, 0777);
        //add header row
        fputcsv($this->file,array('MERCHANT_COMMODITY_REF_ID','COMMODITY_NAME_TITLE','SHORT_DESCRIPTION',
            'LONG_DESCRIPTION','RETAILER_ID','COMMODITY_URL','RETAILER_UNIQUE_ID','PCH_CATEGORY_ID',
            'RH_CATEGORY_ID','STANDARD_PRICE','WEIGHT_UNIT','DISTANCE_UNIT','COO','IMAGE_URL','PARENT_SKU',
            'CHILD_SKU','PARCELS_PER_SKU','UPC','UPC_CHECK_DIGIT','GTIN','MPN','ISBN','BRAND','MANUFACTURER',
            'MODEL_NUMBER','MANUFACTURER_STOCK_NUMBER','COMMODITY_CONDITION','COMMODITY_HEIGHT',
            'COMMODITY_WIDTH','COMMODITY_LENGTH','PACKAGE_WEIGHT','PACKAGE_HEIGHT','PACKAGE_WIDTH',
            'PACKAGE_LENGTH','HAZMAT','ORMD','CHEMICAL_INDICATOR','PESTICIDE_INDICATOR','AEROSOL_INDICATOR',
            'RPPC_INDICATOR','BATTERY_TYPE','NON_SPILLABLE_BATTERY','FUEL_RESTRICTION','SHIP_ALONE',
            'RH_CATEGORY_ID_PATH','RH_CATEGORY_NAME_PATH'));//,'RH_CATEGORY_URL_PATH','GPC','COMMODITY_WEIGHT','HS_CODE','CURRENCY'

        fflush($this->file);
    }
    private function _createNewCategoryFile($part=null) {
        $fileName = $this->_getDataFileName('category-tree',$part);


        $this->filename =  $this->_getTempDir().$fileName;
        $this->file = fopen($this->filename,"w+");
        chmod($this->filename, 0777);
        //add header row
        fputcsv($this->file,array('CATEGORY_ID','PARENT_CATEGORY_ID','NAME',
            'ID_PATH','URL'));

        fflush($this->file);
    }




    private function _getSelectedCategory($categories,$catId) {
        foreach($categories as $cat) {
            if($cat->getId() == $catId)
                return $cat;
        }
        return false;
    }
    /**
     * Extracts the categories and products and exports them into xml file.
     */
    public function createNew() {

        $maxRecordsCount = Pb_Pbgsp_Model_Credentials::getMaxRecordsCount();
        if(!$maxRecordsCount)
            $maxRecordsCount = 10000;

        $prodCount = 0;
        $catCount = 0;
        $fileRecordCount = 0;
        //get stores which has disabled clearpath
        $stores = Mage::app()->getStores();
        $defaultStoreUrl = Mage::getBaseUrl();
        $secDefaultStoreUrl = str_replace("http","https",$defaultStoreUrl);
        $disabledStores = array();
        $addedCategories = array();
        $part=1;
        foreach($stores as $store) {

            $isActive =  Mage::getStoreConfig('carriers/pbgsp/active',$store);
            $baseURL = $store->getBaseUrl();

            if(!$isActive) {
                $disabledStores[] = $store;
                Pb_Pbgsp_Model_Util::log("Disabled store". $store->getId(). ' '. $store->getCode());
            }
            else {
                $rootId     = Mage::app()->getStore($store->getId())->getRootCategoryId();
                $rootCat = Mage::getModel('catalog/category')->load($rootId);
                /* @var $rootCat Mage_Catalog_Model_Category */
                $rootCat->setData('store',$store);
                Pb_Pbgsp_Model_Util::log("Default store url $defaultStoreUrl store ".$store->getCode()." store base url".
                    $baseURL." root cat url". $rootCat->getUrl(). " cat name". $rootCat->getName());
                $catUrl = str_replace($defaultStoreUrl,$baseURL,$rootCat->getUrl());
                if($catUrl == $rootCat->getUrl())
                    $catUrl = str_replace($secDefaultStoreUrl,$baseURL,$rootCat->getUrl());
                $cat = new Pb_Pbgsp_Model_Catalog_Category($rootCat,$catUrl);
                if(!$this->file || $fileRecordCount > $maxRecordsCount)
                {
                    $this->_createNewCategoryFile($part);
                    $fileRecordCount=0;
                    $part++;
                }
                if($cat->writeToFile($this->file,$this->lastDiff))
                    $this->uploadedCategories[] = $rootCat;
                fflush($this->file);
                $catCount++;
                $fileRecordCount++;
                $categories = Mage::getModel('catalog/category')
                    ->getCollection()
                    ->addUrlRewriteToResult()
                    ->addAttributeToSelect('name')
                    ->addAttributeToSelect('pb_pbgsp_upload_active')
                    ->addAttributeToSelect('pb_pbgsp_upload')
                    ->addAttributeToSelect('updated_at')
                    ->addFieldToFilter('path', array('like'=> "1/$rootId/%"));
                $addedCategories[] = $rootCat;

                foreach($categories as $category) {
                    if(!$this->file || $fileRecordCount > $maxRecordsCount)
                    {
                        $this->_createNewCategoryFile($part);
                        $fileRecordCount=0;
                        $part++;
                    }
                    /* @var $category Mage_Catalog_Model_Category */
                    $category->setStoreId($store->getId());
                    $category->setData('store',$store);
                    $addedCategories[] = $category;




                    $catUrl = str_replace($defaultStoreUrl,$baseURL,$category->getUrl());
                    if($catUrl == $category->getUrl())
                        $catUrl = str_replace($secDefaultStoreUrl,$baseURL,$category->getUrl());
                    $cat = new Pb_Pbgsp_Model_Catalog_Category($category,$catUrl);
                    if($cat->writeToFile($this->file,$this->lastDiff))
                        $this->uploadedCategories[] = $category;
                    fflush($this->file);
                    $catCount++;
                    $fileRecordCount++;
                }
            }
        }

        fclose($this->file);
        $this->_stripPartFromFileName($part);
        //fwrite($this->file,"</CategoryList>\n<CommodityList>\n");
        if(count($this->uploadedCategories) == 0) {
            //remove empty category files.
            $tmpDir = $this->_getTempDir();
            $exportedFiles = array_diff(scandir($tmpDir), array('..', '.'));
            $this->_removeExportedFiles($exportedFiles);
        }
        $fileRecordCount=0;
        $part=1;
        $this->_createNewCommoditiyFile($part);
        $part++;
        $addedProducts = array();
        foreach($addedCategories as $category) {
            //Mage::app()->setCurrentStore($category->getStoreId());

            $count = Mage::getModel('catalog/product')->getCollection()
                ->addCategoryFilter($category)
                ->count();
            $pageSize = 400;
            $currIndex = 0;
            while($currIndex < $count) {
                $productCollection = Mage::getModel('catalog/product')->getCollection()

                    ->addCategoryFilter($category)

                    ->addAttributeToSelect('name')
                    ->addAttributeToSelect('sku')
                    ->addAttributeToSelect('country_of_manufacture')
                    ->addAttributeToSelect('description')
                    ->addAttributeToSelect('product_url')
                    ->addAttributeToSelect('type_id')
                    ->addAttributeToSelect('pb_pbgsp_upload')
                    ->addAttributeToSelect('updated_at')
                    // ->addUrlRewrite($category->getId()) //this will add the url rewrite.
                    ->addAttributeToSelect('price')
                    ->addAttributeToSelect('weight');

                $baseURL = Mage::app()->getStore($category->getStoreId())->getBaseUrl();
                $productUrlFormat = $baseURL ."catalog/product/view/id/%d/";
                foreach($productCollection as $product) {
                    /* @var $product Mage_Catalog_Model_Product */
                    if($product->getTypeId() == 'virtual')
                        continue;
                    $cateIds = $product->getCategoryIds();
                    $cateId = 0;
                    foreach($cateIds as $cId) {
                        $cateId = $cId;//get lower level of category
                    }
                    $prodCat = $this->_getSelectedCategory($addedCategories,$cateId);
                    if(!$prodCat)
                        $prodCat = $category;
                    $cIds = explode('/',$prodCat->getPath());
                    $cIds = array_slice($cIds,1);//remove root category
                    $prodCat->setData('id_path',implode(':',$cIds));
                    $prodCat->setData('name_path',$this->_getCatNamePath($addedCategories,$cIds));
                    if($product->getTypeId() == 'configurable'  ) {
                        $productType = $product->getTypeInstance(true);
                        $allowedProducts = $productType->getUsedProducts(null, $product);
                        /** @var $childProduct Mage_Catalog_Model_Product */

                        foreach($allowedProducts as $childProduct) {
                            if(!array_key_exists($childProduct->getSku(),$addedProducts)) {
                                if( $fileRecordCount > $maxRecordsCount)
                                {
                                    $this->_createNewCommoditiyFile($part);
                                    $fileRecordCount=0;
                                    $part++;
                                }
                                $pbChildProduct = new Pb_Pbgsp_Model_Catalog_Product($childProduct->getId(),sprintf($productUrlFormat,$product->getId()));
                                $this->writeProduct($pbChildProduct,$cateId,$product->getSku(),$prodCat);
                                $addedProducts[$childProduct->getSku()] = "added";
                                $prodCount++;
                                $fileRecordCount++;
                            }
                        }
                    }
                    else {
                        if(!array_key_exists($product->getSku(),$addedProducts)) {
                            $pbProduct = new Pb_Pbgsp_Model_Catalog_Product($product,sprintf($productUrlFormat,$product->getId()));
                            if( $fileRecordCount > $maxRecordsCount)
                            {
                                $this->_createNewCommoditiyFile();
                                $fileRecordCount=0;
                                $part++;
                            }
                            $this->writeProduct($pbProduct,$cateId,null,$prodCat);
                            $prodCount++;
                            $fileRecordCount++;
                            $addedProducts[$product->getSku()] = "added";

                        }


                    }


                }
                $currIndex += $pageSize;

            }
        }



//        fwrite($this->file,"</CommodityList>\n</Catalog>\n");
        fflush($this->file);

        $this->_stripPartFromFileName($part);
        if(count($this->productIds) == 0) {
            $this->_removeExportedFilesByeType('catalog');
        }
    }

    private function _getCatNamePath($categories,$cateIds) {

        $names = array();
        foreach($cateIds as $id) {
            foreach($categories as $cat) {
                if($cat->getId() == $id) {
                    array_push($names, $cat->getName());
                    break;
                }
            }
        }
        // Pb_Pbgsp_Model_Util::log($names);
        return implode('|',$names);
    }

    /**
     * Updates the pb_pbgsp_upload time in all uploaded products
     */
    public function updateLastProductUpload() {

        $attribute = Mage::getModel('eav/config')->getAttribute('catalog_product', 'pb_pbgsp_upload');
        if(!$attribute->getAttributeId()) {
            $setup = new Mage_Eav_Model_Entity_Setup('core_setup');
            $setup->addAttribute('catalog_product', 'pb_pbgsp_upload', array(
                'label'		=> 'Last Pb upload timestampt',
                'type'		=> 'datetime',
                'input'		=> 'datetime',
                'visible'		=> false,
                'required'	=> false,
                'position'	=> 1,
            ));
        }

        $productIds = array_unique($this->productIds);
        Pb_Pbgsp_Model_Util::log('Uploaded products' . count($productIds));
        $updated = 0;
        foreach ($productIds as $prodId) {
            $product = Mage::getModel("catalog/product")->load($prodId);
            //Pb_Pbgsp_Model_Util::log('Updating product '. $product->getSKU());
            $product->unlockAttribute('pb_pbgsp_upload');
            $product->setPbPbgspUpload(time());
            try{
                $product->save();
                $updated++;
            }catch(Exception $e){
                Pb_Pbgsp_Model_Util::log("There was a problem saving the product with sku ". $product->getSku() ." Error Message \n" . $e->getMessage());
            }

            //Pb_Pbgsp_Model_Util::log($product->getId()." ".$product->getName()." ".$product->getPbPbgspUpload());
        }
        Pb_Pbgsp_Model_Util::log("$updated products' pb_pbgsp_upload updated");
    }

    public function updateLastCategoryUpload() {

        $attribute = Mage::getModel('eav/config')->getAttribute('catalog_category', 'pb_pbgsp_upload');
        if(!$attribute->getAttributeId()) {
            $setup = new Mage_Eav_Model_Entity_Setup('core_setup');
            $setup->addAttribute('catalog_category', 'pb_pbgsp_upload', array(
                'label'		=> 'Last Pb upload timestampt',
                'type'		=> 'datetime',
                'input'		=> 'datetime',
                'visible'		=> false,
                'required'	=> false,
                'position'	=> 1,
            ));
        }


        Pb_Pbgsp_Model_Util::log('Uploaded categories' . count($this->uploadedCategories));
        $updated = 0;
        foreach ($this->uploadedCategories as $category) {
            $category->unlockAttribute('pb_pbgsp_upload');
            $category->setPbPbgspUpload(time());
            try{
                $category->save();
                $updated++;
            }catch(Exception $e){
                Pb_Pbgsp_Model_Util::log("There was a problem saving the category  ". $category->getName() ." Error Message \n" . $e->getMessage());
            }

            //Pb_Pbgsp_Model_Util::log($product->getId()." ".$product->getName()." ".$product->getPbPbgspUpload());
        }
        Pb_Pbgsp_Model_Util::log("$updated categories' pb_pbgsp_upload updated");
    }

    /**
     * Uploads the xml file to clearpath SFTP server
     */

    private function _getNotificationDir() {
        $tmpDir = $this->_getTempDir();
        $configOptions = Mage::getModel('core/config_options');
        $notificationDir = $tmpDir . 'notifications';
        $configOptions->createDirIfNotExists( $notificationDir);
        chmod($notificationDir, 0777);
        return $notificationDir;
    }

    public function processStatusNotifications() {
        try {

            Pb_Pbgsp_Model_Util::log("Processing status notifications");
            $adminEmail = Pb_Pbgsp_Model_Credentials::getAdminEmail();
            if(!isset($adminEmail) || $adminEmail=='') {
                Pb_Pbgsp_Model_Util::log("Admin email is not set quiting status notification process.");
                return;
            }


            $notificationDir = $this->_getNotificationDir();
            $this->_downloadStatusNotifications($notificationDir);
            $notificationFiles = array_diff(scandir($notificationDir), array('..', '.'));
            if(count($notificationFiles) > 0) {
                $mail = new Zend_Mail();
                $mail->setFrom('no-reply@pb.com','Pitney Bowes');
                $mail->addTo($adminEmail)
                    ->setSubject('Catalog Export Error')
                    ->setBodyText('Catalog Export Error. Please see attached files.');
                $fileCount = 0;
                foreach($notificationFiles as $notificationFile) {
                    $attachFile = false;
                    if($this->_endsWith($notificationFile,'.err') || $this->_endsWith($notificationFile,'.log') ) {
                        if(Pb_Pbgsp_Model_Credentials::isCatalogErrorNotificationEnabled())
                            $attachFile = true;

                    }
                    else if($this->_endsWith($notificationFile,'.ok')) {
                        if(Pb_Pbgsp_Model_Credentials::isCatalogSuccessNotificationEnabled()) {
                            $attachFile = true;
                            $mail->setSubject('Catalog Export Successful');
                        }

                    }
                    if($attachFile) {
                        $file = $notificationDir.'/'. $notificationFile;
                        $at = new Zend_Mime_Part(file_get_contents($file));
                        $at->filename = basename($file);
                        $at->disposition = Zend_Mime::DISPOSITION_ATTACHMENT;
                        $at->encoding = Zend_Mime::ENCODING_8BIT;

                        $mail->addAttachment($at);
                        $fileCount++;
                    }
                }
                if($fileCount > 0) {
                    $mail->send();
                    Pb_Pbgsp_Model_Util::log("Email sent with error or success files.");
                }

                else {
                    Pb_Pbgsp_Model_Util::log(" No error files found.");
                }
                //keep these files until next upload and delete files from old upload
                $this->_cleanNotificationFiles();
            }
        }
        catch (Exception $e) {
            Pb_Pbgsp_Model_Util::log("Error in processStatusNotifications:". $e->getMessage());
            Pb_Pbgsp_Model_Util::log($e->getTraceAsString());

        }

    }
    private function _cleanNotificationFiles() {
        //keep resent files until next upload and delete files from old upload
        $lastExportedFiles = $this->_getLastExportedFileNames();
        if(!$lastExportedFiles)
            return;
        $notificationDir = $this->_getNotificationDir();
        $notificationFiles = array_diff(scandir($notificationDir), array('..', '.'));

        foreach($notificationFiles as $notificationFile) {
            $path_parts = pathinfo($notificationFile);
            $localFileNameWithoutExt = $path_parts['filename'];
            $isOldFile = true;
            foreach($lastExportedFiles as $lastExportedFile) {
                $lastExportedFileNameWithoutExt = str_replace('.gpg','',str_replace('.csv','',$lastExportedFile));
                if($lastExportedFileNameWithoutExt == $localFileNameWithoutExt) {
                    $isOldFile = false;
                    break;
                }
            }
            if($isOldFile) {
                //this is from 2nd last upload, remove it from disk
                unlink($notificationDir.'/'.$notificationFile);
            }
        }

        $exportedFilesVariable = $this->_getExportedFilesVariable();
        if(!isset($exportedFilesVariable))
        {
            $exportedFilesVariable = Mage::getModel("pb_pbgsp/variable");
            $exportedFilesVariable->setName("exportedFiles");
        }

        $exportedFilesVariable->setValue('');
        $exportedFilesVariable->save();
    }
    private function _downloadStatusNotifications($notificationDir) {

        $credentials = $this->_getSftpCredentials();
        try {
            //Pb_Pbgsp_Model_Util::log($credentials);
            $sftpDumpFile = new Varien_Io_Sftp();
            Pb_Pbgsp_Model_Util::log("Connecting SFTP to download notification files.");
            $sftpDumpFile->open(
                $credentials
            );
            $rootDir = Pb_Pbgsp_Model_Credentials::getSftpCatalogDirectory();
            if(!$this->_endsWith($rootDir,'/'))
                $rootDir = $rootDir.'/';
            $processedDir = $rootDir.'outbound';
            $sftpDumpFile->cd($processedDir);
            $files = $sftpDumpFile->ls();

            //Pb_Pbgsp_Model_Util::log($files);

            $exportedFiles = $this->_getLastExportedFileNames();
            Pb_Pbgsp_Model_Util::log("Last exported files.");
            Pb_Pbgsp_Model_Util::log($exportedFiles);
            if(!$exportedFiles) {
                Pb_Pbgsp_Model_Util::log("exportedFiles is null");
                return;
            }


            foreach($files as $file) {
                foreach($exportedFiles as $exportedFile) {
                    if($exportedFile == '')
                        continue;
                    $fileNameWithoutExtension = str_replace(".gpg","",str_replace(".csv","",$exportedFile));
                    if($this->_startsWith($file['text'],$fileNameWithoutExtension)) {
                        $dest = $notificationDir.'/'.$file['text'];
                        $sftpDumpFile->read($file['text'],$dest);
                        Pb_Pbgsp_Model_Util::log($file['text']. " downloaded from server");
                    }
                }

            }
            $sftpDumpFile->close();
        }
        catch (Exception $e) {
            Pb_Pbgsp_Model_Util::log($e->getMessage());
            //Pb_Pbgsp_Model_Util::log($e->getTraceAsString());

            Pb_Pbgsp_Model_Util::log("Pb Module could not connect to sftp server: ".$credentials['host']." Wrong username/password. Postponing Status Notification process.");
            Pb_Pbgsp_Model_Util::log($credentials);
            throw $e;
        }

    }

    private function _encryptExportedFiles($exportedFiles) {
        $encryptedFiles = array();
        $publicKey = Pb_Pbgsp_Model_Credentials::getPublicKey();
        if(!isset($publicKey) || $publicKey=='') {
            Pb_Pbgsp_Model_Util::log('Public key is not set cannot encrypt catalog files.');
            return $exportedFiles;
        }
        try {
            $gnupg = new gnupg();
            $keyInfo = $gnupg->import($publicKey);
            $gnupg->addencryptkey($keyInfo['fingerprint']);
            $tmpDir = $this->_getTempDir();
            Pb_Pbgsp_Model_Util::log('Encrypting the files.');
            foreach($exportedFiles as $exportedFile) {
                $fileName = $tmpDir . $exportedFile;
                if(is_dir($fileName))
                    continue;
                $encryptedFileName = $exportedFile.'.gpg';
                Pb_Pbgsp_Model_Util::log("Encrypted file $encryptedFileName");
                file_put_contents($tmpDir.$encryptedFileName,$gnupg->encrypt(file_get_contents($fileName)));
                $encryptedFiles[] = $encryptedFileName;
            }
            return $encryptedFiles;
        }
        catch (Exception $e) {
            Pb_Pbgsp_Model_Util::log($e->getTraceAsString());
            Pb_Pbgsp_Model_Util::log("Error in encryption.");
            return $encryptedFiles;
        }


    }
    public function upload() {

        $tmpDir = $this->_getTempDir();
        $exportedFiles = array_diff(scandir($tmpDir), array('..', '.'));
//        if (count($this->productIds) == 0) {
//            // No new products to send, don't send anything.
//            Pb_Pbgsp_Model_Util::log("No new products to send, don't send anything.");
//            $this->_removeExportedFiles($exportedFiles);
//
//            return;
//        }

        Pb_Pbgsp_Model_Util::log("Pb catalog file upload started");
        try {

            if(Pb_Pbgsp_Model_Credentials::isEncryptionEnabled()) {
                $exportedFiles = $this->_encryptExportedFiles($exportedFiles);
            }
            else {
                Pb_Pbgsp_Model_Util::log('Encryption is not enabled.'.Pb_Pbgsp_Model_Credentials::isEncryptionEnabled());
            }
            $sftpDumpFile = new Varien_Io_Sftp();
            $credentials = $this->_getSftpCredentials();

            Pb_Pbgsp_Model_Util::log($credentials);

            $sftpDumpFile->open(
                $credentials
            );
            //Upload to SFTP
            $rootDir = Pb_Pbgsp_Model_Credentials::getSftpCatalogDirectory();
            if(!$this->_endsWith($rootDir,'/'))
                $rootDir = $rootDir.'/';
            $tmpSFTPDir = $rootDir.'tmp';
            $inboundDir = $rootDir.'inbound';
            $uploadedFiles = array();
            foreach($exportedFiles as $exportedFile) {
                $fileName = $tmpDir . $exportedFile;
                if(is_dir($fileName))
                    continue;

                Pb_Pbgsp_Model_Util::log("CD to $tmpSFTPDir");
                $sftpDumpFile->cd($tmpSFTPDir);
                Pb_Pbgsp_Model_Util::log("Uploading $fileName");
                $sftpDumpFile->write($exportedFile, file_get_contents($fileName));
                Pb_Pbgsp_Model_Util::log("Moving ".$tmpSFTPDir."/$exportedFile"." to ".$inboundDir."/$exportedFile");
                $sftpDumpFile->mv($tmpSFTPDir."/$exportedFile",$inboundDir."/$exportedFile");
                $uploadedFiles[] = $exportedFile;
            }
            $sftpDumpFile->close();

        } catch (Exception $e) {
            Pb_Pbgsp_Model_Util::log($e->getMessage());
            Pb_Pbgsp_Model_Util::log($e->getTraceAsString());
            Pb_Pbgsp_Model_Util::log("Pb Module could not connect to sftp server: ".$credentials['host']." Wrong username/password. Postponing catalog upload.");
            return;
        }

        Pb_Pbgsp_Model_Util::log("Pb catalog file upload ended");

        $this->_removeExportedFiles($exportedFiles);
        $this->updateLastCategoryUpload();
        $this->updateLastProductUpload();
        $this->_logExportedFileInDB($uploadedFiles);
    }

    private function _getExportedFilesVariable() {
        $collection = Mage::getModel("pb_pbgsp/variable")->getCollection();
        $exportedFilesVariable = null;
        foreach ($collection as $variable) {

            if ($variable->getName() == "exportedFiles") {
                $exportedFilesVariable = $variable;
                break;
            }

        }
        return $exportedFilesVariable;
    }
    private function _getLastExportedFileNames() {
        $exportedFilesVariable = $this->_getExportedFilesVariable();

        if(!isset($exportedFilesVariable) || $exportedFilesVariable->getValue() == '')
            return false;
        $exportedFiles = explode('|',$exportedFilesVariable->getValue());

        return $exportedFiles;
    }
    private function _logExportedFileInDB($exportedFiles) {
        $strExportedFiles = implode('|',$exportedFiles);
        $exportedFilesVariable = $this->_getExportedFilesVariable();
        if(!isset($exportedFilesVariable))
        {
            $exportedFilesVariable = Mage::getModel("pb_pbgsp/variable");
            $exportedFilesVariable->setName("exportedFiles");
        }

        $exportedFilesVariable->setValue($strExportedFiles);
        $exportedFilesVariable->save();
    }
    private function _startsWith($haystack, $needle) {
        // search backwards starting from haystack length characters from the end
        return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== FALSE;
    }
    private function _endsWith($haystack, $needle) {
        // search forward starting from end minus needle length characters
        return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== FALSE);
    }
    private function _removeExportedFiles($exportedFiles) {
        $tmpDir = $this->_getTempDir();
        foreach($exportedFiles as $exportedFile) {
            $fileName = $tmpDir . $exportedFile;
            if(is_dir($fileName))
                continue;
            unlink($fileName);
            $fileName = str_replace(".gpg","",$fileName);//remove unencrypted file
            if(is_file($fileName))
                unlink($fileName);
        }
    }


    private function _removeExportedFilesByeType($fileType) {
        $tmpDir = $this->_getTempDir();
        $exportedFiles = array_diff(scandir($tmpDir), array('..', '.'));
        foreach($exportedFiles as $exportedFile) {
            $fileName = $tmpDir . $exportedFile;
            if(is_dir($fileName))
                continue;
            if(strpos($fileName,$fileType) !== false) {

                unlink($fileName);
                $fileName = str_replace(".gpg","",$fileName);//remove unencrypted file
                if(is_file($fileName))
                    unlink($fileName);
            }


        }
    }




    /**
     * @param Pb_Pbgsp_Model_Catalog_Product $product
     * @param string $categoryCode
     */
    private function writeProduct($product,$categoryCode,$parentSku,$category)
    {
        if ($product->shouldUpload($this->lastDiff)) {
            array_push($this->productIds,$product->getMageProduct()->getId());
            // Pb_Pbgsp_Model_Util::log("Product SKU:" . $product->getSKU());
            $product->writeToFile($this->file,$categoryCode,$parentSku,$category);
            fflush($this->file);
        }
    }

    public static function stripHtml($text)  {
        return preg_replace("/<\s*\/\s*\w\s*.*?>|<\s*br\s*>/",'',preg_replace("/<\s*\w.*?>/", '', $text));
    }

    /**
     * Loads products without categories and logs them in log file
     */
    public function logProdWithoutCategories() {
        $productCollection = Mage::getResourceModel('catalog/product_collection')
            ->setStoreId(0)
            ->joinField('category_id', 'catalog/category_product', 'category_id', 'product_id=entity_id', null, 'left')
            ->addAttributeToFilter('category_id', array('null' => true))
            ->addAttributeToSelect('*');


        $productCollection->getSelect()->group('product_id')->distinct(true);

        $productCollection->load();
        $skus = '';
        foreach($productCollection as $product) {
            $skus = $skus . $product->getSku() . ",";

        }
        Pb_Pbgsp_Model_Util::log("Products without categories:" . $skus);
    }

    /**
     * @return array
     */
    private function _getSftpCredentials()
    {
        $credentials = array(
            'host' => Pb_Pbgsp_Model_Credentials::getSftpHostname(),
            "port" => Pb_Pbgsp_Model_Credentials::getSftpPort(),
            'username' => Pb_Pbgsp_Model_Credentials::getSftpUsername(),
            'password' => Pb_Pbgsp_Model_Credentials::getSftpPassword(),
            'timeout' => '10'
        );
        return $credentials;
    }

    /**
     * @param $part
     */
    private function _stripPartFromFileName($part)
    {
        if ($part == 2) {
            //there is only one part remove part1 from filename
            rename($this->filename, str_replace('_part1', '', $this->filename));
        }
    }
}
?>

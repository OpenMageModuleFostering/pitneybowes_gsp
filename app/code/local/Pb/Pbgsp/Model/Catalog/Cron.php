<?php
/**
 * Product:       Pb_Pbgsp (1.0.3)
 * Packaged:      2015-09-1T15:12:28+00:00
 * Last Modified: 2015-08-25T15:12:28+00:00



 * File:          app/code/local/Pb/Pbgsp/Model/Catalog/Cron.php
 * Copyright:     Copyright (c) 2015 Pitney Bowes <info@pb.com> / All rights reserved.
 */
	class Pb_Pbgsp_Model_Catalog_Cron {
		
		private $lastDiff;
		private $lastFull;
				

		
		public function catalogSync() {
				Pb_Pbgsp_Model_Util::log("Start Sync...");
			$diffPeriod = Mage::getStoreConfig('carriers/pbgsp/catalog_diff');
			$fullPeriod = Mage::getStoreConfig('carriers/pbgsp/catalog_full');

			$collection = Mage::getModel("pb_pbgsp/variable")->getCollection();
			foreach ($collection as $variable) {
				Pb_Pbgsp_Model_Util::log($variable->getName()." -> ".$variable->getValue());
				if ($variable->getName() == "lastFull") {
					$this->lastFull = $variable;
				}
				if ($variable->getName() == "lastDiff") {
					$this->lastDiff = $variable;
				}
			}


            $appRoot = Mage::getRoot();
            $mageRoot = dirname($appRoot);
            $configOptions = Mage::getModel('core/config_options');
            $configOptions->createDirIfNotExists($mageRoot.'/var/pbgsp');
            chmod($mageRoot . '/var/pbgsp/', 0777);

            if (!isset($this->lastFull) || $this->lastFull->getValue() < time() - $fullPeriod*24*3600) {
				// Full catalog upload needed
				$this->uploadCatalog();
			} else if (!isset($this->lastDiff) && $this->lastFull->getValue() < time() - $diffPeriod*3600) {
				// First catalog diff upload
				$this->uploadCatalog($this->lastFull);
			} else if (isset($this->lastDiff) && ($this->lastDiff->getValue() < time() - $diffPeriod*3600 &&
												  $this->lastFull->getValue() < time() - $diffPeriod*3600)) {
				// Catalog diff upload
				$this->uploadCatalog($this->lastDiff);
			}
            else {
                Pb_Pbgsp_Model_Util::log('PB Export cron. Do not export catalog');

            }

		}

		public function uploadCatalog($lastDiff = false) {
			if (!$lastDiff) {
				Pb_Pbgsp_Model_Util::log("Full catalog upload");
				$file = new Pb_Pbgsp_Model_Catalog_File();
				if (isset($this->lastFull)) {
					$this->lastFull->setValue(time());
				} else {
					$this->lastFull = Mage::getModel("pb_pbgsp/variable");
					$this->lastFull->setName("lastFull");
					$this->lastFull->setValue(time());
				}
				$this->lastFull->save();
			} else {
				Pb_Pbgsp_Model_Util::log("catalog diff");
				$file = new Pb_Pbgsp_Model_Catalog_File($lastDiff->getValue());
				if (isset($this->lastDiff)) {
					$this->lastDiff->setValue(time());
				} else {
					$this->lastDiff = Mage::getModel("pb_pbgsp/variable");
					$this->lastDiff->setName("lastDiff");
					$this->lastDiff->setValue(time());
				}
				$this->lastDiff->save();
			}

			Pb_Pbgsp_Model_Util::log("RAY:Create Function Started");

 $file->createNew();

Pb_Pbgsp_Model_Util::log("RAY:Create Function Complete");

Pb_Pbgsp_Model_Util::log("RAY:upload Started");
$file->upload();
$file->logProdWithoutCategories();
Pb_Pbgsp_Model_Util::log("RAY:upload Complete");
        }

        public function processStatusNotifications() {
            $file = new Pb_Pbgsp_Model_Catalog_File();
            $file->processStatusNotifications();
        }

	}


?>

<?php
/**
 * Product:       Pb_Pbgsp (1.4.2)
 * Packaged:      2016-09-21T11:45:00+00:00
 * Last Modified: 2016-09-13T10:50:00+00:00
 * File:          app/code/local/Pb/Pbgsp/Model/Catalog/Cron.php
 * Copyright:     Copyright (c) 2016 Pitney Bowes <info@pb.com> / All rights reserved.
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
				//Pb_Pbgsp_Model_Util::log($variable->getName()." -> ".$variable->getValue());
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

            $lastFull = '';
            $lastDiff = '';
            $currentTime = time();

            $fullPeriodSeconds = $fullPeriod*24*3600;
            $diffPeriodSeconds = $diffPeriod*3600;
            if(isset($this->lastFull))
                $lastFull = $this->lastFull->getValue();
            if(isset($this->lastDiff))
                $lastDiff = $this->lastDiff->getValue();

//            Pb_Pbgsp_Model_Util::log("Current time:" . date('m-d-Y H:i:s',$currentTime)."\n");
//            Pb_Pbgsp_Model_Util::log("Lastfull:".date('m-d-Y H:i:s',$lastFull)."\n" );
//            Pb_Pbgsp_Model_Util::log("LastDiff:".date('m-d-Y H:i:s',$lastDiff)."\n");
//            Pb_Pbgsp_Model_Util::log("fullPeriodSeconds:$fullPeriodSeconds\n");
//            Pb_Pbgsp_Model_Util::log("diffPeriodSeconds:$diffPeriodSeconds\n");

            $fulPeriodDiff = $currentTime - $fullPeriodSeconds;
            $diffPeriodDiff = $currentTime - $diffPeriodSeconds;
//            Pb_Pbgsp_Model_Util::log("fulPeriodDiff :" . $fulPeriodDiff ."\n");
//            Pb_Pbgsp_Model_Util::log("diffPeriodDiff : ". $diffPeriodDiff . "\n");
//            Pb_Pbgsp_Model_Util::log("lastFull - fulPeriodDiff : ". ($lastFull - $fulPeriodDiff ) . "\n");
//            Pb_Pbgsp_Model_Util::log("lastFull - diffPeriodDiff : ". ($lastFull - $diffPeriodDiff ) . "\n");
//            Pb_Pbgsp_Model_Util::log("lastDiff - diffPeriodDiff : ". ($lastDiff - $diffPeriodDiff ) . "\n");

            if (!isset($this->lastFull) || $this->lastFull->getValue() < time() - $fullPeriodSeconds) {
				// Full catalog upload needed
				$this->uploadCatalog();
			} else if (!isset($this->lastDiff) && $this->lastFull->getValue() < time() - $diffPeriodSeconds) {
				// First catalog diff upload
				$this->uploadCatalog($this->lastFull);
			} else if (isset($this->lastDiff) && ($this->lastDiff->getValue() < time() - $diffPeriodSeconds &&
												  $this->lastFull->getValue() < time() - $diffPeriodSeconds)) {
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
                Pb_Pbgsp_Model_Util::log("Full catalog upload\n");
				$file = new Pb_Pbgsp_Model_Catalog_File();
				if (isset($this->lastFull)) {
					$this->lastFull->setValue(time());
				} else {
					$this->lastFull = Mage::getModel("pb_pbgsp/variable");
					$this->lastFull->setName("lastFull");
					$this->lastFull->setValue(time());
				}

			} else {
				Pb_Pbgsp_Model_Util::log("catalog diff");
                Pb_Pbgsp_Model_Util::log("catalog diff\n");
				$file = new Pb_Pbgsp_Model_Catalog_File($lastDiff->getValue());
				if (isset($this->lastDiff)) {
					$this->lastDiff->setValue(time());
				} else {
					$this->lastDiff = Mage::getModel("pb_pbgsp/variable");
					$this->lastDiff->setName("lastDiff");
					$this->lastDiff->setValue(time());
				}

			}

			Pb_Pbgsp_Model_Util::log("RAY:Create Function Started");

            $file->createNew();

            Pb_Pbgsp_Model_Util::log("RAY:Create Function Complete");

            Pb_Pbgsp_Model_Util::log("RAY:upload Started");
            $file->upload();
            if($this->lastFull)
                $this->lastFull->save();
            if($this->lastDiff)
                $this->lastDiff->save();
            $file->logProdWithoutCategories();
            Pb_Pbgsp_Model_Util::log("RAY:upload Complete");
        }

        public function processStatusNotifications() {
            $file = new Pb_Pbgsp_Model_Catalog_File();
            $file->processStatusNotifications();
        }

	}


?>

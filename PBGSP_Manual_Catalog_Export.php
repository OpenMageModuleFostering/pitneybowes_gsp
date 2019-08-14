<?php

ini_set('display_errors', 1);
error_reporting(E_ALL | E_STRICT);

$mageFilename = 'app/Mage.php';
require_once $mageFilename;

umask(0);
$mageRunCode = isset($_SERVER['MAGE_RUN_CODE']) ? $_SERVER['MAGE_RUN_CODE'] : '';
$mageRunType = isset($_SERVER['MAGE_RUN_TYPE']) ? $_SERVER['MAGE_RUN_TYPE'] : 'store';

Mage::app($mageRunCode, $mageRunType);
//Mage::setIsDeveloperMode(true);

Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);


class TestClass {
    public static function TestExport() {
        $pbExport = Mage::getModel('pb_pbgsp/catalog_cron');
        $pbExport->catalogSync();
        //$pbExport->processStatusNotifications();
        Mage::log('Catalog sync process completed successfully');
        echo 'Catalog sync process completed successfully';


    }


    public static function testProcessNotification() {
        $pbExport = Mage::getModel('pb_pbgsp/catalog_cron');

        $pbExport->processStatusNotifications();
    }

    public static function testAsnGeneration() {
        $pbAsnCron = Mage::getModel('pb_pbgsp/inboundparcel');
        $pbAsnCron->generateInboundParcelPreAdviceCron();
    }

}
if(isset($argv) && count($argv) > 1) {
    if($argv[1] == '1')
     TestClass::testProcessNotification();
    else if($argv[1] == '2')
        TestClass::testAsnGeneration();
}
else {
    TestClass::TestExport();
}

//
?>


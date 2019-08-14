<?php

class Pb_Pbgsp_Model_Util  {

    const FILE_NAME = 'pbgsp.log';
    public static function log($message) {
        if( Mage::getStoreConfig('carriers/pbgsp/separate_log_file'))
            Mage::log($message,null,self::FILE_NAME);
        else
            Mage::log($message);
    }
    public static function logException($e) {
        /* @var Exception $e */
        if( Mage::getStoreConfig('carriers/pbgsp/separate_log_file')) {
            Mage::log($e->getMessage(),null,self::FILE_NAME);
            Mage::log($e->getTraceAsString(),null,self::FILE_NAME);
        }
        else
            Mage::logException($e);
    }
}

?>

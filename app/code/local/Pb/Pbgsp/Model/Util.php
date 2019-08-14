<?php
/**
 * Product:       Pb_Pbgsp (1.3.2)
 * Packaged:      2016-01-11T11:12:49+00:00
 * Last Modified: 2015-12-18T11:00:00+00:00





 * File:          app/code/local/Pb/Pbgsp/Model/Util.php
 * Copyright:     Copyright (c) 2015 Pitney Bowes <info@pb.com> / All rights reserved.
 */
class Pb_Pbgsp_Model_Util  {

    const FILE_NAME = 'pbgsp.log';
    public static function log($message) {
        //Mage::log('is logging enabled:'.Pb_Pbgsp_Model_Credentials::isLoggingEnabled(),null,self::FILE_NAME);
        if(Pb_Pbgsp_Model_Credentials::isLoggingEnabled()) {
            if( Mage::getStoreConfig('carriers/pbgsp/separate_log_file'))
                Mage::log($message,null,self::FILE_NAME);
            else
                Mage::log($message);
        }

//        $logDir  = Mage::getBaseDir('var') . DS . 'log';
//        $logFile = $logDir . DS . 'test.log';
//        if (!file_exists($logFile)) {
//            file_put_contents($logFile, '');
//            chmod($logFile, 0777);
//        }
//
//        file_put_contents($logFile, $message. PHP_EOL);
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

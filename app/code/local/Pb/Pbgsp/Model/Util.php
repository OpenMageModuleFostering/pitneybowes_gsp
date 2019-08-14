<?php
/**
 * Product:       Pb_Pbgsp (1.0.3)
 * Packaged:      2015-09-1T15:12:28+00:00
 * Last Modified: 2015-08-25T15:12:28+00:00



 * File:          app/code/local/Pb/Pbgsp/Model/Util.php
 * Copyright:     Copyright (c) 2015 Pitney Bowes <info@pb.com> / All rights reserved.
 */
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

<?php
/**
 * Product:       Pb_Pbgsp (1.4.3)
 * Packaged:      2016-12-06T09:30:00+00:00
 * Last Modified: 2016-09-21T11:45:00+00:00
 * File:          app/code/local/Pb/Pbgsp/Model/Util.php
 * Copyright:     Copyright (c) 2016 Pitney Bowes <info@pb.com> / All rights reserved.
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

    public static function isPbOrder($shipMethod) {


        $len = strlen("pbgsp_");
        if (strlen($shipMethod) > $len && substr($shipMethod,0,$len) == "pbgsp_") {
            return true;
        }

        return false;

    }

    public static function chopString($str, $len) {
        $end = $len;
        $lastFour = substr($str,$end - strlen("&amp"),strlen("&amp"));
        $pos = strpos($lastFour,"&");
        if (!($pos === FALSE)) {
            $end = $end - strlen("&amp") + $pos;
        }
        $ret = substr($str,0,$end);
        return $ret;
    }

    public static function stripHtml($text)  {
        return preg_replace("/<\s*\/\s*\w\s*.*?>|<\s*br\s*>/",'',preg_replace("/<\s*\w.*?>/", '', $text));
    }

    public static function getTempDir() {
        $appRoot = Mage::getRoot();
        $mageRoot = dirname($appRoot);
        $configOptions = Mage::getModel('core/config_options');
        $tmpDir = $mageRoot . '/var/pbgsp/tmp/';
        $configOptions->createDirIfNotExists( $tmpDir);
        chmod($tmpDir, 0777);
        return $tmpDir;
    }

    public static function getSftpCredentials()
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

    public static function getCPORD($order)
    {
        if($order) {
            $cpOrder = self::getCpOrderNumber($order);
            if($cpOrder) {
                return $cpOrder -> getCpOrderNumber();
            }

        }

        return false;
    }
    public static function getCpOrderNumber($order)
    {
        if($order) {

            $cpOrder = Mage::getModel("pb_pbgsp/ordernumber")->load($order -> getRealOrderId(),'mage_order_number');
            return $cpOrder;
        }

        return false;
    }

    public static function endsWith($haystack, $needle) {
        // search forward starting from end minus needle length characters
        return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== FALSE);
    }
}

?>

<?php
/**
 * Product:       Pb_Pbgsp (1.1.2)
 * Packaged:      2015-09-23T12:09:53+00:00
 * Last Modified: 2015-09-14T12:11:20+00:00




 * File:          app/code/local/Pb/Pbgsp/Model/Credentials.php
 * Copyright:     Copyright (c) 2015 Pitney Bowes <info@pb.com> / All rights reserved.
 */
class Pb_Pbgsp_Model_Credentials {
	
	public static function decrypt($string) {
		if (!empty($string)) {
			return Mage::helper('core')->decrypt($string);
		}
		return "";
	}
	
	public static function getCheckoutUrl() {
		return Mage::getStoreConfig('carriers/pbgsp/checkout_endpoint');
	}
    public static function getAuthorizationUrl() {
        return Mage::getStoreConfig('carriers/pbgsp/authorization_endpoint');
    }
    public static function getOrderMgmtAPIUrl() {
        return Mage::getStoreConfig('carriers/pbgsp/order_mgmt_api');
    }
    public static function getMaxRecordsCount() {
        return Mage::getStoreConfig('carriers/pbgsp/catalog_size');
    }
    public static function getAdminEmail() {
        return Mage::getStoreConfig('carriers/pbgsp/admin_email');
    }
    public static function getCatalogSenderID() {
        return Mage::getStoreConfig('carriers/pbgsp/catalog_sender_id');
    }
    public static function getDeliveryAdjustmentMinDays() {
        return self::_getIntValue(Mage::getStoreConfig('carriers/pbgsp/delivery_adjustment_min_days'));
    }
    public static function getDeliveryAdjustmentMaxDays() {
        return self::_getIntValue(Mage::getStoreConfig('carriers/pbgsp/delivery_adjustment_max_days'));
    }
    public static function getDomesticShippingFee() {
        return self::_getFloatValue(Mage::getStoreConfig('carriers/pbgsp/domestic_shipping_fee'));
    }
    public static function getDomesticShippingOption() {
        return Mage::getStoreConfig('carriers/pbgsp/domestic_shipping_option');
    }
    public static function isASNGenerationEnabled() {
        return Mage::getStoreConfig('carriers/pbgsp/asn_generation_enabled');
    }
    public static function isFreeTaxEnabled() {
        return Mage::getStoreConfig('carriers/pbgsp/free_tax_enable');
    }
    public static function isCatalogSuccessNotificationEnabled() {
        return Mage::getStoreConfig('carriers/pbgsp/notify_catalog_ok');
    }
    public static function isCatalogErrorNotificationEnabled() {
        return Mage::getStoreConfig('carriers/pbgsp/notify_catalog_err');
    }
    public static function getHandlingFee() {

        return self::_getFloatValue(Mage::getStoreConfig('carriers/pbgsp/handlingfee'));
    }
    private static function _getIntValue($value) {
        $number = 0;

        if(isset($value)) {
            if(is_numeric($value)) {
                $number = intval($value);
            }
        }
        return $number;
    }
    private static function _getFloatValue($value) {
        $number = 0;

        if(isset($value)) {
            if(is_numeric($value)) {
                $number = floatval($value);
            }
        }
        return $number;
    }
    public static function getHandlingOption() {
        return Mage::getStoreConfig('carriers/pbgsp/handlingoption');
    }
/*	public static function getWSDL() {
		if(Mage::getStoreConfig('carriers/pbgsp/environment') == 1) {
			// Production
		} else {
			// Staging
			return "./app/code/local/Pb/Pbgsp/etc/cpAPI.wsdl.xml";
		}
	}*/
	
	public static function getUsername() {
		return self::decrypt(Mage::getStoreConfig('carriers/pbgsp/apiuser'));
	}
	
	public static function getPassword() {
		return self::decrypt(Mage::getStoreConfig('carriers/pbgsp/apipass'));
	}
	
	public static function getMerchantCode() {
		return self::decrypt(Mage::getStoreConfig('carriers/pbgsp/merchantcode'));
	}
	
	public static function getSftpUsername() {
		return self::decrypt(Mage::getStoreConfig('carriers/pbgsp/ftpuser'));
	}
	public static function getSftpPassword() {
		return self::decrypt(Mage::getStoreConfig('carriers/pbgsp/ftppass'));
	}
	public static function getSftpHostname() {
		return Mage::getStoreConfig('carriers/pbgsp/ftphost');
	}
	public static function getSftpPort() {
		return Mage::getStoreConfig('carriers/pbgsp/ftpport');
	}
	public static function getSftpCatalogDirectory() {
		return self::decrypt(Mage::getStoreConfig('carriers/pbgsp/ftpdir'));
	}
    public static function isEncryptionEnabled() {
        return Mage::getStoreConfig('carriers/pbgsp/catalog_encryption_enabled');
    }
    public static function getPublicKey() {
        return Mage::getStoreConfig('carriers/pbgsp/encryption_public_key');
    }

    public static function getPBID() {
        return "16061";
    }

    public static function getReturnAddressStreet1() {
        return Mage::getStoreConfig('carriers/pbgsp/return_address_street1');
    }
    public static function getReturnAddressCity() {
        return Mage::getStoreConfig('carriers/pbgsp/return_address_city');
    }
    public static function getReturnAddressState() {
        return Mage::getStoreConfig('carriers/pbgsp/return_address_state');
    }
    public static function getReturnAddressCountry() {
        return Mage::getStoreConfig('carriers/pbgsp/return_address_country');
    }
    public static function getReturnAddressZip() {
        return Mage::getStoreConfig('carriers/pbgsp/return_address_zip');
    }
}
?>

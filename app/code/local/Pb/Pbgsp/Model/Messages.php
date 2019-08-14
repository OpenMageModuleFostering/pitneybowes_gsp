<?php
/**
 * Product:       Pb_Pbgsp (1.0.0)
 * Packaged:      2015-06-04T15:09:31+00:00
 * Last Modified: 2015-06-04T15:00:31+00:00
 * File:          app/code/local/Pb/Pbgsp/Model/Messages.php
 * Copyright:     Copyright (c) 2015 Pitney Bowes <info@pb.com> / All rights reserved.
 */
class Pb_Pbgsp_Model_Messages {
//	protected $messages = array(
//		101 => array(
//			"code" => "INTERNAL_ERROR",
//			"original" => "Internal Error.",
//			"display" => "Error 101: An error has occurred. Please contact the merchant for assistance."),
//		102 => array(
//			"code" => "NULL_VALUE",
//			"original" => "Null value.",
//			"display" => "A required field is missing, please select a Province."),
//		103 => array(
//			"code" => "INVALID_VALUE",
//			"original" => "Invalid value.",
//			"display" => "Error 103: An invalid value for a required non-null field has been submitted"),
//		105 => array(
//			"code" => "MISSING_RECORD",
//			"original" => "Missing record.",
//			"display" => "Error 105: One or more items have not yet been prepared for international sale. Please remove the item(s) from your basket and try again. <IDENTIFY SKU>"),
//		108 => array(
//			"code" => "CLASSIFICATION_NOT_AVAILABLE",
//			"original" => "Item cannot be classified.",
//			"display" => "Error 108: One or more items have not yet been prepared for international sale. Please remove the item(s) from your basket and try again. <IDENTIFY SKU>"),
//		109 => array(
//			"code" => "QUANTITY_EXCEEDED",
//			"original" => "Quantity exceeded.",
//			"display" => "Error 109: The quantity of one or more selected items exceeds international shipping regulations. <IDENTIFY SKU>"),
//		131 => array(
//			"code" => "SHIPPING_METHODS_NOT_AVAILABLE ",
//			"original" => "No shipping methods available. ",
//			"display" => "Error 131: No international shipping methods are available for the selected items."),
//		139 => array(
//			"code" => "DUTY_NOT_AVAILABLE",
//			"original" => "Duties are not available.",
//			"display" => "Error 139: International duty items are not available for one or more items. Please remove the item(s) from your basket and try again. <IDENTIFY SKU>"),
//		141 => array(
//			"code" => "MISSING_DUTIABLE_PRICE",
//			"original" => "Missing dutiable price.",
//			"display" => "Error 141: One or more selected items do not have a valid dutiable price for international sale. Please remove the item(s) from your basket and try again. <IDENTIFY SKU>"),
//		142 => array(
//			"code" => "INVALID_DUTIABLE_PRICE",
//			"original" => "Invalid dutiable price.",
//			"display" => "Error 142: One or more selected items do not have a valid dutiable price for international sale. Please remove the item(s) from your basket and try again. <IDENTIFY SKU>"),
//		144 => array(
//			"code" => "INVALID_SALE_PRICE",
//			"original" => "Invalid sale price.",
//			"display" => "Error 144: One or more items do not have a valid sale price for international sale. Please remove the item(s) from your basket and try again. <IDENTIFY SKU>"),
//		145 => array(
//			"code" => "TAX_NOT_AVAILABLE",
//			"original" => "Fail to retrieve taxes.",
//			"display" => "Error 145: One or more items does not have tax information for international sale. Please remove the item(s) from your basket and try again. <IDENTIFY SKU>"),
//		154 => array(
//			"code" => "SHIPPING_CANNOT_BE_CALCULATED",
//			"original" => "Shipping cannot be calculated.",
//			"display" => "Error 154: The selected address is not valid for international delivery. Please note that FedEx cannot deliver to PO Boxes."),
//		155 => array(
//			"code" => "SHIPPING_TARIFF_NOT_FOUND",
//			"original" => "Shipping tariff not found.",
//			"display" => "Error 155: The current basket of items exceeds international shipping guidelines. Please remove some items and try again."),
//		161 => array(
//			"code" => "QUANTITY_SHIPPING_RESTRICTION",
//			"original" => "HS quantity shipping restriction.",
//			"display" => "Error 161: One or more items is restricted from international sale to the selected destination. Please remove the item(s) from your basket and try again. <IDENTIFY SKU>"),
//		162 => array(
//			"code" => "QUANTITY_SHIPPING_RESTRICTION",
//			"original" => "HS quantity shipping restriction.",
//			"display" => "Error 161: One or more items exceed maximum value guidelines for international sale to the selected destination. Please remove the item(s) from your basket and try again. <IDENTIFY SKU>"),
//		168 => array(
//			"code" => "SHIPPING_TARIFF_NOT_AVAILABLE",
//			"original" => "The weight of the item is exceeding the maximum accepted by tariffs.",
//			"display" => "Error 168: The weight of the basket exceeds international shipping guidelines. Please remove one or more items and try again."),
//		174 => array(
//			"code" => "SHIPPING_SIZE_RESTRICTIONS",
//			"original" => "Item (commodity or merchant box) didn’t pass shipping size restrictions",
//			"display" => "Error 174: One or more selected items exceed dimensional limits for international sale. Please remove the item(s) from your basket and try again. <IDENTIFY SKU>"),
//		175 => array(
//			"code" => "INVALID_PROVINCE_POSTALCODE_PAIR",
//			"original" => "Canada address validation failed (postal code does not match the province).",
//			"display" => "Error 175: The postal code and province entered do not match.  Please check the postal code and province to ensure that they are entered correctly and try again."),
//		176 => array(
//			"code" => "INVALID_POSTALCODE_FORMAT",
//			"original" => "Invalid Canadian Postal Code format (should be ldldld, where l=letter and d=digit).",
//			"display" => "Error 176: The postal code entered is invalid.  Please check the postal code and try again."),
//		177 => array(
//			"code" => "SHIPPING_VALUE_RESTRICTIONS",
//			"original" => "The order value exceeds Shipping Value Restrictions.",
//			"display" => "Error 177: The basket total exceeds maximum order value guidelines for international sale. Please remove items from the basket and try again."),
//		1001 => array(
//			"code" => "LICENSE",
//			"original" => "Item is restricted for shipping due to license requirements.",
//			"display" => "Error 1001: One or more items is license restricted for international sale. Please remove the item(s) and try again. <IDENTIFY SKU>"),
//		1002 => array(
//			"code" => "SIZE",
//			"original" => "Item is restricted for shipping due to its size.",
//			"display" => "Error 1002: One or more items exceed size limits for international sale. Please remove the item(s) and try again. <IDENTIFY SKU>"),
//		1003 => array(
//			"code" => "PROHIBITED",
//			"original" => "Item is restricted for shipping.",
//			"display" => "Error 1003: One or more items is restricted for international sale. Please remove the item(s) and try again. <IDENTIFY SKU>"),
//		1004 => array(
//			"code" => "CARRIER",
//			"original" => "Item is restricted for shipping due to a carrier restriction. The logistics carrier will not ship this product ",
//			"display" => "Error 1004: One or more items is restricted for international sale. Please remove the item(s) and try again. <IDENTIFY SKU>"),
//		);
//
	public function getDisplayMessage($code,$message) {
//		if (array_key_exists($code,$this->messages)) {
//			return $this->messages[$code]["display"];
//		}

        // moved to config

        $config_data = Mage::getStoreConfig('carriers/pbgsp/error_messages');

        if(isset($config_data["_$code"]['display']))
            return $config_data["_$code"]['display'];

		return $message;
	}
}

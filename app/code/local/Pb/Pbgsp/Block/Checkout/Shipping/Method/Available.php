<?php

/**
 * Product:       Pb_Pbgsp (1.0.2)
 * Packaged:      2015-09-25T15:12:28+00:00
 * Last Modified: 2015-09-21T15:12:31+00:00


 * File:          app/code/local/Pb/Pbgsp/Block/Checkout/Shipping/Method/Available.php
 * Copyright:     Copyright (c) 2015 Pitney Bowes <info@pb.com> / All rights reserved.
 */

	class Pb_Pbgsp_Block_Checkout_Shipping_Method_Available extends Mage_Checkout_Block_Onepage_Shipping_Method_Available {
		
		protected $errors;
		protected $restrictions;
		
		/**
		 * Internal constructor, that is called from real constructor
		 *
		 */
		protected function _construct()
		{
			parent::_construct();
			$this->errors = Mage::getSingleton("customer/session")->getPbRestrictions();
			$cart = Mage::getModel('checkout/cart');
			$items = array();
			foreach($cart->getItems() as $item) {
				$items[$item->getProductId()] = array("name" => $item->getName(), "url" => $item->getProduct()->getProductUrl());
			}
			
			$this->restrictions = array();
			if(count($this->errors) > 0){
				foreach ($this->errors as $sku => $value) {
					$processor = new Pb_Pbgsp_Model_Messages();
					$message = $processor->getDisplayMessage($value["code"],$value["message"]);

                    $product_sku = $this->getSku($value['index']);
                    if($product_sku!=''):
                        $i_sku = Mage::getModel('catalog/product')->loadByAttribute('sku', $product_sku)->getName();
                        $i_msg = "$i_sku needs to be removed or modified to continue.";
                        $message = str_replace("{IDENTIFY SKU}", $i_msg, $message);
                    else:
                        $message = str_replace("{IDENTIFY SKU}", $product_sku, $message);
                    endif;
            		array_push($this->restrictions,array("name" => $items[$sku]["name"],
														 "message" => $message,
														 "sku" => $sku,
														 "url" => $items[$sku]["url"]));
				}
			}
						
		}

      private function getSku($index){
          if($index == null) return '';
          $items = Mage::getSingleton('checkout/cart')->getItems();
          $i = 0;
          foreach ($items as $item):
              if($i++==$index):
                  return $item->getSku();
              endif;
          endforeach;
          return '';
      }
		public function displayRestrictions() {
			if(isset($this->errors)) {
				return true;
			} else {
				return false;
			}
		}
		
		public function getRestrictions() {
			return $this->restrictions;
		}
	}
?>

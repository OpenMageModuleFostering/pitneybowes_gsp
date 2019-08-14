<?php
/**
 * Product:       Pb_Pbgsp (1.4.3)
 * Packaged:      2016-12-06T09:30:00+00:00
 * Last Modified: 2016-09-21T11:45:00+00:00
 * File:          app/code/local/Pb/Pbgsp/Model/Quote/Duty.php
 * Copyright:     Copyright (c) 2016 Pitney Bowes <info@pb.com> / All rights reserved.
 */

class Pb_Pbgsp_Model_Quote_Duty extends Mage_Tax_Model_Sales_Total_Quote_Tax
{
    /**
     * Discount calculation object
     *
     * @var Mage_SalesRule_Model_Validator
     */
    protected $dutyCalculated = false;
    protected $dutyDisplayed = false;

    public function __construct()
    {
	    parent::__construct();
    }
    
    public function getDutyAndTax() {
        Pb_Pbgsp_Model_Util::log('Pb_Pbgsp_Model_Quote_Duty.getDutyAndTax');
	    $amount = Mage::getSingleton("customer/session")->getPbDutyAndTax();
        if($amount) {
            if(Mage::app()->getStore()->getCurrentCurrencyCode() == 'USD')
                return Mage::getSingleton("customer/session")->getPbDutyAndTaxUSD();
        }

        return $amount;
    }

    /**
     * Collect address discount amount
     *
     * @param   Mage_Sales_Model_Quote_Address $address
     * @return  Mage_SalesRule_Model_Quote_Discount
     */
    public function collect(Mage_Sales_Model_Quote_Address $address)
    {
        Pb_Pbgsp_Model_Util::log('Pb_Pbgsp_Model_Quote_Duty.collect');
        parent::collect($address);
                        foreach($address->getAllItems() as $item) {
                    /* @var Mage_Sales_Model_Quote_Item $item */
                            $order = Mage::getSingleton("customer/session")->getPbOrder();
                            if($order) {
                                foreach($order['order']['quoteLines'] as $quoteLine) {
                                    $sku = $quoteLine['merchantComRefId'];
                                    if($item->getSku() == $sku) {
                                        $itemBaseTax = $quoteLine['lineImportation']['total']['value'];
                                        $item->setBaseTaxAmount(floatval($itemBaseTax));
                                        $itemTax = $itemBaseTax;
                                        if(Mage::app()->getStore()->getCurrentCurrencyCode() != 'USD') {

                                            $itemTax = Mage::app()->getStore()->convertPrice($itemBaseTax);

                                        }
                                        $item->setTaxAmount($itemTax);
                                        //$item->save();
                                    }
                                }
                            }


                }
        $allowSpecific = Mage::getStoreConfig('carriers/pbgsp/sallowspecific');
        if($allowSpecific) {
            $activeCountries = Mage::getStoreConfig('carriers/pbgsp/specificcountry');

            if(!(strpos($activeCountries,$address->getCountryId()) !== false)) {
                //Pb_Pbgsp_Model_Util::log($address->getCountryId().' not found');
                Mage::getSingleton("customer/session")->setPbDutyAndTax(0);

                Mage::getSingleton("customer/session")->setPbDutyAndTaxUSD(0);
                return $this;
            }
        }
		if ($this->getDutyAndTax() && Pb_Pbgsp_Model_Util::isPbOrder($address->getShippingMethod())) {
            $items = $this->_getAddressItems($address);
            if (!count($items)) {
                return $this;
            }
		
			$this->_addAmount($this->getDutyAndTax());
			$this->_addBaseAmount($this->getDutyAndTax());
		}
        return $this;
    }


    /**
     * Add discount total information to address
     *
     * @param   Mage_Sales_Model_Quote_Address $address
     * @return  Mage_SalesRule_Model_Quote_Discount
     */
    public function fetch(Mage_Sales_Model_Quote_Address $address)
    {
        Pb_Pbgsp_Model_Util::log('Pb_Pbgsp_Model_Quote_Duty.fetch');
        $amount = $this->getDutyAndTax();
		if ($amount && Pb_Pbgsp_Model_Util::isPbOrder($address->getShippingMethod())) {
			if($this->dutyDisplayed) {
				return $this;
			}
			$this->dutyDisplayed = true;
	
			//$amount = $address->getTaxAmount();
            //$amount = Mage::app()->getStore()->convertPrice($amount);

			$title = "Importation Charges";
			//Pb_Pbgsp_Model_Util::log("Add Duty & Taxes at Duty:" . $amount);
			$address->addTotal(array(
								 'code'  => $this->getCode(),
							     'title' => $title,
							     'value' => $amount
						));
		} else {
			return parent::fetch($address);
		}
        return $this;
    }
}

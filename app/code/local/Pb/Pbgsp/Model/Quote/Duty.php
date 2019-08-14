<?php

/**
 * Product:       Pb_Pbgsp (1.2.1)
 * Packaged:      2015-10-07T12:08:45+00:00
 * Last Modified: 2015-10-01T12:11:15+00:00





 * File:          app/code/local/Pb/Pbgsp/Model/Quote/Duty.php
 * Copyright:     Copyright (c) 2015 Pitney Bowes <info@pb.com> / All rights reserved.
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
        //Pb_Pbgsp_Model_Util::log('Pb_Pbgsp_Model_Quote_Duty.getDutyAndTax');
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
       // Pb_Pbgsp_Model_Util::log('Pb_Pbgsp_Model_Quote_Duty.collect');
        parent::collect($address);

		if ($this->getDutyAndTax()) {
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
        //Pb_Pbgsp_Model_Util::log('Pb_Pbgsp_Model_Quote_Duty.fetch');
        $amount = $this->getDutyAndTax();
		if ($amount) {
			if($this->dutyDisplayed) {
				return $this;
			}
			$this->dutyDisplayed = true;
	
			//$amount = $address->getTaxAmount();
            //$amount = Mage::app()->getStore()->convertPrice($amount);

			$title = "Duty & Taxes";
			Pb_Pbgsp_Model_Util::log("Add Duty & Taxes at Duty:" . $amount);
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

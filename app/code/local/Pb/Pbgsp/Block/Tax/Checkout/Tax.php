<?php

/**
 * Product:       Pb_Pbgsp (1.3.7)
 * Packaged:      2016-06-01T14:02:28+00:00
 * Last Modified: 2016-04-14T14:05:10+00:00
 */
class Pb_Pbgsp_Block_Tax_Checkout_Tax extends Mage_Tax_Block_Checkout_Tax
{
    public function getTotal() {
        $total = parent::getTotal();
        if ($total->getAddress()) {
            if(Pb_Pbgsp_Model_Util::isPbOrder($total->getAddress()->getShippingMethod())) {
                $total->setTitle('Importation Charges');
            }

        }

        return $total;
    }
}
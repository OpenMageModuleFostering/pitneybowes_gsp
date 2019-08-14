<?php

/**
 * Product:       Pb_Pbgsp (1.4.1)
 * Packaged:      2016-07-26T14:25:00+00:00
 * Last Modified: 2016-09-13T10:50:00+00:00
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
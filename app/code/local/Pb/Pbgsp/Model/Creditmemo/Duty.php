<?php
/**
 * Product:       Pb_Pbgsp (1.2.1)
 * Packaged:      2015-10-07T12:08:45+00:00
 * Last Modified: 2015-10-01T12:11:15+00:00





 * File:          app/code/local/Pb/Pbgsp/Model/Creditmemo/Duty.php
 * Copyright:     Copyright (c) 2015 Pitney Bowes <info@pb.com> / All rights reserved.
 */
 class Pb_Pbgsp_Model_Creditmemo_Duty extends Mage_Sales_Model_Order_Creditmemo_Total_Tax
{
    public function collect(Mage_Sales_Model_Order_Creditmemo $creditmemo)
    {
        Pb_Pbgsp_Model_Util::log('Pb_Pbgsp_Model_Creditmemo_Duty.collect');
        $len = strlen("pbgsp_");
        $order = $creditmemo->getOrder();
        $shipMethod = $order->getShippingMethod();
        if (strlen($shipMethod) > $len && substr($shipMethod, 0, $len) == "pbgsp_") {
            $totalTax = 0;
            $baseTotalTax = 0;
            $totalHiddenTax = 0;
            $baseTotalHiddenTax = 0;
            if ($order->getTaxAmount()) {
                $totalTax = $order->getTaxAmount();
                $baseTotalTax = $totalTax;
            }
            $creditmemo->setTaxAmount($totalTax);
            $creditmemo->setBaseTaxAmount($baseTotalTax);
            $creditmemo->setHiddenTaxAmount($totalHiddenTax);
            $creditmemo->setBaseHiddenTaxAmount($baseTotalHiddenTax);
            $creditmemo->setGrandTotal($creditmemo->getGrandTotal() + $totalTax + $totalHiddenTax);
            $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() + $baseTotalTax + $baseTotalHiddenTax);
        } else {
            parent::collect($creditmemo);
        }

        return $this;
    }
}
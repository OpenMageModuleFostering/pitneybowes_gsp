<?php
/**
 * Product:       Pb_Pbgsp (1.3.2)
 * Packaged:      2016-01-11T11:12:49+00:00
 * Last Modified: 2015-12-18T11:00:00+00:00





 * File:          app/code/local/Pb/Pbgsp/Model/Invoice/Duty.php
 * Copyright:     Copyright (c) 2015 Pitney Bowes <info@pb.com> / All rights reserved.
 */

 class Pb_Pbgsp_Model_Invoice_Duty extends Mage_Sales_Model_Order_Invoice_Total_Tax
{
    public function collect(Mage_Sales_Model_Order_Invoice $invoice)
    {
        Pb_Pbgsp_Model_Util::log('Pb_Pbgsp_Model_Invoice_Duty.collect');
        $len = strlen("pbgsp_");
        $order = $invoice->getOrder();
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
            $invoice->setTaxAmount($totalTax);
            $invoice->setBaseTaxAmount($baseTotalTax);
            $invoice->setHiddenTaxAmount($totalHiddenTax);
            $invoice->setBaseHiddenTaxAmount($baseTotalHiddenTax);

            $invoice->setGrandTotal($invoice->getGrandTotal() + $totalTax + $totalHiddenTax);
            $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $baseTotalTax + $baseTotalHiddenTax);
        } else {
            parent::collect($invoice);
        }

        return $this;
    }
}
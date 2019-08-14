<?php
/**
 * Product:       Pb_Pbgsp (1.4.2)
 * Packaged:      2016-09-21T11:45:00+00:00
 * Last Modified: 2016-09-13T10:50:00+00:00
 * File:          app/code/local/Pb/Pbgsp/Model/Sales/Order/Shipment.php
 * Copyright:     Copyright (c) 2016 Pitney Bowes <info@pb.com> / All rights reserved.
 */

class Pb_Pbgsp_Model_Sales_Order_Shipment extends Mage_Sales_Model_Order_Shipment {

    public function sendEmail($notifyCustomer = true, $comment = '')
    {
        $delayInMinutes = 5;
        Mage::log('Pb_Pbgsp_Model_Sales_Order_Shipment.sendEmail');
        $order = $this->getOrder();
        if(!Pb_Pbgsp_Model_Util::isPbOrder($order->getShippingMethod())){
            return parent::sendEmail($notifyCustomer,$comment);
        }
        $shipmentemail = null;
        $shipmentemailColl = Mage::getModel("pb_pbgsp/shipmentemail")-> getCollection();
        $shipmentemailColl -> addFieldToFilter('shipment_id',$this->getIncrementId());
        foreach($shipmentemailColl as $semail) {
            $shipmentemail = $semail;
            break;
        }
        if(!$shipmentemail) {
            //delay the email
            $shipmentemail = Mage::getModel("pb_pbgsp/shipmentemail");
            $shipmentemail->setShipmentId($this->getIncrementId());
            $shipmentemail->setCreatedDate(time());
            $shipmentemail->save();
            return $this;
        }
        else {
            $createdTime = intval($shipmentemail->getCreatedDate());
            if(time() < ($createdTime + (60 + $delayInMinutes))) {
                //Mage::log("time() < ($createdTime + (60 + $delayInMinutes))");
                return $this;
            }
        }
        if(Mage::getStoreConfig('carriers/pbgsp/suppress_domestic_tracking') != '1') {
            parent::sendEmail($notifyCustomer,$comment);
            if($shipmentemail) {
                $shipmentemail->setEmailSent(time());
                $shipmentemail->save();
            }
            return $this;
        }


        $storeId = $order->getStore()->getId();

        if (!Mage::helper('sales')->canSendNewShipmentEmail($storeId)) {
            return $this;
        }
        // Get the destination email addresses to send copies to
        $copyTo = $this->_getEmails(self::XML_PATH_EMAIL_COPY_TO);
        $copyMethod = Mage::getStoreConfig(self::XML_PATH_EMAIL_COPY_METHOD, $storeId);
        // Check if at least one recepient is found
        if (!$notifyCustomer && !$copyTo) {
            return $this;
        }

        // Start store emulation process
        $appEmulation = Mage::getSingleton('core/app_emulation');
        $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);

        try {
            // Retrieve specified view block from appropriate design package (depends on emulated store)
            $paymentBlock = Mage::helper('payment')->getInfoBlock($order->getPayment())
                ->setIsSecureMode(true);
            $paymentBlock->getMethod()->setStore($storeId);
            $paymentBlockHtml = $paymentBlock->toHtml();
        } catch (Exception $exception) {
            // Stop store emulation process
            $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
            throw $exception;
        }

        // Stop store emulation process
        $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);

        // Retrieve corresponding email template id and customer name
        if ($order->getCustomerIsGuest()) {
            //$templateId = Mage::getStoreConfig(self::XML_PATH_EMAIL_GUEST_TEMPLATE, $storeId);
            $customerName = $order->getBillingAddress()->getName();
        } else {
            //$templateId = Mage::getStoreConfig(self::XML_PATH_EMAIL_TEMPLATE, $storeId);
            $customerName = $order->getCustomerName();
        }

        $cpord = Pb_Pbgsp_Model_Util::getCPORD($order);
        $email = $order->getCustomerEmail();
        $trackingUrl = "https://parceltracking.pb.com/app/#/dashboard/$cpord/$email";
        $emailTemplateVariables = array(
            'order'    => $order,
            'shipment' => $this,
            'comment'  => $comment,
            'billing'  => $order->getBillingAddress(),
            'store' => Mage::app()->getStore($storeId),
            'payment_html' => $paymentBlockHtml,
            'tracking_url' => $trackingUrl


        );
        $emailTemplate  = Mage::getModel('core/email_template');
        // ->loadDefault('pbgsp_shipment_new');
        $emailTemplate = $this->_setTemplateBodyAndSubject($emailTemplate,
            Mage::getStoreConfig('carriers/pbgsp/custom_shipment_email_template'),
            Mage::getStoreConfig('carriers/pbgsp/custom_shipment_email_subject'));
        $emailTemplate->setSenderEmail(Mage::getStoreConfig(self::XML_PATH_EMAIL_IDENTITY, $storeId));
        $processedTemplate = $emailTemplate->getProcessedTemplate($emailTemplateVariables);
        $processedTemplate = str_replace("PBGSP_Manual_Catalog_Export","index",$processedTemplate);
        //Pb_Pbgsp_Model_Util::log($processedTemplate);
        //$emailTemplate->send($order->getCustomerEmail(), $customerName,$emailTemplateVariables);
        //Pb_Pbgsp_Model_Util::log('Email subject'. $emailTemplate->getTemplateSubject());
        //Pb_Pbgsp_Model_Util::log('Processed Email Subject is'. $emailTemplate->getProcessedTemplateSubject($emailTemplateVariables));
        $fromName = Mage::getStoreConfig('trans_email/ident_' . Mage::getStoreConfig(self::XML_PATH_EMAIL_IDENTITY, $storeId) . '/name', $storeId);
        $fromEmail = Mage::getStoreConfig('trans_email/ident_' . Mage::getStoreConfig(self::XML_PATH_EMAIL_IDENTITY, $storeId) . '/email', $storeId);
        $mail = Mage::getModel('core/email')
            ->setToName($customerName)
            ->setToEmail($order->getCustomerEmail())
            ->setBody($processedTemplate)
            ->setSubject($emailTemplate->getProcessedTemplateSubject($emailTemplateVariables))
            ->setFromEmail($fromEmail)
            ->setFromName($fromName)
            ->setType('html');
        try{

            Pb_Pbgsp_Model_Util::log("Form email $fromEmail from name $fromName");
            $mail->send();
            //Pb_Pbgsp_Model_Util::log("Email sent to");
            //Pb_Pbgsp_Model_Util::log($order->getCustomerEmail());
            if($shipmentemail) {
                $shipmentemail->setEmailSent(time());
                $shipmentemail->save();
                Mage::log("shipmentemail set".$shipmentemail->getEmailSent());
            }
        }
        catch(Exception $error)
        {
            Pb_Pbgsp_Model_Util::log("Error sending shipment email". $error->getMessage());
            Pb_Pbgsp_Model_Util::log($error->getTraceAsString());

        }

        return $this;
    }
    private function _setTemplateBodyAndSubject($template,$body,$subject)
    {

        $template->setTemplateType( Mage_Core_Model_Template::TYPE_HTML);

        $templateText = $body;
        $template->setTemplateSubject($subject);


        if (preg_match('/<!--@vars\s*((?:.)*?)\s*@-->/us', $templateText, $matches)) {
            $template->setData('orig_template_variables', str_replace("\n", '', $matches[1]));
            $templateText = str_replace($matches[0], '', $templateText);
        }

        if (preg_match('/<!--@styles\s*(.*?)\s*@-->/s', $templateText, $matches)) {
            $this->setTemplateStyles($matches[1]);
            $templateText = str_replace($matches[0], '', $templateText);
        }

        /**
         * Remove comment lines
         */
        $templateText = preg_replace('#\{\*.*\*\}#suU', '', $templateText);

        $template->setTemplateText($templateText);
       // $this->setId($templateId);

        return $template;
    }

    public function getTracksCollection()
    {

        try {
            $order = $this->getOrder();
            $shipMethod = $order->getShippingMethod();
            $len = strlen("pbgsp_");
            $isCPOrder = false;
            if (strlen($shipMethod) > $len && substr($shipMethod, 0, $len) == "pbgsp_") {
                $isCPOrder = true;
            }
            if(!$this->isAdmin() && $isCPOrder) {
                if(Mage::getStoreConfig('carriers/pbgsp/suppress_domestic_tracking') == '1') {

                    return Mage::getResourceModel('sales/order_shipment_track_collection')
                        ->addFieldToFilter('title', 'PB')
                        ->setShipmentFilter($this->getId());
                }
            }
        }
        catch(Exception $e) {
            Pb_Pbgsp_Model_Util::logException($e);
        }

        return parent::getTracksCollection();
    }

    public function isAdmin()
    {
        if(Mage::app()->getStore()->isAdmin())
        {
            return true;
        }

        if(Mage::getDesign()->getArea() == 'adminhtml')
        {
            return true;
        }

        return false;
    }

}
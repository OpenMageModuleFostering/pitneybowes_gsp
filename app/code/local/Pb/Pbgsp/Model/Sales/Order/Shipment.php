<?php
/**
 * Product:       Pb_Pbgsp (1.0.3)
 * Packaged:      2015-09-1T15:12:28+00:00
 * Last Modified: 2015-08-25T15:12:28+00:00



 * File:          app/code/local/Pb/Pbgsp/Model/Sales/Order/Shipment.php
 * Copyright:     Copyright (c) 2015 Pitney Bowes <info@pb.com> / All rights reserved.
 */

class Pb_Pbgsp_Model_Sales_Order_Shipment extends Mage_Sales_Model_Order_Shipment {

    public function sendEmail($notifyCustomer = true, $comment = '')
    {
        if(Mage::getStoreConfig('carriers/pbgsp/suppress_domestic_tracking') != '1')
            return parent::sendEmail($notifyCustomer,$comment);
        $order = $this->getOrder();
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



        $emailTemplateVariables = array(
            'order'    => $order,
            'shipment' => $this,
            'comment'  => $comment,
            'billing'  => $order->getBillingAddress(),
            'store' => Mage::app()->getStore($storeId),
            'payment_html' => $paymentBlockHtml


        );
        $emailTemplate  = Mage::getModel('core/email_template');
        // ->loadDefault('pbgsp_shipment_new');
        $emailTemplate = $this->_setTemplateBodyAndSubject($emailTemplate,
            Mage::getStoreConfig('carriers/pbgsp/custom_shipment_email_template'),
            Mage::getStoreConfig('carriers/pbgsp/custom_shipment_email_subject'));
        $emailTemplate->setSenderEmail(Mage::getStoreConfig(self::XML_PATH_EMAIL_IDENTITY, $storeId));
        $processedTemplate = $emailTemplate->getProcessedTemplate($emailTemplateVariables);
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


}
<?php

/**
 * Created by PhpStorm.
 * User: Muhammad
 * Date: 9/16/2016
 * Time: 10:54 PM
 */
class Pb_Pbgsp_Helper_Shipping extends Mage_Shipping_Helper_Data
{
    public function getTrackingPopupUrlBySalesModel($model)
    {
        $url = "https://parceltracking.pb.com/app/#/dashboard/";
        if ($model instanceof Mage_Sales_Model_Order) {
            $cpord = Pb_Pbgsp_Model_Util::getCPORD($model);
            if($cpord) {
                $email = $model->getCustomerEmail();
                return $url."$cpord/$email";
            }
            return parent::getTrackingPopupUrlBySalesModel($model);
        } elseif ($model instanceof Mage_Sales_Model_Order_Shipment) {
            $order = $model->getOrder();
            $cpord = Pb_Pbgsp_Model_Util::getCPORD($order);
            if($cpord) {
                $email = $order->getCustomerEmail();
                return $url."$cpord/$email";
            }
            return parent::getTrackingPopupUrlBySalesModel($model);

        } elseif ($model instanceof Mage_Sales_Model_Order_Shipment_Track) {
            return parent::getTrackingPopupUrlBySalesModel($model);
//            $order = Mage::getModel("sales/order")->load($model->getOrderId());
//            $cpord = Pb_Pbgsp_Model_Util::getCPORD($order);
//            if($cpord) {
//                $email = $order->getCustomerEmail();
//                return $url."$cpord/$email";
//            }
//            return parent::getTrackingPopupUrlBySalesModel($model);
        }
        return '';
    }
}
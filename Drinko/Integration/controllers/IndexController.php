<?php

class Drinko_Integration_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {

        $this->createControllerMethod();
    }

    public function createControllerMethod()
    {
        Mage::dispatchEvent('send_email_notification'
        );


//        $orderData = array(
//            array(
//                'increment_id' => '100000001',
//                'status' => 'avbruten'
//            )
//        );
//
//        foreach ($orderData as $item) {
//            $order = $this->_initOrder($item["increment_id"]);
//            try {
//
//                $order->setStatus($item["status"], true);
//                $order->save();
//                Mage::getSingleton('integration/order_status_notifier')->sendEmail($order);
//            } catch (Mage_Core_Exception $e) {
//
//            }
//        }
//        return true;
    }

    protected function _initOrder($orderIncrementId)
    {
        $order = Mage::getModel('sales/order');

        /* @var $order Mage_Sales_Model_Order */

        $order->loadByIncrementId($orderIncrementId);

        if (!$order->getId()) {
            $this->_fault('not_exists');
        }

        return $order;
    }
}
<?php

class Drinko_Integration_Model_Api extends Mage_Api_Model_Resource_Abstract
{
    /**
     * @param array $orderData
     * @return bool
     * @throws Mage_Api_Exception
     */
    public function update($orderData = array())
    {
        foreach ($orderData as $item) {
            $order = $this->_initOrder($item->increment_id);
            try {
                $order->setStatus($item->status, true);
                $order->save();
                
                // create invoice capture
                if ($item->status == "packing_finished"){
                    $capture = Mage_Sales_Model_Order_Invoice::CAPTURE_ONLINE;
                    /** @var Mage_Sales_Model_Order_Invoice $invoice */
                    $invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice();
                    $invoice->setRequestedCaptureCase($capture);
                    $invoice->register();

                    $transaction = Mage::getModel('core/resource_transaction')
                        ->addObject($invoice)
                        ->addObject($invoice->getOrder());

                    $transaction->save();
                }
                //
                
                Mage::getSingleton('integration/order_status_notifier')->sendEmail($order);

            } catch (Mage_Core_Exception $e) {
                $this->_fault('status_not_changed', $e->getMessage());
            }
        }
        return true;
    }

    /**
     * @param $orderIncrementId
     * @return false|Mage_Core_Model_Abstract|Mage_Sales_Model_Order
     * @throws Mage_Api_Exception
     */
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

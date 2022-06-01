<?php

class Drinko_Integration_Model_Order_Status_Notifier
{
    public function sendEmail($order)
    {
        try {
            //Getting the Store E-Mail Sender Name.
            $senderName = Mage::getStoreConfig('trans_email/ident_general/name');

            //Getting the Store General E-Mail.
            $senderEmail = Mage::getStoreConfig('trans_email/ident_general/email');

            //Get Template Email
            if ($order->getStatus() === "accepted") {
                $emailTemplate = Mage::getModel('core/email_template')->loadByCode('Drinko - Accepterad order');
            } else if ($order->getStatus() === "packing_finished") {
                $emailTemplate = Mage::getModel('core/email_template')->loadByCode('ditt_paket_hamtas');
            } else if ($order->getStatus() === "avbruten") {
                $emailTemplate = Mage::getModel('core/email_template')->loadByCode('Makuleras');
            }

            if (!$emailTemplate || !$emailTemplate->getId()) {
                return;
            }

            $variables = array(
                'increment_id' => $order->getIncrementId()
            );
            $emailTemplate->getProcessedTemplate($variables);

            //Prepare for Sender info
            $emailTemplate->setSenderName($senderName);
            $emailTemplate->setSenderEmail($senderEmail);

            // Get information customer
            $receipentName = $order->getCustomerName();
            $receipentEmail = $order->getCustomerEmail();

            //send email
            $emailTemplate->send($receipentEmail, $receipentName, $variables);
        } catch (Exception $e) {
            // do nothing
        }

    }
}
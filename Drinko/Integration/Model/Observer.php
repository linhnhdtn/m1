<?php

class Drinko_Integration_Model_Observer extends Varien_Event_Observer
{
    public function setStock()
    {
        $baseUrl = Mage::getStoreConfig('catalog/integration/url');
        $user = Mage::getStoreConfig('catalog/integration/user');
        $password = Mage::getStoreConfig('catalog/integration/password');
        $endPoint = Mage::getStoreConfig('catalog/integration/endpoint');
        $endPointToken = Mage::getStoreConfig('catalog/integration/endpoint_token');

        $userData = array("username" => $user, "password" => $password);
        $ch = curl_init($baseUrl . $endPointToken);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($userData));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "Content-Lenght: " . strlen(json_encode($userData))));

        $token = curl_exec($ch);

        $ch = curl_init($baseUrl . $endPoint);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "Authorization: Bearer " . json_decode($token)));

        $result = curl_exec($ch);
        $products = json_decode($result, 1);

        try {
            if (isset($products)) {
                $items = $products["items"];
                foreach ($items as $item) {
                    $productDrinko = Mage::getModel('catalog/product')->loadByAttribute('sku', $item["sku"]);
                    if (!empty($productDrinko)) {
                        $productId = Mage::getModel('catalog/product')->getIdBySku($item["sku"]);
                        $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($productId);
                        $stockItem->setData("qty", $item["quantity"]);
                        $stockItem->setData("is_in_stock", $item["quantity"] > 0 ? 1 : 0);
                        $stockItem->save();
                    }
                }
            }
        } catch (Mage_Core_Exception $exception) {
            Mage::throwException($exception);
        }
    }
}
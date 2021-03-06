<?php

class Drinko_Integration_Model_Observer extends Varien_Event_Observer
{
    public function syncStock()
    {
        try {
            $baseUrl = Mage::getStoreConfig('integration/api/url');
            $user = Mage::getStoreConfig('integration/api/user');
            $password = Mage::getStoreConfig('integration/api/password');
            $endPoint = Mage::getStoreConfig('integration/api/endpoint');
            $endPointToken = Mage::getStoreConfig('integration/api/endpoint_token');

            if (!empty($baseUrl) && !empty($user) && !empty($password) && !empty($endPoint) && !empty($endPointToken)) {

                $userData = array("username" => $user, "password" => $password);
                $ch = curl_init($baseUrl . $endPointToken);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($userData));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "Content-Lenght: " . strlen(json_encode($userData))));

                $token = curl_exec($ch);
                $httpCodeToken = curl_getinfo($ch, CURLINFO_HTTP_CODE);

                $ch = curl_init($baseUrl . $endPoint);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "Authorization: Bearer " . json_decode($token)));

                $result = curl_exec($ch);
                $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $products = json_decode($result, 1);

                if (($httpcode == 200) && ($httpCodeToken == 200) && !empty($products)) {
                    $items = $products["items"];
                    foreach ($items as $item) {
                        $productDrinko = Mage::getModel('catalog/product')->loadByAttribute('sku', $item["sku"]);
                        if (!empty($productDrinko)) {
                            $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($productDrinko->getId());
                            $stockItem->setData("qty", $item["quantity"]);
                            $stockItem->setData("is_in_stock", $item["quantity"] > 0 ? 1 : 0);
                            $stockItem->save();
                        }
                    }
                }
            }
        } catch (Mage_Core_Exception $exception) {
            Mage::throwException($exception);
        }
    }
}

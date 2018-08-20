<?php

namespace Wasa\WkPaymentGateway\Helper;

use Magento\Checkout\Model\Session;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Helper\AbstractHelper;

use Sdk\Client;
use Sdk\Response;

class Shotcaller extends AbstractHelper
{
    private $directoryList;
    private $checkoutSession;
    private $quote;
    private $_client_sdk;

    /**
     * Data constructor.
     * @param DirectoryList $directoryList
     * @param ScopeConfigInterface $scopeConfig
     * @param Session $checkoutSession
     */
    public function __construct(
        DirectoryList $directoryList,
        ScopeConfigInterface $scopeConfig,
        Session $checkoutSession
    )
    {
        $this->directoryList = $directoryList;
        $this->scopeConfig = $scopeConfig;
        $this->checkoutSession = $checkoutSession;
        $this->quote = $this->checkoutSession->getQuote();

        $clientId       = $this->scopeConfig->getValue('payment/wasa_gateway/merchant_gateway_key');
        $clientSecret   = $this->scopeConfig->getValue('payment/wasa_gateway/client_secret');
        $testMode       = $this->scopeConfig->getValue('payment/wasa_gateway/debug');

        /* requiring SDK files */
        $rootPath = $directoryList->getRoot();
        require_once($rootPath."/lib/wasa/php-checkout-sdk/Wasa.php");

        $this->_client_sdk = new Client($clientId, $clientSecret, $testMode);
    }


    /**
     * @param $method
     * @param mixed ...$params
     * @return mixed
     */
    public function call($method, ...$params)
    {
        if(!$params) return null;
        $result = null;

        try
        {
            // Attempt API call
            /** @var Response $result */
            $result = call_user_func(array($this->_client_sdk, $method), ...$params);

            if(!$result->statusCode) return null;
            if(!$this->validateStatusCode($result->statusCode)) return null;
            if(!$result->data) return null;

            //Enable in developer mode
            if($result->errorMessage) {
                throw new \Exception('Oh no! The following error occurred: '.$result->errorMessage);
            }

        } catch (\Exception $ex) {
            echo $ex->getMessage();
            return null;
        }

        return $result->data;
    }

    /**
     * validate http code
     *
     * @param $httpCode
     * @return bool
     */
    private function validateStatusCode($httpCode)
    {
        return  ($httpCode >= 200 && $httpCode < 400);
    }
}
<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Wasa\WkPaymentGateway\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Checkout\Model\Session;
use Wasa\WkPaymentGateway\Model\Wkcheckout;

/**
 * Class ConfigProvider
 */
final class ConfigProvider implements ConfigProviderInterface
{
    const CODE = 'wasa_gateway';

    protected $checkoutSession;

    /**
     * @var Wkcheckout
     */
    protected $wkcheckout;

    public function __construct(
        Session $checkoutSession,
        Wkcheckout $wkcheckout
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->wkcheckout = $wkcheckout;
    }

    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     */
    public function getConfig()
    {
        return [
            'payment' => [
                self::CODE => [
                    'leasing_options'   => $this->getLeasingOptions(),
                    'default_option'    => $this->getDefaultOption(),
                    'reserved_order_id' => $this->getReservedOrderId(),
                    'base_url'          => $this->getBaseUrl(),
                ]
            ]
        ];
    }

    /**
     * Get default option index by contract length
     * @return array|null
     */
    protected function getDefaultOption()
    {
        $leasingOptions = $this->getLeasingOptions()['options'];
        $defaultOptionLength = $leasingOptions['default_contract_length'];

        if(is_array($leasingOptions )){
            $defaultOptionIndex = array_search($defaultOptionLength, array_column($leasingOptions['contract_lengths'], 'contract_length'));

            return $leasingOptions['contract_lengths'][$defaultOptionIndex];
        }

        return null;
    }

    /**
     *  Get leasing payment method
     * (https://github.com/wasakredit/php-checkout-sdk/#response-8)
     *
     * @return array|null
     */
    protected function getLeasingOptions()
    {
        $paymentMethods = $this->wkcheckout->getPaymentMethods();

        if(isset($paymentMethods['payment_methods']))
            return $paymentMethods['payment_methods'][0];

        return null;
    }

    /**
     * @return string
     */
    protected function getReservedOrderId()
    {
        $orderId = $this->checkoutSession->getQuote()->reserveOrderId()->getReservedOrderId() + 1;
        //reformat in as incrementId string
        $orderId = str_pad((string)$orderId, 9, '0', STR_PAD_LEFT);
        return $orderId;
    }

    protected function getBaseUrl()
    {
        return $this->wkcheckout->getRequestDomain();
    }
}

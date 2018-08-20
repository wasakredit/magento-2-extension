<?php

namespace Wasa\WkPaymentGateway\Model\Method;

use Magento\Framework\Event\ManagerInterface;
use Magento\Payment\Gateway\Command\CommandManagerInterface;
use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Magento\Payment\Gateway\Config\ValueHandlerPoolInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectFactory;
use Magento\Payment\Gateway\Validator\ValidatorPoolInterface;
use Magento\Quote\Api\Data\CartInterface;
use Psr\Log\LoggerInterface;
use Wasa\WkPaymentGateway\Model\Wkcheckout;

class Adapter extends \Magento\Payment\Model\Method\Adapter
{
    /**
     * @var Wkcheckout
     */
    private $wkcheckout;

    public function __construct(
        ManagerInterface $eventManager,
        ValueHandlerPoolInterface $valueHandlerPool,
        PaymentDataObjectFactory $paymentDataObjectFactory,
        $code,
        $formBlockType,
        $infoBlockType,
        Wkcheckout $wkcheckout,
        CommandPoolInterface $commandPool = null,
        ValidatorPoolInterface $validatorPool = null,
        CommandManagerInterface $commandExecutor = null,
        LoggerInterface $logger = null
    ) {
        parent::__construct($eventManager, $valueHandlerPool, $paymentDataObjectFactory, $code, $formBlockType, $infoBlockType, $commandPool, $validatorPool, $commandExecutor, $logger);

        $this->wkcheckout = $wkcheckout;
    }

    public function isAvailable(CartInterface $quote = null)
    {
        // TODO: Add shipping cost
        $shippingCost  = 0;
        $subTotal      = $this->wkcheckout->getQuote()->getSubtotal();
        $finalCost     = (string)($shippingCost + $subTotal);

        $currencyValidation = $this->wkcheckout->validateAllowedCurrency();
        $isWithinRange = $this->wkcheckout->validateLeasingAmount($finalCost);

        //isEnabled validation is included in parent::isAvailable
        return parent::isAvailable($quote) && $currencyValidation && $isWithinRange;
    }
}
<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Wasa\WkPaymentGateway\Controller\Checkout;

ob_start();
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ObjectManager;
use Psr\Log\LoggerInterface;
use Wasa\WkPaymentGateway\Model\Wkcheckout;

class CreateWasaKreditCheckout extends Action
{
    protected $wkcheckout;

    public function __construct(
        Context $context,
        Wkcheckout $wkcheckout
    ) {
        parent::__construct($context);
        $this->wkcheckout = $wkcheckout;
    }

    public function execute()
    {
        $result = $this->wkcheckout->createCheckout();

        echo $result;
    }
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Wasa\WkPaymentGateway\Controller\Checkout;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Action\Action;
use Psr\Log\LoggerInterface;

class Redirect extends Action
{
    public function __construct(
        Context $context,
        LoggerInterface $logger = null
    ) {
        parent::__construct($context);
        $this->logger = $logger ?: ObjectManager::getInstance()->get(LoggerInterface::class);
    }

    public function execute()
    {
        echo "Redirect";
    }
}

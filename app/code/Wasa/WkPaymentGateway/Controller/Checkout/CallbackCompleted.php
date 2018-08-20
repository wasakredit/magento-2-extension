<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Wasa\WkPaymentGateway\Controller\Checkout;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ObjectManager;
use Psr\Log\LoggerInterface;
use Wasa\WkPaymentGateway\Model\Wkcheckout;

class CallbackCompleted extends Action
{
    protected $logger;

    protected $wkcheckout;

    public function __construct(
        Context $context,
        Wkcheckout $wkcheckout,
        LoggerInterface $logger = null
    ) {
        parent::__construct($context);
        $this->logger = $logger ?: ObjectManager::getInstance()->get(LoggerInterface::class);
        $this->wkcheckout = $wkcheckout;
    }

    public function execute()
    {
        $orderId = $this->getRequest()->getParam('order_id');
        $wasaOrderId = $this->getRequest()->getParam('wasa_order_id');

        $result = $this->wkcheckout->addOrderReferences($wasaOrderId, $orderId);

        echo json_encode([$orderId, $wasaOrderId, $result]);
    }
}

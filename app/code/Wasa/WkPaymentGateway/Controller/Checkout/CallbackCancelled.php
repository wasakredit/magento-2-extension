<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Wasa\WkPaymentGateway\Controller\Checkout;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\OrderFactory;
use Psr\Log\LoggerInterface;

class CallbackCancelled extends Action
{
    /**
     * Logger for exception details
     *
     * @var LoggerInterface
     */
    protected $logger;

    protected $orderRepository;

    protected $orderFactory;
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    public function __construct(
        Context $context,
        OrderRepositoryInterface $orderRepository,
        OrderFactory $orderFactory,
        ScopeConfigInterface $scopeConfig,
        LoggerInterface $logger = null
    ) {
        parent::__construct($context);
        $this->logger = $logger ?: ObjectManager::getInstance()->get(LoggerInterface::class);
        $this->orderRepository = $orderRepository;
        $this->orderFactory = $orderFactory;
        $this->scopeConfig = $scopeConfig;
    }

    public function execute()
    {
        $redirectUrl = $this->_url->getUrl('checkout/onepage/error', ['_secure' => false]);
        $orderId = $this->getRequest()->getParam('order_id');

        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->orderFactory->create();
        $order->loadByIncrementId($orderId);

        $order->addStatusToHistory(\Magento\Sales\Model\Order::STATE_CANCELED, "Order canceled either by Wasa Kredit or user.");
        $order->setState(\Magento\Sales\Model\Order::STATE_CANCELED);
        $order->save();

        echo $redirectUrl;
    }
}

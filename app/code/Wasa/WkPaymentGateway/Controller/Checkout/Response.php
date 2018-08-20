<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Wasa\WkPaymentGateway\Controller\Checkout;

use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Controller\ResultFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Psr\Log\LoggerInterface;

use Magento\Framework\App\Action\Action;

class Response extends Action
{
    /**
     * @var Session
     */
    protected $checkoutSession;

    /**
     * Logger for exception details
     *
     * @var LoggerInterface
     */
    private $logger;

    private $orderRepository;

    /**
     * Response constructor.
     * @param Context $context
     * @param OrderRepositoryInterface $orderRepository
     * @param LoggerInterface|null $logger
     */
    public function __construct(
        Context $context,
        OrderRepositoryInterface $orderRepository,
        LoggerInterface $logger = null
    ) {
        parent::__construct($context);
        $this->logger = $logger ?: ObjectManager::getInstance()->get(LoggerInterface::class);
        $this->orderRepository = $orderRepository;
    }

    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        if ($this->getRequest()->getParam("flag") == "1" && $this->getRequest()->getParam("orderId")) {
            $orderId = $this->getRequest()->getParam("orderId");

            /** @var \Magento\Sales\Api\Data\OrderInterface $order */
            $order = $this->orderRepository->get($orderId);

            $order->setState(\Magento\Sales\Model\Order::STATE_PAYMENT_REVIEW);
            $order->setStatus(\Magento\Sales\Model\Order::STATE_PAYMENT_REVIEW);

            try {
                $this->orderRepository->save($order);
            } catch (\Exception $e) {
                $this->logger->error($e);
                $this->messageManager->addExceptionMessage($e, $e->getMessage());
            }

            return $resultRedirect->setPath('checkout/onepage/success', ['_secure' => false]);
        } else {
            return $resultRedirect->setPath('checkout/onepage/error', ['_secure' => false]);
        }
    }
}

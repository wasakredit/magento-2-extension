<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Wasa\WkPaymentGateway\Controller\Checkout;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Sales\Model\OrderFactory;
use Psr\Log\LoggerInterface;
use Wasa\WkPaymentGateway\Model\Wkcheckout;

class Ping extends Action
{
    protected $logger;

    protected $wkcheckout;

    protected $orderFactory;

    public function __construct(
        Context $context,
        Wkcheckout $wkcheckout,
        OrderFactory $orderFactory,
        LoggerInterface $logger = null
    ) {
        parent::__construct($context);
        $this->logger = $logger ?: ObjectManager::getInstance()->get(LoggerInterface::class);
        $this->wkcheckout = $wkcheckout;
        $this->orderFactory = $orderFactory;
    }

    public function execute()
    {
        // Extract Wasa Kredit Order ID from POST body
        $data = json_decode(file_get_contents('php://input'), true);
        $orderId = isset($data["order_id"])?$data["order_id"]: null;

        // Make request to retrieve Wasa Kredit Order from API
        $order = $this->wkcheckout->getOrder($orderId);

        // Return if the Wasa Kredit Order does not contain any data
        if(!is_array($order)) return null;

        // Retrieve the status of the Wasa Kredit Order
        $wasaOrderStatus = $order['status']['status'];

        // Initialize variable for storing Magento Order Id, set to null by default
        $magentoOrderId = null;

        // Return if the Wasa Kredit Order does not contain any order references
        if(!$order['order_references']) return null;

        // Retrieve Merchand Order Reference
        $targetOrder = array_filter($order['order_references'], function($ar) {
            return $ar['key']=='magento_order_id';
        });

        // Exit if the Magento Order ID does not exists
        if(!$targetOrder) return null;

        // Store the value of the Magento Order ID
        $magentoOrderId = array_pop($targetOrder)['value'];

        // Load corrsponding Magento order with the Magento Order ID
        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->orderFactory->create();
        $order->loadByIncrementId($magentoOrderId);

        // Return null if the Magento order is empty
        if(empty($order->getData())) return null;

        // Set status of Magento order to reflect status of Wasa Kredit Order
        // based on the status of the Wasa Kredit Order status
        switch($wasaOrderStatus) {
            case 'completed':
                $order->addStatusToHistory(\Magento\Sales\Model\Order::STATE_COMPLETE, "Order has been paid by Wasa Kredit.");
                $order->setData('state', \Magento\Sales\Model\Order::STATE_COMPLETE);
                break;
            case 'canceled':
                $order->addStatusToHistory(\Magento\Sales\Model\Order::STATE_CANCELED, "Order canceled either by Wasa Kredit or user.");
                $order->setData('state', \Magento\Sales\Model\Order::STATE_CANCELED);
                break;
            case 'shipped':
                $order->addStatusToHistory(\Magento\Sales\Model\Order::STATE_COMPLETE, "Order marked as shipped by us when shipped.");
                $order->setData('state', \Magento\Sales\Model\Order::STATE_COMPLETE);
                break;
            case 'pending':
                $order->addStatusToHistory('pending_wasa_checkout', "Awaiting information or more signatories. An order could stay in this state for multiple days.");
                $order->setData('state', 'pending_wasa_checkout');
                break;
            case 'ready_to_ship':
                $order->addStatusToHistory(\Magento\Sales\Model\Order::STATE_COMPLETE, "Wasa Kredit has approved to finance this order.");
                $order->setData('state', \Magento\Sales\Model\Order::STATE_COMPLETE);
                break;
            default:
                break;
        }

        $order->save();

        echo 'OK';
    }
}

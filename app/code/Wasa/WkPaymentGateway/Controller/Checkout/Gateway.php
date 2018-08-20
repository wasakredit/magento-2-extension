<?php

namespace Wasa\WkPaymentGateway\Controller\Checkout;

use Magento\Framework\App\Action\Action;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\Result\Redirect;
use Psr\Log\LoggerInterface;

class Gateway extends Action
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
    protected $logger;

    /**
     * Constructor
     *
     * @param Context $context
     * @param Session $checkoutSession
     * @param LoggerInterface|null $logger
     */
    public function __construct(
        Context $context,
        Session $checkoutSession,
        LoggerInterface $logger = null
    ) {
        parent::__construct($context);
        $this->checkoutSession = $checkoutSession;
        $this->logger = $logger ?: ObjectManager::getInstance()->get(LoggerInterface::class);
    }

    /**
     * @inheritdoc
     * @return Redirect
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        if($this->getRequest()->getParam("orderId")) {
            $arr_querystring = array(
                'flag' => 1,
                'orderId' => $this->getRequest()->getParam("orderId")
            );

            return $resultRedirect->setPath('wkcheckout/checkout/response', ['_secure' => true, '_query'=> $arr_querystring]);
        }
    }
}

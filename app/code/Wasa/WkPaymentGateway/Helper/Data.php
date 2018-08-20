<?php

namespace Wasa\WkPaymentGateway\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use Wasa\WkPaymentGateway\Model\Wkcheckout;

class Data extends AbstractHelper
{
    /**
     * @var Wkcheckout
     */
    private $wkcheckout;

    /**
     * Data constructor.
     * @param Context $context
     * @param Wkcheckout $wkcheckout
     */
    public function __construct(
        Context $context,
        Wkcheckout $wkcheckout
    ) {
        parent::__construct($context);
        $this->wkcheckout = $wkcheckout;
    }

    /**
     * Retrieves isWidgetVisibleOnProductPage from admin
     *
     * @param string      $scope
     * @param null|string $scopeCode
     *
     * @return bool
     */
    public function isWidgetVisibleOnProductPage($scope = ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        return $this->isPaymentEnabled($scope, $scopeCode) && $this->scopeConfig->isSetFlag('payment/wasa_gateway/is_detail_widget_visible', $scope, $scopeCode);
    }

    /**
     * Retrieves isWidgetVisibleOnProductListing from admin
     *
     * @param string      $scope
     * @param null|string $scopeCode
     *
     * @return bool
     */
    public function isWidgetVisibleOnProductListing($scope = ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        return $this->isPaymentEnabled($scope, $scopeCode) && $this->scopeConfig->isSetFlag('payment/wasa_gateway/is_listing_widget_visible', $scope, $scopeCode);
    }

    /**
     * Retrieves isPaymentEnabled from admin
     *
     * @param string $scope
     * @param null $scopeCode
     *
     * @return bool
     */
    public function isPaymentEnabled($scope = ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        return $this->scopeConfig->isSetFlag('payment/wasa_gateway/active', $scope, $scopeCode);
    }

    /**
     * @return Wkcheckout
     */
    public function getWkCheckoutModel()
    {
        return $this->wkcheckout;
    }
}

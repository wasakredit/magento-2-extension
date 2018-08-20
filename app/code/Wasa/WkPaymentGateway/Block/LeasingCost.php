<?php

namespace Wasa\WkPaymentGateway\Block;

use Magento\Catalog\Model\Product;
use Wasa\WkPaymentGateway\Helper\Data;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class LeasingCost extends Template
{
    /**
     * @var Registry
     */
    private $registry;
    /**
     * @var Data
     */
    private $coreHelper;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param Data $coreHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        Data $coreHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->registry = $registry;
        $this->coreHelper = $coreHelper;
    }

    /**
     * {@inheritdoc}
     */
    protected function _toHtml()
    {
        if (! $this->coreHelper->isWidgetVisibleOnProductPage()) {
            return '';
        }

        try {
            /** @var Product $product */
            $product = $this->registry->registry('product');

            $productPrice = $product->getPrice();

            /** @var string $currency */
            $currency = $this->_storeManager->getStore()->getCurrentCurrency()->getCode();

            /** @var string|array $widget */
            $widget = $this->coreHelper->getWkCheckoutModel()->createProductWidget($productPrice, $currency);
        } catch (\Exception $e) {
            return '';
        }

        if(!$widget || is_array($widget)){
            return '';
        }

        return $widget;
    }
}

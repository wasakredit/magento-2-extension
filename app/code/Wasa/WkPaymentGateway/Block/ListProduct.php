<?php

namespace Wasa\WkPaymentGateway\Block;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use Magento\Framework\Data\Helper\PostHelper;
use Magento\Framework\Url\Helper\Data as DataUrl;
use Wasa\WkPaymentGateway\Helper\Data;

/**
 * Product list
 */
class ListProduct extends \Magento\Catalog\Block\Product\ListProduct
{

    /**
     * @var Data
     */
    private $coreHelper;

    public function __construct(
        Context $context,
        PostHelper $postDataHelper,
        Resolver $layerResolver,
        CategoryRepositoryInterface $categoryRepository,
        DataUrl $urlHelper,
        Data $coreHelper,
        array $data = []
    ) {
        parent::__construct($context, $postDataHelper, $layerResolver, $categoryRepository, $urlHelper, $data);

        $this->coreHelper = $coreHelper;
    }

    /**
     * Leasing Calculated Costs Collection
     *
     * @var AbstractCollection
     */
    protected $_leasingCosts;

    /**
     * Retrieve Product Collection an initialize leasing costs
     *
     * @return AbstractCollection
     */
    protected function _getProductCollection()
    {
        $productCollection = parent::_getProductCollection();

        if($this->coreHelper->isWidgetVisibleOnProductListing()){
            $this->getLeasingCosts();
        }

        return $productCollection;
    }

    /**
     * Retrieve the products leasing costs
     */
    protected function getLeasingCosts()
    {
        if ($this->_leasingCosts === null) {
            $products = parent::_getProductCollection()->toArray();
            $this->_leasingCosts = $this->coreHelper->getWkCheckoutModel()->getMonthlyCosts($products)['monthly_costs'];
        }
        return $this->_leasingCosts;
    }

    /**
     * @param Product $product
     * @return string
     */
    public function getProductPrice(Product $product)
    {
        $price = parent::getProductPrice($product);

        if(!$this->_leasingCosts)
            return $price;

        $productId = array_search($product->getEntityId(), array_column($this->_leasingCosts, 'product_id'));

        if($productId!==false){
            $productLeasingCost = $this->_leasingCosts[$productId]['monthly_cost'];

            $productLeasingCostRendered = $this->getLeasingPrice($productLeasingCost['currency'], $productLeasingCost['amount']);
            $price .= $productLeasingCostRendered;
        }

        return $price;
    }

    /**
     * Format Leasing Cost div
     *
     * @param $currency
     * @param $amount
     * @return string
     */
    public function getLeasingPrice($currency, $amount)
    {
        $formatedAmount = number_format($amount, 0, ',', ' ') ;

        return '<span class="price-box price-container price-final_price">
                    <span class="price-label">Finansiering</span>
                    <span class="price">'.$formatedAmount.' kr/mån</span>
                </span>';
    }
}
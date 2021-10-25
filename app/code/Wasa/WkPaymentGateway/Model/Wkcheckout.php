<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Wasa\WkPaymentGateway\Model;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Directory\Model\CountryFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Store\Model\StoreManagerInterface;
use Wasa\WkPaymentGateway\Helper\Shotcaller;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Model\Quote\Item;
use Sdk\Response;

class Wkcheckout
{
    /**
     * @var Shotcaller
     */
    protected $shotcaller;
    /**
     * @var Quote
     */
    protected $quote;
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var CustomerSession
     */
    private $customerSession;
    /**
     * @var CheckoutSession
     */
    private $checkoutSession;
    /**
     * @var CountryFactory
     */
    private $countryFactory;

    /**
     * Wkcheckout constructor.
     * @param Shotcaller $shotcaller
     * @param CheckoutSession $checkoutSession
     * @param CustomerSession $customerSession
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param CountryFactory $countryFactory
     */
    public function __construct(
        Shotcaller            $shotcaller,
        CheckoutSession       $checkoutSession,
        CustomerSession       $customerSession,
        ScopeConfigInterface  $scopeConfig,
        StoreManagerInterface $storeManager,
        CountryFactory        $countryFactory
    )
    {
        $this->shotcaller = $shotcaller;
        $this->customerSession = $customerSession;
        $this->checkoutSession = $checkoutSession;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->quote = $this->checkoutSession->getQuote();
        $this->countryFactory = $countryFactory;
    }

    /**
     * Checks if user is logged in
     *
     * @return bool $customerStatus
     */
    public function getCustomerStatus()
    {
        $customerStatus = $this->customerSession->isLoggedIn();
        return $customerStatus;
    }

    /**
     * Checks if billing/shipping addresses are equal
     *
     * @return bool $addressType
     */
    public function getAddressType()
    {
        $addressType = $this->quote->getShippingAddress()->getData('same_as_billing');
        return $addressType;
    }

    /**
     * Retrieves payment type
     *
     * @return string $type
     */
    public function getPaymentType()
    {
        $type = 'leasing';
        return $type;
    }

    /**
     * Retrieves the organisation number
     *
     * @return int $orgNumber
     */
    public function getOrgNumber()
    {
        $orgNumber = $this->quote->getShippingAddress()->getVatId();
        return $orgNumber;
    }

    /**
     * Retrieves the request domain field from
     * the admin panel
     * @return string $requestDomain
     */
    public function getRequestDomain()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
        return $storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB);
    }

    /**
     * Retrieves the total shipping cost
     *
     * @return array $shippingCost
     */
    public function getShippingCost()
    {
        $shippingAmount = $this->quote->getShippingAddress()->getShippingAmount();
        $formattedCost = (string)$shippingAmount;
        $currency = $this->getStoreCurrency();

        $shippingCost = array(
            'amount' => $formattedCost,
            'currency' => $currency
        );

        return $shippingCost;
    }

    /**
     * Makes API call to retrieve checkout
     * replaces deprecated getCheckoutURL()
     *
     * @return string|null
     */
    public function createCheckout()
    {
        $quoteId = $this->quote->getId();
        $requestDomain = $this->getRequestDomain();

        $shippingCost = $this->getShippingCost();
        $paymentType = $this->getPaymentType();
        $orgNumber = $this->getOrgNumber();
        $orderId = $this->checkoutSession->getQuoteId();
        $customerDetails = $this->getCustomerDetails();

        // Check address type
        $addressIsSameAsBilling = $this->getAddressType();

        if ($addressIsSameAsBilling) {
            $billingAddress = $this->getAddress($addressIsSameAsBilling);
            $shippingAddress = $this->getAddress($addressIsSameAsBilling);
        } else {
            $billingAddress = $this->getAddress(!$addressIsSameAsBilling);
            $shippingAddress = $this->getAddress($addressIsSameAsBilling);
        }

        /** @var CartItemInterface[] $cartItems */
        $cartItems = $this->getCartItems();

        $orderReferences = array();
        $orderReferences[] = array('key' => 'magento_quote_id', 'value' => $orderId);
        $pingUrl = $this->storeManager->getStore()->getBaseUrl() . 'wkcheckout/checkout/ping';

        $payload = [
            'payment_types' => $paymentType,
            'order_references' => [
                [
                    'key' => 'magento_quote_id',
                    'value' => $quoteId
                ]
            ],
            'cart_items' => $cartItems,
            'shipping_cost_ex_vat' => $shippingCost,
            'customer_organization_number' => $orgNumber,
            'purchaser_name' => $customerDetails['purchaser_name'],
            'purchaser_email' => $customerDetails['purchaser_email'],
            'purchaser_phone' => $customerDetails['purchaser_phone'],
            'billing_address' => $billingAddress,
            'delivery_address' => $shippingAddress,
            'recipient_name' => $customerDetails['purchaser_name'],
            'recipient_phone' => $customerDetails['purchaser_phone'],
            'request_domain' => $requestDomain,
            'confirmation_callback_url' => '',
            'ping_url' => $pingUrl,
        ];

        /** @var string $response */
        $response = $this->shotcaller->call('create_checkout', $payload);

        return $response;
    }

    /**
     * Get order object from order reference
     *
     * @param int $orderId
     *
     * @return array $response
     */
    public function getOrder($orderId)
    {
        $response = $this->shotcaller->call('get_order', $orderId);

        return $response;
    }

    /**
     *  Makes API call to retrieve payment methods
     *
     * @return Response
     */
    public function getPaymentMethods()
    {
        // TODO: Add shipping cost
        $shippingCost = 0;
        $subTotal = round($this->quote->getSubtotal());
        $formatedTotalAmount = (string)($subTotal + $shippingCost);

        $currency = $this->getStoreCurrency();

        /** @var Response $response */
        $response = $this->shotcaller->call('get_payment_methods', $formatedTotalAmount, $currency);

        return $response;
    }

    /**
     *  Makes API call to retrieve leasing payment options methods
     *
     * @return Response
     */
    public function getLeasingPaymentOptions()
    {
        // TODO: Add shipping cost
        $shippingCost = 0;
        $subTotal = round($this->quote->getSubtotal());
        $formatedTotalAmount = (string)($subTotal + $shippingCost);

        $currency = $this->getStoreCurrency();

        /** @var Response $response */
        $response = $this->shotcaller->call('get_leasing_payment_options', $formatedTotalAmount, $currency);

        return $response;
    }

    /**
     * Create mapping between Wasa order id
     * and Magento order id
     *
     * @param $wasaOrderId
     * @param $orderId
     * @return mixed status
     */
    public function addOrderReferences($wasaOrderId, $orderId)
    {
        $payload = array(
            'key' => 'magento_order_id',
            'value' => $orderId
        );

        return $this->shotcaller->call('add_order_reference', $wasaOrderId, $payload);
    }

    /**
     * Retrieves customer details
     *
     * @return array $personalDetails
     */
    public function getCustomerDetails()
    {
        $customerAddress = $this->getBillingAddress();
        $personalDetails = array(
            'purchaser_name' => $customerAddress->getFirstname() . ' ' . $customerAddress->getLastname(),
            'purchaser_phone' => $customerAddress->getTelephone(),
            'purchaser_email' => $customerAddress->getEmail()
        );

        return $personalDetails;
    }

    /**
     * Retrieves billing address core object
     *
     * @return object $address
     */
    public function getBillingAddress()
    {
        $address = $this->quote->getBillingAddress();
        return $address;
    }

    /**
     * Checks if the price contains a period or comma and
     * formats price to contain no more than two decimals
     *
     * @param string $price
     *
     * @return string $formattedPrice
     */
    private function formatPrice($price)
    {
        (!is_string($price)) ? $literalRepPrice = $price : $literalRepPrice = (string)$price;
        (strpos($literalRepPrice, ",") === false) ? $adjustedPrice = $literalRepPrice : $adjustedPrice = str_replace(",", ".", $literalRepPrice);


        $dotIndex = strpos($adjustedPrice, ".") + 1;
        $length = strlen($adjustedPrice);
        $decimalSpan = $length - $dotIndex;
        $formattedPrice = (($decimalSpan) > 2) ? substr($adjustedPrice, 0, -($decimalSpan - 2)) : $adjustedPrice;

        return $formattedPrice;
    }

    /**
     * Retrieves the store currency
     *
     * @return string $currency
     */
    public function getStoreCurrency()
    {
        return $this->quote->getStoreCurrencyCode();
    }

    /**
     * Retrieves cart items
     *
     * @return array
     */
    public function getCartItems()
    {
        $cartItems = array();
        $currency = $this->getStoreCurrency();

        /** @var Item $value */
        foreach ($this->quote->getItems() as $key => $value) {

            $baseTaxPercent = $value->getTaxPercent();
            $baseTaxAmount = $value->getBaseTaxAmount();
            $basePrice = $value->getBasePrice();

            $taxPercent = (int)$baseTaxPercent;
            $taxAmount = (string)$baseTaxAmount;
            $price = (string)$basePrice;

            $cartItem = [
                'product_id' => $value->getItemId(),
                'product_name' => $value->getName(),
                'price_ex_vat' => [
                    'amount' => $price,
                    'currency' => $currency
                ],
                'quantity' => $value->getQty(),
                'vat_percentage' => $taxPercent,
                'vat_amount' => [
                    'amount' => $taxAmount,
                    'currency' => $currency
                ],
                'image_url' => ''
            ];

            $cartItems[$key] = $cartItem;
        }

        return $cartItems;
    }

    /**
     * Retrieves shipping or billing address
     *
     * @param $isSameAsBilling
     * @return array $address
     */
    public function getAddress($isSameAsBilling)
    {
        /** @var Address $address */
        $address = $isSameAsBilling ? $this->getBillingAddress() : $this->getShippingAddress();

        $streetAddresses = $address->getStreet();

        $streetElement = "";

        foreach ($streetAddresses as $streetAddress) {
            if (!empty(trim($streetAddress))) {
                $streetElement = $streetElement . ", " . $streetAddress;
            }
        }

        $countryId = $address->getCountryId();
        $country = $countryId ? $this->getCountryNameById($address->getCountryId()) : null;

        return array(
            'company_name' => $address->getCompany(),
            'street_address' => $streetElement,
            'postal_code' => $address->getPostcode(),
            'city' => $address->getCity(),
            'country' => $country
        );
    }

    /**
     * Retrieves shipping address core object
     *
     * @return object $address
     */
    public function getShippingAddress()
    {
        $address = $this->quote->getShippingAddress();
        return $address;
    }

    /**
     * Get country id
     *
     * @param $countryId
     * @return string $countryName
     */
    public function getCountryNameById($countryId)
    {
        $countryModel = $this->countryFactory->create()->loadByCode($countryId);
        $countryName = $countryModel->getName();
        return $countryName;
    }

    /**
     * Makes API call to retrieve amount validation
     * necessary to make the payment method available
     *
     * @param $finalCost
     * @return bool
     */
    public function validateLeasingAmount($finalCost)
    {
        if (!$finalCost) {
            return false;
        }

        $result = $this->shotcaller->call('validate_financed_amount', $finalCost);

        $isWithinRange = isset($result['validation_result']) ? $result['validation_result'] : false;

        return $isWithinRange;
    }


    /**
     * @param $productPrice
     * @param string|null $currency
     * @return string|array
     */
    public function createProductWidget($productPrice, $currency = null)
    {
        $price = (string)round($productPrice, 2);

        if (!$currency) {
            $currency = $this->getStoreCurrency();
        }

        $response = $this->shotcaller->call('get_monthly_cost_widget', $price);

        return $response;
    }

    /**
     * @param array $products
     * @return string|array
     */
    public function getMonthlyCosts(array $products)
    {
        $items = array_map(function ($product) {
            return [
                'financed_price' => [
                    'amount' => number_format((float)$product['final_price'], 2, '.', ''),
                    'currency' => 'SEK'
                ],
                'product_id' => $product['entity_id']
            ];
        }, array_values($products));

        $payload = [
            'items' => $items
        ];

        $response = $this->shotcaller->call('calculate_monthly_cost', $payload);

        return $response;
    }

    /**
     * @return Quote
     */
    public function getQuote()
    {
        return $this->quote;
    }

    public function validateAllowedCurrency()
    {
        $currencyCode = $this->getStoreCurrency();
        return $currencyCode == 'SEK';
    }
}

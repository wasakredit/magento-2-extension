# Wasa Kredit Magento Extension v2.0
Official Wasa Kredit payment extension for Magento 2. Allows store builders to offer **Wasa Kredit** as a payment option.


**Table of Content**

* [Change log](#change_log)
* [Requirements](#requirements)
* [Installation](#installation)
* [First time setup](#first_time_setup)
* [Add widget on product page](#product_widget)
* [Add leasing cost in product list](#list_view)
* [Folder structure](#folder_structure)

## <a name="change_log"></a>Change log

### v2.0

1. Update the internal php SDK to version 2.4.
2. Leasing Monthly Cost display on product listing page and product detail page are handled in admin config

### Earlier versions

Earlier versions are magento-1x compatible, see https://github.com/wasakredit/magento-1x-extension

## <a name="requirements">Magento Version Requirements</a>

Type       | Version            | Status              
---------- | ------------------ |  ------------------              
Community  | 2.2.4              | Tested


## <a name="installation">Installation</a>

1. Extract the zip file to your server or local machine.
2. Copy all files into the corresponding file location. ***Be careful not to replace the containing directory!***
3. Flush the Magento Cache in `System > Cache Management` or run `php bin/magento c:f`.
4. Run `php bin/magento setup:upgrade` & `php bin/magento setup:static-content:deploy`
                                         
## <a name="first_time_setup">First time setup</a>

1. Proceed to `Stores > Configuration > Sales > Payment Methods`.
2. Fill in your assigned ***Partner ID*** and ***Client Secret ID***.
3. Fill in your base domain
4. Put in test mode.
5. If your system use a custom field for the organisations number, please fill in "Custom organisation number field".

## <a name="product_widget">Add widget showing leasing price on product page</a>

To display the leasing widget in product detail page, use the `Product Detail Page Widget` handle in module configuration (`Stores > Configuration > Sales > Payment Methods`).

## <a name="list_view">Add leasing cost in product list</a>

To calculate and display the leasing cost for each product in a list, use the `Product Listing Page Widget` handle.

## <a name="folder_structure">Folder structure</a>

```sh
.
|── app
|   └── code
|       └── Wasa
|           └── WkPaymentGateway
|               ├── Block
|               │   ├── LeasingCost.php
|               │   └── ListProduct.php
|               ├── composer.json
|               ├── Controller
|               │   └── Checkout
|               │       ├── CallbackCancelled.php
|               │       ├── CallbackCompleted.php
|               │       ├── CallbackRedirected.php
|               │       ├── Gateway.php
|               │       ├── Ping.php
|               │       ├── Redirect.php
|               │       └── Response.php
|               ├── etc
|               │   ├── adminhtml
|               │   │   └── system.xml
|               │   ├── config.xml
|               │   ├── di.xml
|               │   ├── frontend
|               │   │   ├── di.xml
|               │   │   └── routes.xml
|               │   └── module.xml
|               ├── Gateway
|               │   ├── Http
|               │   │   ├── Client
|               │   │   │   └── Client.php
|               │   │   └── TransferFactory.php
|               │   ├── Request
|               │   │   ├── AuthorizationRequest.php
|               │   │   └── CaptureRequest.php
|               │   ├── Response
|               │   │   └── TxnIdHandler.php
|               │   └── Validator
|               │       └── ResponseCodeValidator.php
|               ├── Helper
|               │   ├── Data.php
|               │   └── Shotcaller.php
|               ├── i18n
|               │   └── en_US.csv
|               ├── Model
|               │   ├── Config
|               │   │   └── Source
|               │   │       └── Order
|               │   │           └── Status.php
|               │   ├── Method
|               │   │   └── Adapter.php
|               │   ├── Ui
|               │   │   └── ConfigProvider.php
|               │   └── Wkcheckout.php
|               ├── README.md
|               ├── registration.php
|               ├── Setup
|               │   └── InstallData.php
|               ├── Test
|               │   └── Unit
|               │       ├── Gateway
|               │       │   ├── Request
|               │       │   │   ├── AuthorizeRequestTest.php
|               │       │   │   ├── CaptureRequestTest.php
|               │       │   │   └── VoidRequestTest.php
|               │       │   ├── Response
|               │       │   │   └── TxnIdHandlerTest.php
|               │       │   └── Validator
|               │       │       └── ResponseCodeValidatorTest.php
|               │       └── Model
|               │           └── Ui
|               │               └── ConfigProviderTest.php
|               └── view
|                   └── frontend
|                       ├── layout
|                       │   ├── catalog_product_view.xml
|                       │   └── checkout_index_index.xml
|                       ├── templates
|                       │   └── leasing-cost-product-page.phtml
|                       └── web
|                           ├── js
|                           │   └── view
|                           │       └── payment
|                           │           ├── method-renderer
|                           │           │   └── wasa_gateway.js
|                           │           └── wasa_gateway.js
|                           └── template
|                               └── payment
|                                   └── form.html
└── lib
    └── wasa
        └── php-checkout-sdk
            └── composer.json
                ├── composer.lock
                ├── config.php
                ├── _config.yml
                ├── LICENSE
                ├── phpunit.xml
                ├── README.md
                ├── registration.php
                ├── sdk
                │   ├── AccessToken.php
                │   ├── Api.php
                │   ├── Client.php
                │   ├── input
                │   │   ├── Address.php
                │   │   ├── Cart.php
                │   │   ├── Payload.php
                │   │   └── Price.php
                │   └── Response.php
                ├── tests
                │   ├── Authenticate
                │   │   └── AuthenticateTest.php
                │   └── bootstrap.php
                └── Wasa.php
```

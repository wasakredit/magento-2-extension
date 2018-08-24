# Wasa Kredit Magento 2 Extension
Official Wasa Kredit payment extension for Magento 2. Allows store builders to offer **Wasa Kredit** as a payment option.


**Table of Content**

* [Change log](#change_log)
* [Compability](#compability)
* [Installation](#installation)
* [First time setup](#first_time_setup)
* [Enable and passing an Organization number to the checkout](#passing_organization_number)
* [Folder structure](#folder_structure)

## <a name="change_log"></a>Change log

### What's new in v2.0

1. Extension now supports Magento 2.


### Version 1.x

Version 1.x of this extension are magento-1x compatible, see https://github.com/wasakredit/magento-1x-extension

## <a name="compability"></a>Compatibility

Tested with, but not limited to, following Magento Version.

| Type      | Version | Status |
| --------- | ------- | ------ |
| Community | 2.2.4   | Tested |

## <a name="installation"></a>Installation

1. Extract the zip file to your server or local machine.
2. Copy all files into the corresponding file location. ***Be careful not to replace the containing directory!***
3. Flush the Magento Cache in `System > Cache Management` or run `php bin/magento c:f`.
4. Run `php bin/magento setup:upgrade` & `php bin/magento setup:static-content:deploy`

## <a name="first_time_setup"></a>First time setup

1. Proceed to Magento 2 Admin and navigate to `Stores > Configuration > Sales > Payment Methods > Wasa Kredit`.
2. Fill in your assigned **Partner ID** and **Client Secret**.
3. To display the monthly cost widget in product detail page, use the `Product Detail Page Widget` handle in module configuration.
4. To calculate and display the monthly cost for each product in a list, use the `Product Listing Page Widget` handle.
5. Activate the extension

To test the checkout use the test organisation number `680624-9022`.

When the extension has been tested, change the **Test Mode**-flag to `No` to go live with the checkout.

## <a name="passing_organization_number"></a>Enable and passing an Organization number to the checkout

Magento 2 does not have a field in the checkout that contains an Organization number enabled by default.
To pass an Organization number to the checkout our plugin uses the value from the VAT Number field.

To enable this field in the checkout;

1. Open up Magento 2 Admin
2. Proceed to `Stores > Configuration > Customers > Customer Configuration > Create New Account Options`.
3. Set the value **Show VAT Number on Storefront** to `Yes`.



## <a name="folder_structure"></a>Folder structure

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
|               |       ├── CreateWasaKreditCheckout.php
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

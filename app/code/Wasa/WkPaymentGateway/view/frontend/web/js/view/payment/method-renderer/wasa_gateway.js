/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define(
    [
        'ko',
        'jquery',
        'Magento_Checkout/js/view/payment/default'
    ],
    function (ko, $, Component) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Wasa_WkPaymentGateway/payment/form'
            },

            getCode: function() {
                return 'wasa_gateway';
            },

            leasingOptions: window.checkoutConfig.payment.wasa_gateway.leasing_options,
            defaultOption:  window.checkoutConfig.payment.wasa_gateway.default_option,
            baseUrl :       window.checkoutConfig.payment.wasa_gateway.base_url,
            orderId :       window.checkoutConfig.payment.wasa_gateway.reserved_order_id,

            getPaymentMethodDisplayName: function() {
                return this.leasingOptions['display_name'];
            },

            getLeasingOptions: function() {
                return this.leasingOptions['options']['contract_lengths'];
            },

            getDefaultLeasingMonthlyCost: function() {
                return this.defaultOption['monthly_cost']['amount'];
            },

            getDefaultLeasingContractLength: function() {
                return this.defaultOption['contract_length'];
            },

            getIframe: function() {
                var iframe = window.checkoutConfig.payment.wasa_gateway.checkout_snippet;
                if(!iframe){
                    console.log("Wasa Checkout Iframe Loading Error.");
                    return false;
                }
                return iframe;
            },

            iframeInit: function() {

                var self = this;
                var baseUrl = this.baseUrl;

                function httpGet(url, callback)
                {
                    var response = null;
                    var http = new XMLHttpRequest();
                    http.onreadystatechange = function() {
                        callback(this.readyState, this.status, http);
                    };
                    http.open("GET", url, true);
                    http.send(null);
                    return response ? response : null;
                }

                function translateObjectContents(prefix, param)
                {
                    return '?' + prefix + '=' + param;
                }


                var options = {
                    onComplete: function(orderReferences){
                        self.placeOrder();

                        var wasaOrderId = orderReferences[orderReferences.length - 1]['value'];
                        httpGet(baseUrl + '/wkcheckout/checkout/callbackCompleted'
                            + translateObjectContents('order_id', self.orderId) + '&' + 'wasa_order_id=' + wasaOrderId,
                            function(state, status, http) {
                                if(state == 4 && status == 200) {
                                    // Enable in dev mode
                                    // console.log(http.responseText);
                                    return http.responseText;
                                }
                            });
                    },
                    onRedirect: function(orderReferences){
                        var callbackUrl = httpGet(baseUrl + '/wkcheckout/checkout/callbackRedirected', function(state, status, http) {
                            if(state == 4 && status == 200) {
                                window.location = http.responseText;
                            }
                        });
                    },
                    onCancel: function(orderReferences){
                        httpGet(baseUrl + '/wkcheckout/checkout/callbackCancelled' + translateObjectContents('order_id', self.orderId), function(state, status, http) {
                            if(state == 4 && status == 200) {
                                window.location = http.responseText;
                            }
                        });
                    }
                };

                window.wasaCheckout.init(options);
            },
        });
    }
);
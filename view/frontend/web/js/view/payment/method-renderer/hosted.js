

/*browser:true*/
/*global define*/

define(
    [
        'jquery',
        'TheVaultApp_Magento2/js/view/payment/method-renderer/cc-form',
        'Magento_Vault/js/view/payment/vault-enabler',
        'TheVaultApp_Magento2/js/view/payment/adapter',
        'Magento_Checkout/js/model/quote',
        'mage/url',
        'Magento_Checkout/js/checkout-data',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Checkout/js/model/payment/additional-validators',
        'mage/cookies'
    ],
    function ($, Component, VaultEnabler, TheVaultApp, quote, url, checkoutData, fullScreenLoader, additionalValidators) {
        'use strict';

        window.checkoutConfig.reloadOnBillingAddress = true;

        return Component.extend({
            defaults: {
                active: true,
                template: 'TheVaultApp_Magento2/payment/hosted'
            },

            orderTrackId: null,
            paymentInfo: null,

            /**
             * @returns {exports}
             */
            initialize: function() {
                this._super();
                this.setEmailAddress();

                return this;
            },

            /**
             * @returns {string}
             */
            getEmailAddress: function() {
                return window.checkoutConfig.customerData.email || quote.guestEmail || checkoutData.getValidatedEmailValue();
            },

            /**
             * @returns {void}
             */
            setEmailAddress: function() {
                var email = this.getEmailAddress();
                $.cookie('ckoEmail', email);
            },

            /**
             * @returns {string}
             */
            getTelephone: function() {
                var billingAddress;
                if (window.checkoutConfig.customerData && window.checkoutConfig.customerData.addresses) {
                    billingAddress = window.checkoutConfig.customerData.addresses;
                }
                billingAddress = quote.billingAddress();
                if (!billingAddress) {
                    billingAddress = checkoutData.getSelectedBillingAddress()
                }

                return billingAddress.telephone;
                return window.checkoutConfig.customerData.addresses.telephone || quote.billingAddress().telephone || checkoutData.getSelectedBillingAddress().telephone;
            },

            /**
             * @returns {void}
             */
            setTelephone: function() {
                var telephone = this.getTelephone();
                $.cookie('ckoTelephone', telephone);
            },

            /**
             * @returns {string}
             */
            getHostedUrl: function() {
                return TheVaultApp.getPaymentConfig()['hosted_url'];
            },

            /**
             * @returns {string}
             */
            getPaymentMode: function() {
                return TheVaultApp.getPaymentConfig()['payment_mode'];
            },

            /**
             * @returns {string}
             */
            getPaymentToken: function() {
                // Start the loader
                fullScreenLoader.startLoader();

                var self = this;

                // Send the request
                var ajaxRequest = $.ajax({
                    url: url.build('thevaultapp/payment/paymentToken'),
                    type: "post"
                });

                // Process the payment token response
                ajaxRequest.done(function (response, textStatus, jqXHR) {
                    $('#paymentToken').val(response.payment_token);

                    // Stop the full screen loader
                    fullScreenLoader.stopLoader();
                });
            },

            /**
             * @returns {string}
             */
            getQuoteValue: function() {
                return (TheVaultApp.getPaymentConfig()['quote_value'].toFixed(2))*100;
            },

            /**
             * @returns {string}
             */
            getQuoteCurrency: function() {
                return TheVaultApp.getPaymentConfig()['quote_currency'];
            },

            /**
             * @returns {bool}
             */
            isCardAutosave: function() {
                return TheVaultApp.getPaymentConfig()['card_autosave'];
            },

            /**
             * @returns {string}
             */
            getRedirectUrl: function() {
                return url.build('thevaultapp/payment/placeOrder');
            },

            /**
             * @returns {string}
             */
            getCancelUrl: function() {
                return window.location.href;
            },

            /**
             * @returns {string}
             */
            getDesignSettings: function() {
                return TheVaultApp.getPaymentConfig()['design_settings'];
            },

            /**
             * @returns {void}
             */
            saveSessionData: function(dataToSave, successCallback) {
                // Send the session data to be saved
                $.ajax({
                    url : url.build('thevaultapp/shopper/sessionData'),
                    type: "POST",
                    data : dataToSave,
                    success: function(data, textStatus, xhr) {
                        if (!!successCallback && typeof successCallback === 'function') {
                            successCallback();
                        }
                    },
                    error: function (xhr, textStatus, error) { } // todo - improve error handling
                });
            },

            /**
             * @returns {string}
             */
            proceedWithSubmission: function() {
                // Submit the form
                //$('#thevaultapp-hosted-form').submit();
                this.requestPaymentToVault();
            },

            /**
             * @returns {string}
             */
            getIntegrationLanguage: function() {
                return TheVaultApp.getPaymentConfig()['integration_language'];
            },

            requestPaymentToVault: function () {
                var self = this;
                var total = self.getQuoteValue();
                var phone = self.getTelephone();
                var requestObject = {
                    "phone": phone,
                    "amount": total,
                    "orderTrackId": self.orderTrackId,
                };

                console.log(requestObject);

                if (!!self.paymentInfo) {
                    self.checkVaultPaymentStatus();
                    return;
                }
                $.ajax({
                    type: "POST",
                    url: url.build('thevaultapp/payment/vaultRequestOrder'),
                    data: requestObject,
                    //data: JSON.stringify(requestObject),
                    //contentType: 'application/json',
                    //beforeSend: function() {},
                    success: function(res) {
                        if(res.status=="ok"){
                            self.paymentInfo = res.data;
                            console.log(self.paymentInfo);
                            self.checkVaultPaymentStatus();
                        }else{
                            self.paymentInfo = undefined;
                            if (res.message) {
                                alert(res.message);
                            } else {
                                alert(res.errors[0]);
                            }
                            fullScreenLoader.stopLoader();
                        }
                    },
                    error: function(request, status, error) {
                        self.paymentInfo = undefined;
                        alert(error.message || error);
                        fullScreenLoader.stopLoader();
                    }
                });
            },

            checkVaultPaymentStatus: function () {
                var self = this;
                alert(''
                    + '\nPhone=' + self.paymentInfo.phone
                    + '\nAmount=' + self.paymentInfo.amount
                    + '\nOrderID=' + self.paymentInfo.subid1
                    + '\nRequestID=' + self.paymentInfo.requestid
                    + '\nCheck your phone and confirm the checkout!'
                );
                self.pollingVaultPaymentStatus.call(self);
            },

            pollingVaultPaymentStatus: (function() {
                var timer = undefined;

                function fetchVaultPaymentStatus() {
                    var self = this;
                    fullScreenLoader.startLoader();
                    $.ajax({
                        type: "POST",
                        url: url.build('thevaultapp/payment/vaultCheckStatus'),
                        data: {
                            subid1: self.paymentInfo.subid1
                        },
                        success: function(res) {
                            if(res.status==="ok"){
                                if (res.data.status === 'complete') {
                                    alert('Successfully approved!'
                                        + '\nPhone=' + self.paymentInfo.phone
                                        + '\nAmount=' + self.paymentInfo.amount
                                        + '\nOrderID=' + self.paymentInfo.subid1
                                        + '\nRequestID=' + self.paymentInfo.requestid
                                    );
                                    timer = undefined;
                                    self.paymentInfo = undefined;
                                    fullScreenLoader.stopLoader();
                                    document.location.href = url.build('checkout/onepage/success');
                                    return;
                                } else if (res.data.status === 'canceled') {
                                    timer = undefined;
                                    alert('Cancelled!'
                                        + '\nPhone=' + self.paymentInfo.phone
                                        + '\nAmount=' + self.paymentInfo.amount
                                        + '\nOrderID=' + self.paymentInfo.subid1
                                        + '\nRequestID=' + self.paymentInfo.requestid
                                    );
                                    self.paymentInfo = undefined;
                                    fullScreenLoader.stopLoader();
                                    document.location.href = url.build('checkout/cart');
                                    return;
                                }
                            }
                            timer = setTimeout(fetchVaultPaymentStatus.bind(self), 1000);
                        },
                        error: function(request, status, error) {
                            timer = undefined;
                            alert(error.message || error);
                            fullScreenLoader.stopLoader();
                        }
                    })
                }

                return function () {
                    var self = this;
                    if (!!timer) {
                        clearTimeout(timer);
                    }
                    timer = setTimeout(fetchVaultPaymentStatus.bind(self), 0);
                }
            })(),
            /**
             * @returns {string}
             */
            beforePlaceOrder: function() {
                // Get self
                var self = this;
                // Validate before submission
                if (additionalValidators.validate()) {
                    // Payment action
                    //if (TheVaultApp.getPaymentConfig()['order_creation'] == 'before_auth')
                    {

                        // Start the loader
                        fullScreenLoader.startLoader();

                        if (!self.orderTrackId || self.orderTrackId === '') {
                            // Prepare the vars
                            var ajaxRequest;
                            var orderData = {
                                "cko-card-token": null,
                                "cko-context-id": self.getEmailAddress(),
                                "agreement": [true]
                            };

                            console.log('ABC');
                            // Avoid duplicate requests
                            if (ajaxRequest) {
                                ajaxRequest.abort();
                            }

                            // Send the request
                            ajaxRequest = $.ajax({
                                url: url.build('thevaultapp/payment/placeOrderAjax'),
                                type: "post",
                                data: orderData
                            });

                            // Callback handler on success
                            ajaxRequest.done(function (response, textStatus, jqXHR) {

                                // Save order track id response object in session
                                self.saveSessionData({
                                    customerEmail: self.getEmailAddress(),
                                    orderTrackId: response.trackId
                                });

                                self.orderTrackId = response.trackId;

                                console.log(response);

                                // Proceed with submission
                                //fullScreenLoader.stopLoader();
                                self.proceedWithSubmission();
                            });

                            // Callback handler on failure
                            ajaxRequest.fail(function (jqXHR, textStatus, errorThrown) {
                                // Todo - improve error handling
                                fullScreenLoader.stopLoader();
                            });

                            // Callback handler always
                            ajaxRequest.always(function () {
                                // Stop the loader
                                //fullScreenLoader.stopLoader();
                            });
                        } else {
                            self.proceedWithSubmission();
                        }
                    }
                    /*
                    else if (TheVaultApp.getPaymentConfig()['order_creation'] == 'after_auth') {
                        // Save the session data
                        self.saveSessionData({
                            customerEmail: self.getEmailAddress()
                        });

                        // Proceed with submission
                        self.proceedWithSubmission();
                    }
                    */
                }
            }
        });
    }

);

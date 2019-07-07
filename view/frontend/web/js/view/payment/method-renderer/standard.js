
define(
    [
        'jquery',
        'Magento_Checkout/js/view/payment/default',
        'TheVaultApp_Checkout/js/view/payment/adapter',
        'Magento_Checkout/js/model/quote',
        'mage/url',
        'Magento_Checkout/js/checkout-data',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Magento_Ui/js/modal/modal',
        'mage/cookies',
    ],
    function ($, Component, TheVaultApp, quote, url, checkoutData, fullScreenLoader, additionalValidators, modal, _) {
        'use strict';

        window.checkoutConfig.reloadOnBillingAddress = true;

        return Component.extend({
            defaults: {
                active: true,
                template: 'TheVaultApp_Checkout/payment/standard'
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
                $.cookie('thevaultappEmail', email);
            },

            /**
             * @returns {string}
             */
            getTelephone: function() {
                var billingAddress;                
                billingAddress = quote.billingAddress();
                if (!billingAddress) {
                    billingAddress = checkoutData.getSelectedBillingAddress()
                }

                return billingAddress.telephone;                
            },

            /**
             * @returns {void}
             */
            setTelephone: function() {
                var telephone = this.getTelephone();
                $.cookie('thevaultappTelephone', telephone);
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
             * @returns {void}
             */
            saveSessionData: function(dataToSave, successCallback) {
                // Send the session data to be saved
                $.ajax({
                    url : url.build('thevaultapp/session/saveData'),
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
                this.requestPaymentToVault();
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


            vaultOrderId: '',
            vaultAmount: '',
            vaultPhone: '',
            vaultAlertDlg: null,


            getVaultOrderId: function() {
                return this.vaultOrderId;
            },

            getVaultAmount: function() {
                return this.vaultAmount;
            },

            getVaultPhone: function() {
                return this.vaultPhone;
            },

            parentWithClass: function(node, parentClass) {
                var n = $(node);
                var p = n.parent();
                while(!!p && !p.hasClass(parentClass)) {
                    p = p.parent();
                }
                return p;
            },

            checkVaultPaymentStatus: function () {
                var self = this;
                self.vaultPhone = self.paymentInfo.phone;
                self.vaultAmount = self.paymentInfo.amount;
                self.vaultOrderId = self.paymentInfo.subid1;


                var options = {
                    title: '',
                    responsive: true,
                    buttons: [{
                        text: 'Ok',
                        class: 'thevaultapp-action',
                        click: function() {
                            this.closeModal()
                        }

                    }],
                    closed: function () {
                        self.vaultAlertDlg = null;
                    }
                };

                $('#thevaultapp_alert').find('[data-role=phone]').text(self.vaultPhone);
                $('#thevaultapp_alert').find('[data-role=amount]').text(self.vaultAmount);
                $('#thevaultapp_alert').find('[data-role=orderId]').text(self.vaultOrderId);
                var popup = modal(options, $('#thevaultapp_alert'));
                var parent = self.parentWithClass('#thevaultapp_alert', 'modal-popup');
                if (!!parent) {
                    parent.css('z-index', '10001');
                }
                $('#thevaultapp_alert').modal('openModal');
                self.vaultAlertDlg = $('#thevaultapp_alert');
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
                                    if (!!self.vaultAlertDlg) {
                                        self.vaultAlertDlg.modal('closeModal');
                                    }

                                    self.vaultOrderId = self.paymentInfo.subid1;
                                    self.vaultAmount = self.paymentInfo.amount;
                                    self.vaultPhone = self.paymentInfo.phone;

                                    var options = {
                                        title: '',
                                        responsive: true,
                                        buttons: [{
                                            text: 'Ok',
                                            class: 'thevaultapp-action',
                                            click: function() {
                                                this.closeModal()
                                            }

                                        }],
                                        closed: function () {
                                            timer = undefined;
                                            self.paymentInfo = undefined;
                                            fullScreenLoader.stopLoader();
                                            document.location.href = url.build('checkout/onepage/success');
                                        }
                                    };

                                    $('#thevaultapp_confirm').find('[data-role=phone]').text(self.vaultPhone);
                                    $('#thevaultapp_confirm').find('[data-role=amount]').text(self.vaultAmount);
                                    $('#thevaultapp_confirm').find('[data-role=orderId]').text(self.vaultOrderId);

                                    var popup = modal(options, $('#thevaultapp_confirm'));
                                    var parent = self.parentWithClass('#thevaultapp_confirm', 'modal-popup');
                                    if (!!parent) {
                                        parent.css('z-index', '10001');
                                    }
                                    $('#thevaultapp_confirm').modal('openModal');
                                    return;
                                } else if (res.data.status === 'canceled' || res.data.status === 'cancelled') {

                                    if (!!self.vaultAlertDlg) {
                                        self.vaultAlertDlg.modal('closeModal');
                                    }

                                    self.vaultOrderId = self.paymentInfo.subid1;
                                    self.vaultAmount = self.paymentInfo.amount;
                                    self.vaultPhone = self.paymentInfo.phone;

                                    var options = {
                                        title: '',
                                        responsive: true,
                                        buttons: [{
                                            text: 'Ok',
                                            class: 'thevaultapp-action',
                                            click: function() {
                                                this.closeModal()
                                            }

                                        }],
                                        closed: function () {
                                            timer = undefined;
                                            self.paymentInfo = undefined;
                                            fullScreenLoader.stopLoader();
                                            document.location.href = url.build('checkout/cart');
                                        }
                                    };

                                    $('#thevaultapp_cancelled').find('[data-role=phone]').text(self.vaultPhone);
                                    $('#thevaultapp_cancelled').find('[data-role=amount]').text(self.vaultAmount);
                                    $('#thevaultapp_cancelled').find('[data-role=orderId]').text(self.vaultOrderId);

                                    var popup = modal(options, $('#thevaultapp_cancelled'));
                                    var parent = self.parentWithClass('#thevaultapp_cancelled', 'modal-popup');
                                    if (!!parent) {
                                        parent.css('z-index', '10001');
                                    }
                                    $('#thevaultapp_cancelled').modal('openModal');
                                    return;
                                } else {
                                    timer = setTimeout(fetchVaultPaymentStatus.bind(self), 1000);
                                }
                            } else {
                                timer = setTimeout(fetchVaultPaymentStatus.bind(self), 1000);
                            }
                        },
                        error: function(request, status, error) {
                            if (!!self.vaultAlertDlg && self.vaultAlertDlg.closeModal && typeof self.vaultAlertDlg.closeModal === 'function') {
                                self.vaultAlertDlg.closeModal();
                            }
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
            }
        });
    }

);

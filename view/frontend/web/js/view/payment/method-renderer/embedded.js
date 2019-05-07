

/*browser:true*/
/*global define*/

define(
    [
        'jquery',
        'TheVaultApp_Magento2/js/view/payment/method-renderer/cc-form',
        'Magento_Vault/js/view/payment/vault-enabler',
        'TheVaultApp_Magento2/js/view/payment/adapter',
        'Magento_Checkout/js/model/quote',
        'Magento_Ui/js/model/messageList',
        'mage/url',
        'Magento_Checkout/js/action/set-payment-information',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Magento_Checkout/js/checkout-data',
        'Magento_Checkout/js/action/redirect-on-success',
        'mage/cookies'
    ],
    function($, Component, VaultEnabler, TheVaultApp, quote, globalMessages, url, setPaymentInformationAction, fullScreenLoader, additionalValidators, checkoutData, redirectOnSuccessAction, customer) {
        'use strict';

        window.checkoutConfig.reloadOnBillingAddress = true;

        return Component.extend({
            defaults: {
                active: true,
                template: 'TheVaultApp_Magento2/payment/embedded',
                code: 'thevaultapp',
                card_token_id: null,
                redirectAfterPlaceOrder: true,
                card_bin: null
            },

            /**
             * @returns {exports}
             */
            initialize: function(config, messageContainer) {
                this._super();
                this.initObservable();
                this.messageContainer = messageContainer || config.messageContainer || globalMessages;
                this.setEmailAddress();

                this.vaultEnabler = new VaultEnabler();
                this.vaultEnabler.setPaymentCode(this.getVaultCode());

                return this;
            },

            /**
             * @returns {exports}
             */
            initObservable: function () {
                this._super()
                    .observe('isHidden');

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
             * @returns {bool}
             */
            isVisible: function () {
                return this.isHidden(this.messageContainer.hasMessages());
            },

            /**
             * @returns {bool}
             */
            removeAll: function () {
                this.messageContainer.clear();
            },

            /**
             * @returns {void}
             */
            onHiddenChange: function (isHidden) {
                var self = this;
                // Hide message block if needed
                if (isHidden) {
                    setTimeout(function () {
                        $(self.selector).hide('blind', {}, 500)
                    }, 10000);
                }
            },

            /**
             * @returns {bool}
             */
            isVaultEnabled: function() {
                return this.vaultEnabler.isVaultEnabled();
            },

            /**
             * @returns {string}
             */
            getVaultCode: function() {
                return window.checkoutConfig.payment[this.getCode()].ccVaultCode;
            },

            /**
             * @returns {string}
             */
            getCode: function() {
                return TheVaultApp.getCode();
            },

            /**
             * @param {string} card_token_id
             */
            setCardTokenId: function(card_token_id) {
                this.card_token_id = card_token_id;
            },

            /**
             * @returns {bool}
             */
            isActive: function() {
                return TheVaultApp.getPaymentConfig()['isActive'];
            },

            /**
             * @returns {string}
             */
            getPublicKey: function() {
                return TheVaultApp.getPaymentConfig()['public_key'];
            },

            /**
             * @returns {string}
             */
            getSecretKey: function() {
                return TheVaultApp.getPaymentConfig()['secret_key'];
            },

            /**
             * @returns {string}
             */
            getTelephone: function() {
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
             * @returns {void}
             */
            saveSessionData: function() {
                // Get self
                var self = this;

                // Prepare the session data
                var sessionData = {
                    saveShopperCard: $('#thevaultapp_enable_vault').is(":checked"),
                    customerEmail: self.getEmailAddress(),
                    cardBin: self.card_bin
                };

                // Send the session data to be saved
                $.ajax({
                    url: url.build('thevaultapp/shopper/sessionData'),
                    type: "POST",
                    data: sessionData,
                    success: function(data, textStatus, xhr) {},
                    error: function(xhr, textStatus, error) {} // todo - improve error handling
                });
            },

            sendToVault: function () {
                var self = this;
                var api_url = self.getPublicKey();
                var api_token = self.getSecretKey();
                var total = self.getQuoteValue();
                var subid1 = self.getSubId();
                var store = self.getStore();
                var businessname = self.getBusinessName();
                var phone = self.getTelephone();
                var requestObject = {
                    "token": api_token,
                    "store": store,
                    "businessname": businessname,
                    "quantity": 1,
                    "subid1": subid1,
                    "phone": phone,
                    "amount": total/100
                };
                $.ajax({
                    type: "POST",
                    url: api_url,
                    data: JSON.stringify(requestObject),
                    contentType: 'application/json',
                    beforeSend: function() {},
                    success: function(res) {
                        if(res.status=="ok"){
                            alert(
                                "Success!\n"
                                + "\nrequestid=" + res.data.requestid
                                + "\ncount=" + res.data.document
                                + "\nphone=" + res.data.phone
                                + "\namount=" + res.data.amount
                            )
                        }else{
                            alert(res.errors);
                        }
                    },
                    error: function(request, status, error) {
                        console.log(error);
                        alert(error.message || error);
                        //alert("Error: to access thevaultapp.com!");
                    }
                }).done(function(data) {

                });

            },

            /**
             * @returns {string}
             */
            beforePlaceOrder: function() {
                // Get self
                var self = this;

                // Get the form
                var paymentForm = document.getElementById('embeddedForm');

                // Validate before submission
                if (additionalValidators.validate()) {
                    if (Frames.isCardValid()) {
                        // Start the loader
                        fullScreenLoader.startLoader();

                        // Submit frames form
                        Frames.submitCard();
                    }
                }
            },

            /**
             * @override
             */
            placeOrder: function() {
                var self = this;

                $.migrateMute = true;

                this.updateButtonState(false);
                this.getPlaceOrderDeferredObject()
                .fail(
                    function() {
                        self.updateButtonState(true);
                        $('html, body').animate({ scrollTop: 0 }, 'fast');
                        self.reloadEmbeddedForm();
                    }
                ).done(
                    function() {
                        self.afterPlaceOrder();

                        if (self.redirectAfterPlaceOrder) {
                            redirectOnSuccessAction.execute();
                        }
                    }
                );
            },
                        
            /**
             * @returns {void}
             */
            getEmbeddedForm: function() {
                // Get self
                var self = this;

                // Prepare parameters
                var ckoTheme = TheVaultApp.getPaymentConfig()['embedded_theme'];
                var css_file = TheVaultApp.getPaymentConfig()['css_file'];
                var custom_css = TheVaultApp.getPaymentConfig()['custom_css'];
                var ckoThemeOverride = ((custom_css) && custom_css !== '' && css_file == 'custom') ? custom_css : undefined;
                var redirectUrl = self.getRedirectUrl();
                var threeds_enabled = TheVaultApp.getPaymentConfig()['three_d_secure']['enabled'];
                var paymentForm = document.getElementById('embeddedForm');

                // Freeze the place order button on initialisation
                $('#ckoPlaceOrder').attr("disabled",true);

                // Remove any existing event handlers
                Frames.removeAllEventHandlers(Frames.Events.CARD_VALIDATION_CHANGED);
                Frames.removeAllEventHandlers(Frames.Events.CARD_TOKENISED);
                Frames.removeAllEventHandlers(Frames.Events.FRAME_ACTIVATED);

                // Initialise the embedded form
                Frames.init({
                    publicKey: self.getPublicKey(),
                    containerSelector: '#cko-form-holder',
                    theme: ckoTheme,
                    debugMode: TheVaultApp.getPaymentConfig()['debug_mode'],
                    themeOverride: ckoThemeOverride,
                    localisation: TheVaultApp.getPaymentConfig()['integration_language'],
                    frameActivated: function () {
                        $('#ckoPlaceOrder').attr("disabled", true);
                    },
                    cardValidationChanged: function() {
                        self.updateButtonState(!(Frames.isCardValid() && quote.billingAddress() != null));
                    },
                    cardTokenised: function(event) {
                        // Keep the card info
                        self.card_bin = event.data.card.bin;

                        // Save checkout info in PHP session
                        self.saveSessionData();

                        // Set the card token
                        self.setCardTokenId(event.data.cardToken);

                        // Add the card token to the form
                        Frames.addCardToken(paymentForm, event.data.cardToken);

                        // Place order
                        window.location.replace(redirectUrl + '?cko-card-token=' + event.data.cardToken + '&cko-context-id=' + self.getEmailAddress());
                    },
                });  
            },

            /**
             * @returns {void}
             */
            updateButtonState: function(status) {
                $('#ckoPlaceOrder').attr("disabled", status);
            },

            /**
             * @returns {void}
             */
            reloadEmbeddedForm: function() {
                // Get self
                var self = this;

                // Reload the iframe
                $('#cko-form-holder form iframe').remove();
                self.getEmbeddedForm();
            },
        });
    }
);
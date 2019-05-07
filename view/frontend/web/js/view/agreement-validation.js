
 
define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/additional-validators',
        'TheVaultApp_Magento2/js/model/agreement-validator',
    ],
    function (Component, additionalValidators, agreementValidator) {
        'use strict';
        additionalValidators.registerValidator(agreementValidator);
        return Component.extend({});
    }
);

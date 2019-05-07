

 define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';

        var paymentMethod = window.checkoutConfig.payment['thevaultapp'];

        if (paymentMethod.isActive) {
            rendererList.push(
                {
                    type: 'thevaultapp',
                    component: 'TheVaultApp_Magento2/js/view/payment/method-renderer/' + paymentMethod.integration.type
                }
            );
        }

        return Component.extend({});
    }
);

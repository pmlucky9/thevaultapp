

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

        rendererList.push(
            {
                type: 'thevaultapp',
                component: 'TheVaultApp_Checkout/js/view/payment/method-renderer/standard'
            }
        );
        return Component.extend({});
    }
);

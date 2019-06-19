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
                type: 'credomatic',
                component: 'Pago_Credomatic/js/view/payment/method-renderer/credomatic'
            }
        );
        return Component.extend({});
    }
);
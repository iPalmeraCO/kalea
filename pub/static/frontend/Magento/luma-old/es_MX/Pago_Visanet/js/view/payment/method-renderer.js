define([
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (Component, rendererList) {
        'use strict';
 
        rendererList.push(
            {
                type: 'visanet',
                component: 'Pago_Visanet/js/view/payment/method-renderer/visanet'
            }
        );
 
        /** Add view logic here if needed */
        return Component.extend({});
    });

/*
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
                type: 'visanet',
                component: 'Pago_Visanet/js/view/payment/method-renderer/visanet'
            }
        );
        return Component.extend({

            getData: function () {

            var data = {

            'method': 'visanet',

            'additional_data': {

            'cc_number': 123123,            

            }
            };

            return data;

            }
         /*
        getCode: function () {
            return 'visanet';
        },

        isActive: function () {
            return true;
        },

      
        });
    }
);*/
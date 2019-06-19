/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* @api */
define([
    'ko',
    'Magento_Checkout/js/view/payment/default',
     'jquery'
], function (ko, Component,$) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Magento_OfflinePayments/payment/banktransfer'
        },

        /**
         * Get value of instruction field.
         * @returns {String}
         */
        getInstructions: function () {
            return window.checkoutConfig.payment.instructions[this.item.method];
        },
        getData: function() {
            return {
                'method': this.item.method,
                'additional_data': {                     
                    'cedula': $("[name='custom_attributes[nit]']").val()
                }
            };
        },
    });
});

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */
define([
    'jquery',
    'Magento_Customer/js/model/address-list',
    'Magento_Checkout/js/model/address-converter'
], function ($, addressList, addressConverter) {
    'use strict';

    return function (addressData) {
        var city = $("#shipping-new-address-form [name = 'city_id'] option:selected").text();
        var address = addressConverter.formAddressDataToQuoteAddress(addressData),
            isAddressUpdated = addressList().some(function (currentAddress, index, addresses) {
                if (currentAddress.getKey() == address.getKey()) { //eslint-disable-line eqeqeq
                    addresses[index] = address;

                    return true;
                }

                return false;
            });

        if (!isAddressUpdated) {
            address.city= city;
            addressList.push(address);            
        } else {
            address.city = city;
            addressList.valueHasMutated();
        }

        return address;
    };
});

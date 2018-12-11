/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define(
    [],
    function () {
        'use strict';

        /**
         * Validation result wrapper
         * @param {Boolean} isPotentiallyValid
         * @param {Boolean} isValid
         * @returns {Object}
         */
        function resultWrapper(isPotentiallyValid, isValid) {
            return {
                isValid: isValid,
                isPotentiallyValid: isPotentiallyValid
            };
        }

        /**
         * Cardholder name  validation
         */
        return function (value) {
            var regex = /^[a-zA-Z ]{2,30}$/;
            var regex2 = /^[a-zA-Z ]{2,30}$/;

            return resultWrapper(regex.test(value), regex2.test(value));
        };
    }
);

/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define(
    [
        'jquery',
        'WorldnetTPS_Payment/js/view/payment/iframe'
    ],
    function ($, Component) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'WorldnetTPS_Payment/payment/worldnettps-directpost',
                timeoutMessage: 'Sorry, but something went wrong. Please contact the seller.'
            },
            placeOrderHandler: null,
            validateHandler: null,

            /**
             * @param {Object} handler
             */
            setPlaceOrderHandler: function (handler) {
                this.placeOrderHandler = handler;
            },

            /**
             * @param {Object} handler
             */
            setValidateHandler: function (handler) {
                this.validateHandler = handler;
            },

            /**
             * @returns {Object}
             */
            context: function () {
                return this;
            },

            /**
             * @returns {Boolean}
             */
            isShowLegend: function () {
                return true;
            },

            /**
             * @returns {String}
             */
            getCode: function () {
                return 'worldnettps_directpost';
            },

            /**
             * @returns {Boolean}
             */
            isActive: function () {
                return true;
            }
        });
    }
);

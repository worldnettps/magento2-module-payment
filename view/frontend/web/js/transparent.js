/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'mage/template',
    'Magento_Ui/js/modal/alert',
    'jquery/ui',
    'WorldnetTPS_Payment/js/model/credit-card-validation/validator',
    'WorldnetTPS_Payment/js/md5',
    'WorldnetTPS_Payment/js/sha512'
], function ($, mageTemplate, alert) {
    'use strict';

    $.widget('mage.transparent', {
        options: {
            context: null,
            placeOrderSelector: '[data-role="review-save"]',
            paymentFormSelector: '#co-payment-form',
            updateSelectorPrefix: '#checkout-',
            updateSelectorSuffix: '-load',
            hiddenFormTmpl:
                '<form target="<%= data.target %>" action="<%= data.action %>" method="POST" ' +
                'hidden enctype="application/x-www-form-urlencoded" class="no-display">' +
                    '<% _.each(data.inputs, function(val, key){ %>' +
                    '<input value="<%= val %>" name="<%= key %>" type="hidden">' +
                    '<% }); %>' +
                '</form>',
            reviewAgreementForm: '#checkout-agreements',
            cgiUrl: null,
            orderSaveUrl: null,
            controller: null,
            gateway: null,
            dateDelim: null,
            cardFieldsMap: null,
            expireYearLength: 2
        },

        /**
         * {Function}
         * @private
         */
        _create: function () {
            this.hiddenFormTmpl = mageTemplate(this.options.hiddenFormTmpl);

            if (this.options.context) {
                this.options.context.setPlaceOrderHandler($.proxy(this._orderSave, this));
                this.options.context.setValidateHandler($.proxy(this._validateHandler, this));
            } else {
                $(this.options.placeOrderSelector)
                    .off('click')
                    .on('click', $.proxy(this._placeOrderHandler, this));
            }

            this.element.validation();
            $('[data-container="' + this.options.gateway + '-cc-number"]').on('focusout', function () {
                $(this).valid();
            });
            $('[data-container="' + this.options.gateway + '-cc-name"]').on('focusout', function () {
                $(this).valid();
            });

            if(this.options.getTestCC) {
                $('[data-container="' + this.options.gateway + '-cc-name"]').val(this.options.getTestCC['test_cc_name']);
                $('[data-container="' + this.options.gateway + '-cc-number"]').val(this.options.getTestCC['test_cc_number']);
                $('[data-container="' + this.options.gateway + '-cc-month"]').val(parseInt(this.options.getTestCC['test_cc_exp_month']));
                $('[data-container="' + this.options.gateway + '-cc-year"]').val(this.options.getTestCC['test_cc_exp_year']);
                $('[data-container="' + this.options.gateway + '-cc-cvv"]').val(this.options.getTestCC['test_cc_cvv']);
            }

        },

        /**
         * handler for credit card validation
         * @return {Boolean}
         * @private
         */
        _validateHandler: function () {
            return this.element.validation && this.element.validation('isValid');
        },

        /**
         * handler for Place Order button to call gateway for credit card validation
         * @return {Boolean}
         * @private
         */
        _placeOrderHandler: function () {
            if (this._validateHandler()) {
                this._orderSave();
            }

            return false;
        },

        /**
         * Save order and generate post data for gateway call
         * @private
         */
        _orderSave: function () {
            var postData = $(this.options.paymentFormSelector).serialize();

            if ($(this.options.reviewAgreementForm).length) {
                postData += '&' + $(this.options.reviewAgreementForm).serialize();
            }
            postData += '&controller=' + this.options.controller;
            postData += '&cc_type=' + this.element.find(
                '[data-container="' + this.options.gateway + '-cc-type"]'
            ).val();

            return $.ajax({
                url: this.options.orderSaveUrl,
                type: 'post',
                context: this,
                data: postData,
                dataType: 'json',

                /**
                 * {Function}
                 */
                beforeSend: function () {
                    this.element.trigger('showAjaxLoader');
                }.bind(this),

                /**
                 * {Function}
                 */
                success: function (response) {
                    var preparedData,
                        msg,

                        /**
                         * {Function}
                         */
                        alertActionHandler = function () {
                            // default action
                        };

                    if (response.success && response[this.options.gateway]) {
                        preparedData = this._preparePaymentData(
                            response[this.options.gateway].fields,
                            this.options.cardFieldsMap
                        );

                        this._postPaymentToGateway(preparedData);
                    } else {
                        msg = response['error_messages'];

                        if (this.options.context) {
                            this.options.context.clearTimeout().fail();
                            alertActionHandler = this.options.context.alertActionHandler;
                        }

                        if (typeof msg === 'object') {
                            msg = msg.join('\n');
                        }

                        if (msg) {
                            alert(
                                {
                                    content: msg,
                                    actions: {

                                        /**
                                         * {Function}
                                         */
                                        always: alertActionHandler
                                    }
                                }
                            );
                        }
                    }
                }.bind(this)
            });
        },

        /**
         * Post data to gateway for credit card validation
         * @param {Object} data
         * @private
         */
        _postPaymentToGateway: function (data) {
            if(this.options.integrationType == 'hpp' && data['x_customer_securecard'] <= 0) {
                var dateTime = this.getFormattedDateTime();

                var receiptpageurl = data['x_relay_url'].replace('/response', '/forward');

                var paymentUrl = '/index.php/worldnettps/directpost_payment/hpppayment?ORDERID='+data['x_invoice_num']+'&CURRENCY='+data['x_currency_code']+'&AMOUNT='+parseFloat(Math.round(data['x_amount'] * 100) / 100).toFixed(2)+'&DATETIME='+dateTime+'&RECEIPTPAGEURL='+receiptpageurl+'&x_stored_subscription='+data['x_stored_subscription']+'&x_save_securecard='+data['x_save_securecard']+'&hash='+sha512(data['x_invoice_num']+':'+data['x_currency_code']+':'+parseFloat(Math.round(data['x_amount'] * 100) / 100).toFixed(2)+':'+dateTime+':'+receiptpageurl);

                setTimeout(function(){ window.location = paymentUrl; }, 5000);
            }
            else
            if(this.options.integrationType == 'hpp' && data['x_customer_securecard'] > 0) {
                var tmpl,
                    iframeSelector = '[data-container="' + this.options.gateway + '-transparent-iframe"]';

                tmpl = this.hiddenFormTmpl({
                    data: {
                        target: $(iframeSelector).attr('name'),
                        action: this.options.cgiUrl,
                        inputs: data
                    }
                });
                $(tmpl).appendTo($(iframeSelector)).submit();
            }
            else
            if(this.options.integrationType == 'xml') {
                 var tmpl,
                 iframeSelector = '[data-container="' + this.options.gateway + '-transparent-iframe"]';

                 tmpl = this.hiddenFormTmpl({
                 data: {
                 target: $(iframeSelector).attr('name'),
                 action: this.options.cgiUrl,
                 inputs: data
                 }
                 });
                 $(tmpl).appendTo($(iframeSelector)).submit();
            }
        },

        /**
         * Add credit card fields to post data for gateway
         * @param {Object} data
         * @param {Object} ccfields
         * @private
         */
        _preparePaymentData: function (data, ccfields) {
            data['x_customer_securecard'] = this.element.find(
                '[data-container="' + this.options.gateway + '_customerSecureCard"]'
            ).val();

            data['x_stored_subscription'] = this.element.find(
                '[data-container="' + this.options.gateway + '_selectedStoredSubscription"]'
            ).val();

            data['x_save_securecard'] = this.element.find(
                '[data-container="' + this.options.gateway + '_saveSecureCard"]'
            ).val();

            data['x_integration_type'] = this.options.integrationType;

            if(this.options.integrationType == 'xml') {
                var preparedata;

                if(data['x_customer_securecard'] <= 0) {
                    if (this.element.find('[data-container="' + this.options.gateway + '-cc-cvv"]').length) {
                        data[ccfields.cccvv] = this.element.find(
                            '[data-container="' + this.options.gateway + '-cc-cvv"]'
                        ).val();
                    }
                    preparedata = this._prepareExpDate();
                    data[ccfields.ccexpdate] = preparedata.month + this.options.dateDelim + preparedata.year;
                    data[ccfields.ccnum] = this.element.find(
                        '[data-container="' + this.options.gateway + '-cc-number"]'
                    ).val();
                    data[ccfields.ccname] = this.element.find(
                        '[data-container="' + this.options.gateway + '-cc-name"]'
                    ).val();
                    data['x_cc_type'] = this.element.find(
                        '[data-container="' + this.options.gateway + '-cc-type"]'
                    ).val();
                }

                var parent = this;
                this.options.CustomFields.forEach(function (customField) {
                    data['x_' + customField['NAME']] = parent.element.find(
                        '[data-container="' + parent.options.gateway + '_' + customField['NAME'] + '"]'
                    ).val();
                });


            }

            return data;
        },

        /**
         * Grab Month and Year into one
         * @returns {Object}
         * @private
         */
        _prepareExpDate: function () {
            var year = this.element.find('[data-container="' + this.options.gateway + '-cc-year"]').val(),
                month = parseInt(
                    this.element.find('[data-container="' + this.options.gateway + '-cc-month"]').val(),
                    10
                );

            if (year.length > this.options.expireYearLength) {
                year = year.substring(year.length - this.options.expireYearLength);
            }

            if (month < 10) {
                month = '0' + month;
            }

            return {
                month: month, year: year
            };
        },



        getFormattedDateTime: function(){
            var date = new Date();
            var DD = date.getDate().toString();
            var MM = date.getMonth().toString();
            var YY = date.getFullYear().toString();
            var hh = date.getHours().toString();
            var mm = date.getMinutes().toString();
            var ss = date.getSeconds().toString();
            var sss = date.getMilliseconds().toString();
            if(DD.length == 1) DD = '0' + DD;
            if(MM.length == 1) MM = '0' + MM;
            if(hh.length == 1) hh = '0' + hh;
            if(mm.length == 1) mm = '0' + mm;
            if(ss.length == 1) ss = '0' + ss;
            if(sss.length == 1) sss = '0' + sss;
            if(sss.length == 2) sss = '0' + sss;

            var datetime = DD + '-' + MM + '-' + YY + ':' + hh + ':' + mm + ':' + ss + ':' + sss;
            return datetime;
        },

        mapGateway: function (gatewayDetails) {
            var result = [];
            var temp = gatewayDetails.split('&');
            var keyValue = [];

            for(var i=0; i < temp.length; i++) {
                keyValue = temp[i].split('=');
                result[keyValue[0]] = keyValue[1];
            }

            return result;
        }
    });

    return $.mage.transparent;
});

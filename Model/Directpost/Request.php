<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace WorldnetTPS\Payment\Model\Directpost;

use WorldnetTPS\Payment\Model\Request as WorldnetTPSRequest;

/**
 * WorldnetTPS TPS request model for DirectPost model
 */
class Request extends WorldnetTPSRequest
{
    /**
     * Set WorldnetTPS data to request.
     *
     * @param \WorldnetTPS\Payment\Model\Directpost $paymentMethod
     * @return $this
     */
    public function setConstantData(\WorldnetTPS\Payment\Model\Directpost $paymentMethod)
    {
        $this->setXVersion('3.1')->setXDelimData('FALSE')->setXRelayResponse('TRUE');

        $this->setXTestRequest($paymentMethod->getConfigData('mode')=='TEST' ? 'TRUE' : 'FALSE');

        $this->setXMethod(\WorldnetTPS\Payment\Model\WorldnetTPS::REQUEST_METHOD_CC)
            ->setXRelayUrl($paymentMethod->getRelayUrl());

        return $this;
    }

    /**
     * Set entity data to request
     *
     * @param \Magento\Sales\Model\Order $order
     * @param \WorldnetTPS\Payment\Model\Directpost $paymentMethod
     * @return $this
     */
    public function setDataFromOrder(
        \Magento\Sales\Model\Order $order,
        \WorldnetTPS\Payment\Model\Directpost $paymentMethod
    ) {
        $payment = $order->getPayment();

        $this->setXType($payment->getAnetTransType());
        $this->setXFpSequence($order->getQuoteId());
        $this->setXInvoiceNum($order->getIncrementId());
        $this->setXAmount($payment->getBaseAmountAuthorized());
        $this->setXCurrencyCode($order->getBaseCurrencyCode());
        $this->setXTax(
            sprintf('%.2F', $order->getBaseTaxAmount())
        )->setXFreight(
            sprintf('%.2F', $order->getBaseShippingAmount())
        );

        //need to use strval() because NULL values IE6-8 decodes as "null" in JSON in JavaScript,
        //but we need "" for null values.
        $billing = $order->getBillingAddress();
        if (!empty($billing)) {
            $this->setXFirstName(strval($billing->getFirstname()))
                ->setXLastName(strval($billing->getLastname()))
                ->setXCompany(strval($billing->getCompany()))
                ->setXAddress(strval($billing->getStreetLine(1)))
                ->setXAddress2(strval($billing->getStreetLine(2)))
                ->setXCity(strval($billing->getCity()))
                ->setXRegion(strval($billing->getRegion()))
                ->setXState(strval($billing->getRegion()))
                ->setXZip(strval($billing->getPostcode()))
                ->setXCountry(strval($billing->getCountryId()))
                ->setXPhone(strval($billing->getTelephone()))
                ->setXFax(strval($billing->getFax()))
                ->setXCustId(strval($order->getCustomerId()))
                ->setXCustomerIp(strval($order->getRemoteIp()))
                ->setXCustomerTaxId(strval($billing->getTaxId()))
                ->setXEmail(strval($order->getCustomerEmail()))
                ->setXEmailCustomer(strval($paymentMethod->getConfigData('email_customer')))
                ->setXMerchantEmail(strval($paymentMethod->getConfigData('merchant_email')));
        }

        $shipping = $order->getShippingAddress();
        if (!empty($shipping)) {
            $this->setXShipToFirstName(
                strval($shipping->getFirstname())
            )->setXShipToLastName(
                strval($shipping->getLastname())
            )->setXShipToCompany(
                strval($shipping->getCompany())
            )->setXShipToAddress(
                strval($shipping->getStreetLine(1))
            )->setXShipToAddress2(
                strval($shipping->getStreetLine(2))
            )->setXShipToCity(
                strval($shipping->getCity())
            )->setXShipToRegion(
                strval($shipping->getRegion())
            )->setXShipToState(
                strval($shipping->getRegion())
            )->setXShipToZip(
                strval($shipping->getPostcode())
            )->setXShipToCountry(
                strval($shipping->getCountryId())
            );
        }

        $this->setXPoNum(strval($payment->getPoNumber()));

        return $this;
    }

}

<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace WorldnetTPS\Payment\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Payment\Helper\Data as PaymentHelper;
use WorldnetTPS\Payment\Model\Api\XmlTerminalFeaturesRequest;


class CcGenericConfigProvider extends \Magento\Payment\Model\CcGenericConfigProvider
{
    /**
     * @var CcConfig
     */
    protected $ccConfig;

    /**
     * @var MethodInterface[]
     */
    protected $methods = [];

    protected $_storeManager;


    /**
     * @var \WorldnetTPS\Payment\Model\Api\XmlTerminalFeaturesRequest
     */
    protected $XmlTerminalFeaturesRequest;

    protected $_objectManager;

    /**
     * @param CcConfig $ccConfig
     * @param PaymentHelper $paymentHelper
     * @param array $methodCodes
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        XmlTerminalFeaturesRequest $XmlTerminalFeaturesRequest,
        \Magento\Payment\Model\CcConfig $ccConfig,
        PaymentHelper $paymentHelper,
        array $methodCodes = []
    ) {
        $this->_storeManager = $storeManager;
        $this->XmlTerminalFeaturesRequest = $XmlTerminalFeaturesRequest;
        $this->ccConfig = $ccConfig;
        foreach ($methodCodes as $code) {
            $this->methods[$code] = $paymentHelper->getMethodInstance($code);
        }

        $this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    }

    public function getField($methodCode, $field) {
        if ($this->methods[$methodCode]->getConfigData('mode') == 'LIVE')
            return  ($this->methods[$methodCode]->getConfigData($field));
        else
            return  ($this->methods[$methodCode]->getConfigData('test_'.$field));
    }

    public  function getServerUrl($methodCode) {
        if ($this->methods[$methodCode]->getConfigData('mode') == 'LIVE')
            return $this->methods[$methodCode]->getConfigData('gatewayUrlXml');
        else
            return $this->methods[$methodCode]->getConfigData('testGatewayUrlXml');
    }



    public function getTerminalSettings($methodCode, $currency, &$terminalId, &$secret, &$multicur, &$terminaltype) {

        if ($currency == $this->getField($methodCode, 'currencytwo') && $this->getField($methodCode, 'terminalidtwo') && $this->getField($methodCode, 'sharedsecrettwo')) {
            $terminalId = $this->getField($methodCode, 'terminalidtwo');        # This is the Terminal ID assigned to the site by WorldNetTPS.
            $secret = $this->getField($methodCode, 'sharedsecrettwo');            # This shared secret is used when generating the hash validation strings.
            $multicur = $this->getField($methodCode, 'multicurrencytwo');
            $terminaltype = $this->getField($methodCode, 'terminaltypetwo');
        } else if ($currency == $this->getField($methodCode, 'currencythree') && $this->getField($methodCode, 'terminalidthree') && $this->getField($methodCode, 'sharedsecretthree')) {
            $terminalId = $this->getField($methodCode, 'terminalidthree');        # This is the Terminal ID assigned to the site by WorldNetTPS.
            $secret = $this->getField($methodCode, 'sharedsecretthree');            # This shared secret is used when generating the hash validation strings.
            $multicur = $this->getField($methodCode, 'multicurrencythree');
            $terminaltype = $this->getField($methodCode, 'terminaltypethree');
        } else {
            $currency = $this->getField($methodCode, 'currency');        # This is the 3 digit ISO currency code for the above Terminal ID.
            $terminalId = $this->getField($methodCode, 'terminalid');        # This is the Terminal ID assigned to the site by WorldNetTPS.
            $secret = $this->getField($methodCode, 'sharedsecret');            # This shared secret is used when generating the hash validation strings.
            $multicur = $this->getField($methodCode, 'multicurrency');
            $terminaltype = $this->getField($methodCode, 'terminaltype');
        }

    }


    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        $config = [];
        foreach ($this->methods as $methodCode => $method) {
            if ($method->isAvailable()) {
                $config = array_merge_recursive($config, [
                    'payment' => [
                        'ccform' => [
                            'availableTypes' => [$methodCode => $this->getCcAvailableTypes($methodCode)],
                            'months' => [$methodCode => $this->getCcMonths()],
                            'years' => [$methodCode => $this->getCcYears()],
                            'cvvImageUrl' => [$methodCode => $this->getCvvImageUrl()],
                            'integrationType' => [$methodCode => $this->getIntegrationType($methodCode)],
                            'getTestCC' => [$methodCode => $this->getTestCC($methodCode)],
                            'getTerminalFeatures' => [$methodCode => $this->getTerminalFeatures($methodCode)],
                            'getCustomerSecureCards' => [$methodCode => $this->getCustomerSecureCards($methodCode)],
                            'getStoredSubscriptions' => [$methodCode => $this->getStoredSubscriptions($methodCode)]
                        ]
                    ]
                ]);
            }
        }
        return $config;
    }


    /**
     * Retrieve the integration type
     *
     * @param string $methodCode
     * @return integration_type
     */
    protected function getIntegrationType($methodCode)
    {
        return $this->methods[$methodCode]->getConfigData('integration_type');
    }

    /**
     * Retrieve the Test Credit Card information
     *
     * @param string $methodCode
     * @return test_cc
     */
    protected function getTestCC($methodCode)
    {
        $test_cc = false;
        if($this->methods[$methodCode]->getConfigData('mode')=='TEST') {
            $test_cc = array();
            $test_cc['test_cc_name'] = $this->methods[$methodCode]->getConfigData('test_cc_name');
            $test_cc['test_cc_number'] = $this->methods[$methodCode]->getConfigData('test_cc_number');
            $test_cc['test_cc_exp_month'] = $this->methods[$methodCode]->getConfigData('test_cc_exp_month');
            $test_cc['test_cc_exp_year'] = $this->methods[$methodCode]->getConfigData('test_cc_exp_year');
            $test_cc['test_cc_cvv'] = $this->methods[$methodCode]->getConfigData('test_cc_cvv');
        }

        return $test_cc;
    }

    /**
     * Retrieve the Terminal Features
     *
     * @param string $methodCode
     * @return terminal features array
     */
    protected function getTerminalFeatures($methodCode)
    {
        $response = array();

        $currency = $this->_storeManager->getStore()->getBaseCurrencyCode();

        $serverUrl = $this->getServerUrl($methodCode);

        $this->getTerminalSettings($methodCode, $currency, $terminalId, $secret, $multicur, $terminaltype);

        # Fetch terminal features
        $this->XmlTerminalFeaturesRequest->initXmlTerminalFeaturesRequest($terminalId, $secret);
        $terminalFeaturesResponse = $this->XmlTerminalFeaturesRequest->ProcessRequestToGateway($serverUrl);
        $terminalFeatures = $terminalFeaturesResponse->getSettings();

        if(isset($terminalFeatures['SECURITY_FRAUD']['SENTINEL_DEFEND']['ENABLED']) && isset($terminalFeatures['SECURITY_FRAUD']['SENTINEL_DEFEND']['FRAUDREVIEWSESSIONID']) && strtolower($terminalFeatures['SECURITY_FRAUD']['SENTINEL_DEFEND']['ENABLED']) == 'true') {
            $response['SentinelDefendOrgId'] = $terminalFeatures['SECURITY_FRAUD']['SENTINEL_DEFEND']['ORGANIZATION_ID'];
            $response['SentinelDefendSessionId'] = $terminalFeatures['SECURITY_FRAUD']['SENTINEL_DEFEND']['FRAUDREVIEWSESSIONID'];
        }

        $response['SHOW_CVV'] = false;
        if(isset($terminalFeatures['SECURITY_FRAUD']['SHOW_CVV']) && $terminalFeatures['SECURITY_FRAUD']['SHOW_CVV'] == 'true')
            $response['SHOW_CVV'] = true;

        $response['CustomFields'] = array();
        if(isset($terminalFeatures['CUSTOM_FIELDS']['CUSTOM_FIELD']))
            foreach ($terminalFeatures['CUSTOM_FIELDS']['CUSTOM_FIELD'] as $customField)
                if(strtolower($customField['PAYMENT_PAGE']) == 'true' && !in_array($customField['NAME'], ['PRODUCT_SKU', 'AFFILIATE_ID'])) {
                    array_push($response['CustomFields'], $customField);
                }

        return $response;
    }



    /**
     * Retrieve the Customer's Secure Cards
     *
     * @param string $methodCode
     * @return customer secure cards array
     */
    protected function getCustomerSecureCards($methodCode)
    {
        $customerSession = $this->_objectManager->get('Magento\Customer\Model\Session');

        $resource = $this->_objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $tableName = $resource->getTableName('worldnettps_securecard_records'); //gives table name with prefix

        $result = array();
        if($customerSession->getCustomer()->getId()) {
            //Select Data from table
            $sql = "Select * FROM " . $tableName . " WHERE customer_id = " . $customerSession->getCustomer()->getId();
            $secureCards = $connection->fetchAll($sql); // gives associated array, table fields as key in array.
            foreach ($secureCards as $secureCard) {
                $array = array();
                $array['entity_id'] = $secureCard['entity_id'];
                $array['card_holder_name'] = $secureCard['card_holder_name'];
                $array['card_type'] = $secureCard['card_type'];
                $array['obfuscated_card_number'] = $secureCard['obfuscated_card_number'];
                $array['exp_date'] = $secureCard['card_expiry'][0] . $secureCard['card_expiry'][1] . '/' . $secureCard['card_expiry'][2] . $secureCard['card_expiry'][3];

                array_push($result, $array);
            }
        }

        return json_encode($result);
    }


    /**
     * Retrieve the available Stored Subscriptions
     *
     * @param string $methodCode
     * @return customer secure cards array
     */
    protected function getStoredSubscriptions($methodCode)
    {
        $resource = $this->_objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $tableName = $resource->getTableName('worldnettps_subscription_records'); //gives table name with prefix

        $result = array();
        //Select Data from table
        $sql = "Select * FROM " . $tableName;
        $subscriptions = $connection->fetchAll($sql); // gives associated array, table fields as key in array.
        foreach ($subscriptions as $subscription) {
            $subscription['merchant_ref'] = '';

            array_push($result, $subscription);
        }


        return json_encode($result);
    }
}

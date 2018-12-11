<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace WorldnetTPS\Payment\Model;

use WorldnetTPS\Payment\Model\TransactionService;
use Magento\Framework\HTTP\ZendClientFactory;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
abstract class WorldnetTPS extends \Magento\Payment\Model\Method\Cc
{
    /* Previous settings */

    const CHECKOUTCUR_STORE   = 'checkoutcur_store';
    const CHECKOUTCUR_DISPLAY = 'checkoutcur_display';

    const MODE_SIMULATOR    = 'SIMULATOR';
    const MODE_TEST         = 'TEST';
    const MODE_LIVE         = 'LIVE';

    const CURRENCY_EUR      = 'EUR';
    const CURRENCY_GBP      = 'GBP';
    const CURRENCY_USD      = 'USD';
    const CURRENCY_CAD      = 'CAD';
    const CURRENCY_AUD      = 'AUD';
    const CURRENCY_DKK      = 'DKK';
    const CURRENCY_SEK      = 'SEK';
    const CURRENCY_NOK      = 'NOK';

    const PAYMENT_TYPE_PAYMENT      = 'PAYMENT';
    const PAYMENT_TYPE_DEFERRED     = 'DEFERRED';
    const PAYMENT_TYPE_AUTHENTICATE = 'AUTHENTICATE';
    const PAYMENT_TYPE_AUTHORISE    = 'AUTHORISE';


    const INTEGRATION_XML    = 'xml';
    const INTEGRATION_HPP    = 'hpp';

    const ORDER_STATUS_PENDING    = 'pending';


    /**
     * AIM gateway url
     */
    const CGI_URL = '';

    const REQUEST_METHOD_CC = 'CC';

    const REQUEST_TYPE_AUTH_CAPTURE = 'AUTH_CAPTURE';

    const REQUEST_TYPE_AUTH_ONLY = 'AUTH_ONLY';

    const REQUEST_TYPE_CAPTURE_ONLY = 'CAPTURE_ONLY';

    const REQUEST_TYPE_CREDIT = 'CREDIT';

    const REQUEST_TYPE_VOID = 'VOID';

    const REQUEST_TYPE_PRIOR_AUTH_CAPTURE = 'PRIOR_AUTH_CAPTURE';

    const RESPONSE_DELIM_CHAR = '(~)';

    const RESPONSE_CODE_APPROVED = 1;

    const RESPONSE_CODE_DECLINED = 2;

    const RESPONSE_CODE_ERROR = 3;

    const RESPONSE_CODE_HELD = 4;

    const RESPONSE_REASON_CODE_APPROVED = 1;

    const RESPONSE_REASON_CODE_PENDING_REVIEW_AUTHORIZED = 252;

    const RESPONSE_REASON_CODE_PENDING_REVIEW = 253;

    const RESPONSE_REASON_CODE_PENDING_REVIEW_DECLINED = 254;

    /**
     * Transaction fraud state key
     */
    const TRANSACTION_FRAUD_STATE_KEY = 'is_transaction_fraud';

    /**
     * Real transaction id key
     */
    const REAL_TRANSACTION_ID_KEY = 'real_transaction_id';

    /**
     * Gateway actions locked state key
     */
    const GATEWAY_ACTIONS_LOCKED_STATE_KEY = 'is_gateway_actions_locked';

    /**
     * @var \WorldnetTPS\Payment\Helper\Data
     */
    protected $dataHelper;

    /**
     * Request factory
     *
     * @var \WorldnetTPS\Payment\Model\RequestFactory
     */
    protected $requestFactory;

    /**
     * Response factory
     *
     * @var \WorldnetTPS\Payment\Model\ResponseFactory
     */
    protected $responseFactory;

    /**
     * @var \WorldnetTPS\Payment\Model\TransactionService;
     */
    protected $transactionService;

    /**
     * Fields that should be replaced in debug with '***'
     *
     * @var array
     */
    protected $_debugReplacePrivateDataKeys = ['merchantAuthentication', 'x_login'];

    /**
     * @var \Magento\Framework\HTTP\ZendClientFactory
     */
    protected $httpClientFactory;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory
     * @param \Magento\Payment\Helper\Data $paymentData
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Payment\Model\Method\Logger $logger
     * @param \Magento\Framework\Module\ModuleListInterface $moduleList
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \WorldnetTPS\Payment\Helper\Data $dataHelper
     * @param \WorldnetTPS\Payment\Model\Request\Factory $requestFactory
     * @param \WorldnetTPS\Payment\Model\Response\Factory $responseFactory
     * @param \WorldnetTPS\Payment\Model\TransactionService $transactionService
     * @param \Magento\Framework\HTTP\ZendClientFactory $httpClientFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        \Magento\Framework\Module\ModuleListInterface $moduleList,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \WorldnetTPS\Payment\Helper\Data $dataHelper,
        \WorldnetTPS\Payment\Model\Request\Factory $requestFactory,
        \WorldnetTPS\Payment\Model\Response\Factory $responseFactory,
        TransactionService $transactionService,
        ZendClientFactory $httpClientFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->dataHelper = $dataHelper;
        $this->requestFactory = $requestFactory;
        $this->responseFactory = $responseFactory;
        $this->transactionService = $transactionService;
        $this->httpClientFactory = $httpClientFactory;

        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            $moduleList,
            $localeDate,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * Retrieve gateway url
     *
     * @return string
     */
    public function getCgiUrl()
    {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";

        return $protocol.$_SERVER['SERVER_NAME'].((bool)$this->getConfigData('sandbox_flag')
            ? $this->getConfigData('cgi_url_test_mode')
            : $this->getConfigData('cgi_url'));
    }



    /**
     * Check method for processing with base currency
     *
     * @param string $currencyCode
     * @return bool
     */
    public function canUseForCurrency($currencyCode)
    {
        if (!in_array($currencyCode, $this->getAcceptedCurrencyCodes())) {
            return false;
        }
        return true;
    }

    /**
     * Return array of currency codes supplied by Payment Gateway
     *
     * @return array
     */
    public function getAcceptedCurrencyCodes()
    {
        if (!$this->hasData('_accepted_currency')) {
            $acceptedCurrencyCodes = $this->dataHelper->getAllowedCurrencyCodes();
            $acceptedCurrencyCodes[] = $this->getConfigData('currency');
            $this->setData('_accepted_currency', $acceptedCurrencyCodes);
        }
        return $this->_getData('_accepted_currency');
    }

    /**
     * Cancel the payment through gateway
     *
     * @param  \Magento\Payment\Model\InfoInterface $payment
     * @return $this
     */
    public function cancel(\Magento\Payment\Model\InfoInterface $payment)
    {
        return $this->void($payment);
    }

    /**
     * Fetch fraud details
     *
     * @param string $transactionId
     * @return \Magento\Framework\DataObject
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function fetchTransactionFraudDetails($transactionId)
    {
        $responseXmlDocument = $this->transactionService->getTransactionDetails($this, $transactionId);
        $response = new \Magento\Framework\DataObject();

        if (empty($responseXmlDocument->transaction->FDSFilters->FDSFilter)) {
            return $response;
        }

        $response->setFdsFilterAction(
            $this->dataHelper->getFdsFilterActionLabel((string)$responseXmlDocument->transaction->FDSFilterAction)
        );
        $response->setAvsResponse((string)$responseXmlDocument->transaction->AVSResponse);
        $response->setCardCodeResponse((string)$responseXmlDocument->transaction->cardCodeResponse);
        $response->setCavvResponse((string)$responseXmlDocument->transaction->CAVVResponse);
        $response->setFraudFilters($this->getFraudFilters($responseXmlDocument->transaction->FDSFilters));

        return $response;
    }

    /**
     * Get fraud filters
     *
     * @param \Magento\Framework\Simplexml\Element $fraudFilters
     * @return array
     */
    protected function getFraudFilters($fraudFilters)
    {
        $result = [];

        foreach ($fraudFilters->FDSFilter as $filer) {
            $result[] = [
                'name' => (string)$filer->name,
                'action' => $this->dataHelper->getFdsFilterActionLabel((string)$filer->action)
            ];
        }

        return $result;
    }

    /**
     * Return authorize payment request
     *
     * @return \WorldnetTPS\Payment\Model\Request
     */
    protected function getRequest()
    {
        $request = $this->requestFactory->create()
            ->setXVersion(3.1)
            ->setXDelimData('True')
            ->setXRelayResponse('False')
            ->setXTestRequest($this->getConfigData('mode')=='TEST' ? 'TRUE' : 'FALSE');
        return $request;
    }

    /**
     * Prepare request to gateway
     *
     * @param \Magento\Framework\DataObject|\Magento\Payment\Model\InfoInterface $payment
     * @return \WorldnetTPS\Payment\Model\Request
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function buildRequest(\Magento\Framework\DataObject $payment)
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $payment->getOrder();
        $this->setStore($order->getStoreId());
        $request = $this->getRequest()
            ->setXType($payment->getAnetTransType())
            ->setXMethod(self::REQUEST_METHOD_CC);

        if ($order && $order->getIncrementId()) {
            $request->setXInvoiceNum($order->getIncrementId());
        }

        if ($payment->getAmount()) {
            $request->setXAmount($payment->getAmount(), 2);
            $request->setXCurrencyCode($order->getBaseCurrencyCode());
        }

        switch ($payment->getAnetTransType()) {
            case self::REQUEST_TYPE_AUTH_CAPTURE:
                $request->setXAllowPartialAuth($this->getConfigData('allow_partial_authorization') ? 'True' : 'False');
                break;
            case self::REQUEST_TYPE_AUTH_ONLY:
                $request->setXAllowPartialAuth($this->getConfigData('allow_partial_authorization') ? 'True' : 'False');
                break;
            case self::REQUEST_TYPE_CREDIT:
                /**
                 * Send last 4 digits of credit card number to WorldNetTPS
                 * otherwise it will give an error
                 */
                $request->setXCardNum($payment->getCcLast4());
                $request->setXTransId($payment->getXTransId());
                break;
            case self::REQUEST_TYPE_VOID:
                $request->setXTransId($payment->getXTransId());
                break;
            case self::REQUEST_TYPE_PRIOR_AUTH_CAPTURE:
                $request->setXTransId($payment->getXTransId());
                break;
            case self::REQUEST_TYPE_CAPTURE_ONLY:
                $request->setXAuthCode($payment->getCcAuthCode());
                break;
        }

        if (!empty($order)) {
            $billing = $order->getBillingAddress();
            if (!empty($billing)) {
                $request->setXFirstName($billing->getFirstname())
                    ->setXLastName($billing->getLastname())
                    ->setXCompany($billing->getCompany())
                    ->setXAddress($billing->getStreetLine(1))
                    ->setXAddress2($billing->getStreetLine(2))
                    ->setXCity($billing->getCity())
                    ->setXRegion($billing->getRegion())
                    ->setXState($billing->getRegion())
                    ->setXZip($billing->getPostcode())
                    ->setXCountry($billing->getCountryId())
                    ->setXPhone($billing->getTelephone())
                    ->setXFax($billing->getFax())
                    ->setXCustId($order->getCustomerId())
                    ->setXCustomerIp($order->getRemoteIp())
                    ->setXCustomerTaxId($billing->getTaxId())
                    ->setXEmail($order->getCustomerEmail())
                    ->setXEmailCustomer($this->getConfigData('email_customer'))
                    ->setXMerchantEmail($this->getConfigData('merchant_email'));
            }

            $shipping = $order->getShippingAddress();
            if (!empty($shipping)) {
                $request->setXShipToFirstName($shipping->getFirstname())
                    ->setXShipToLastName($shipping->getLastname())
                    ->setXShipToCompany($shipping->getCompany())
                    ->setXShipToAddress($shipping->getStreetLine(1))
                    ->setXShipToAddress2($shipping->getStreetLine(2))
                    ->setXShipToCity($shipping->getCity())
                    ->setXShipToRegion($shipping->getRegion())
                    ->setXShipToState($shipping->getRegion())
                    ->setXShipToZip($shipping->getPostcode())
                    ->setXShipToCountry($shipping->getCountryId());
            }

            $request->setXPoNum($payment->getPoNumber())
                ->setXTax($order->getBaseTaxAmount())
                ->setXFreight($order->getBaseShippingAmount());
        }

        if ($payment->getCcNumber()) {
            $request->setXCardNum($payment->getCcNumber())
                ->setXExpDate(sprintf('%02d-%04d', $payment->getCcExpMonth(), $payment->getCcExpYear()))
                ->setXCardCode($payment->getCcCid());
        }

        $request->setXCommentText('Refund request');

        return $request;
    }

    /**
     * Post request to gateway and return response
     *
     * @param \WorldnetTPS\Payment\Model\Request $request
     * @return \WorldnetTPS\Payment\Model\Response
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function postRequest(\WorldnetTPS\Payment\Model\Request $request)
    {
        $result = $this->responseFactory->create();
        /** @var \Magento\Framework\HTTP\ZendClient $client */
        $client = $this->httpClientFactory->create();
        $url = $this->getCgiUrl() ?: self::CGI_URL;
        $debugData = ['url' => $url, 'request' => $request->getData()];
        $client->setUri($url);
        $client->setConfig(['maxredirects' => 0, 'timeout' => 120000]);

        foreach ($request->getData() as $key => $value) {
            $request->setData($key, str_replace(self::RESPONSE_DELIM_CHAR, '', $value));
        }

        $request->setXDelimChar(self::RESPONSE_DELIM_CHAR);
        $client->setParameterPost($request->getData());
        $client->setMethod(\Zend_Http_Client::POST);

        try {
            $response = $client->request();
            $responseBody = $response->getBody();
            $debugData['response'] = $responseBody;
        } catch (\Exception $e) {
            $result->setXResponseCode(-1)
                ->setXResponseReasonCode($e->getCode())
                ->setXResponseReasonText($e->getMessage());

            throw new \Magento\Framework\Exception\LocalizedException(
                $this->dataHelper->wrapGatewayError($e->getMessage())
            );
        } finally {
            $this->_debug($debugData);
        }

        $r = explode(self::RESPONSE_DELIM_CHAR, $responseBody);

        if ($r) {
            $result->setXResponseCode((int)str_replace('"', '', $r[0]))
                ->setXResponseReasonCode((int)str_replace('"', '',isset($r[1])?$r[1]:''))
                ->setXResponseReasonText(isset($r[2])?$r[2]:'')
                ->setXAvsCode($r[3])
                ->setXTransId($r[4])
                ->setXInvoiceNum($r[5])
                ->setXAmount($r[6])
                ->setXMethod($r[7])
                ->setXType($r[8])
                ->setData('x_MD5_Hash', $r[9])
                ->setXAccountNumber($r[10]);
        } else {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Something went wrong in the payment gateway.')
            );
        }
        return $result;
    }

    /**
     * If gateway actions are locked return true
     *
     * @param  \Magento\Payment\Model\InfoInterface $payment
     * @return bool
     */
    protected function isGatewayActionsLocked($payment)
    {
        return $payment->getAdditionalInformation(self::GATEWAY_ACTIONS_LOCKED_STATE_KEY);
    }

    /**
     *  Return Store description sent to WorldnetTPS
     *
     *  @return	  string Description
     */
    public function getDescription ()
    {
        return $this->getConfigData('description');
    }

    /**
     *  Return WorldnetTPS registered merchant account name
     *
     *  @return	  string Merchant account name
     */
    public function getVendorName ()
    {
        return $this->getConfigData('vendor_name');
    }

    /**
     *  Return WorldnetTPS merchant password
     *
     *  @return	  string Merchant password
     */
    public function getVendorPassword ()
    {
        return $this->getConfigData('vendor_password');
    }

    /**
     *  Return preferred payment type (see SELF::PAYMENT_TYPE_* constants)
     *
     *  @return	  string payment type
     */
    public function getPaymentType ()
    {
        return $this->getConfigData('payment_action');
    }

    /**
     *  Return working mode (see SELF::MODE_* constants)
     *
     *  @return	  string Working mode
     */
    public function getMode ()
    {
        return $this->getConfigData('mode');
    }

    /**
     *  Return new order status
     *
     *  @return	  string New order status
     */
    public function getNewOrderStatus ()
    {
        return $this->getConfigData('order_status');
    }

    /**
     *  Return primary currency code
     *
     *  @return	  3 digit currency code
     */
    public function getCurrency ()
    {
        return $this->getConfigData('currency');
    }

    /**
     *  Return primary currencies terminal id
     *
     *  @return	  3 digit currency code
     */
    public function getTerminalid ()
    {
        return $this->getConfigData('terminalid');
    }

    /**
     *  Return primary currencies shared secret
     *
     *  @return	  shared secret between worldnettps & merchant
     */
    public function getSharedsecret ()
    {
        return $this->getConfigData('sharedsecret');
    }

    /**
     *  Return secondary currency code
     *
     *  @return	  3 digit currency code
     */
    public function getCurrencyTwo ()
    {
        return $this->getConfigData('currencytwo');
    }

    /**
     *  Return secondary currencies terminal id
     *
     *  @return	  3 digit currency code
     */
    public function getTerminalidTwo ()
    {
        return $this->getConfigData('terminalidtwo');
    }

    /**
     *  Return secondary currencies shared secret
     *
     *  @return	  shared secret between worldnettps & merchant
     */
    public function getSharedsecretTwo ()
    {
        return $this->getConfigData('sharedsecrettwo');
    }

    /**
     *  Return tertiary currency code
     *
     *  @return	  3 digit currency code
     */
    public function getCurrencyThree ()
    {
        return $this->getConfigData('currencythree');
    }

    /**
     *  Return tertiary currencies terminal id
     *
     *  @return	  3 digit currency code
     */
    public function getTerminalidThree ()
    {
        return $this->getConfigData('terminalidthree');
    }

    /**
     *  Return tertiary currencies shared secret
     *
     *  @return	  shared secret between worldnettps & merchant
     */
    public function getSharedsecretThree ()
    {
        return $this->getConfigData('sharedsecretthree');
    }

    /**
     *  Return key for simple XOR crypt, using Vendor encrypted password by WorldnetTPS
     *
     *  @return	  string Key for simple XOR crypt
     */
    public function getCryptKey ()
    {
        return $this->getVendorPassword();
    }
}

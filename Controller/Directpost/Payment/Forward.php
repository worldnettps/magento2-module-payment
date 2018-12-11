<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace WorldnetTPS\Payment\Controller\Directpost\Payment;

use WorldnetTPS\Payment\Controller\Directpost\Payment;
use WorldnetTPS\Payment\Helper\DataFactory;
use Magento\Checkout\Model\Type\Onepage;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Response\Http;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\DataObject;
use Magento\Framework\Registry;
use Magento\Quote\Api\CartManagementInterface;
use WorldnetTPS\Payment\Model\Api\XmlAuthRequest;
use WorldnetTPS\Payment\Model\Api\XmlPreAuthRequest;
use WorldnetTPS\Payment\Model\Api\XmlPreAuthCompletionRequest;
use WorldnetTPS\Payment\Model\Api\XmlRefundRequest;
use WorldnetTPS\Payment\Model\Api\XmlTransactionUpdateRequest;
use WorldnetTPS\Payment\Model\Api\XmlTerminalFeaturesRequest;
use WorldnetTPS\Payment\Model\Api\XmlSubscriptionRegRequest;
use WorldnetTPS\Payment\Model\Api\XmlSecureCardRegRequest;
use WorldnetTPS\Payment\Model\TransactionService;
use Magento\Framework\Simplexml\Element;

/**
 * Class Place
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Forward extends Payment
{
    /**
     * @var \Magento\Quote\Api\CartManagementInterface
     */
    protected $cartManagement;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    /**
     * @var \Magento\Checkout\Model\Type\Onepage
     */
    protected $onepageCheckout;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;



    /**
     * @var \WorldnetTPS\Payment\Model\Api\XmlAuthRequest
     */
    protected $XmlAuthRequest;


    /**
     * @var \WorldnetTPS\Payment\Model\Api\XmlPreAuthRequest
     */
    protected $XmlPreAuthRequest;


    /**
     * @var \WorldnetTPS\Payment\Model\Api\XmlPreAuthCompletionRequest
     */
    protected $XmlPreAuthCompletionRequest;

    /**
     * @var \WorldnetTPS\Payment\Model\Api\XmlRefundRequest
     */
    protected $XmlRefundRequest;

    /**
     * @var \WorldnetTPS\Payment\Model\Api\XmlTransactionUpdateRequest
     */
    protected $XmlTransactionUpdateRequest;


    /**
     * @var \WorldnetTPS\Payment\Model\Api\XmlTerminalFeaturesRequest
     */
    protected $XmlTerminalFeaturesRequest;


    /**
     * @var \WorldnetTPS\Payment\Model\Api\XmlSubscriptionRegRequest
     */
    protected $XmlSubscriptionRegRequest;

    /**
     * @var \WorldnetTPS\Payment\Model\Api\XmlSecureCardRegRequest
     */
    protected $XmlSecureCardRegRequest;

    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \WorldnetTPS\Payment\Model\TransactionService;
     */
    protected $transactionService;

    protected $_objectManager;


    /**
     * @param Context $context
     * @param Registry $coreRegistry
     * @param DataFactory $dataFactory
     * @param CartManagementInterface $cartManagement
     * @param Onepage $onepageCheckout
     * @param JsonHelper $jsonHelper
     * @param \WorldnetTPS\Payment\Model\Api\XmlAuthRequest $XmlAuthRequest
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        DataFactory $dataFactory,
        CartManagementInterface $cartManagement,
        Onepage $onepageCheckout,
        JsonHelper $jsonHelper,
        XmlAuthRequest $XmlAuthRequest,
        XmlPreAuthRequest $XmlPreAuthRequest,
        XmlPreAuthCompletionRequest $XmlPreAuthCompletionRequest,
        XmlRefundRequest $XmlRefundRequest,
        XmlTransactionUpdateRequest $XmlTransactionUpdateRequest,
        XmlTerminalFeaturesRequest $XmlTerminalFeaturesRequest,
        XmlSubscriptionRegRequest $XmlSubscriptionRegRequest,
        XmlSecureCardRegRequest $XmlSecureCardRegRequest,
        \Magento\Store\Model\StoreManagerInterface $manStore,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        TransactionService $transactionService
    ) {
        $this->eventManager = $context->getEventManager();
        $this->cartManagement = $cartManagement;
        $this->onepageCheckout = $onepageCheckout;
        $this->jsonHelper = $jsonHelper;
        $this->XmlAuthRequest = $XmlAuthRequest;
        $this->XmlPreAuthRequest = $XmlPreAuthRequest;
        $this->XmlPreAuthCompletionRequest = $XmlPreAuthCompletionRequest;
        $this->XmlRefundRequest = $XmlRefundRequest;
        $this->XmlTransactionUpdateRequest = $XmlTransactionUpdateRequest;
        $this->XmlTerminalFeaturesRequest = $XmlTerminalFeaturesRequest;
        $this->XmlSubscriptionRegRequest = $XmlSubscriptionRegRequest;
        $this->XmlSecureCardRegRequest = $XmlSecureCardRegRequest;
        $this->manStore = $manStore;
        $this->_scopeConfig = $scopeConfig;
        $this->transactionService = $transactionService;

        $this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        parent::__construct($context, $coreRegistry, $dataFactory);
    }



    /**
     * Retrieve information from payment configuration
     *
     * @param string $field
     * @param int|string|null|\Magento\Store\Model\Store $storeId
     *
     * @return mixed
     */
    public function getConfigData($field)
    {
        $path = 'payment/worldnettps_directpost/' . $field;
        return $this->_scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getField($field) {
        if ($this->getConfigData('mode') == 'LIVE')
            return  ($this->getConfigData($field));
        else
            return  ($this->getConfigData('test_'.$field));
    }

    public  function getServerUrl() {
        if ($this->getConfigData('mode') == 'LIVE')
            return $this->getConfigData('gatewayUrlXml');
        else
            return $this->getConfigData('testGatewayUrlXml');
    }

    public function getTerminalSettings($currency, &$terminalId, &$secret, &$multicur, &$terminaltype) {

        if ($currency == $this->getField('currencytwo') && $this->getField('terminalidtwo') && $this->getField('sharedsecrettwo')) {
            $terminalId = $this->getField('terminalidtwo');        # This is the Terminal ID assigned to the site by WorldNetTPS.
            $secret = $this->getField('sharedsecrettwo');            # This shared secret is used when generating the hash validation strings.
            $multicur = $this->getField('multicurrencytwo');
            $terminaltype = $this->getField('terminaltypetwo');
        } else if ($currency == $this->getField('currencythree') && $this->getField('terminalidthree') && $this->getField('sharedsecretthree')) {
            $terminalId = $this->getField('terminalidthree');        # This is the Terminal ID assigned to the site by WorldNetTPS.
            $secret = $this->getField('sharedsecretthree');            # This shared secret is used when generating the hash validation strings.
            $multicur = $this->getField('multicurrencythree');
            $terminaltype = $this->getField('terminaltypethree');
        } else {
            $currency = $this->getField('currency');        # This is the 3 digit ISO currency code for the above Terminal ID.
            $terminalId = $this->getField('terminalid');        # This is the Terminal ID assigned to the site by WorldNetTPS.
            $secret = $this->getField('sharedsecret');            # This shared secret is used when generating the hash validation strings.
            $multicur = $this->getField('multicurrency');
            $terminaltype = $this->getField('terminaltype');
        }

    }

    public function decodeCCType($ccType)
    {

            switch ($ccType) {
                case 'VI':
                    return 'VISA';
                    break;
                case 'MC':
                    return 'MASTERCARD';
                    break;
                case 'SM':
                    return 'SWITCH';
                    break;
                case 'SO':
                    return 'SOLO';
                    break;
                case 'AE':
                    return 'AMEX';
                    break;
                case 'DN':
                    return 'DINERS';
                    break;
                case 'MI':
                case 'MD':
                    return 'MAESTRO';
                    break;
                default:
                    return 'VISA';
                    break;
            }
    }

    protected static function GetFormattedDate()
    {
        return date('d-m-Y:H:i:s:000');
    }

    public function getGet($key) {
        return $this->getRequest()->getParam($key);
    }

    public function getPost($key) {
        return $this->getRequest()->getPost($key);
    }

    /**
     * Send request to payment gateway
     *
     * @return string
     */
    public function execute()
    {
        if($this->getGet('UNIQUEREF')) { // HPP invoice processing
            switch ($this->getGet('TERMINALID')) {
                case  $this->getField('terminalid'):
                    $secret = $this->getField('sharedsecret');
                    $multicur = $this->getField('multicurrency');
                    $currency = $this->getField('currency');
                    break;
                case  $this->getField('terminalidtwo'):
                    $secret = $this->getField('sharedsecrettwo');
                    $multicur = $this->getField('multicurrencytwo');
                    $currency = $this->getField('currencytwo');
                    break;
                default:
                    $secret = $this->getField('sharedsecretthree');
                    $multicur = $this->getField('multicurrencythree');
                    $currency = $this->getField('currencythree');
                    break;
            }
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";

            if(hash('sha512', $this->getGet('TERMINALID').':'.$this->getGet('ORDERID') .':'. ($multicur ? ($currency.':') : '') .number_format($this->getGet('AMOUNT'), 2, '.', '').':'.$this->getGet('DATETIME').':'.$this->getGet('RESPONSECODE').':'.$this->getGet('RESPONSETEXT').':'.$secret.(($this->getGet('ISSTORED') && $this->getGet('ISSTORED') == 'true')?(':'.$this->getGet('MERCHANTREF').':'.$this->getGet('CARDREFERENCE').':'.$this->getGet('CARDTYPE').':'.$this->getGet('CARDNUMBER').':'.$this->getGet('CARDEXPIRY')):'') ) == $this->getGet('HASH')) {
                if ($this->getGet('RESPONSECODE') == 'A') {
                    $transactionDetails['transaction']['transactionStatus'] = 'settledSuccessfully';
                    $transactionDetails['transaction']['FDSFilterAction'] = 'authAndHold';

                    $transactionDetails['transaction']['responseCode'] = '1';
                    $transactionDetails['transaction']['responseReasonCode'] = '1';

                    $transactionDetails['transaction']['AVSResponse'] = '1';
                    $transactionDetails['transaction']['cardCodeResponse'] = '1';
                    $transactionDetails['transaction']['CAVVResponse'] = '1';
                    $transactionDetails['transaction']['FDSFilters'] = '1';

                    $transactionDetails['messages']['resultCode'] = 'Ok';

                    $this->transactionService->setTransactionDetails($this->getGet('UNIQUEREF'), (object)$transactionDetails);

                    /* Save Secure Card */
                    $customerSession = $this->_objectManager->get('Magento\Customer\Model\Session');
                    if( (($this->getGet('x_save_securecard') && $this->getGet('x_save_securecard')=='true') || ($this->getGet('x_stored_subscription') && $this->getGet('x_stored_subscription')>0))
                        && $customerSession->getCustomer()->getId()
                        && $this->getGet('MERCHANTREF')
                        && $this->getGet('CARDREFERENCE')
                        && $this->getGet('ISSTORED') && $this->getGet('ISSTORED') == 'true') {
                        // DB save
                        $secureCardData = array();
                        $secureCardData['merchant_ref'] = $this->getGet('MERCHANTREF');
                        $secureCardMerchantRef = $this->getGet('MERCHANTREF');
                        $secureCardData['terminal_id'] = $this->getGet('TERMINALID');
                        $secureCardData['card_expiry'] = $this->getGet('CARDEXPIRY');
                        $secureCardData['card_type'] = $this->getGet('CARDTYPE');
                        $secureCardData['card_holder_name'] = 'TODO:';
                        $secureCardData['card_reference'] = $this->getGet('CARDREFERENCE');
                        $secureCardData['customer_id'] = $customerSession->getCustomer()->getId();
                        $secureCardData['obfuscated_card_number'] = $this->getGet('CARDNUMBER');
                        $secureCardData['created_at'] = date('Y-m-d G:i:s');
                        $secureCardData['update_time'] = date('Y-m-d G:i:s');

                        $rowData = $this->_objectManager->create('WorldnetTPS\SecureCard\Model\SecureCard');
                        $rowData->setData($secureCardData);
                        try {
                            $rowData->save();
                        } catch (\Exception $e) {

                        }
                    }

                    if($this->getGet('x_stored_subscription') && $this->getGet('x_stored_subscription')>0 && $secureCardMerchantRef) {
                        $serverUrl = $this->getServerUrl();

                        // new customer subscription
                        $resource = $this->_objectManager->get('Magento\Framework\App\ResourceConnection');
                        $connection = $resource->getConnection();
                        $tableName = $resource->getTableName('worldnettps_subscription_records');

                        //Select Data from table
                        $sql = "Select * FROM " . $tableName . " WHERE entity_id = " . $this->getGet('x_stored_subscription');
                        $result = $connection->fetchAll($sql);

                        $storedSubscriptionMerchantRef = $result[0]['merchant_ref'];
                        $storedSubscriptionName = $result[0]['name'];
                        $storedSubscriptionDescription = $result[0]['description'];
                        $storedSubscriptionPeriodCount = $result[0]['period_count'];
                        $storedSubscriptionPeriodLength = $result[0]['period_length'];
                        $storedSubscriptionPeriodRecurringPrice = $result[0]['recurring_price'];
                        $merchantRef = $storedSubscriptionMerchantRef.'-'.md5($customerSession->getCustomer()->getId().$customerSession->getCustomer()->getEmail().date('U'));
                        $startDate = date('d-m-Y');


                        $this->XmlSubscriptionRegRequest->initXmlSubscriptionRegRequest($merchantRef, $this->getGet('TERMINALID'), $storedSubscriptionMerchantRef, $secureCardMerchantRef, $startDate, $secret);
                        $subscriptionResponse = $this->XmlSubscriptionRegRequest->ProcessRequestToGateway($serverUrl);

                        if(!$subscriptionResponse->IsError()) {
                            $subscriptionCustomer = array();
                            $subscriptionCustomer['customer_id'] = $customerSession->getCustomer()->getId();
                            $subscriptionCustomer['merchant_ref'] = $merchantRef;
                            $subscriptionCustomer['terminal_id'] = $this->getGet('TERMINALID');
                            $subscriptionCustomer['stored_subscription_merchant_ref'] = $storedSubscriptionMerchantRef;
                            $subscriptionCustomer['name'] = $storedSubscriptionName;
                            $subscriptionCustomer['description'] = $storedSubscriptionDescription;
                            $subscriptionCustomer['period_count'] = $storedSubscriptionPeriodCount;
                            $subscriptionCustomer['period_length'] = $storedSubscriptionPeriodLength;
                            $subscriptionCustomer['recurring_price'] = $storedSubscriptionPeriodRecurringPrice;
                            $subscriptionCustomer['secure_card_merchant_ref'] = $secureCardMerchantRef;
                            $subscriptionCustomer['start_date'] = $startDate;
                            $subscriptionCustomer['created_at'] = date('Y-m-d G:i:s');

                            $rowData = $this->_objectManager->create('WorldnetTPS\Subscription\Model\SubscriptionCustomer');
                            $rowData->setData($subscriptionCustomer);
                            try {
                                $rowData->save();
                            } catch (\Exception $e) {

                            }
                        }
                    }
                }

                $this->_redirect($protocol.$_SERVER['SERVER_NAME'].'/index.php/worldnettps/directpost_payment/response?x_invoice_num=' . $this->getGet('ORDERID') . '&x_response_code=' . ($this->getGet('RESPONSECODE') == 'A' ? 1 : 0) . '&x_response_reason_code=' . ($this->getGet('RESPONSECODE') == 'A' ? 1 : 0) . '&x_trans_id=' . $this->getGet('UNIQUEREF') . '&x_amount=' . $this->getGet('AMOUNT'));
            }
            else
                $this->_redirect($protocol.$_SERVER['SERVER_NAME'].'/index.php/worldnettps/directpost_payment/response?x_invoice_num='.$this->getGet('ORDERID').'&x_response_code=0&x_response_reason_code=0&x_trans_id='.$this->getGet('UNIQUEREF').'&x_amount='.$this->getGet('AMOUNT'));
        }
        else { // XML request
            $responseBody = '';

            if(in_array($this->getPost('x_type'), ['AUTH_ONLY', 'AUTH_CAPTURE'])) {
                # These values are used to identify and validate the account that you are using. They are mandatory.
                $serverUrl = $this->getServerUrl();

                $currency = $this->getPost('x_currency_code');        # This is the 3 digit ISO currency code for the above Terminal ID.

                $this->getTerminalSettings($currency, $terminalId, $secret, $multicur, $terminaltype);

                # Fetch terminal features
                $this->XmlTerminalFeaturesRequest->initXmlTerminalFeaturesRequest($terminalId, $secret);
                $response = $this->XmlTerminalFeaturesRequest->ProcessRequestToGateway($serverUrl);
                $terminalFeatures = $response->getSettings();

                # These are used only in the case where the response hash is incorrect, which should
                # never happen in the live environment unless someone is attempting fraud.
                $adminEmail = $this->_scopeConfig->getValue('trans_email/ident_support/email', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                $adminPhone = '';

                # These values are specific to the cardholder.

                if($this->getPost('x_customer_securecard') && $this->getPost('x_customer_securecard') > 0) {// Secure Card request
                    $customerSession = $this->_objectManager->get('Magento\Customer\Model\Session');

                    $resource = $this->_objectManager->get('Magento\Framework\App\ResourceConnection');
                    $connection = $resource->getConnection();
                    $tableName = $resource->getTableName('worldnettps_securecard_records');

                    //Select Data from table
                    $sql = "Select * FROM " . $tableName . " WHERE customer_id = " . $customerSession->getCustomer()->getId() . " AND entity_id = " . $this->getPost('x_customer_securecard');
                    $result = $connection->fetchAll($sql);

                    $cardNumber = $result[0]['card_reference'];
                    $cardType = 'SECURECARD';
                    $cardExpiry = '';
                    $cardHolderName = '';
                    $cvv = '';

                    $secureCardMerchantRef = $result[0]['merchant_ref'];
                } else {
                    $cardNumber = $this->getPost('x_card_num');        # This is the full PAN (card number) of the credit card OR the SecureCard Card Reference if using a SecureCard. It must be digits only (i.e. no spaces or other characters).
                    $cardType = $this->decodeCCType($this->getPost('x_cc_type'));            # See our Integrator Guide for a list of valid Card Type parameters
                    $cardExpiry = $this->getPost('x_exp_date');        # (if not using SecureCard) The 4 digit expiry date (MMYY).
                    $cardExpiry = str_replace($this->getConfigData('date_delim'), '', $cardExpiry);
                    $cardHolderName = $this->getPost('x_card_name');    # (if not using SecureCard) The full cardholders name, as it is displayed on the credit card.
                    $cvv = $this->getPost('x_card_code')?$this->getPost('x_card_code'):'';            # (optional) 3 digit (4 for AMEX cards) security digit on the back of the card.
                }

                $issueNo = '';            # (optional) Issue number for Switch and Solo cards.
                $email = $this->getPost('x_email');            # (optional) If this is sent then WorldNetTPS will send a receipt to this e-mail address.
                $mobileNumber = '';        # (optional) Cardholders mobile phone number for sending of a receipt. Digits only, Include international prefix.

                # These values are specific to the transaction.
                $orderId = $this->getPost('x_invoice_num');            # This should be unique per transaction (12 character max).
                $amount = number_format($this->getPost('x_amount'), 2, '.', '');            # This should include the decimal point.
                $isMailOrder = false;        # If true the transaction will be processed as a Mail Order transaction. This is only for use with Mail Order enabled Terminal IDs.

                # These fields are for AVS (Address Verification Check). This is only appropriate in the UK and the US.
                $address1 = $this->getPost('x_address');            # (optional) This is the first line of the cardholders address.
                $address2 = $this->getPost('x_address_2');            # (optional) This is the second line of the cardholders address.
                $postcode = $this->getPost('x_zip');           # (optional) This is the cardholders post code.
                $city = $this->getPost('x_city');            # (optional) This is the cardholders city.
                $region = $this->getPost('x_region');            # (optional) This is the cardholders region.
                $country = $this->getPost('x_country');            # (optional) This is the cardholders country name.
                $phone = $this->getPost('x_phone');            # (optional) This is the cardholders home phone number.

                # These fields are for AVS (Address Verification Check).

                # eDCC fields. Populate these if you have retreived a rate for the transaction, offered it to the cardholder and they have accepted that rate.
                $cardCurrency = '';        # (optional) This is the three character ISO currency code returned in the rate request.
                $cardAmount = '';        # (optional) This is the foreign currency transaction amount returned in the rate request.
                $conversionRate = '';        # (optional) This is the currency conversion rate returned in the rate request.

                # 3D Secure reference. Only include if you have verified 3D Secure throuugh the WorldNetTPS MPI and received an MPIREF back.
                $mpiref = '';            # This should be blank unless instructed otherwise by WorldNetTPS.
                $deviceId = '';            # This should be blank unless instructed otherwise by WorldNetTPS.

                $description = '';        # (optional) This can is a decription for the transaction that will be available in the merchant notification e-mail and in the SelfCare system.
                $autoReady = ($this->getPost('x_type') == 'AUTH_CAPTURE'?'Y':'N');        # (optional) Y or N. Automatically set the transaction to a status of Ready in the batch. If not present the terminal default will be used.

                $transactionType = $this->getConfigData('transaction_type');

                $ipAddress = $this->getPost('x_customer_ip');

                # Set up the authorisation object
                $this->XmlAuthRequest->initXmlAuthRequest($terminalId, $orderId, $currency, $amount, $cardNumber, $cardType);

                if ($transactionType != "") $this->XmlAuthRequest->SetTransactionType($transactionType);

                if ($cardType != "SECURECARD") $this->XmlAuthRequest->SetNonSecureCardCardInfo($cardExpiry, $cardHolderName);
                if ($cardCurrency != "" && $cardAmount != "" && $conversionRate != "") $this->XmlAuthRequest->SetForeignCurrencyInformation($cardCurrency, $cardAmount, $conversionRate);
                if ($email != "") $this->XmlAuthRequest->SetEmail($email);
                if ($mobileNumber != "") $this->XmlAuthRequest->SetMobileNumber($mobileNumber);
                if ($description != "") $this->XmlAuthRequest->SetDescription($description);

                if ($issueNo != "") $this->XmlAuthRequest->SetIssueNo($issueNo);

                if ($mpiref != "") $this->XmlAuthRequest->SetMpiRef($mpiref);
                if ($deviceId != "") $this->XmlAuthRequest->SetDeviceId($deviceId);

                if ($multicur) $this->XmlAuthRequest->SetMultiCur();
                if ($autoReady) $this->XmlAuthRequest->SetAutoReady($autoReady);
                if ($isMailOrder) $this->XmlAuthRequest->SetMotoTrans();

                if(isset($terminalFeatures['SECURITY_FRAUD']['SHOW_CVV']) && strtolower($terminalFeatures['SECURITY_FRAUD']['SHOW_CVV']) == 'true')
                    if ($cvv != "") $this->XmlAuthRequest->SetCvv($cvv);


                if(isset($terminalFeatures['SECURITY_FRAUD']['AVS']['ENABLED']) && strtolower($terminalFeatures['SECURITY_FRAUD']['AVS']['ENABLED']) == 'true') {
                    if ($address1 != "" && $address2 != "" && $postcode != "") $this->XmlAuthRequest->SetAvs($address1, $address2, $postcode);
                }


                if ($phone != "") $this->XmlAuthRequest->SetPhone($phone);
                if(isset($terminalFeatures['SECURITY_FRAUD']['ALLOW_MAX_MIND']) && strtolower($terminalFeatures['SECURITY_FRAUD']['ALLOW_MAX_MIND']) == 'true') {
                    if ($city != "") $this->XmlAuthRequest->SetCity($city);
                    if ($region != "") $this->XmlAuthRequest->SetRegion($region);
                    if ($country != "") $this->XmlAuthRequest->SetCountry($country);
                    if ($ipAddress != "") $this->XmlAuthRequest->SetIPAddress($ipAddress);
                }

                if($this->getConfigData('dynamic_descriptor'))
                    $this->XmlAuthRequest->AddCustomField(['NAME'=> 'DynamicDescriptorCF', 'value' => $this->getConfigData('dynamic_descriptor')]);

                if(isset($terminalFeatures['CUSTOM_FIELDS']['CUSTOM_FIELD']))
                    foreach ($terminalFeatures['CUSTOM_FIELDS']['CUSTOM_FIELD'] as $customField)
                        if(strtolower($customField['PAYMENT_PAGE']) == 'true' && $this->getPost('x_'.$customField['NAME'])) {
                            $this->XmlAuthRequest->AddCustomField(['NAME'=> $customField['NAME'], 'value' => $this->getPost('x_'.$customField['NAME'])]);
                        }

                if(isset($terminalFeatures['SECURITY_FRAUD']['SENTINEL_DEFEND']['ENABLED']) && isset($terminalFeatures['SECURITY_FRAUD']['SENTINEL_DEFEND']['FRAUDREVIEWSESSIONID']) && strtolower($terminalFeatures['SECURITY_FRAUD']['SENTINEL_DEFEND']['ENABLED']) == 'true')
                    $this->XmlAuthRequest->SetFraudReviewSessionId($terminalFeatures['SECURITY_FRAUD']['SENTINEL_DEFEND']['FRAUDREVIEWSESSIONID']);

                # Perform the online authorisation and read in the result
                $response = $this->XmlAuthRequest->ProcessRequestToGateway($secret, $serverUrl);

                $expectedResponseHash = hash('sha512', $terminalId .':'. $response->UniqueRef() .':'. ($multicur ? ($currency.':') : '') . $amount .':'. $response->DateTime() .':'. $response->ResponseCode() .':'. $response->ResponseText() .':'. $secret);

                $worldnettpsResponse = '';

                if ($response->IsError()) $worldnettpsResponse .= 'AN ERROR OCCURED! You transaction was not processed. Error details: ' . $response->ErrorString();
                elseif ($expectedResponseHash == $response->Hash()) {
                    switch ($response->ResponseCode()) {
                        case "A" :    # -- If using local database, update order as Authorised.
                            $worldnettpsResponse .= 'Payment Processed successfully. Thanks you for your order.';
                            $uniqueRef = $response->UniqueRef();
                            $responseText = $response->ResponseText();
                            $approvalCode = $response->ApprovalCode();
                            $avsResponse = $response->AvsResponse();
                            $cvvResponse = $response->CvvResponse();
                            break;
                        case "R" :
                        case "D" :
                        case "C" :
                        case "S" :
                        default  :    # -- If using local database, update order as declined/failed --
                            $worldnettpsResponse .= 'PAYMENT DECLINED! Please try again with another card. Bank response: ' . $response->ResponseText();
                    }
                } else {
                    $worldnettpsResponse .= 'PAYMENT FAILED: INVALID RESPONSE HASH. Please contact <a href="mailto:' . $adminEmail . '">' . $adminEmail . '</a> or call ' . $adminPhone . ' to clarify if you will get charged for this order.';
                    if ($response->UniqueRef()) $worldnettpsResponse .= 'Please quote WorldNetTPS Terminal ID: ' . $terminalId . ', and Unique Reference: ' . $response->UniqueRef() . ' when mailing or calling.';
                }

                if ($response->ResponseCode() == 'A') {
                    $transactionDetails['transaction']['transactionStatus'] = 'authorizedPendingCapture';
                    $transactionDetails['transaction']['FDSFilterAction'] = 'authAndHold';

                    $transactionDetails['transaction']['responseCode'] = '1';
                    $transactionDetails['transaction']['responseReasonCode'] = '1';

                    $transactionDetails['transaction']['AVSResponse'] = '1';
                    $transactionDetails['transaction']['cardCodeResponse'] = '1';
                    $transactionDetails['transaction']['CAVVResponse'] = '1';
                    $transactionDetails['transaction']['FDSFilters'] = '1';

                    $transactionDetails['messages']['resultCode'] = 'Ok';

                    $this->transactionService->setTransactionDetails($response->UniqueRef(), (object)$transactionDetails);

                    /* Save Secure Card */
                    $customerSession = $this->_objectManager->get('Magento\Customer\Model\Session');
                    if( (($this->getPost('x_save_securecard') && $this->getPost('x_save_securecard')=='true') || ($this->getPost('x_stored_subscription') && $this->getPost('x_stored_subscription')>0)) && !isset($secureCardMerchantRef) && $customerSession->getCustomer()->getId()) {
                        // Gateway save secure card request
                        if(!$response->MerchantRef() && !$response->CardReference()) {
                            $merchantRef = 'MREF_mage-' . md5($terminalId . $orderId . $secret . date('U'));

                            $this->XmlSecureCardRegRequest->initXmlSecureCardRegRequest($merchantRef, $terminalId, $cardNumber, $cardExpiry, $cardType, $cardHolderName, $secret);
                            $secureCardResponse = $this->XmlSecureCardRegRequest->ProcessRequestToGateway($serverUrl);
                        }

                        // DB save
                        $secureCardData = array();
                        $secureCardData['merchant_ref'] = $response->MerchantRef()?:$secureCardResponse->MerchantRef();
                        $secureCardMerchantRef = $response->MerchantRef()?:$secureCardResponse->MerchantRef();
                        $secureCardData['terminal_id'] = $terminalId;
                        $secureCardData['card_expiry'] = $cardExpiry;
                        $secureCardData['card_type'] = $cardType;
                        $secureCardData['card_holder_name'] = $cardHolderName;
                        $secureCardData['card_reference'] = $response->CardReference()?:$secureCardResponse->CardReference();
                        $secureCardData['customer_id'] = $customerSession->getCustomer()->getId();
                        $secureCardData['obfuscated_card_number'] = substr_replace($cardNumber, '******', 6, 6);
                        $secureCardData['created_at'] = date('Y-m-d G:i:s');
                        $secureCardData['update_time'] = date('Y-m-d G:i:s');

                        $rowData = $this->_objectManager->create('WorldnetTPS\SecureCard\Model\SecureCard');
                        $rowData->setData($secureCardData);
                        try {
                            $rowData->save();
                        } catch (\Exception $e) {

                        }
                    }

                    if($this->getPost('x_stored_subscription') && $this->getPost('x_stored_subscription')>0) {
                        // new customer subscription
                        $resource = $this->_objectManager->get('Magento\Framework\App\ResourceConnection');
                        $connection = $resource->getConnection();
                        $tableName = $resource->getTableName('worldnettps_subscription_records');

                        //Select Data from table
                        $sql = "Select * FROM " . $tableName . " WHERE entity_id = " . $this->getPost('x_stored_subscription');
                        $result = $connection->fetchAll($sql);

                        $storedSubscriptionMerchantRef = $result[0]['merchant_ref'];
                        $storedSubscriptionName = $result[0]['name'];
                        $storedSubscriptionDescription = $result[0]['description'];
                        $storedSubscriptionPeriodCount = $result[0]['period_count'];
                        $storedSubscriptionPeriodLength = $result[0]['period_length'];
                        $storedSubscriptionPeriodRecurringPrice = $result[0]['recurring_price'];
                        $merchantRef = substr($storedSubscriptionMerchantRef.'-'.md5($customerSession->getCustomer()->getId().$customerSession->getCustomer()->getEmail().date('U')), 0, 48);
                        $startDate = date('d-m-Y');


                        $this->XmlSubscriptionRegRequest->initXmlSubscriptionRegRequest($merchantRef, $terminalId, $storedSubscriptionMerchantRef, $secureCardMerchantRef, $startDate, $secret);
                        $subscriptionResponse = $this->XmlSubscriptionRegRequest->ProcessRequestToGateway($serverUrl);

                        if(!$subscriptionResponse->IsError()) {
                            $subscriptionCustomer = array();
                            $subscriptionCustomer['customer_id'] = $customerSession->getCustomer()->getId();
                            $subscriptionCustomer['merchant_ref'] = $merchantRef;
                            $subscriptionCustomer['terminal_id'] = $terminalId;
                            $subscriptionCustomer['stored_subscription_merchant_ref'] = $storedSubscriptionMerchantRef;
                            $subscriptionCustomer['name'] = $storedSubscriptionName;
                            $subscriptionCustomer['description'] = $storedSubscriptionDescription;
                            $subscriptionCustomer['period_count'] = $storedSubscriptionPeriodCount;
                            $subscriptionCustomer['period_length'] = $storedSubscriptionPeriodLength;
                            $subscriptionCustomer['recurring_price'] = $storedSubscriptionPeriodRecurringPrice;
                            $subscriptionCustomer['secure_card_merchant_ref'] = $secureCardMerchantRef;
                            $subscriptionCustomer['start_date'] = $startDate;
                            $subscriptionCustomer['created_at'] = date('Y-m-d G:i:s');

                            $rowData = $this->_objectManager->create('WorldnetTPS\Subscription\Model\SubscriptionCustomer');
                            $rowData->setData($subscriptionCustomer);
                            try {
                                $rowData->save();
                            } catch (\Exception $e) {

                            }
                        }
                    }


                    $this->_redirect($this->getPost('x_relay_url') . '?x_invoice_num=' . $this->getPost('x_invoice_num') . '&x_response_code=' . ($response->ResponseCode() == 'A' ? 1 : 0) . '&x_response_reason_code=' . ($response->ResponseCode() == 'A' ? 1 : 0) . '&x_trans_id=' . $response->UniqueRef() . '&x_amount=' . $amount);
                } else {
                    $this->_redirect($this->getPost('x_relay_url') . '?x_invoice_num=' . $this->getPost('x_invoice_num') . '&x_response_code=0&x_response_reason_code=0&x_trans_id=null&x_amount=' . $amount  . '&x_error_string=' . urlencode($worldnettpsResponse));
                }

            } else if (in_array($this->getPost('x_type'), ['CREDIT'])) { //refund request
                $serverUrl = $this->getServerUrl();

                $currency = $this->getPost('x_currency_code');

                $this->getTerminalSettings($currency, $terminalId, $secret, $multicur, $terminaltype);

                $uniqueRef = $this->getPost('x_trans_id');
                $orderId = $this->getPost('x_invoice_num');
                $amount = number_format($this->getPost('x_amount'), 2, '.', '');
                $datetime = $this->GetFormattedDate();
                $hash = hash('sha512', $terminalId.':'.$uniqueRef.':'.$amount.':'.$datetime.':'.$secret);
                $operator = 'Magento payment plugin';
                $reason = $this->getPost('x_comment_text');

                $this->XmlRefundRequest->initXmlRefundRequest($terminalId, $orderId, $amount, $operator, $reason);

                $this->XmlRefundRequest->SetUniqueRef($uniqueRef);
                $this->XmlRefundRequest->SetHash($hash);

                $response = $this->XmlRefundRequest->ProcessRequestToGateway($secret, $serverUrl);

                $expectedResponseHash = hash('sha512', $terminalId.':'.$response->UniqueRef().':'.$amount.':'.$response->DateTime() .':'. $response->ResponseCode() .':'. $response->ResponseText().':'.$secret);

                if ($response->IsError()) {
                    $responseBody .= 'AN ERROR OCCURED! Your transaction was not processed. Error details: ' . $response->ErrorString();
                }
                else {
                    if ($expectedResponseHash == $response->Hash()) {

                        $responseBody .= ($response->ResponseCode() == 'A' ? 1 : 0) . $this->getPost('x_delim_char');
                        $responseBody .= ($response->ResponseCode() == 'A' ? 1 : 0) . $this->getPost('x_delim_char');
                        $responseBody .= $response->ResponseText() . $this->getPost('x_delim_char');
                        $responseBody .= '' . $this->getPost('x_delim_char');
                        $responseBody .= $response->UniqueRef() . $this->getPost('x_delim_char');
                        $responseBody .= $orderId . $this->getPost('x_delim_char');
                        $responseBody .= $amount . $this->getPost('x_delim_char');
                        $responseBody .= $this->getPost('x_method') . $this->getPost('x_delim_char');
                        $responseBody .= $this->getPost('x_type') . $this->getPost('x_delim_char');
                        $responseBody .= '' . $this->getPost('x_delim_char');
                        $responseBody .= '';

                    } else {
                        $responseBody .= 'AN ERROR OCCURED! You transaction was not processed. Wrong response HASH.';
                    }
                }

            } else if (in_array($this->getPost('x_type'), ['VOID'])) { //void payment request
                $responseBody .= 3 . $this->getPost('x_delim_char');
                $responseBody .= 254 . $this->getPost('x_delim_char');
                $responseBody .= 'The transaction was not processed and it won\'t be unless you Invoice the order. The VOID action isn\'t required.'.$this->getPost('x_delim_char');
                $responseBody .= '' . $this->getPost('x_delim_char');
                $responseBody .= '' . $this->getPost('x_delim_char');
                $responseBody .= '' . $this->getPost('x_delim_char');
                $responseBody .= '' . $this->getPost('x_delim_char');
                $responseBody .= '' . $this->getPost('x_delim_char');
                $responseBody .= '' . $this->getPost('x_delim_char');
                $responseBody .= '' . $this->getPost('x_delim_char');
                $responseBody .= '' . $this->getPost('x_delim_char');
                $responseBody .= '' . $this->getPost('x_delim_char');

            } else if (in_array($this->getPost('x_type'), ['PRIOR_AUTH_CAPTURE'])) { //capture authorized request
                $serverUrl = $this->getServerUrl();

                $currency = $this->getPost('x_currency_code');

                $this->getTerminalSettings($currency, $terminalId, $secret, $multicur, $terminaltype);

                $uniqueRef = $this->getPost('x_trans_id');
                $operator = 'Magento payment plugin';
                $fromStatus = 'PENDING';
                $toStatus = 'READY';

                # Fetch terminal features
                $this->XmlTerminalFeaturesRequest->initXmlTerminalFeaturesRequest($terminalId, $secret);
                $response = $this->XmlTerminalFeaturesRequest->ProcessRequestToGateway($serverUrl);

                # Set up the authorisation object
                $this->XmlTransactionUpdateRequest->initXmlTransactionUpdateRequest($uniqueRef, $terminalId, $operator, $fromStatus, $toStatus);

                # Perform the online authorisation and read in the result
                $response = $this->XmlTransactionUpdateRequest->ProcessRequestToGateway($secret, $serverUrl);

                $expectedResponseHash = hash('sha512', $terminalId .':'. $response->ResponseCode() .':'. $response->ResponseText() .':'. $response->UniqueRef() .':'. $response->DateTime() .':'. $secret);

                if ($response->IsError()) {
                    $responseBody .= 'AN ERROR OCCURED! Your transaction was not processed. Error details: ' . $response->ErrorString();
                } else {
                    if ($expectedResponseHash == $response->Hash()) {

                        $responseBody .= ($response->ResponseCode() == 'A' ? 1 : 0) . $this->getPost('x_delim_char');
                        $responseBody .= ($response->ResponseCode() == 'A' ? 1 : 0) . $this->getPost('x_delim_char');
                        $responseBody .= $response->ResponseText() . $this->getPost('x_delim_char');
                        $responseBody .= '' . $this->getPost('x_delim_char');
                        $responseBody .= $response->UniqueRef() . $this->getPost('x_delim_char');
                        $responseBody .= $this->getPost('x_invoice_num') . $this->getPost('x_delim_char');
                        $responseBody .= number_format($this->getPost('x_amount'), 2, '.', '') . $this->getPost('x_delim_char');
                        $responseBody .= $this->getPost('x_method') . $this->getPost('x_delim_char');
                        $responseBody .= $this->getPost('x_type') . $this->getPost('x_delim_char');
                        $responseBody .= '' . $this->getPost('x_delim_char');
                        $responseBody .= '';

                    } else {
                        $responseBody .= 'AN ERROR OCCURED! You transaction was not processed. Wrong response HASH.';
                    }
                }
            }

            return $this->getResponse()->setBody($responseBody);
        }


    }
}

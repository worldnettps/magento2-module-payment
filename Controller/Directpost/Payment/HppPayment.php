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
use WorldnetTPS\Payment\Model\Api\XmlTerminalFeaturesRequest;
use WorldnetTPS\Payment\Model\TransactionService;
use Magento\Framework\Simplexml\Element;

/**
 * Class Place
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class HppPayment extends Payment
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
     * @var \WorldnetTPS\Payment\Model\Api\XmlTerminalFeaturesRequest
     */
    protected $XmlTerminalFeaturesRequest;

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

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;


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
        XmlTerminalFeaturesRequest $XmlTerminalFeaturesRequest,
        \Magento\Store\Model\StoreManagerInterface $manStore,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        TransactionService $transactionService
    ) {
        $this->eventManager = $context->getEventManager();
        $this->cartManagement = $cartManagement;
        $this->onepageCheckout = $onepageCheckout;
        $this->jsonHelper = $jsonHelper;
        $this->XmlAuthRequest = $XmlAuthRequest;
        $this->XmlTerminalFeaturesRequest = $XmlTerminalFeaturesRequest;
        $this->manStore = $manStore;
        $this->_scopeConfig = $scopeConfig;
        $this->transactionService = $transactionService;
        $this->orderFactory = $orderFactory;
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


    /**
     * Send request to payment gateway
     *
     * @return string
     */
    public function execute()
    {
        $hash = $this->getRequest()->getParam('hash');
        $orderId = $this->getRequest()->getParam('ORDERID');
        $currency = $this->getRequest()->getParam('CURRENCY');
        $amount = $this->getRequest()->getParam('AMOUNT');
        $datetime = $this->getRequest()->getParam('DATETIME');
        $receiptpageurl = $this->getRequest()->getParam('RECEIPTPAGEURL');
        $x_save_securecard = $this->getRequest()->getParam('x_save_securecard');
        $x_stored_subscription = $this->getRequest()->getParam('x_stored_subscription');

        if($hash == hash('sha512', $orderId .':'. $currency .':'. $amount .':'. $datetime .':'. $receiptpageurl )) {
            $order = $this->orderFactory->create()->loadByIncrementId($orderId);

            if($currency == $order->getBaseCurrencyCode() && $amount == $order->getTotalDue()) {
                $billing = $order->getBillingAddress();

                if ($this->getConfigData('mode') == 'LIVE')
                    $serverUrl = $this->getConfigData('gatewayUrl');
                else
                    $serverUrl = $this->getConfigData('testGatewayUrl');

                if ($this->getConfigData('mode') == 'LIVE')
                    $xmlServerUrl = $this->getConfigData('gatewayUrlXml');
                else
                    $xmlServerUrl = $this->getConfigData('testGatewayUrlXml');

                if($currency == $this->getField('currencytwo') && $this->getField('terminalidtwo') && $this->getField('sharedsecrettwo')) {
                    $terminalId = $this->getField('sharedsecrettwo');        # This is the Terminal ID assigned to the site by WorldNetTPS.
                    $secret = $this->getField('sharedsecrettwo');            # This shared secret is used when generating the hash validation strings.
                    $multicur = $this->getField('multicurrencytwo');
                } else
                if($currency == $this->getField('currencythree') && $this->getField('terminalidthree') && $this->getField('sharedsecretthree')) {
                    $terminalId = $this->getField('terminalidthree');        # This is the Terminal ID assigned to the site by WorldNetTPS.
                    $secret = $this->getField('sharedsecretthree');            # This shared secret is used when generating the hash validation strings.
                    $multicur = $this->getField('multicurrencythree');
                } else {
                    $currency = $this->getField('currency');		# This is the 3 digit ISO currency code for the above Terminal ID.
                    $terminalId = $this->getField('terminalid');        # This is the Terminal ID assigned to the site by WorldNetTPS.
                    $secret = $this->getField('sharedsecret');            # This shared secret is used when generating the hash validation strings.
                    $multicur = $this->getField('multicurrency');
                }

                # Fetch terminal features
                $this->XmlTerminalFeaturesRequest->initXmlTerminalFeaturesRequest($terminalId, $secret);
                $response = $this->XmlTerminalFeaturesRequest->ProcessRequestToGateway($xmlServerUrl);
                $terminalFeatures = $response->getSettings();

                //check Payment Action
                $autoReady = '';
                if($this->getConfigData('payment_action') == 'authorize')
                    $autoReady = '&AUTOREADY=N';

                //Dynamic Descriptor value
                $dynamicDescriptor = '';
                if($this->getConfigData('dynamic_descriptor'))
                    $dynamicDescriptor = '&DynamicDescriptorCF=' . urlencode($this->getConfigData('dynamic_descriptor'));

                $transactionType = $this->getConfigData('transaction_type');

                $serverUrl.='?TERMINALID='.$terminalId.'&ORDERID='.$orderId.'&CURRENCY='.$currency.'&AMOUNT='.number_format($amount, 2, '.', '').'&DATETIME='.$datetime.'&RECEIPTPAGEURL='.urlencode($receiptpageurl).'&HASH='.hash('sha512', $terminalId.':'.$orderId.':'.($multicur?($currency.':'):'').number_format($amount, 2, '.', '').':'.$datetime.':'.$receiptpageurl.':'.$secret).'&TRANSACTIONTYPE='.$transactionType.'&x_stored_subscription='.$x_stored_subscription.'&x_save_securecard='.$x_save_securecard.$autoReady.$dynamicDescriptor;

                if(isset($terminalFeatures['SECURITY_FRAUD']['AVS']['ENABLED']) && strtolower($terminalFeatures['SECURITY_FRAUD']['AVS']['ENABLED']) == 'true')
                    $serverUrl.='&ADDRESS1='. urlencode($billing->getStreetLine(1)) .'&ADDRESS2='. urlencode($billing->getStreetLine(2)) .'&POSTCODE='. urlencode($billing->getPostcode());

                if(isset($terminalFeatures['SECURITY_FRAUD']['ALLOW_MAX_MIND']) && strtolower($terminalFeatures['SECURITY_FRAUD']['ALLOW_MAX_MIND']) == 'true')
                    $serverUrl.='&CITY='. urlencode($billing->getCity()).'&REGION='. urlencode($billing->getRegion()) .'&COUNTRY='. $billing->getCountryId();

                //Stored Subscription / Secure Card
                if($x_save_securecard == 'true' || $x_stored_subscription > 0) {
                    $merchantRef = 'MREF_mage-'.md5($terminalId.$orderId.$secret.date('U'));
                    $serverUrl.='&SECURECARDMERCHANTREF='.$merchantRef;
                }

                $this->_redirect($serverUrl);
            }
        }
    }
}

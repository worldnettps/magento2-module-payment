<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace WorldnetTPS\Payment\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\HTTP\ZendClientFactory;
use Magento\Framework\Simplexml\Element;
use Magento\Framework\Xml\Security;
use WorldnetTPS\Payment\Model\WorldnetTPS;
use Magento\Payment\Model\Method\Logger;

/**
 * Class TransactionService
 * @package WorldnetTPS\Payment\Model
 */
class TransactionService
{
    /**
     * Transaction Details gateway url
     */
    const CGI_URL_TD = '';

    const PAYMENT_UPDATE_STATUS_CODE_SUCCESS = 'Ok';

    const CONNECTION_TIMEOUT = 120000;

    /**
     * Stored information about transaction
     *
     * @var array
     */
    protected $transactionDetails = [];

    /**
     * @var \Magento\Framework\Xml\Security
     */
    protected $xmlSecurityHelper;

    /**
     * @var \Magento\Payment\Model\Method\Logger
     */
    protected $logger;

    /**
     * @var \Magento\Framework\HTTP\ZendClientFactory
     */
    protected $httpClientFactory;

    /**
     * Fields that should be replaced in debug with '***'
     *
     * @var array
     */
    protected $debugReplacePrivateDataKeys = ['merchantAuthentication', 'x_login'];

    /**
     * @param Security $xmlSecurityHelper
     * @param Logger $logger
     * @param ZendClientFactory $httpClientFactory
     */
    public function __construct(
        Security $xmlSecurityHelper,
        Logger $logger,
        ZendClientFactory $httpClientFactory
    ) {
        $this->xmlSecurityHelper = $xmlSecurityHelper;
        $this->logger = $logger;
        $this->httpClientFactory = $httpClientFactory;
    }

    /**
     * Get transaction information
     * @param \WorldnetTPS\Payment\Model\WorldnetTPS $context
     * @param string $transactionId
     * @return \Magento\Framework\Simplexml\Element
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getTransactionDetails(WorldnetTPS $context, $transactionId)
    {
        return isset($this->transactionDetails[$transactionId])
            ? $this->transactionDetails[$transactionId]
            : $this->loadTransactionDetails($context, $transactionId);
    }

    /**
     * Set transaction information
     * @param string $transactionId
     * @param \Magento\Framework\Simplexml\Element $details
     */
    public function setTransactionDetails($transactionId, $details)
    {
        $this->transactionDetails[$transactionId] = $details;
    }

    /**
     * Load transaction details
     *
     * @param \WorldnetTPS\Payment\Model\WorldnetTPS $context
     * @param string $transactionId
     * @return object
     */
    protected function loadTransactionDetails(WorldnetTPS $context, $transactionId)
    {
        $transactionDetails['transaction']['transactionStatus'] = 'settledSuccessfully';
        $transactionDetails['transaction']['FDSFilterAction'] = 'authAndHold';

        $transactionDetails['transaction']['responseCode'] = '1';
        $transactionDetails['transaction']['responseReasonCode'] = '1';

        $transactionDetails['transaction']['AVSResponse'] = '1';
        $transactionDetails['transaction']['cardCodeResponse'] = '1';
        $transactionDetails['transaction']['CAVVResponse'] = '1';
        $transactionDetails['transaction']['FDSFilters'] = '1';

        $transactionDetails['messages']['resultCode'] = 'Ok';
        return (object) $transactionDetails;
    }


}

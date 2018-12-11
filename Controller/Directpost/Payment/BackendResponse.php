<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace WorldnetTPS\Payment\Controller\Directpost\Payment;

use WorldnetTPS\Payment\Helper\DataFactory;
use WorldnetTPS\Payment\Model\Directpost;
use WorldnetTPS\Payment\Model\DirectpostFactory;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Psr\Log\LoggerInterface;

class BackendResponse extends \WorldnetTPS\Payment\Controller\Directpost\Payment
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var DirectpostFactory
     */
    private $directpostFactory;

    /**
     * BackendResponse constructor.
     *
     * @param Context $context
     * @param Registry $coreRegistry
     * @param DataFactory $dataFactory
     * @param DirectpostFactory $directpostFactory
     * @param LoggerInterface|null $logger
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        DataFactory $dataFactory,
        DirectpostFactory $directpostFactory = null,
        LoggerInterface $logger = null
    ) {
        parent::__construct($context, $coreRegistry, $dataFactory);
        $this->directpostFactory = $directpostFactory ?: $this->_objectManager->create(DirectpostFactory::class);
        $this->logger = $logger ?: $this->_objectManager->get(LoggerInterface::class);
    }

    /**
     * Response action.
     * Action for WorldnetTPS TPS SIM Relay Request.
     *
     * @return void
     */
    public function execute()
    {
        $data = $this->getRequest()->getParams();
        /** @var Directpost $paymentMethod */
        $paymentMethod = $this->directpostFactory->create();
        if (!empty($data['store_id'])) {
            $paymentMethod->setStore($data['store_id']);
        }
        $paymentMethod->setResponseData($data);

        $this->_responseAction('adminhtml');
        $this->resultFactory->create(ResultFactory::TYPE_PAGE);
    }
}

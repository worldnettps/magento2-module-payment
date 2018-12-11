<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace WorldnetTPS\Payment\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;

class AddFieldsToResponseObserver implements ObserverInterface
{
    /**
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var \WorldnetTPS\Payment\Model\Directpost
     */
    protected $payment;

    /**
     * @var \WorldnetTPS\Payment\Model\Directpost\Session
     */
    protected $session;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \WorldnetTPS\Payment\Model\Directpost $payment
     * @param \WorldnetTPS\Payment\Model\Directpost\Session $session
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Framework\Registry $coreRegistry,
        \WorldnetTPS\Payment\Model\Directpost $payment,
        \WorldnetTPS\Payment\Model\Directpost\Session $session,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->coreRegistry = $coreRegistry;
        $this->payment = $payment;
        $this->session = $session;
        $this->storeManager = $storeManager;
    }

    /**
     * Save order into registry to use it in the overloaded controller.
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /* @var $order Order */
        $order = $this->coreRegistry->registry('worldnettps_directpost_order');

        if (!$order || !$order->getId()) {
            return $this;
        }

        $payment = $order->getPayment();

        if (!$payment || $payment->getMethod() != $this->payment->getCode()) {
            return $this;
        }

        $result = $observer->getData('result')->getData();

        if (!empty($result['error'])) {
            return $this;
        }

        // if success, then set order to session and add new fields
        $this->session->addCheckoutOrderIncrementId($order->getIncrementId());
        $this->session->setLastOrderIncrementId($order->getIncrementId());

        $requestToWorldnetTPS = $payment->getMethodInstance()
            ->generateRequestFromOrder($order);
        $requestToWorldnetTPS->setControllerActionName(
            $observer->getData('action')
                ->getRequest()
                ->getControllerName()
        );
        $requestToWorldnetTPS->setIsSecure(
            (string)$this->storeManager->getStore()
                ->isCurrentlySecure()
        );

        $result[$this->payment->getCode()] = ['fields' => $requestToWorldnetTPS->getData()];

        $observer->getData('result')->setData($result);

        return $this;
    }
}

<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace WorldnetTPS\Payment\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;

class UpdateAllEditIncrementsObserver implements ObserverInterface
{
    /**
     *
     * @var \WorldnetTPS\Payment\Helper\Data
     */
    protected $worldnettpsData;

    /**
     * @param \WorldnetTPS\Payment\Helper\Data $worldnettpsData
     */
    public function __construct(
        \WorldnetTPS\Payment\Helper\Data $worldnettpsData
    ) {
        $this->worldnettpsData = $worldnettpsData;
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
        $order = $observer->getEvent()->getData('order');
        $this->worldnettpsData->updateOrderEditIncrements($order);

        return $this;
    }
}

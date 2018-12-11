<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace WorldnetTPS\Payment\Controller\Adminhtml\WorldnetTPS\Directpost\Payment;

class ReturnQuote extends \Magento\Sales\Controller\Adminhtml\Order\Create
{
    /**
     * Return quote
     *
     * @return void
     */
    protected function _returnQuote()
    {
        $directpostSession = $this->_objectManager->get('WorldnetTPS\Payment\Model\Directpost\Session');
        $incrementId = $directpostSession->getLastOrderIncrementId();
        if ($incrementId && $directpostSession->isCheckoutOrderIncrementIdExist($incrementId)) {
            /* @var $order \Magento\Sales\Model\Order */
            $order = $this->_objectManager->create('Magento\Sales\Model\Order')->loadByIncrementId($incrementId);
            if ($order->getId()) {
                $directpostSession->removeCheckoutOrderIncrementId($order->getIncrementId());
            }
        }
    }

    /**
     * Return order quote by ajax
     *
     * @return void
     */
    public function execute()
    {
        $this->_returnQuote();
        $this->getResponse()->representJson(
            $this->_objectManager->get('Magento\Framework\Json\Helper\Data')->jsonEncode(['success' => 1])
        );
    }
}

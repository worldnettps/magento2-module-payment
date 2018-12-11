<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace WorldnetTPS\Payment\Controller\Adminhtml\WorldnetTPS\Directpost\Payment;

use Magento\Framework\Escaper;
use Magento\Catalog\Helper\Product;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\Model\View\Result\ForwardFactory;
use WorldnetTPS\Payment\Helper\Backend\Data as DataHelper;

/**
 * Class Place
 */
class Place extends \Magento\Sales\Controller\Adminhtml\Order\Create
{
    /**
     * @var DataHelper
     */
    protected $helper;

    /**
     * Constructor
     *
     * @param Context $context
     * @param Product $productHelper
     * @param Escaper $escaper
     * @param PageFactory $resultPageFactory
     * @param ForwardFactory $resultForwardFactory
     * @param DataHelper $helper
     */
    public function __construct(
        Context $context,
        Product $productHelper,
        Escaper $escaper,
        PageFactory $resultPageFactory,
        ForwardFactory $resultForwardFactory,
        DataHelper $helper
    ) {
        $this->helper = $helper;
        parent::__construct($context, $productHelper, $escaper, $resultPageFactory, $resultForwardFactory);
    }

    /**
     * Send request to WorldNetTPS
     *
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function execute()
    {
        $paymentParam = $this->getRequest()->getParam('payment');
        $controller = $this->getRequest()->getParam('controller');
        $this->getRequest()->setPostValue('collect_shipping_rates', 1);
        $this->_processActionData('save');

        //get confirmation by email flag
        $orderData = $this->getRequest()->getPost('order');
        $sendConfirmationFlag = 0;
        if ($orderData) {
            $sendConfirmationFlag = !empty($orderData['send_confirmation']) ? 1 : 0;
        } else {
            $orderData = [];
        }

        if (isset($paymentParam['method'])) {
            $result = [];
            //create order partially
            $this->_getOrderCreateModel()->setPaymentData($paymentParam);
            $this->_getOrderCreateModel()->getQuote()->getPayment()->addData($paymentParam);

            $orderData['send_confirmation'] = 0;
            $this->getRequest()->setPostValue('order', $orderData);

            try {
                //do not cancel old order.
                $oldOrder = $this->_getOrderCreateModel()->getSession()->getOrder();
                $oldOrder->setActionFlag(\Magento\Sales\Model\Order::ACTION_FLAG_CANCEL, false);

                $order = $this->_getOrderCreateModel()->setIsValidate(
                    true
                )->importPostData(
                    $this->getRequest()->getPost('order')
                )->createOrder();

                $payment = $order->getPayment();
                if ($payment && $payment->getMethod() == $this->_objectManager->create(
                    'WorldnetTPS\Payment\Model\Directpost'
                )->getCode()
                ) {
                    //return json with data.
                    $session = $this->_objectManager->get('WorldnetTPS\Payment\Model\Directpost\Session');
                    $session->addCheckoutOrderIncrementId($order->getIncrementId());
                    $session->setLastOrderIncrementId($order->getIncrementId());

                    /** @var \WorldnetTPS\Payment\Model\Directpost $method */
                    $method = $payment->getMethodInstance();
                    $method->setDataHelper($this->helper);
                    $requestToWorldnetTPS = $method->generateRequestFromOrder($order);
                    $requestToWorldnetTPS->setControllerActionName($controller);
                    $requestToWorldnetTPS->setOrderSendConfirmation($sendConfirmationFlag);
                    $requestToWorldnetTPS->setStoreId($this->_getOrderCreateModel()->getQuote()->getStoreId());

                    $adminUrl = $this->_objectManager->get('Magento\Backend\Model\UrlInterface');
                    if ($adminUrl->useSecretKey()) {
                        $requestToWorldnetTPS->setKey(
                            $adminUrl->getSecretKey('adminhtml', 'worldnettps_directpost_payment', 'redirect')
                        );
                    }
                    $result['directpost'] = ['fields' => $requestToWorldnetTPS->getData()];
                }

                $result['success'] = 1;
                $isError = false;
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $message = $e->getMessage();
                if (!empty($message)) {
                    $this->messageManager->addErrorMessage($message);
                }
                $isError = true;
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Order saving error: %1', $e->getMessage()));
                $isError = true;
            }

            if ($isError) {
                $result['success'] = 0;
                $result['error'] = 1;
                $result['redirect'] = $this->_objectManager->get(
                    'Magento\Backend\Model\UrlInterface'
                )->getUrl(
                    'sales/order_create/'
                );
            }

            $this->getResponse()->representJson(
                $this->_objectManager->get('Magento\Framework\Json\Helper\Data')->jsonEncode($result)
            );
        } else {
            $result = ['error_messages' => __('Please choose a payment method.')];
            $this->getResponse()->representJson(
                $this->_objectManager->get('Magento\Framework\Json\Helper\Data')->jsonEncode($result)
            );
        }
    }
}

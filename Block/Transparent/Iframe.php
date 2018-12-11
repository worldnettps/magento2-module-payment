<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace WorldnetTPS\Payment\Block\Transparent;

use Magento\Payment\Block\Transparent\Iframe as TransparentIframe;

/**
 * Class Iframe
 */
class Iframe extends TransparentIframe
{
    /**
     * @var \WorldnetTPS\Payment\Helper\DataFactory
     */
    protected $dataFactory;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $messageManager;

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \WorldnetTPS\Payment\Helper\DataFactory $dataFactory
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \WorldnetTPS\Payment\Helper\DataFactory $dataFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        array $data = []
    ) {
        $this->dataFactory = $dataFactory;
        $this->messageManager = $messageManager;
        parent::__construct($context, $registry, $data);
    }

    /**
     * Get helper data
     *
     * @param string $area
     * @return \WorldnetTPS\Payment\Helper\Backend\Data|\WorldnetTPS\Payment\Helper\Data
     */
    public function getHelper($area)
    {
        return $this->dataFactory->create($area);
    }

    /**
     * {inheritdoc}
     */
    protected function _beforeToHtml()
    {
        $this->addSuccessMessage();
        return parent::_beforeToHtml();
    }

    /**
     * Add success message
     *
     * @return void
     */
    private function addSuccessMessage()
    {
        $params = $this->getParams();
        if (isset($params['redirect_parent'])) {
            $this->messageManager->addSuccess(__('You created the order.'));
        }
    }
}

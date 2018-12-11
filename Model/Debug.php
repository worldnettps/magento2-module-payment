<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace WorldnetTPS\Payment\Model;

/**
 * @method \WorldnetTPS\Payment\Model\ResourceModel\Debug _getResource()
 * @method \WorldnetTPS\Payment\Model\ResourceModel\Debug getResource()
 * @method string getRequestBody()
 * @method \WorldnetTPS\Payment\Model\Debug setRequestBody(string $value)
 * @method string getResponseBody()
 * @method \WorldnetTPS\Payment\Model\Debug setResponseBody(string $value)
 * @method string getRequestSerialized()
 * @method \WorldnetTPS\Payment\Model\Debug setRequestSerialized(string $value)
 * @method string getResultSerialized()
 * @method \WorldnetTPS\Payment\Model\Debug setResultSerialized(string $value)
 * @method string getRequestDump()
 * @method \WorldnetTPS\Payment\Model\Debug setRequestDump(string $value)
 * @method string getResultDump()
 * @method \WorldnetTPS\Payment\Model\Debug setResultDump(string $value)
 */
class Debug extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('WorldnetTPS\Payment\Model\ResourceModel\Debug');
    }
}

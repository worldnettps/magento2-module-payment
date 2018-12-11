<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace WorldnetTPS\Payment\Model\ResourceModel\Debug;

/**
 * Resource WorldnetTPS TPS debug collection model
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            'WorldnetTPS\Payment\Model\Debug',
            'WorldnetTPS\Payment\Model\ResourceModel\Debug'
        );
    }
}

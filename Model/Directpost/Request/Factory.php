<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace WorldnetTPS\Payment\Model\Directpost\Request;

use WorldnetTPS\Payment\Model\Request\Factory as WorldnetTPSRequestFactory;

/**
 * Factory class for @see \WorldnetTPS\Payment\Model\Directpost\Request
 */
class Factory extends WorldnetTPSRequestFactory
{
    /**
     * Factory constructor
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param string $instanceName
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        $instanceName = 'WorldnetTPS\Payment\Model\Directpost\Request'
    ) {
        parent::__construct($objectManager, $instanceName);
    }
}

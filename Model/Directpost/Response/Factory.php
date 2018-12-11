<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace WorldnetTPS\Payment\Model\Directpost\Response;

use WorldnetTPS\Payment\Model\Response\Factory as WorldnetTPSResponseFactory;

/**
 * Factory class for @see \WorldnetTPS\Payment\Model\Directpost\Response
 */
class Factory extends WorldnetTPSResponseFactory
{
    /**
     * Factory constructor
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param string $instanceName
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        $instanceName = 'WorldnetTPS\Payment\Model\Directpost\Response'
    ) {
        parent::__construct($objectManager, $instanceName);
    }
}

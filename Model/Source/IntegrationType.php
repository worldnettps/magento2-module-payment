<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace WorldnetTPS\Payment\Model\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 *
 * Gateway Payment Action Dropdown source
 */
class IntegrationType implements ArrayInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => \WorldnetTPS\Payment\Model\WorldnetTPS::INTEGRATION_XML,
                'label' => __('XML')
            ],
            [
                'value' => \WorldnetTPS\Payment\Model\WorldnetTPS::INTEGRATION_HPP,
                'label' => __('HPP')
            ]
        ];
    }
}

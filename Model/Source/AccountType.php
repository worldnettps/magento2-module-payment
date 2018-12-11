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
class AccountType implements ArrayInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => \WorldnetTPS\Payment\Model\WorldnetTPS::MODE_TEST,
                'label' => __('Test')
            ],
            [
                'value' => \WorldnetTPS\Payment\Model\WorldnetTPS::MODE_LIVE,
                'label' => __('Live')
            ]
        ];
    }
}

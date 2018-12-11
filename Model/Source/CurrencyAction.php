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
class CurrencyAction implements ArrayInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => \WorldnetTPS\Payment\Model\WorldnetTPS::CURRENCY_EUR,
                'label' => __('Euro')
            ],
            [
                'value' => \WorldnetTPS\Payment\Model\WorldnetTPS::CURRENCY_GBP,
                'label' => __('Sterling')
            ],
            [
                'value' => \WorldnetTPS\Payment\Model\WorldnetTPS::CURRENCY_USD,
                'label' => __('US Dollar')
            ],
            [
                'value' => \WorldnetTPS\Payment\Model\WorldnetTPS::CURRENCY_CAD,
                'label' => __('Canadian Dollar')
            ],
            [
                'value' => \WorldnetTPS\Payment\Model\WorldnetTPS::CURRENCY_AUD,
                'label' => __('Australian Dollar')
            ],
            [
                'value' => \WorldnetTPS\Payment\Model\WorldnetTPS::CURRENCY_DKK,
                'label' => __('Danish Krone')
            ],
            [
                'value' => \WorldnetTPS\Payment\Model\WorldnetTPS::CURRENCY_SEK,
                'label' => __('Swedish Krona')
            ],
            [
                'value' => \WorldnetTPS\Payment\Model\WorldnetTPS::CURRENCY_NOK,
                'label' => __('Norwegian Krone')
            ]
        ];
    }
}

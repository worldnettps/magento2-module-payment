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
class CheckoutCurrency implements ArrayInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => \WorldnetTPS\Payment\Model\WorldnetTPS::CHECKOUTCUR_STORE,
                'label' => __('Store base currency')
            ],
            [
                'value' => \WorldnetTPS\Payment\Model\WorldnetTPS::CHECKOUTCUR_DISPLAY,
                'label' => __('Cart Display currency')
            ]
        ];
    }
}

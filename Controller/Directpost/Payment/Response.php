<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace WorldnetTPS\Payment\Controller\Directpost\Payment;

class Response extends \WorldnetTPS\Payment\Controller\Directpost\Payment
{
    /**
     * Response action.
     * Action for WorldnetTPS TPS SIM Relay Request.
     *
     * @return void
     */
    public function execute()
    {
        $this->_responseAction('frontend');
    }
}

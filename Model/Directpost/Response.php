<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace WorldnetTPS\Payment\Model\Directpost;

use WorldnetTPS\Payment\Model\Response as WorldnetTPSResponse;
use Magento\Framework\Encryption\Helper\Security;

/**
 * WorldnetTPS TPS response model for DirectPost model
 */
class Response extends WorldnetTPSResponse
{
    /**
     * Return if this is approved response from WorldnetTPS TPS auth request.
     *
     * @return bool
     */
    public function isApproved()
    {
        return $this->getXResponseCode() == \WorldnetTPS\Payment\Model\Directpost::RESPONSE_CODE_APPROVED;
    }
}

<?php

declare(strict_types=1);

namespace VPNDetector;

use VPNDetector\Exception\IPAddressResolvingException;

interface VPNDetector
{
    /**
     * @throws IPAddressResolvingException
     */
    public function isBehindVpn(): bool;
}

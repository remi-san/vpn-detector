<?php

declare(strict_types=1);

namespace VPNDetector;

use VPNDetector\Exception\IPAddressResolvingException;

interface IPAddressResolver
{
    /**
     * @throws IPAddressResolvingException
     */
    public function resolveIpAddress(): IPAddress;
}

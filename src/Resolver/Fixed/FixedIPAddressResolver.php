<?php

declare(strict_types=1);

namespace VPNDetector\Resolver\Fixed;

use VPNDetector\IPAddress;
use VPNDetector\IPAddressResolver;

final readonly class FixedIPAddressResolver implements IPAddressResolver
{
    public function __construct(private ?IPAddress $ip = null)
    {
    }

    public function resolveIpAddress(): IPAddress
    {
        if ($this->ip === null) {
            throw FixedIPAddressResolvingException::create();
        }

        return $this->ip;
    }
}

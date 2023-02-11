<?php

declare(strict_types=1);

namespace VPNDetector\Builder\IPAddressResolver;

use VPNDetector\Builder\IPAddressResolverBuilder;
use VPNDetector\IPAddress;
use VPNDetector\IPAddressResolver;
use VPNDetector\Resolver\Fixed\FixedIPAddressResolver;

final class FixedIPAddressResolverBuilder implements IPAddressResolverBuilder
{
    use OptionsTrait;

    public function build(): IPAddressResolver
    {
        $ip = $this->options[IPAddressResolver::IP_PARAM] ?? null;

        return new FixedIPAddressResolver(
            $ip === null ? null : IPAddress::create($ip)
        );
    }
}

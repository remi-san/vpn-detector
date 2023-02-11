<?php

declare(strict_types=1);

namespace VPNDetector\Builder\IPAddressResolver;

use VPNDetector\Builder\IPAddressResolverBuilder;
use VPNDetector\IPAddressResolver;
use VPNDetector\Resolver\IPProvider\Ipify\IpifyIPAddressResolver;

final class IpifyIPAddressResolverBuilder implements IPAddressResolverBuilder
{
    use HttpClientTrait;

    /**
     * @param array<string, ?string> $options
     */
    public function withOptions(array $options): self
    {
        return $this;
    }

    public function build(): IPAddressResolver
    {
        return IpifyIPAddressResolver::build(
            $this->httpClient
        );
    }
}

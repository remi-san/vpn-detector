<?php

declare(strict_types=1);

namespace VPNDetector\Builder\IPAddressResolver;

use VPNDetector\Builder\IPAddressResolverBuilder;
use VPNDetector\IPAddressResolver;
use VPNDetector\Resolver\IPProvider\Livebox\LiveboxIPAddressResolver;

final class LiveboxIPAddressResolverBuilder implements IPAddressResolverBuilder
{
    use HttpClientTrait;
    use OptionsTrait;

    public function build(): IPAddressResolver
    {
        return LiveboxIPAddressResolver::build(
            $this->httpClient,
            $this->options
        );
    }
}

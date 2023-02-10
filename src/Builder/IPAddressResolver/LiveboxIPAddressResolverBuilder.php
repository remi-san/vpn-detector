<?php

declare(strict_types=1);

namespace VPNDetector\Builder\IPAddressResolver;

use VPNDetector\Builder\IPAddressResolverBuilder;
use VPNDetector\IPAddressResolver;
use VPNDetector\Resolver\IPProvider\Livebox\LiveboxIPAddressResolver;

final class LiveboxIPAddressResolverBuilder implements IPAddressResolverBuilder
{
    use HttpClientTrait;

    /** @var array<string, ?string> */
    private array $options = [];

    /**
     * @param array<string, ?string> $options
     */
    public function withOptions(array $options): self
    {
        $this->options = [...$this->options, ...$options];

        return $this;
    }

    public function build(): IPAddressResolver
    {
        return LiveboxIPAddressResolver::build(
            $this->httpClient,
            $this->options
        );
    }
}

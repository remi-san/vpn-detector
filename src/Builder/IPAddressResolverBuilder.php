<?php

declare(strict_types=1);

namespace VPNDetector\Builder;

use VPNDetector\IPAddressResolver;

interface IPAddressResolverBuilder
{
    /**
     * @param array<string, ?string> $options
     */
    public function withOptions(array $options): self;

    public function build(): IPAddressResolver;
}

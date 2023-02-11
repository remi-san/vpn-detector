<?php

declare(strict_types=1);

namespace VPNDetector\Builder;

interface IPAddressResolverFactory
{
    public function build(string $resolver): IPAddressResolverBuilder;
}

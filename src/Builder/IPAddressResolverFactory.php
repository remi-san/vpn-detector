<?php

declare(strict_types=1);

namespace VPNDetector\Builder;

use Symfony\Contracts\HttpClient\HttpClientInterface;

interface IPAddressResolverFactory
{
    /**
     * @param callable(HttpClientInterface): IPAddressResolverBuilder $builder
     * @param array<string, ?string>                                  $defaultOptions
     */
    public function addResolverBuilder(string $resolverName, callable $builder, array $defaultOptions = []): self;

    public function setDefaultResolver(string $resolverName): self;

    public function getDefaultResolver(): string;

    public function getResolverBuilderFor(string $resolver): IPAddressResolverBuilder;
}

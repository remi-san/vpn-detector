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

    public function setDefaultLocalResolver(string $resolverName): self;

    public function setDefaultRemoteResolver(string $resolverName): self;

    public function getDefaultLocalResolver(): string;

    public function getDefaultRemoteResolver(): string;

    public function getResolverBuilderFor(string $resolver): IPAddressResolverBuilder;
}

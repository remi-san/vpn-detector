<?php

declare(strict_types=1);

namespace VPNDetector\Builder;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use VPNDetector\Builder\IPAddressResolver\FixedIPAddressResolverBuilder;
use VPNDetector\Builder\IPAddressResolver\HttpClientTrait;
use VPNDetector\Builder\IPAddressResolver\IPAddressResolvers;
use VPNDetector\Builder\IPAddressResolver\IpifyIPAddressResolverBuilder;
use VPNDetector\Builder\IPAddressResolver\LiveboxIPAddressResolverBuilder;

final class SimpleIPAddressResolverFactory implements IPAddressResolverFactory
{
    use HttpClientTrait {
        HttpClientTrait::__construct as private __httpClientTraitConstruct;
    }

    /**
     * @var array<string, callable(HttpClientInterface): IPAddressResolverBuilder>
     */
    private array $resolverBuilders;

    private string $defaultLocalResolver  = IPAddressResolvers::FIXED;
    private string $defaultRemoteResolver = IPAddressResolvers::IPIFY;

    /**
     * @param array<string, array<string, ?string>> $defaultOptions
     */
    private function __construct(
        HttpClientInterface $httpClient,
        private array $defaultOptions = []
    ) {
        $this->__httpClientTraitConstruct($httpClient);

        $this->resolverBuilders = [
            IPAddressResolvers::FIXED   => fn (HttpClientInterface $httpClient): IPAddressResolverBuilder => new FixedIPAddressResolverBuilder(),
            IPAddressResolvers::IPIFY   => fn (HttpClientInterface $httpClient): IPAddressResolverBuilder => new IpifyIPAddressResolverBuilder($httpClient),
            IPAddressResolvers::LIVEBOX => fn (HttpClientInterface $httpClient): IPAddressResolverBuilder => new LiveboxIPAddressResolverBuilder($httpClient),
        ];
    }

    /**
     * @param array<string, array<string, ?string>> $defaultOptions
     */
    public static function create(HttpClientInterface $httpClient = null, array $defaultOptions = []): self
    {
        $factory                 = new self($httpClient ?? HttpClient::create());
        $factory->defaultOptions = $defaultOptions;

        return $factory;
    }

    /**
     * @param callable(HttpClientInterface): IPAddressResolverBuilder $builder
     * @param array<string, ?string>                                  $defaultOptions
     */
    public function addResolverBuilder(string $resolverName, callable $builder, array $defaultOptions = []): self
    {
        $this->resolverBuilders[$resolverName] = $builder;
        $this->defaultOptions[$resolverName]   = [
            ...($this->defaultOptions[$resolverName] ?? []),
            ...array_filter($defaultOptions),
        ];

        return $this;
    }

    public function setDefaultLocalResolver(string $resolverName): self
    {
        $this->defaultLocalResolver = $resolverName;

        return $this;
    }

    public function setDefaultRemoteResolver(string $resolverName): IPAddressResolverFactory
    {
        $this->defaultRemoteResolver = $resolverName;

        return $this;
    }

    public function getDefaultLocalResolver(): string
    {
        return $this->defaultLocalResolver;
    }

    public function getDefaultRemoteResolver(): string
    {
        return $this->defaultRemoteResolver;
    }

    public function getResolverBuilderFor(string $resolver): IPAddressResolverBuilder
    {
        $builder = $this->resolverBuilders[$resolver] ?? throw new \InvalidArgumentException('Invalid IP address resolver type');

        return $builder($this->httpClient)->withOptions($this->defaultOptions[$resolver] ?? []);
    }
}

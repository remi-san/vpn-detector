<?php

declare(strict_types=1);

namespace VPNDetector\Builder;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use VPNDetector\Builder\IPAddressResolver\HttpClientTrait;
use VPNDetector\Builder\IPAddressResolver\IPAddressResolvers;
use VPNDetector\Builder\IPAddressResolver\IpifyIPAddressResolverBuilder;
use VPNDetector\Builder\IPAddressResolver\LiveboxIPAddressResolverBuilder;

final class IPAddressResolverFactory
{
    use HttpClientTrait;

    /** @var array<string, array<string, string>> */
    private array $defaultOptions = [];

    /**
     * @param array<string, array<string, string>> $defaultOptions
     */
    public static function create(HttpClientInterface $httpClient = null, array $defaultOptions = []): self
    {
        $factory                 = new self($httpClient);
        $factory->defaultOptions = $defaultOptions;

        return $factory;
    }

    public function build(string $resolver): IPAddressResolverBuilder
    {
        $builder = match ($resolver) {
            IPAddressResolvers::IPIFY   => new IpifyIPAddressResolverBuilder($this->httpClient),
            IPAddressResolvers::LIVEBOX => new LiveboxIPAddressResolverBuilder($this->httpClient),
            default                     => throw new \InvalidArgumentException('Invalid IP address resolver type'),
        };

        return $builder->withOptions($this->defaultOptions[$resolver] ?? []);
    }
}

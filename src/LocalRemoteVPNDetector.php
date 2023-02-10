<?php

declare(strict_types=1);

namespace VPNDetector;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use VPNDetector\Exception\IPAddressResolvingException;
use VPNDetector\IPProvider\Ipify\IpifyIPAddressResolver;
use VPNDetector\IPProvider\Livebox\LiveboxIPAddressResolver;

final readonly class LocalRemoteVPNDetector implements VPNDetector
{
    private function __construct(
        private IPAddressResolver $localIpAddressResolver,
        private IPAddressResolver $remoteIpAddressResolver
    ) {
    }

    /**
     * @throws IPAddressResolvingException
     */
    public function isBehindVpn(): bool
    {
        $localIp  = $this->localIpAddressResolver->resolveIpAddress();
        $remoteIp = $this->remoteIpAddressResolver->resolveIpAddress();

        return !$localIp->equals($remoteIp);
    }

    public static function build(
        IPAddressResolver $localIpAddressResolver,
        IPAddressResolver $remoteIpAddressResolver
    ): self {
        return new self($localIpAddressResolver, $remoteIpAddressResolver);
    }

    /**
     * @see LiveboxAdminAPIClient::build for $options definition
     *
     * @param array<string, string> $options
     */
    public static function livebox(
        ?HttpClientInterface $httpClient = null,
        array $options = []
    ): self {
        return self::build(
            LiveboxIPAddressResolver::build($httpClient, $options),
            IpifyIPAddressResolver::build($httpClient)
        );
    }
}

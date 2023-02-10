<?php

declare(strict_types=1);

namespace VPNDetector;

use VPNDetector\Exception\IPAddressResolvingException;

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
}

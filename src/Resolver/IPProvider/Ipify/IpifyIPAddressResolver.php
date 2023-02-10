<?php

declare(strict_types=1);

namespace VPNDetector\Resolver\IPProvider\Ipify;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use VPNDetector\IPAddress;
use VPNDetector\IPAddressResolver;

final readonly class IpifyIPAddressResolver implements IPAddressResolver
{
    public function __construct(private IpifyAPI $client)
    {
    }

    public function resolveIpAddress(): IPAddress
    {
        try {
            return $this->getIPAddress(
                $this->client->getIP()
            );
        } catch (\Throwable $e) {
            throw IpifyIPAddressResolvingException::from($e);
        }
    }

    private function getIPAddress(string $ipAddress): IPAddress
    {
        if (str_contains($ipAddress, '.')) {
            return IPAddress::v4($ipAddress);
        }

        if (str_contains($ipAddress, ':')) {
            return IPAddress::v6($ipAddress);
        }

        throw new \InvalidArgumentException(sprintf('Invalid IP address provided: %s', $ipAddress));
    }

    public static function build(?HttpClientInterface $httpClient = null): self
    {
        return new self(IpifyAPIClient::build($httpClient));
    }
}

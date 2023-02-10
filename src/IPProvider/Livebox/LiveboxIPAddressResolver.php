<?php

declare(strict_types=1);

namespace VPNDetector\IPProvider\Livebox;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use VPNDetector\IPAddress;
use VPNDetector\IPAddressResolver;

final readonly class LiveboxIPAddressResolver implements IPAddressResolver
{
    public function __construct(private LiveboxAdminAPI $client)
    {
    }

    public function resolveIpAddress(): IPAddress
    {
        try {
            $wlan = $this->client->getWANStatus();

            $ipv4 = $wlan['data']['IPAddress']   ?? null;
            $ipv6 = $wlan['data']['IPv6Address'] ?? null;

            return new IPAddress(
                ($ipv4 === '') ? null : $ipv4,
                ($ipv6 === '') ? null : $ipv6
            );
        } catch (LiveboxAdminAPIException|\InvalidArgumentException $e) {
            throw LiveboxIPAddressResolvingException::from($e);
        }
    }

    /**
     * @param array<string, string> $options
     *
     *@see LiveboxAdminAPIClient::build for $options definition
     */
    public static function build(
        ?HttpClientInterface $httpClient = null,
        array $options = []
    ): self {
        return new self(
            LiveboxAdminAPIClient::build(
                $httpClient,
                $options
            )
        );
    }
}

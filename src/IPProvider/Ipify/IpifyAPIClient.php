<?php

declare(strict_types=1);

namespace VPNDetector\IPProvider\Ipify;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final readonly class IpifyAPIClient implements IpifyAPI
{
    public const URL  = 'https://api64.ipify.org';
    public const PATH = '/?format=json';
    private const KEY = 'ip';

    private function __construct(
        private HttpClientInterface $httpClient,
        private string $url
    ) {
    }

    /**
     * @throws ExceptionInterface
     */
    public function getIP(): string
    {
        return $this->httpClient
            ->request('GET', $this->url.self::PATH)
            ->toArray()[self::KEY] ?? throw new \InvalidArgumentException('Could not get IP address from Ipify response.');
    }

    public static function build(?HttpClientInterface $httpClient = null, string $url = self::URL): self
    {
        if ($httpClient === null) {
            $httpClient = HttpClient::create();
        }

        return new self($httpClient, $url);
    }
}

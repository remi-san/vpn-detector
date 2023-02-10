<?php

declare(strict_types=1);

namespace VPNDetector\Builder\IPAddressResolver;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

trait HttpClientTrait
{
    protected readonly HttpClientInterface $httpClient;

    public function __construct(HttpClientInterface $httpClient = null)
    {
        $this->httpClient = $httpClient ?? HttpClient::create();
    }
}

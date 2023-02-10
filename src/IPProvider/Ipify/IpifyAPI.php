<?php

declare(strict_types=1);

namespace VPNDetector\IPProvider\Ipify;

use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;

interface IpifyAPI
{
    /**
     * @throws ExceptionInterface
     */
    public function getIP(): string;
}

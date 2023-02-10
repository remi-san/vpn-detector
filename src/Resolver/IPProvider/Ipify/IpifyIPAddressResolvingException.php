<?php

declare(strict_types=1);

namespace VPNDetector\Resolver\IPProvider\Ipify;

use VPNDetector\Exception\IPAddressResolvingException;

final class IpifyIPAddressResolvingException extends \DomainException implements IPAddressResolvingException
{
    public static function from(\Throwable $e): self
    {
        return new self('Could not retrieve IP address through Ipify.', $e->getCode(), $e->getPrevious());
    }
}

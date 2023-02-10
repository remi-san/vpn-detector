<?php

declare(strict_types=1);

namespace VPNDetector\Resolver\Fixed;

use VPNDetector\Exception\IPAddressResolvingException;

final class FixedIPAddressResolvingException extends \DomainException implements IPAddressResolvingException
{
    public static function create(?\Throwable $e = null): self
    {
        return new self('No IP address has been set.', (int) ($e?->getCode() ?? 0), $e);
    }
}

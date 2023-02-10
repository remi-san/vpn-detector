<?php

declare(strict_types=1);

namespace VPNDetector\Resolver\IPProvider\Livebox;

use VPNDetector\Exception\IPAddressResolvingException;

final class LiveboxIPAddressResolvingException extends \DomainException implements IPAddressResolvingException
{
    public static function from(\Throwable $e): self
    {
        return new self($e->getMessage(), $e->getCode(), $e->getPrevious());
    }
}

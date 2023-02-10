<?php

declare(strict_types=1);

namespace VPNDetector\IPProvider\Livebox;

use VPNDetector\Exception\IPAddressResolvingException;

final class LiveboxIPAddressResolvingException extends \DomainException implements IPAddressResolvingException
{
    public static function from(\Throwable $e): self
    {
        return new self('Could not retrieve IP address through Livebox.', $e->getCode(), $e->getPrevious());
    }
}

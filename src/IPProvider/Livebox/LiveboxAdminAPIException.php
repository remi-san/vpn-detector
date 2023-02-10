<?php

declare(strict_types=1);

namespace VPNDetector\IPProvider\Livebox;

final class LiveboxAdminAPIException extends \DomainException
{
    public static function from(\Throwable $e): self
    {
        return new self('Could not retrieve information from Livebox.', $e->getCode(), $e->getPrevious());
    }

    public static function authenticationFailed(\Throwable $e): self
    {
        return new self('Could not authenticate to Livebox.', $e->getCode(), $e->getPrevious());
    }
}

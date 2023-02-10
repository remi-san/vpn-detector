<?php

declare(strict_types=1);

namespace VPNDetector\Tests\IPProvider\Livebox;

use VPNDetector\IPProvider\Livebox\LiveboxAdminAPIClient;

final readonly class LiveboxUser
{
    private function __construct(public string $username, public string $password, public bool $valid)
    {
    }

    public static function validUser(): self
    {
        return new self(
            LiveboxAdminAPIClient::DEFAULT_USER,
            LiveboxAdminAPIClient::DEFAULT_PASSWORD,
            true
        );
    }

    public static function unknownUser(): self
    {
        return new self('unknown', 'unknownPwd', false);
    }
}

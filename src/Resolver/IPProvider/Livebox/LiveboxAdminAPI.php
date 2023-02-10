<?php

declare(strict_types=1);

namespace VPNDetector\Resolver\IPProvider\Livebox;

interface LiveboxAdminAPI
{
    public const DEFAULT_URL      = 'http://192.168.1.1';
    public const DEFAULT_USER     = 'admin';
    public const DEFAULT_PASSWORD = 'admin';

    /**
     * @throws LiveboxAdminAPIException
     */
    public function authenticate(): void;

    /**
     * @return array<string, array<string, string>>
     *
     * @throws LiveboxAdminAPIException
     */
    public function getWANStatus(): array;

    public function isAuthenticated(): bool;
}

<?php

declare(strict_types=1);

namespace VPNDetector\IPProvider\Livebox;

interface LiveboxAdminAPI
{
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

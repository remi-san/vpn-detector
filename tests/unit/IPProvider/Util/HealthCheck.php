<?php

declare(strict_types=1);

namespace VPNDetector\Tests\IPProvider\Util;

final class HealthCheck
{
    public static function isServerUp(string $host, int $port): bool
    {
        $socket = @fsockopen($host, $port, $errorCode, $errorMessage, 1);
        if ($socket === false) {
            return false;
        }

        fclose($socket);

        return true;
    }
}

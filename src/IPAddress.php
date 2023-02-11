<?php

declare(strict_types=1);

namespace VPNDetector;

use Assert\Assert;

final readonly class IPAddress
{
    public function __construct(public ?string $ipv4, public ?string $ipv6)
    {
        Assert::that($ipv4)->nullOr()->ipv4();
        Assert::that($ipv6)->nullOr()->ipv6();
        Assert::that($ipv4 ?? $ipv6)->notNull('You have to provide at least one IPv4 or IPv6 address.');
    }

    public function equals(self $other): bool
    {
        return ($this->ipv4 !== null && $this->ipv4 === $other->ipv4)
            || ($this->ipv6 !== null && $this->ipv6 === $other->ipv6);
    }

    public static function v4(string $ip): self
    {
        return new self($ip, null);
    }

    public static function v6(string $ip): self
    {
        return new self(null, $ip);
    }

    public static function create(string $ip): self
    {
        if (filter_var($ip, \FILTER_VALIDATE_IP, \FILTER_FLAG_IPV4) !== false) {
            return self::v4($ip);
        }

        if (filter_var($ip, \FILTER_VALIDATE_IP, \FILTER_FLAG_IPV6) !== false) {
            return self::v6($ip);
        }

        throw new \InvalidArgumentException('Invalid IP address.');
    }
}

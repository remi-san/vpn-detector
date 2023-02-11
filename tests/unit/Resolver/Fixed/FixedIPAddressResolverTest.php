<?php

declare(strict_types=1);

namespace VPNDetector\Tests\Resolver\Fixed;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use VPNDetector\Exception\IPAddressResolvingException;
use VPNDetector\IPAddress;
use VPNDetector\Resolver\Fixed\FixedIPAddressResolver;

final class FixedIPAddressResolverTest extends TestCase
{
    /**
     * @throws IPAddressResolvingException
     */
    #[Test]
    public function it_resolves_the_fixed_ip(): void
    {
        $ip = IPAddress::v4('0.0.0.0');

        $resolver = new FixedIPAddressResolver($ip);

        self::assertTrue($ip->equals($resolver->resolveIpAddress()));
    }

    #[Test]
    public function it_cannot_resolve_the_fixed_ip_if_not_given_any(): void
    {
        $resolver = new FixedIPAddressResolver();

        $this->expectException(IPAddressResolvingException::class);
        $resolver->resolveIpAddress();
    }
}

<?php

declare(strict_types=1);

namespace VPNDetector\Tests\IPProvider\Livebox;

use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use VPNDetector\Exception\IPAddressResolvingException;
use VPNDetector\IPAddress;
use VPNDetector\IPProvider\Livebox\LiveboxAdminAPI;
use VPNDetector\IPProvider\Livebox\LiveboxAdminAPIException;
use VPNDetector\IPProvider\Livebox\LiveboxIPAddressResolver;
use VPNDetector\IPProvider\Livebox\LiveboxIPAddressResolvingException;

#[Group('unit')]
#[Group('network')]
#[Group('livebox')]
final class LiveboxIPAddressResolverTest extends TestCase
{
    private LiveboxAdminAPI&MockInterface $liveboxClient;
    private LiveboxIPAddressResolver $resolver;
    private IPAddress $resolvedIPAddress;

    private const INVALID_I_PV4_ADDRESS = 'invalid';
    private const I_PV6_ADDRESS         = '2001:67c:2e8:22::c100:68b';
    private const I_PV4_ADDRESS         = '127.0.0.1';
    private const INVALID_I_PV6_ADDRESS = 'invalid';

    protected function setUp(): void
    {
        $this->liveboxClient = \Mockery::mock(LiveboxAdminAPI::class);
        $this->resolver      = new LiveboxIPAddressResolver($this->liveboxClient);
    }

    protected function tearDown(): void
    {
        \Mockery::close();
    }

    /**
     * @throws IPAddressResolvingException
     */
    #[DataProvider('ips')]
    #[Test]
    public function it_returns_an_ip_address(?string $IPv4_address, ?string $IPv6_address): void
    {
        $_this         = $this; // #ignoreLine
        $_ipv4_address = $IPv4_address; // #ignoreLine
        $_ipv6_address = $IPv6_address; // #ignoreLine

        $_this->given_the_livebox_admin_API_will_return(an: $IPv4_address, and_an: $IPv6_address);
        $_this->when_resolving_the_IP_address();
        $_this->I_should_get_the_same_IPv4_address($_ipv4_address);
        $_this->I_should_get_the_same_IPv6_address($_ipv6_address);
    }

    /**
     * @throws IPAddressResolvingException
     */
    #[Test]
    public function it_returns_an_error_when_resolving_an_invalid_ipv4_address(): void
    {
        $_this                = $this; // #ignoreLine

        $_this->given_the_livebox_admin_API_will_return(an: self::INVALID_I_PV4_ADDRESS, and_an: self::I_PV6_ADDRESS);
        $_this->I_should_get_an_error()->when_resolving_the_IP_address();
    }

    /**
     * @throws IPAddressResolvingException
     */
    #[Test]
    public function it_returns_an_error_when_resolving_an_invalid_ipv6_address(): void
    {
        $_this                = $this; // #ignoreLine

        $_this->given_the_livebox_admin_API_will_return(an: self::I_PV4_ADDRESS, and_an: self::INVALID_I_PV6_ADDRESS);
        $_this->I_should_get_an_error()->when_resolving_the_IP_address();
    }

    /**
     * @throws IPAddressResolvingException
     */
    #[DataProvider('emptyIps')]
    #[Test]
    public function it_returns_an_error_when_both_ips_are_missing(?string $empty_IPv4_address, ?string $empty_IPv6_address): void
    {
        $_this = $this; // #ignoreLine

        $_this->given_the_livebox_admin_API_will_return(an: $empty_IPv4_address, and_an: $empty_IPv6_address);
        $_this->I_should_get_an_error()->when_resolving_the_IP_address();
    }

    /**
     * @throws IPAddressResolvingException
     */
    #[Test]
    public function it_returns_an_error_when_livebox_admin_api_is_in_error(): void
    {
        $_this = $this; // #ignoreLine

        $_this->given_the_livebox_admin_API_is_in_error();
        $_this->I_should_get_an_error()->when_resolving_the_IP_address();
    }

    // 1. Arrange

    private function given_the_livebox_admin_API_will_return(?string $an, ?string $and_an): void
    {
        $this->liveboxClient->expects('getWANStatus')->andReturns([
            'data' => [
                'IPAddress'   => $an,
                'IPv6Address' => $and_an,
            ],
        ]);
    }

    private function given_the_livebox_admin_API_is_in_error(): void
    {
        $this->liveboxClient->expects('getWANStatus')->andThrows(new LiveboxAdminAPIException());
    }

    // 2. Act

    /**
     * @throws IPAddressResolvingException
     */
    public function when_resolving_the_IP_address(): void
    {
        $this->resolvedIPAddress = $this->resolver->resolveIpAddress();
    }

    // 3. Assert

    private function I_should_get_an_error(): self
    {
        $this->expectException(LiveboxIPAddressResolvingException::class);

        return $this;
    }

    public function I_should_get_the_same_IPv4_address(?string $expectedIPv4): void
    {
        self::assertEquals($expectedIPv4, $this->resolvedIPAddress->ipv4);
    }

    public function I_should_get_the_same_IPv6_address(?string $expectedIPv6): void
    {
        self::assertEquals($expectedIPv6, $this->resolvedIPAddress->ipv6);
    }

    // Data providers

    /**
     * @return array<string, array<?string>>
     */
    public static function ips(): array
    {
        return [
            'local'   => ['127.0.0.1', '::1'],
            'private' => ['192.168.1.1', '2001:67c:2e8:22::c100:68b'],
            'only_v4' => ['192.168.1.1', null],
            'only_v6' => [null, '2001:67c:2e8:22::c100:68b'],
        ];
    }

    /**
     * @return array<string, array<?string>>
     */
    public static function emptyIps(): array
    {
        return [
            'empty' => ['', ''],
            'null'  => [null, null],
        ];
    }
}

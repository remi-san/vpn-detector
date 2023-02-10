<?php

declare(strict_types=1);

namespace VPNDetector\Tests\IPProvider\Ipify;

use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpClient\Response\MockResponse;
use VPNDetector\Exception\IPAddressResolvingException;
use VPNDetector\IPAddress;
use VPNDetector\IPProvider\Ipify\IpifyAPI;
use VPNDetector\IPProvider\Ipify\IpifyIPAddressResolver;
use VPNDetector\IPProvider\Ipify\IpifyIPAddressResolvingException;

#[Group('unit')]
#[Group('network')]
#[Group('ipify')]
final class IpifyIPAddressResolverTest extends TestCase
{
    private IpifyAPI&MockInterface $ipifyClient;
    private IpifyIPAddressResolver $resolver;
    private IPAddress $resolvedIPAddress;
    // #ignoreLine
    /**
     * @var string
     */
    private const INVALID_IP_ADDRESS = '';

    protected function setUp(): void
    {
        $this->ipifyClient = \Mockery::mock(IpifyAPI::class);
        $this->resolver    = new IpifyIPAddressResolver($this->ipifyClient);
    }

    protected function tearDown(): void
    {
        \Mockery::close();
    }

    /**
     * @throws IPAddressResolvingException
     */
    #[DataProvider('ipv4')]
    #[Test]
    public function it_returns_an_ipv4_ip_address(string $IPv4_address): void
    {
        $_this    = $this; // #ignoreLine
        $_address = $IPv4_address; // #ignoreLine

        $_this->given_ipify_API_will_return_an($IPv4_address);
        $_this->when_resolving_the_IP_address();
        $_this->I_should_get_the_same_IPv4_address($_address);
    }

    /**
     * @throws IPAddressResolvingException
     */
    #[DataProvider('ipv6')]
    #[Test]
    public function it_returns_an_ipv6_ip_address(string $IPv6_address): void
    {
        $_this    = $this; // #ignoreLine
        $_address = $IPv6_address; // #ignoreLine

        $_this->given_ipify_API_will_return_an($IPv6_address);
        $_this->when_resolving_the_IP_address();
        $_this->I_should_get_the_same_IPv6_address($_address);
    }

    /**
     * @throws IPAddressResolvingException
     */
    #[Test]
    public function it_returns_an_error_when_resolving_an_invalid_ip_address(): void
    {
        $_this              = $this; // #ignoreLine

        $_this->given_ipify_API_will_return_an(self::INVALID_IP_ADDRESS);
        $_this->I_should_get_an_error()->when_resolving_the_IP_address();
    }

    /**
     * @throws IPAddressResolvingException
     */
    #[Test]
    public function it_returns_an_error_when_the_ipify_api_is_in_error(): void
    {
        $_this = $this; // #ignoreLine

        $_this->given_ipify_API_is_in_error();
        $_this->I_should_get_an_error()->when_resolving_the_IP_address();
    }

    // 1. Arrange

    private function given_ipify_API_will_return_an(string $address): void
    {
        $this->ipifyClient->expects('getIP')->andReturns($address);
    }

    private function given_ipify_API_is_in_error(): void
    {
        $this->ipifyClient->expects('getIP')->andThrows(new ClientException(new MockResponse()));
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
        $this->expectException(IpifyIPAddressResolvingException::class);

        return $this;
    }

    public function I_should_get_the_same_IPv4_address(string $expectedIPv4): void
    {
        self::assertEquals($expectedIPv4, $this->resolvedIPAddress->ipv4);
    }

    public function I_should_get_the_same_IPv6_address(string $expectedIPv6): void
    {
        self::assertEquals($expectedIPv6, $this->resolvedIPAddress->ipv6);
    }

    // Data providers

    /**
     * @return array<string, array<string>>
     */
    public static function ipv4(): array
    {
        return [
            'local'   => ['127.0.0.1'],
            'private' => ['192.168.1.1'],
        ];
    }

    /**
     * @return array<string, array<string>>
     */
    public static function ipv6(): array
    {
        return [
            'local'  => ['::1'],
            'random' => ['2001:67c:2e8:22::c100:68b'],
        ];
    }
}

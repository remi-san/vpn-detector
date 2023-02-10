<?php

declare(strict_types=1);

namespace VPNDetector\Tests;

use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use VPNDetector\Exception\IPAddressResolvingException;
use VPNDetector\IPAddress;
use VPNDetector\IPAddressResolver;
use VPNDetector\LocalRemoteVPNDetector;

#[Group('unit')]
#[Group('network')]
#[Group('vpn')]
final class VPNDetectorTest extends TestCase
{
    private ?IPAddress $localIPAddress;
    private ?IPAddress $remoteIPAddress;
    private IPAddressResolver&MockInterface $localIPAddressResolver;
    private IPAddressResolver&MockInterface $remoteIPAddressResolver;
    private LocalRemoteVPNDetector $VPNDetector;

    protected function setUp(): void
    {
        $this->localIPAddressResolver  = \Mockery::mock(IPAddressResolver::class);
        $this->remoteIPAddressResolver = \Mockery::mock(IPAddressResolver::class);
        $this->localIPAddress          = null;
        $this->remoteIPAddress         = null;
        $this->VPNDetector             = LocalRemoteVPNDetector::build($this->localIPAddressResolver, $this->remoteIPAddressResolver);
    }

    protected function tearDown(): void
    {
        \Mockery::close();
    }

    /**
     * @throws IPAddressResolvingException
     */
    #[DataProvider('differentIPs')]
    #[Test]
    public function it_should_inform_it_is_behind_vpn_if_local_and_remote_ip_resolvers_have_different_ips(IPAddress $_localIPAddress, IPAddress $_remoteIPAddress): void
    {
        $_this = $this; // #ignoreLine

        $_this->given($_this->the_resolved_local_IP_address($_localIPAddress))->and($_this->the_resolved_remote_IP_address($_remoteIPAddress))->are_different();
        $_behindVPN = $_this->when_I_want_to_check_if_I_am_behind_a_VPN();
        $_this->it_should_inform_me_I_am($_behindVPN);
    }

    /**
     * @throws IPAddressResolvingException
     */
    #[DataProvider('equalIPs')]
    #[Test]
    public function it_should_inform_it_is_not_behind_vpn_if_local_and_remote_ip_resolvers_have_the_same_ips(IPAddress $_localIPAddress, IPAddress $_remoteIPAddress): void
    {
        $_this = $this; // #ignoreLine

        $_this->given($_this->the_resolved_local_IP_address($_localIPAddress))->and($_this->the_resolved_remote_IP_address($_remoteIPAddress))->are_identical();
        $_behindVPN = $_this->when_I_want_to_check_if_I_am_behind_a_VPN();
        $_this->it_should_inform_me_I_am_not($_behindVPN);
    }

    /**
     * @throws IPAddressResolvingException
     */
    #[Test]
    public function it_should_build_the_detector_for_a_livebox(): void
    {
        $_this = $this; // #ignoreLine

        $_this->given_a_badly_configured_livebox_VPN_detector();
        $_this->it_should_fail()->detecting_if_it_is_behind_a_VPN();
    }

    // Util

    private function given(mixed ...$args): self
    {
        return $this;
    }

    private function and(mixed ...$args): self
    {
        return $this;
    }

    // 1. Arrange

    private function the_resolved_local_IP_address(IPAddress $localIPAddress): IPAddress
    {
        $this->localIPAddress = $localIPAddress;

        $this->localIPAddressResolver->shouldReceive('resolveIpAddress')->andReturn($this->localIPAddress);

        return $this->localIPAddress;
    }

    private function the_resolved_remote_IP_address(IPAddress $remoteIPAddress): IPAddress
    {
        $this->remoteIPAddress = $remoteIPAddress;

        $this->remoteIPAddressResolver->shouldReceive('resolveIpAddress')->andReturn($this->remoteIPAddress);

        return $this->remoteIPAddress;
    }

    private function are_identical(): void
    {
        self::assertNotNull($this->localIPAddress);
        self::assertNotNull($this->remoteIPAddress);
        self::assertTrue($this->remoteIPAddress->equals($this->localIPAddress));
    }

    private function are_different(): void
    {
        self::assertNotNull($this->localIPAddress);
        self::assertNotNull($this->remoteIPAddress);
        self::assertFalse($this->remoteIPAddress->equals($this->localIPAddress));
    }

    private function given_a_badly_configured_livebox_VPN_detector(): void
    {
        $this->VPNDetector = LocalRemoteVPNDetector::livebox(new MockHttpClient());
    }

    // 2. Act

    /**
     * @throws IPAddressResolvingException
     */
    private function when_I_want_to_check_if_I_am_behind_a_VPN(): bool
    {
        return $this->VPNDetector->isBehindVpn();
    }

    /**
     * @throws IPAddressResolvingException
     */
    public function detecting_if_it_is_behind_a_VPN(): void
    {
        $this->when_I_want_to_check_if_I_am_behind_a_VPN();
    }

    // 3. Assert

    private function it_should_inform_me_I_am_not(bool $behindVPN): void
    {
        self::assertFalse($behindVPN);
    }

    private function it_should_inform_me_I_am(bool $behindVPN): void
    {
        self::assertTrue($behindVPN);
    }

    public function it_should_fail(): self
    {
        $this->expectException(IPAddressResolvingException::class);

        return $this;
    }

    // Data Provider

    /**
     * @return array<string, array<IPAddress>>
     */
    public static function equalIPs(): array
    {
        return [
            'onlyV4' => [IPAddress::v4('1.2.3.4'), IPAddress::v4('1.2.3.4')],
            'onlyV6' => [IPAddress::v6('::1'), IPAddress::v6('::1')],
            'bothV4' => [new IPAddress('1.2.3.4', '::1'), new IPAddress('1.2.3.4', '::2')],
            'bothV6' => [new IPAddress('1.2.3.4', '::1'), new IPAddress('4.3.2.1', '::1')],
            'both'   => [new IPAddress('1.2.3.4', '::1'), new IPAddress('1.2.3.4', '::1')],
        ];
    }

    /**
     * @return array<string, array<IPAddress>>
     */
    public static function differentIPs(): array
    {
        return [
            'onlyV4' => [IPAddress::v4('1.2.3.4'), IPAddress::v4('4.3.2.1')],
            'onlyV6' => [IPAddress::v6('::1'), IPAddress::v6('::2')],
            'both'   => [new IPAddress('1.2.3.4', '::1'), new IPAddress('4.3.2.1', '::2')],
        ];
    }
}

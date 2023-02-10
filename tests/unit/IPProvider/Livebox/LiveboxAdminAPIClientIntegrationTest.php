<?php

declare(strict_types=1);

namespace VPNDetector\Tests\IPProvider\Livebox;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use VPNDetector\IPAddressResolver;
use VPNDetector\IPProvider\Livebox\LiveboxAdminAPIClient;
use VPNDetector\IPProvider\Livebox\LiveboxAdminAPIException;
use VPNDetector\Tests\IPProvider\Util\HealthCheck;

#[Group('integration')]
#[Group('network')]
#[Group('livebox')]
#[Group('pact')]
final class LiveboxAdminAPIClientIntegrationTest extends TestCase
{
    private const URL  = 'http://livebox:1337';
    private const IPV4 = '1.2.3.4';
    private const IPV6 = '';

    private string $url = self::URL;
    private ?LiveboxAdminAPIClient $liveboxAdminAPIClient;

    /** @var mixed[]|null */
    private ?array $wanStatus;

    protected function setUp(): void
    {
        if (!HealthCheck::isServerUp('livebox', 1337)) {
            self::markTestSkipped('Pact server is not running. Skipping test.');
        }

        $this->url                   = self::URL;
        $this->wanStatus             = null;
        $this->liveboxAdminAPIClient = null;
    }

    #[Test]
    public function it_authenticates_a_user(): void
    {
        $_this = $this; // #ignoreLine

        $_this->given_no_user_is_authenticated();
        $_this->when_I_authenticate($_this->an_existing_user());
        $_this->I_should_be_authenticated();
    }

    #[Test]
    public function it_does_not_authenticate_if_already_authenticated(): void
    {
        $_this = $this; // #ignoreLine

        $_this->given_an_existing_user_is_authenticated();
        $_this->it_should_not_authenticate_again()->when_I_authenticate($_this->an_existing_user());
    }

    #[Test]
    public function it_does_not_authenticate_if_given_an_unknown_user(): void
    {
        $_this = $this; // #ignoreLine

        $_this->given_no_user_is_authenticated();
        $_this->I_should_get_an_error()->when_I_authenticate($_this->an_unknown_user());
    }

    #[Test]
    public function it_gets_the_wan_status_if_already_authenticated(): void
    {
        $_this = $this; // #ignoreLine

        $_this->given_an_existing_user_is_authenticated();
        $_this->when_I_ask_for_the_WAN_status();
        $_this->I_should_get_the_WAN_information();
    }

    #[Test]
    public function it_authenticates_then_gets_the_wan_status_if_not_already_authenticated(): void
    {
        $_this = $this; // #ignoreLine

        $_this->given_no_user_is_authenticated();
        $_this->it_should_automatically_connect_me_with_a_known_user()->when_I_ask_for_the_WAN_status();
        $_this->I_should_get_the_WAN_information();
    }

    #[Test]
    public function it_fails_getting_the_wan_status_if_failing_authenticating(): void
    {
        $_this = $this; // #ignoreLine

        $_this->given_no_user_is_authenticated();
        $_this->it_should_fail_connecting_me_with_an_unknown_user()->when_I_ask_for_the_WAN_status();
    }

    #[Test]
    public function it_fails_getting_the_wan_status_if_the_livebox_api_fails(): void
    {
        $_this = $this; // #ignoreLine

        $_this->given_the_api_fails();
        $_this->I_should_get_an_error()->when_I_ask_for_the_WAN_status();
    }

    // 1. Arrange

    private function given_no_user_is_authenticated(): void
    {
    }

    private function an_existing_user(): LiveboxUser
    {
        return LiveboxUser::validUser();
    }

    private function an_unknown_user(): LiveboxUser
    {
        return LiveboxUser::unknownUser();
    }

    private function given_an_existing_user_is_authenticated(): void
    {
        $this->when_I_authenticate($this->an_existing_user());
    }

    private function given_the_api_fails(): void
    {
        $this->url = self::URL.'/error';
        $this->setupClient($this->an_existing_user());
    }

    // 2. Act

    private function when_I_authenticate(LiveboxUser $user): void
    {
        $this->setupClient($user);
        self::assertNotNull($this->liveboxAdminAPIClient);
        $this->liveboxAdminAPIClient->authenticate();
    }

    private function it_should_automatically_connect_me_with_a_known_user(): self
    {
        $this->setupClient($this->an_existing_user());

        return $this;
    }

    private function it_should_fail_connecting_me_with_an_unknown_user(): self
    {
        $this->setupClient($this->an_unknown_user());

        $this->expectException(LiveboxAdminAPIException::class);

        return $this;
    }

    private function when_I_ask_for_the_WAN_status(): void
    {
        self::assertNotNull($this->liveboxAdminAPIClient);
        $this->wanStatus = $this->liveboxAdminAPIClient->getWANStatus();
    }

    private function setupClient(LiveboxUser $user): void
    {
        $this->liveboxAdminAPIClient ??= LiveboxAdminAPIClient::build(
            null,
            [
                IPAddressResolver::URL_PARAM      => $this->url,
                IPAddressResolver::USER_PARAM     => $user->username,
                IPAddressResolver::PASSWORD_PARAM => $user->password,
            ]
        );
    }

    // 3. Assert

    private function I_should_be_authenticated(): void
    {
        self::assertNotNull($this->liveboxAdminAPIClient);
        self::assertTrue($this->liveboxAdminAPIClient->isAuthenticated());
    }

    private function it_should_not_authenticate_again(): self
    {
        $this->I_should_be_authenticated();

        return $this;
    }

    private function I_should_get_an_error(): self
    {
        $this->expectException(LiveboxAdminAPIException::class);

        return $this;
    }

    private function I_should_get_the_WAN_information(): void
    {
        self::assertIsArray($this->wanStatus);
        self::assertTrue($this->wanStatus['status']);
        self::assertEquals(self::IPV4, $this->wanStatus['data']['IPAddress']);
        self::assertEquals(self::IPV6, $this->wanStatus['data']['IPv6Address']);
    }
}

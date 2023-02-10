<?php

declare(strict_types=1);

namespace VPNDetector\Tests\IPProvider\Ipify;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\Exception\JsonException;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use VPNDetector\IPProvider\Ipify\IpifyAPIClient;
use VPNDetector\Tests\IPProvider\Util\HealthCheck;

#[Group('integration')]
#[Group('network')]
#[Group('ipify')]
#[Group('pact')]
final class IpifyAPIClientIntegrationTest extends TestCase
{
    private const URL = 'http://ipify:4148';
    private ?IpifyApiClient $ipifyAPIClient;
    private ?string $returnedIPAddress;

    protected function setUp(): void
    {
        if (!HealthCheck::isServerUp('ipify', 4148)) {
            self::markTestSkipped('Pact server is not running. Skipping test.');
        }

        $this->ipifyAPIClient    = null;
        $this->returnedIPAddress = null;
    }

    /**
     * @throws ExceptionInterface
     */
    #[Test]
    public function it_should_return_an_ip_address(): void
    {
        $_this = $this; // #ignoreLine

        $_this->given_the_API_will_return_an_IP_address();
        $_this->when_I_get_the_IP_address();
        $_this->then_I_should_retrieve_the_IP_address();
    }

    /**
     * @throws ExceptionInterface
     */
    #[Test]
    public function it_should_raise_an_error_if_ipify_returns_a_bad_message(): void
    {
        $_this  = $this; // #ignoreLine
        $_error = JsonException::class; // #ignoreLine

        $_this->given_the_API_will_return_a_bad_message();
        $_this->it_should_raise_an_error($_error)->when_I_get_the_IP_address();
    }

    /**
     * @throws ExceptionInterface
     */
    #[Test]
    public function it_should_raise_an_error_if_ipify_returns_a_message_without_ip(): void
    {
        $_this  = $this; // #ignoreLine
        $_error = \InvalidArgumentException::class; // #ignoreLine

        $_this->given_the_API_will_return_a_message_without_ip();
        $_this->it_should_raise_an_error($_error)->when_I_get_the_IP_address();
    }

    /**
     * @throws ExceptionInterface
     */
    #[Test]
    public function it_should_raise_an_error_if_ipify_is_in_error(): void
    {
        $_this  = $this; // #ignoreLine
        $_error = ExceptionInterface::class; // #ignoreLine

        $_this->given_the_API_will_be_in_error();
        $_this->it_should_raise_an_error($_error)->when_I_get_the_IP_address();
    }

    // 1. Arrange

    private function given_the_API_will_return_an_IP_address(): void
    {
        $this->ipifyAPIClient = IpifyApiClient::build(null, self::URL);
    }

    private function given_the_API_will_return_a_bad_message(): void
    {
        $this->ipifyAPIClient = IpifyApiClient::build(null, self::URL.'/malformed');
    }

    private function given_the_API_will_return_a_message_without_ip(): void
    {
        $this->ipifyAPIClient = IpifyApiClient::build(null, self::URL.'/missing');
    }

    private function given_the_API_will_be_in_error(): void
    {
        $this->ipifyAPIClient = IpifyApiClient::build(null, self::URL.'/error');
    }

    // 2. Act

    /**
     * @throws ExceptionInterface
     */
    private function when_I_get_the_IP_address(): void
    {
        self::assertNotNull($this->ipifyAPIClient);
        $this->returnedIPAddress = $this->ipifyAPIClient->getIP();
    }

    // 3. Assert

    private function then_I_should_retrieve_the_IP_address(): void
    {
        self::assertEquals('127.0.0.1', $this->returnedIPAddress);
    }

    /**
     * @param class-string<\Throwable> $error
     */
    private function it_should_raise_an_error(string $error): self
    {
        $this->expectException($error);

        return $this;
    }

    // Data providers

    /**
     * @return array<string, array<string>>
     */
    public function ips(): array
    {
        return [
            'local_v4'   => ['127.0.0.1'],
            'private_v4' => ['192.168.1.1'],
            'local_v6'   => ['::1'],
            'random_v6'  => ['2001:67c:2e8:22::c100:68b'],
        ];
    }
}

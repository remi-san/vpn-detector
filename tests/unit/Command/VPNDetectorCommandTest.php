<?php

declare(strict_types=1);

namespace VPNDetector\Tests\Command;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Exception\ExceptionInterface;
use Symfony\Component\Console\Input\StringInput;
use VPNDetector\Builder\IPAddressResolverBuilder;
use VPNDetector\Builder\IPAddressResolverFactory;
use VPNDetector\Builder\VPNDetectorBuilder;
use VPNDetector\Command\VPNDetectorCommand;
use VPNDetector\IPAddressResolver;
use VPNDetector\Tests\Helper\InMemoryOutput;
use VPNDetector\VPNDetector;

final class VPNDetectorCommandTest extends TestCase
{
    private InMemoryOutput $output;

    private VPNDetector              $VPNDetector;

    private VPNDetectorCommand $command;

    protected function setUp(): void
    {
        $this->output = new InMemoryOutput();

        $this->VPNDetector = \Mockery::mock(VPNDetector::class);
        $this->VPNDetector->shouldReceive('isBehindVpn')->andReturn(true)->byDefault();

        $this->command = new VPNDetectorCommand(
            new TestIPAddressResolverFactory(),
            new TestVPNDetectorBuilder($this->VPNDetector)
        );
    }

    protected function tearDown(): void
    {
        \Mockery::close();
    }

    /**
     * @throws ExceptionInterface
     */
    #[Test]
    public function it_detects_it_is_behind_a_vpn(): void
    {
        $input  = new StringInput('');
        $return = $this->command->run($input, $this->output);

        self::assertSame(0, $return);
        self::assertCount(1, $this->output->lines);
        self::assertEquals('<info>You are behind a VPN</info>'.\PHP_EOL, $this->output->lines[0]);
    }

    /**
     * @throws ExceptionInterface
     */
    #[Test]
    public function it_detects_it_is_not_behind_a_vpn(): void
    {
        $this->VPNDetector->shouldReceive('isBehindVpn')->andReturn(false); // @phpstan-ignore-line

        $input  = new StringInput('');
        $return = $this->command->run($input, $this->output);

        self::assertSame(0, $return);
        self::assertCount(1, $this->output->lines);
        self::assertEquals('<error>You are not behind a VPN</error>'.\PHP_EOL, $this->output->lines[0]);
    }
}

final class TestIPAddressResolverFactory implements IPAddressResolverFactory
{
    public function build(string $resolver): IPAddressResolverBuilder
    {
        return new TestIPAddressResolverBuilder();
    }
}

final class TestIPAddressResolverBuilder implements IPAddressResolverBuilder
{
    public function withOptions(array $options): self
    {
        return $this;
    }

    public function build(): IPAddressResolver
    {
        return \Mockery::mock(IPAddressResolver::class);
    }
}

final readonly class TestVPNDetectorBuilder implements VPNDetectorBuilder
{
    public function __construct(private VPNDetector $VPNDetector)
    {
    }

    public function withLocalIPAddressResolver(IPAddressResolver $localIPAddressResolver): self
    {
        return $this;
    }

    public function withRemoteIPAddressResolver(IPAddressResolver $remoteIPAddressResolver): self
    {
        return $this;
    }

    public function build(): VPNDetector
    {
        return $this->VPNDetector;
    }
}

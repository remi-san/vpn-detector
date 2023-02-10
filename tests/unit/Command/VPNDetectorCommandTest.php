<?php

declare(strict_types=1);

namespace VPNDetector\Tests\Command;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Exception\ExceptionInterface;
use Symfony\Component\Console\Input\StringInput;
use VPNDetector\Command\VPNDetectorCommand;
use VPNDetector\Tests\Helper\InMemoryOutput;
use VPNDetector\VPNDetector;

final class VPNDetectorCommandTest extends TestCase
{
    private InMemoryOutput $output;

    private VPNDetector $VPNDetector;

    private VPNDetectorCommand $command;

    protected function setUp(): void
    {
        $this->output = new InMemoryOutput();

        $this->VPNDetector = \Mockery::mock(VPNDetector::class);
        $this->VPNDetector->shouldReceive('isBehindVpn')->andReturn(true)->byDefault();

        $this->command = new VPNDetectorCommand($this->VPNDetector);
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

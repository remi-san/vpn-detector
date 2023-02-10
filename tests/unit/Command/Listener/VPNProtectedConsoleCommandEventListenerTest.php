<?php

declare(strict_types=1);

namespace VPNDetector\Tests\Command\Listener;

use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use VPNDetector\Command\Listener\VPNProtectedConsoleCommandEventListener;
use VPNDetector\Tests\Helper\InMemoryOutput;
use VPNDetector\VPNDetector;

final class VPNProtectedConsoleCommandEventListenerTest extends TestCase
{
    private InMemoryOutput $output;
    private Command $command;
    private VPNDetector&MockInterface $VPNDetector;
    private VPNProtectedConsoleCommandEventListener $listener;

    private Application $application;

    protected function setUp(): void
    {
        $this->output  = new InMemoryOutput();
        $this->command = new class('test') extends Command {
            protected function execute(InputInterface $input, OutputInterface $output): int
            {
                return Command::SUCCESS;
            }
        };

        $this->VPNDetector = \Mockery::mock(VPNDetector::class);
        $this->VPNDetector->shouldReceive('isBehindVpn')->andReturn(false)->byDefault();

        $this->listener = new VPNProtectedConsoleCommandEventListener($this->VPNDetector);

        $dispatcher = new EventDispatcher();
        $dispatcher->addListener(ConsoleEvents::COMMAND, $this->listener);

        $this->application = new Application();
        $this->application->add($this->command);
        $this->application->setAutoExit(false);
        $this->application->setCatchExceptions(false);
        $this->application->setDispatcher($dispatcher);
    }

    protected function tearDown(): void
    {
        \Mockery::close();
    }

    /**
     * @throws \Exception
     */
    #[Test]
    public function it_prevents_executing_the_protected_command_if_not_behind_a_vpn(): void
    {
        $this->listener->protect($this->command);

        $return = $this->application->run(new StringInput('test'), $this->output);

        self::assertEquals(ConsoleCommandEvent::RETURN_CODE_DISABLED, $return);
        self::assertCount(1, $this->output->lines);
        self::assertEquals('<error>You must be behind a VPN to run this command. Execution cancelled.</error>'.\PHP_EOL, $this->output->lines[0]);
    }

    /**
     * @throws \Exception
     */
    #[Test]
    public function it_does_not_prevent_executing_a_protected_command_if_behind_a_vpn(): void
    {
        $this->listener->protect($this->command);
        $this->VPNDetector->shouldReceive('isBehindVpn')->andReturn(true);

        $return = $this->application->run(new StringInput('test'), $this->output);

        self::assertEquals(Command::SUCCESS, $return);
        self::assertCount(0, $this->output->lines);
    }

    /**
     * @throws \Exception
     */
    #[Test]
    public function it_does_not_prevent_executing_a_protected_command_if_overridden(): void
    {
        $this->listener->protect($this->command);

        $return = $this->application->run(new StringInput('test --skip-vpn'), $this->output);

        self::assertEquals(Command::SUCCESS, $return);
        self::assertCount(0, $this->output->lines);
    }

    /**
     * @throws \Exception
     */
    #[Test]
    public function it_does_not_prevent_executing_a_non_protected_command(): void
    {
        $return = $this->application->run(new StringInput('test'), $this->output);

        self::assertEquals(Command::SUCCESS, $return);
        self::assertCount(0, $this->output->lines);
    }
}

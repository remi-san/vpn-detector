<?php

declare(strict_types=1);

namespace VPNDetector\Command\Listener;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Extended\Command\Listener\ProtectiveConsoleCommandEventListener;
use Symfony\Component\Console\Input\InputOption;
use VPNDetector\Exception\IPAddressResolvingException;
use VPNDetector\VPNDetector;

final class VPNProtectedConsoleCommandEventListener extends ProtectiveConsoleCommandEventListener
{
    private const OPTION_SKIP = 'skip-vpn';

    public function __construct(private readonly VPNDetector $VPNDetector)
    {
        parent::__construct('You must be behind a VPN to run this command. Execution cancelled.');
    }

    protected function configureCommand(Command $command): void
    {
        $command->addOption(
            self::OPTION_SKIP,
            null,
            InputOption::VALUE_NONE,
            'Skip VPN detection.'
        );
    }

    /**
     * @throws IPAddressResolvingException
     */
    protected function mustPreventCommandExecution(ConsoleCommandEvent $event): bool
    {
        return $event->getInput()->getOption('skip-vpn') === false && !$this->VPNDetector->isBehindVpn();
    }
}

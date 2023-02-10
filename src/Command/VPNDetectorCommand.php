<?php

declare(strict_types=1);

namespace VPNDetector\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use VPNDetector\Exception\IPAddressResolvingException;
use VPNDetector\VPNDetector;

#[AsCommand(name: self::NAME, description: 'Detect if you are behind a VPN')]
final class VPNDetectorCommand extends Command
{
    public const NAME = 'vpn:detect';

    public function __construct(
        private readonly VPNDetector $vpnDetector
    ) {
        parent::__construct();
    }

    /**
     * @throws IPAddressResolvingException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln(
            $this->vpnDetector->isBehindVpn() ?
                '<info>You are behind a VPN</info>' :
                '<error>You are not behind a VPN</error>'
        );

        return Command::SUCCESS;
    }

    protected function configure(): void
    {
    }
}

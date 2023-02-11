<?php

declare(strict_types=1);

namespace VPNDetector\Command;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use VPNDetector\Builder\IPAddressResolver\IPAddressResolvers;
use VPNDetector\Builder\IPAddressResolverFactory;
use VPNDetector\Builder\VPNDetectorBuilder;
use VPNDetector\Command\ParametersHelper\IPResolverOptionHelper;
use VPNDetector\Command\ParametersHelper\IPResolverOptionsOptionHelper;
use VPNDetector\Exception\IPAddressResolvingException;

#[AsCommand(name: self::NAME, description: 'Detect if you are behind a VPN')]
final class VPNDetectorCommand extends Command
{
    public const NAME = 'detect';

    public function __construct(
        private readonly IPAddressResolverFactory $ipAddressResolverFactory,
        private readonly VPNDetectorBuilder $vpnDetectorBuilder,
        private readonly string $defaultResolver = IPAddressResolvers::FIXED,
        private readonly LoggerInterface $logger = new NullLogger()
    ) {
        parent::__construct();
    }

    /**
     * @throws IPAddressResolvingException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $options      = IPResolverOptionsOptionHelper::getOptions($input);
        $resolverName = IPResolverOptionHelper::getResolverName($input) ?? $this->defaultResolver;

        $this->logger->info('Using IP resolver', ['resolver' => $resolverName, 'options'  => $options]);

        $resolver     = $this->ipAddressResolverFactory->build($resolverName)->withOptions($options)->build();
        $this->vpnDetectorBuilder->withLocalIPAddressResolver($resolver);

        $output->writeln(
            $this->vpnDetectorBuilder->build()->isBehindVpn() ?
                '<info>You are behind a VPN</info>' :
                '<error>You are not behind a VPN</error>'
        );

        return Command::SUCCESS;
    }

    protected function configure(): void
    {
        $this
            ->setDefinition(
                new InputDefinition([
                    ...IPResolverOptionsOptionHelper::optionsDefinition(),
                    ...IPResolverOptionHelper::optionsDefinition(),
                ])
            );
    }
}

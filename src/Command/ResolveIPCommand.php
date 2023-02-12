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
use VPNDetector\Builder\IPAddressResolverFactory;
use VPNDetector\Command\ParametersHelper\ConfigFileOptionHelper;
use VPNDetector\Command\ParametersHelper\IPResolverOptionHelper;
use VPNDetector\Command\ParametersHelper\LocalRemoteArgumentHelper;
use VPNDetector\Exception\IPAddressResolvingException;

#[AsCommand(name: self::NAME, description: 'Get your IP address')]
final class ResolveIPCommand extends Command
{
    public const NAME = 'ip';

    public function __construct(
        private readonly IPAddressResolverFactory $ipAddressResolverFactory,
        private readonly LoggerInterface $logger = new NullLogger()
    ) {
        parent::__construct();
    }

    /**
     * @throws IPAddressResolvingException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $factory           = ConfigFileOptionHelper::getFactory($input)      ?? $this->ipAddressResolverFactory;
        $resolverType      = LocalRemoteArgumentHelper::getType($input);
        $localResolverName = match ($resolverType) {
            LocalRemoteArgumentHelper::TYPE_LOCAL  => IPResolverOptionHelper::getResolverName($input) ?? $factory->getDefaultLocalResolver(),
            LocalRemoteArgumentHelper::TYPE_REMOTE => $factory->getDefaultRemoteResolver(),
            default                                => throw new \InvalidArgumentException('Invalid type'),
        };

        $this->logger->info('Using IP resolver', ['resolverType' => $resolverType, 'resolver' => $localResolverName]);

        $resolver = $factory
            ->getResolverBuilderFor($localResolverName)
            ->build();

        $output->writeln(sprintf('Your <info>%s</> IP address is: <info>%s</>', $resolverType, $resolver->resolveIpAddress()));

        return Command::SUCCESS;
    }

    protected function configure(): void
    {
        $this
            ->setDefinition(
                new InputDefinition([
                    ...LocalRemoteArgumentHelper::optionsDefinition(),
                    ...IPResolverOptionHelper::optionsDefinition(),
                    ...ConfigFileOptionHelper::optionsDefinition(),
                ])
            );
    }
}

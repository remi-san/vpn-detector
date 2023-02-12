<?php

declare(strict_types=1);

namespace VPNDetector\Command\ParametersHelper;

use Assert\Assert;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use VPNDetector\Builder\IPAddressResolverFactory;

final class ConfigFileOptionHelper
{
    private const ARG_CONFIG_FILE = 'config';

    /**
     * @return array<InputOption>
     */
    public static function optionsDefinition(): array
    {
        return [
            new InputOption(self::ARG_CONFIG_FILE, null, InputOption::VALUE_REQUIRED, 'The config file to load', null),
        ];
    }

    public static function getFactory(InputInterface $input): ?IPAddressResolverFactory
    {
        $configFile = $input->getOption(self::ARG_CONFIG_FILE);
        Assert::that($configFile)->nullOr()->string();

        if ($configFile === null) {
            return null;
        }

        if (!file_exists($configFile)) {
            throw new \InvalidArgumentException(sprintf('The given config file "%s" does not exist', $configFile));
        }

        return (require $configFile)();
    }
}

<?php

declare(strict_types=1);

namespace VPNDetector\Command\ParametersHelper;

use Assert\Assert;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use VPNDetector\Builder\IPAddressResolver\IPAddressResolvers;

final class IPResolverOptionHelper
{
    private const ARG_RESOLVER = 'resolver';

    private const ALLOWED_RESOLVERS = [
        IPAddressResolvers::FIXED,
        IPAddressResolvers::LIVEBOX,
    ];

    /**
     * @return array<InputOption>
     */
    public static function optionsDefinition(): array
    {
        return [
            new InputOption(
                self::ARG_RESOLVER,
                null,
                InputOption::VALUE_REQUIRED,
                sprintf('The IP resolver to use [ <info>%s</> ]', implode('</> | <info>', self::ALLOWED_RESOLVERS)),
                null,
                self::ALLOWED_RESOLVERS
            ),
        ];
    }

    public static function getResolverName(InputInterface $input): ?string
    {
        /** @var ?string $resolverName */
        $resolverName = $input->getOption(self::ARG_RESOLVER);
        if ($resolverName === null) {
            return null;
        }

        Assert::that($resolverName)
            ->string()
            ->inArray(self::ALLOWED_RESOLVERS);

        return $resolverName;
    }
}

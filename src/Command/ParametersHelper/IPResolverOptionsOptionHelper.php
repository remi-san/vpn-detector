<?php

declare(strict_types=1);

namespace VPNDetector\Command\ParametersHelper;

use Assert\Assert;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use VPNDetector\IPAddressResolver;

final class IPResolverOptionsOptionHelper
{
    private const ARG_IP_ADDRESS        = 'fixed-ip';
    private const ARG_RESOLVER_URL      = 'url';
    private const ARG_RESOLVER_USERNAME = 'username';
    private const ARG_RESOLVER_PASSWORD = 'password';

    /**
     * @return array<InputOption>
     */
    public static function optionsDefinition(): array
    {
        return [
            new InputOption(self::ARG_IP_ADDRESS, null, InputOption::VALUE_REQUIRED, 'The IP address from your ISP', null),
            new InputOption(self::ARG_RESOLVER_URL, null, InputOption::VALUE_REQUIRED, 'The selected resolver URL', null),
            new InputOption(self::ARG_RESOLVER_USERNAME, null, InputOption::VALUE_REQUIRED, 'The selected resolver username', null),
            new InputOption(self::ARG_RESOLVER_PASSWORD, null, InputOption::VALUE_REQUIRED, 'The selected resolver password', null),
        ];
    }

    /**
     * @return array{ip: string|null, url: string|null, username: string|null, password: string|null}
     */
    public static function getOptions(InputInterface $input): array
    {
        return [
            IPAddressResolver::IP_PARAM       => self::getOption($input, self::ARG_IP_ADDRESS),
            IPAddressResolver::URL_PARAM      => self::getOption($input, self::ARG_RESOLVER_URL),
            IPAddressResolver::USER_PARAM     => self::getOption($input, self::ARG_RESOLVER_USERNAME),
            IPAddressResolver::PASSWORD_PARAM => self::getOption($input, self::ARG_RESOLVER_PASSWORD),
        ];
    }

    private static function getOption(InputInterface $input, string $optionName): ?string
    {
        $option = $input->getOption($optionName);
        Assert::that($option)->nullOr()->string();

        return $option;
    }
}

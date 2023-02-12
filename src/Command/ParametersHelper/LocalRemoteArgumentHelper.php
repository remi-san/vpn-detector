<?php

declare(strict_types=1);

namespace VPNDetector\Command\ParametersHelper;

use Assert\Assert;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;

final class LocalRemoteArgumentHelper
{
    private const ARG_LOCAL_REMOTE = 'local-remote';

    public const TYPE_LOCAL        = 'local';
    public const TYPE_REMOTE       = 'remote';

    private const ALLOWED_VALUES = [
        self::TYPE_LOCAL,
        self::TYPE_REMOTE,
    ];

    /**
     * @return array<InputArgument>
     */
    public static function optionsDefinition(): array
    {
        return [
            new InputArgument(
                self::ARG_LOCAL_REMOTE,
                InputArgument::OPTIONAL,
                sprintf('The type of resolver to use [ <info>%s</> ]', implode('</> | <info>', self::ALLOWED_VALUES)),
                self::TYPE_REMOTE,
                self::ALLOWED_VALUES
            ),
        ];
    }

    public static function getType(InputInterface $input): string
    {
        $type = $input->getArgument(self::ARG_LOCAL_REMOTE);
        Assert::that($type)->string();

        if (!\in_array($type, self::ALLOWED_VALUES, true)) {
            throw new \InvalidArgumentException(sprintf('The given type "%s" is not allowed', $type));
        }

        return $type;
    }
}

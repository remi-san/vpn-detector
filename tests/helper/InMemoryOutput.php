<?php

declare(strict_types=1);

namespace VPNDetector\Tests\Helper;

use Symfony\Component\Console\Formatter\OutputFormatterInterface;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\ConsoleSectionOutput;
use Symfony\Component\Console\Output\OutputInterface;

final class InMemoryOutput implements ConsoleOutputInterface
{
    /**
     * @param array<string> $lines
     */
    public function __construct(public array $lines = [])
    {
    }

    public function reset(): void
    {
        $this->lines = [];
    }

    public function getErrorOutput(): OutputInterface
    {
        return $this;
    }

    public function setErrorOutput(OutputInterface $error): void
    {
    }

    public function section(): ConsoleSectionOutput
    {
        return \Mockery::spy(ConsoleSectionOutput::class);
    }

    public function write(iterable|string $messages, bool $newline = false, int $options = 0): void
    {
        $messages = $this->formatMessages($messages);

        $this->lines[] = $messages;
    }

    public function writeln(iterable|string $messages, int $options = 0): void
    {
        $messages = $this->formatMessages($messages);

        $this->lines[] = $messages.\PHP_EOL;
    }

    public function setVerbosity(int $level): void
    {
    }

    public function getVerbosity(): int
    {
        return OutputInterface::VERBOSITY_VERY_VERBOSE;
    }

    public function isQuiet(): bool
    {
        return false;
    }

    public function isVerbose(): bool
    {
        return true;
    }

    public function isVeryVerbose(): bool
    {
        return true;
    }

    public function isDebug(): bool
    {
        return true;
    }

    public function setDecorated(bool $decorated): void
    {
    }

    public function isDecorated(): bool
    {
        return false;
    }

    public function setFormatter(OutputFormatterInterface $formatter): void
    {
    }

    public function getFormatter(): OutputFormatterInterface
    {
        return \Mockery::spy(OutputFormatterInterface::class);
    }

    /**
     * @param iterable<string>|string $messages
     */
    public function formatMessages(iterable|string $messages): string
    {
        if (is_iterable($messages)) {
            if (!\is_array($messages)) {
                $messages = iterator_to_array($messages);
            }
            $messages = implode(\PHP_EOL, $messages);
        }

        return $messages;
    }
}

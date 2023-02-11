<?php

declare(strict_types=1);

namespace VPNDetector\Builder\IPAddressResolver;

trait OptionsTrait
{
    /** @var array<string, ?string> */
    private array $options = [];

    /**
     * @param array<string, ?string> $options
     */
    public function withOptions(array $options): self
    {
        $this->options = [...$this->options, ...$options];

        return $this;
    }
}

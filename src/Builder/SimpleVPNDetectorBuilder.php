<?php

declare(strict_types=1);

namespace VPNDetector\Builder;

use VPNDetector\IPAddressResolver;
use VPNDetector\LocalRemoteVPNDetector;
use VPNDetector\VPNDetector;

final class SimpleVPNDetectorBuilder implements VPNDetectorBuilder
{
    private IPAddressResolver $localIPAddressResolver;
    private IPAddressResolver $remoteIPAddressResolver;

    public function withLocalIPAddressResolver(IPAddressResolver $localIPAddressResolver): self
    {
        $this->localIPAddressResolver = $localIPAddressResolver;

        return $this;
    }

    public function withRemoteIPAddressResolver(IPAddressResolver $remoteIPAddressResolver): self
    {
        $this->remoteIPAddressResolver = $remoteIPAddressResolver;

        return $this;
    }

    public function build(): VPNDetector
    {
        if (!isset($this->localIPAddressResolver)) {
            throw new \InvalidArgumentException('You must provide a local IP address resolver.');
        }

        if (!isset($this->remoteIPAddressResolver)) {
            throw new \InvalidArgumentException('You must provide a remote IP address resolver.');
        }

        return LocalRemoteVPNDetector::build(
            $this->localIPAddressResolver,
            $this->remoteIPAddressResolver
        );
    }
}

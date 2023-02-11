<?php

declare(strict_types=1);

namespace VPNDetector\Builder;

use VPNDetector\IPAddressResolver;
use VPNDetector\VPNDetector;

interface VPNDetectorBuilder
{
    public function withLocalIPAddressResolver(IPAddressResolver $localIPAddressResolver): self;

    public function withRemoteIPAddressResolver(IPAddressResolver $remoteIPAddressResolver): self;

    public function build(): VPNDetector;
}

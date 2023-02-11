<?php

declare(strict_types=1);

namespace VPNDetector;

use VPNDetector\Exception\IPAddressResolvingException;

interface IPAddressResolver
{
    public const URL_PARAM      = 'url';
    public const USER_PARAM     = 'username';
    public const PASSWORD_PARAM = 'password';
    public const IP_PARAM       = 'ip';

    /**
     * @throws IPAddressResolvingException
     */
    public function resolveIpAddress(): IPAddress;
}

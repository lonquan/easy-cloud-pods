<?php

declare(strict_types=1);

namespace AntCool\CloudPods\Interfaces;

interface CacheInterface extends \Psr\SimpleCache\CacheInterface
{
    public function getEndpointKey(): string;

    public function getAccessTokenKey(): string;
}

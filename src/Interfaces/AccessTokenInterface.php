<?php
declare(strict_types=1);

namespace AntCool\CloudPods\Interfaces;

interface AccessTokenInterface
{
    public function getToken(): string;
}

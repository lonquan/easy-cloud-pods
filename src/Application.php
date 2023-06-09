<?php

declare(strict_types=1);

namespace AntCool\CloudPods;

use AntCool\CloudPods\Interfaces\CacheInterface;
use AntCool\CloudPods\Kernel\Client;
use AntCool\CloudPods\Kernel\Config;
use AntCool\CloudPods\Kernel\Utils;
use AntCool\CloudPods\Support\Cache;
use AntCool\CloudPods\Support\Logger;
use Throwable;

class Application
{
    protected Config $config;

    protected Client $client;

    // protected Server $server;

    protected Utils $utils;

    protected CacheInterface $cache;

    protected ?Logger $logger = null;

    /**
     * @throws Exceptions\InvalidArgumentException
     */
    public function __construct(array|Config $config)
    {
        $this->config = is_array($config) ? new Config($config) : $config;

        if ($this->config->get('debug', false)) {
            $this->logger = new Logger($this->config);
        }

        $this->cache = new Cache($this->config, $this->logger);
    }

    public function getConfig(): Config
    {
        return $this->config;
    }

    /**
     * @throws Throwable
     */
    public function getClient(): Client
    {
        return $this->client ?? $this->client = new Client($this->config, $this->cache, $this->logger);
    }

    public function getUtils(): Utils
    {
        return $this->utils ?? $this->utils = new Utils();
    }
}

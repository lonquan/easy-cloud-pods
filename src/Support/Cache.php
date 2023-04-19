<?php

declare(strict_types=1);

namespace AntCool\CloudPods\Support;

use AntCool\CloudPods\Interfaces\CacheInterface;
use AntCool\CloudPods\Kernel\Config;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Psr16Cache;

class Cache extends Psr16Cache implements CacheInterface
{
    public function __construct(protected Config $config, protected ?Logger $logger)
    {
        parent::__construct(new FilesystemAdapter(
            namespace: 'cloud-pods',
            defaultLifetime: 0,
            directory: $this->config->get('runtime_path', '/tmp/cloud-pods') . '/cache/'
        ));
    }

    /**
     * @throws \Throwable
     */
    public function getEndpointKey(): string
    {
        return sprintf('endpoint_%s', $this->config->getProjectId());
    }

    /**
     * @throws \Throwable
     */
    public function getAccessTokenKey(): string
    {
        return sprintf('token_%s', $this->config->getProjectId());
    }
}

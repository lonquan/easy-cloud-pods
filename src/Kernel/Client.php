<?php

declare(strict_types=1);

namespace AntCool\CloudPods\Kernel;

use AntCool\CloudPods\Exceptions\InvalidArgumentException;
use AntCool\CloudPods\Middleware\AccessTokenMiddleware;
use AntCool\CloudPods\Support\AccessToken;
use AntCool\CloudPods\Support\Logger;
use AntCool\CloudPods\Traits\InteractWithHttpClient;
use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\HandlerStack;
use AntCool\CloudPods\Interfaces\CacheInterface;

class Client
{
    use InteractWithHttpClient;

    protected array $endpoints;

    protected string $currentBaseUri;

    /**
     * @throws \Throwable
     */
    public function __construct(protected Config $config, protected CacheInterface $cache, protected ?Logger $logger)
    {
        $this->initEndpoints();
        $this->createHttp();
    }

    public function withHandleStacks(): HandlerStack
    {
        $stack = new HandlerStack();
        $stack->setHandler(new CurlHandler());
        $stack->push(new AccessTokenMiddleware($this->config, $this->cache, $this->logger));
        $this->withRequestLogMiddleware($stack);

        return $stack;
    }

    public function getEndpoints(): array
    {
        return $this->endpoints;
    }

    /**
     * @throws \Throwable
     */
    public function withService(string $name): static
    {
        $endpoint = $this->endpoints[$name] ?? null;

        if (is_null($endpoint)) {
            throw new InvalidArgumentException('The endpoint of the service was not found');
        }

        $this->currentBaseUri = sprintf('%s/%s', $this->config->getProjectGateway(), $this->config->getEndpointPath($endpoint['type']));

        return $this;
    }

    /**
     * @throws \Throwable
     */
    protected function initEndpoints()
    {
        $this->endpoints = (new AccessToken($this->config, $this->cache, $this->logger))->getEndpoints();
    }
}

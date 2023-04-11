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

    protected ?string $serviceName;

    protected ?array $serviceEndpoint;

    protected array $endpoints;

    public function __construct(protected Config $config, protected CacheInterface $cache, protected ?Logger $logger)
    {
        $this->createHttp();

        $this->initEndpoints();
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

    public function withService(string $serviceName): static
    {
        $this->serviceName = $serviceName;
        $this->serviceEndpoint = $this->endpoints[$this->serviceName] ?? null;

        if (is_null($this->serviceEndpoint)) {
            throw new InvalidArgumentException('The endpoint of the service was not found');
        }

        $this->createHttp($this->serviceEndpoint['url'], true);

        return $this;
    }

    public function withoutService(): static
    {
        return $this;
    }

    protected function initEndpoints()
    {
        $this->endpoints = (new AccessToken($this->config, $this->cache, $this->logger))->getEndpoints();
    }
}

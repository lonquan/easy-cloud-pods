<?php

declare(strict_types=1);

namespace AntCool\CloudPods\Middleware;

use AntCool\CloudPods\Interfaces\AccessTokenInterface;
use AntCool\CloudPods\Kernel\Config;
use AntCool\CloudPods\Support\AccessToken;
use AntCool\CloudPods\Support\Logger;
use Psr\Http\Message\RequestInterface;
use AntCool\CloudPods\Interfaces\CacheInterface;

class AccessTokenMiddleware
{
    protected AccessTokenInterface $accessToken;

    public function __construct(protected Config $config, protected CacheInterface $cache, protected ?Logger $logger)
    {
        $customAccessToken = $this->config->get('access_token', false);
        $class = $customAccessToken && class_exists($customAccessToken) ? $customAccessToken : AccessToken::class;

        $this->accessToken = new ($class)($this->config, $this->cache, $this->logger);
    }

    public function __invoke(callable $handler): callable
    {
        return function (RequestInterface $request, array $options) use ($handler) {
            $request = $request->withHeader('X-Auth-Token', $this->accessToken->getToken());

            return $handler($request, $options);
        };
    }
}

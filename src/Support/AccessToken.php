<?php

declare(strict_types=1);

namespace AntCool\CloudPods\Support;

use AntCool\CloudPods\Exceptions\InvalidArgumentException;
use AntCool\CloudPods\Exceptions\ResponseInvalidException;
use AntCool\CloudPods\Interfaces\AccessTokenInterface;
use AntCool\CloudPods\Traits\InteractWithHttpClient;
use GuzzleHttp\Exception\GuzzleException;
use AntCool\CloudPods\Kernel\Config;
use JetBrains\PhpStorm\ArrayShape;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Psr16Cache;
use AntCool\CloudPods\Interfaces\CacheInterface;

class AccessToken implements AccessTokenInterface
{
    use InteractWithHttpClient;

    public function __construct(protected Config $config, protected CacheInterface $cache, protected ?Logger $logger)
    {
    }

    /**
     * @throws \Throwable
     */
    public function getToken(): string
    {
        if ($token = $this->cache->get($this->cache->getAccessTokenKey(), false)) {
            return $token['id'];
        }

        return $this->requestAuthUrl()['token'];
    }

    /**
     * @throws \Throwable
     */
    public function getEndpoints()
    {
        if ($endpoints = $this->cache->get($this->cache->getEndpointKey(), false)) {
            return $endpoints;
        }

        return $this->requestAuthUrl()['endpoints'];
    }

    /**
     * @throws \Throwable
     */
    #[ArrayShape(['token' => "mixed", 'endpoints' => "mixed"])]
    protected function requestAuthUrl(): array
    {
        $config = $this->config->get('current');

        $auth = [
            'auth' => match ($config['auth_type']) {
                'password' => $this->getPasswordAuth($config),
                default => throw new InvalidArgumentException('Auth type is not supported'),
            },
        ];

        $response = $this->createHttp()->postJson(
            sprintf('%s/%s/auth/tokens', $this->config->getProjectGateway(), $this->config->getEndpointPath('identity')),
            $auth
        );

        if (empty($response['token']['catalog'])) {
            throw new ResponseInvalidException('The response has no catalog field');
        }

        $endpoints = array_reduce(
            $response['token']['catalog'],
            function ($carry, $item) {
                $carry[$item['type']] = ['id' => $item['id'], 'name' => $item['name'], 'type' => $item['type']];

                return $carry;
            },
            []
        );

        // endpoints
        $this->cache->set($this->cache->getEndpointKey(), $endpoints);

        // token
        $this->cache->set($this->cache->getAccessTokenKey(), $response, \DateInterval::createFromDateString('23 hours 59 minutes'));

        return [
            'token'     => $response['id'],
            'endpoints' => $endpoints,
        ];
    }

    protected function getPasswordAuth(array $config): array
    {
        $auth = [];
        $auth['identity'] = [
            'methods'  => ['password'],
            'password' => [
                'user' => [
                    'name'     => $config['user_name'] ?? '',
                    'password' => $config['user_password'] ?? '',
                    'domain'   => ($config['domain'] ?? false) ? ['name' => $config['domain']] : ['id' => 'default'],
                ],
            ],
        ];

        if ($config['project_id'] ?? false) {
            $auth['scope']['project']['id'] = $config['project_id'];
        }

        if ($config['project_name'] ?? false) {
            $auth['scope']['project']['name'] = $config['project_name'];
            $auth['scope']['project']['domain'] = ['name' => $config['project_name']];
        }

        return $auth;
    }
}

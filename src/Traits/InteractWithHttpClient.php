<?php

declare(strict_types=1);

namespace AntCool\CloudPods\Traits;

use AntCool\CloudPods\Middleware\RequestLogMiddleware;
use AntCool\CloudPods\Support\File;
use GuzzleHttp\Client as Http;
use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\HandlerStack;
use AntCool\CloudPods\Exceptions\ResponseInvalidException;

trait InteractWithHttpClient
{
    protected Http $http;

    /**
     * @throws \Throwable
     */
    public function getJson(string $uri, array $query = []): array
    {
        return $this->request(method: 'GET', uri: $uri, options: ['query' => $query]);
    }

    /**
     * @throws \Throwable
     */
    public function postJson(string $uri, array $data = [], array $query = []): array
    {
        return $this->request(method: 'POST', uri: $uri, options: [
            'query' => $query,
            'json'  => $data,
        ]);
    }

    /**
     * @throws \Throwable
     */
    public function deleteJson(string $uri, array $data = [], array $query = []): array
    {
        return $this->request(method: 'DELETE', uri: $uri, options: [
            'query' => $query,
            'json'  => $data,
        ]);
    }

    /**
     * @throws \Throwable
     */
    public function uploadFile(string $uri, File $file, array $data = [], array $query = []): array
    {
        return $this->request(method: 'POST', uri: $uri, options: [
            'query'     => $query,
            'multipart' => $this->buildForm($file, $data),
        ]);
    }

    /**
     * @throws \Throwable
     */
    public function request(string $method, string $uri, $options = []): array
    {
        $uri = empty($this->currentBaseUri) ? $uri : sprintf('%s/%s', $this->currentBaseUri, ltrim($uri, '/'));
        $response = $this->http->request($method, $uri, $options);
        $status = $response->getStatusCode();
        $body = $response->getBody();
        $body->rewind();
        $response = $body->getContents();

        if ($status < 200 || $status > 299) {
            throw new ResponseInvalidException($response, $status);
        }

        return json_decode($response, true);
    }

    protected function buildForm(File $file, array $data): array
    {
        $form = [];

        foreach ($data as $key => $value) {
            $form[] = ['name' => $key, 'contents' => $value];
        }

        $form[] = ['name' => 'file', 'contents' => $file->getContents()];

        return $form;
    }

    protected function createHttp(string $baseUri = null, bool $refresh = false): self
    {
        if (empty($this->http) || $refresh) {
            $this->http = new Http([
                'base_uri' => $baseUri ?: null,
                'timeout'  => data_get($this->config, 'http.timeout', 30),
                'verify'   => data_get($this->config, 'http.verify', true),
                'handler'  => $this->withHandleStacks(),
                'headers'  => [
                    'User-Agent'     => 'Easy-Cloud-Pods',
                    'Content-Length' => '', // The field is required
                ],
            ]);
        }

        return $this;
    }

    protected function withHandleStacks(): HandlerStack
    {
        $stack = new HandlerStack();
        $stack->setHandler(new CurlHandler());
        $this->withRequestLogMiddleware($stack);

        return $stack;
    }

    protected function withRequestLogMiddleware(HandlerStack $stock): void
    {
        if ($this->config->get('debug', false)) {
            $stock->push(new RequestLogMiddleware($this->config, $this->logger));
        }
    }
}

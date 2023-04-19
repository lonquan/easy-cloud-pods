<?php

declare(strict_types=1);

namespace AntCool\CloudPods\Kernel;

use AntCool\CloudPods\Exceptions\InvalidArgumentException;
use AntCool\CloudPods\Traits\HasAttributes;
use Illuminate\Support\Collection;

class Config extends Collection
{
    protected array $requiredKeys = [
        'default',
        'projects',
    ];

    protected array $projectRequiredKeys = [
        'api_gateway',
        'auth_type',
        'project_id',
    ];

    /**
     * @param  array  $attributes
     * @throws InvalidArgumentException
     */
    public function __construct(array $attributes)
    {
        $this->checkMissingKeys($this->requiredKeys, $attributes);
        parent::__construct($attributes);
        $this->useProject($this->get('default'));
    }

    /**
     * @param  string  $project
     * @return $this
     * @throws InvalidArgumentException
     */
    public function useProject(string $project): static
    {
        $config = data_get($this, 'projects.' . $project, []);

        $this->checkMissingKeys($this->projectRequiredKeys, $config);

        $this->offsetSet('current', $config);

        return $this;
    }

    /**
     * @throws \Throwable
     */
    public function getProjectId(): string
    {
        if ($id = data_get($this, 'current.project_id')) {
            return $id;
        }

        throw new InvalidArgumentException('The project id does not exist');
    }

    /**
     * @throws \Throwable
     */
    public function getProjectGateway(): string
    {
        if ($value = data_get($this, 'current.api_gateway')) {
            return rtrim($value, '/');
        }

        throw new InvalidArgumentException('The api gateway does not exist');
    }

    public function getEndpointPath(string $endpoint): string
    {
        if ($path = data_get($this, 'endpoint_path.' . $endpoint, false)) {
            return rtrim($path, '/');
        }

        return $endpoint;
    }

    /**
     * @param  array  $keys
     * @param  array  $values
     * @return bool
     * @throws InvalidArgumentException
     */
    public function checkMissingKeys(array $keys, array $values): bool
    {
        if (empty($keys)) {
            return true;
        }

        $missingKeys = [];

        foreach ($keys as $key) {
            if (!isset($values[$key])) {
                $missingKeys[] = $key;
            }
        }

        if (!empty($missingKeys)) {
            throw new InvalidArgumentException(sprintf("\"%s\" cannot be empty.", join(',', $missingKeys)));
        }

        return true;
    }
}

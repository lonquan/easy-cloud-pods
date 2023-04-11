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
        'base_uri',
        'project_id',
        'auth_type',
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
     * @param  string  $projectName
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

    public function getProjectId(): string
    {
        $id = data_get($this, 'current.project_id');

        if ($id) {
            return $id;
        }

        throw new InvalidArgumentException('The project id does not exist');
    }

    /**
     * @param  array  $keys
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

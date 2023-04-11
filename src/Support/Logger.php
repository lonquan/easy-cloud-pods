<?php

declare(strict_types=1);

namespace AntCool\CloudPods\Support;

use AntCool\CloudPods\Kernel\Config;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger as MonologLogger;

class Logger
{
    protected MonologLogger $logger;

    public function __construct(protected Config $config)
    {
        $this->logger = new MonologLogger('CloudPods');
        $this->logger->pushHandler(
            new RotatingFileHandler($this->config->get('runtime_path', '/tmp/cloud-pods') . '/logs/cloud-pods.log', 30)
        );
    }

    public function __call(string $name, array $arguments)
    {
        call_user_func_array([$this->logger, $name], $arguments);
    }
}

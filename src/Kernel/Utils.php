<?php

declare(strict_types=1);
namespace AntCool\CloudPods\Kernel;

class Utils
{
    /**
     * 获取 monitor 监控服务 unified monitor 的查询接口签名
     *
     * @param  array  $params
     * @return string
     */
    public function unifiedMonitorSign(array $params): string
    {
        $this->recursiveSortByKey($params);

        return \hash('sha256', \json_encode($params, JSON_UNESCAPED_UNICODE));
    }

    protected function recursiveSortByKey(&$array): bool
    {
        if (!\is_array($array)) {
            return false;
        }
        \ksort($array);
        foreach ($array as &$value) {
            if (\is_array($value)) {
                $this->recursiveSortByKey($value);
            }
        }

        return true;
    }
}
<h1 align="center"> EasyCloudPods - CloudPods PHP SDK </h1>

<p align="center"> PHP SDK for Cloud Pods.</p>
<p align="center">Overtrue! Respect!</p>

## 文档链接

- [CloudPods Docs](https://www.cloudpods.org/zh/docs/)
- [APIs](https://www.cloudpods.org/zh/docs/swagger/?&apiIdx=0#section/Authentication)

## 安装

```shell
$ composer require antcool/easy-cloud-pods -vvv
```

## 使用

### 配置项

```php
$config = [
    'default'  => 'production',
    'projects' => [
        'production' => [
            'base_uri'     => 'https://ip:30500/v3/',
            'project_id'   => '09dd****00c21a',
            'project_name' => null,

            'auth_type' => 'password', // ak/sk

            'key_id' => 'b32c*****60a',
            'secret' => 'YXVVVV********5UUzU=',

            'domain'        => null,
            'domain_name'   => null,
            'user_name'     => '*****',
            'user_password' => '%*****',
        ],
    ],

    'debug'        => true, // 开启会在 runtime_path/logs 下生成请求的日志
    'runtime_path' => storage_path('cloud-pods'),

    'http' => [
        'timeout' => 30,
        'verify'  => false,
    ],
];
```

### 创建实例

```php
use AntCool\CloudPods\Application;

$app = new Application(new Config(\config('cloudpods'));

// use other project config
$config = $app->getConfig()->useProject('project_name');

/** @var \AntCool\CloudPods\Kernel\Client $client */
$client = $app->getClient();
```

### API 调用示范

> \AntCool\CloudPods\Middleware\AccessTokenMiddleware 已经实现对 AuthToken 和 Endpoints 的自动处理

```php
// 查看 Endpoints
$client->getEndpoints();

// 获取支持的镜像
$client->withService('image-public')->getJson('images', ['limit' => 1000, 'details' => true])

// 获取主机规格
$client->withService('compute_v2-public')->getJson('serverskus', ['limit' => 1000, 'details' => true]);

// 创建秘钥对 
$client->withService('compute_v2-public')->postJson('keypairs', [
            'count'   => 1,
            'keypair' => [
                'description' => 'description',
                'name'        => 'name',
            ],
        ]);

// 创建虚拟机
$client->withService('compute_v2-public')->postJson('servers', $params);

// 删除虚拟机
$client->withService('compute_v2-public')->deleteJson('servers/' . $id, [
            'OverridePendingDelete' => true,
            'Purge'                 => true,
            'DeleteSnapshots'       => true,
            'DeleteEip'             => true,
            'DeleteDisks'           => true,
        ]);
```

## Contributing
You can contribute in one of three ways:
1. ...
2. ...

## License

MIT

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

**需开启统一API入口, [查看文档](https://www.cloudpods.org/zh/docs/development/apisdk/01_apigateway/#%E5%BC%80%E5%90%AF%E7%BB%9F%E4%B8%80api%E5%85%A5%E5%8F%A3)**

### 配置项

```php
$config = [
    'default'       => 'production',
    'projects'      => [
        'production' => [
            'api_gateway'  => 'https://<ip_or_domain_of_apigatway>/api/s/',
            'project_id'   => '****',
            'project_name' => null,

            'auth_type' => 'password', // ak/sk
            'user_name'     => '****',
            'user_password' => '****',
            'key_id' => '****',
            'secret' => '****',

            'domain'        => null,
            'domain_name'   => null,
           
        ],
    ],

    /**
     * 默认使用各 endpoint type 字段作为统一 API 入口请求时的服务类型路径
     * 由于每个版本的路径会有不同, 当默认路径错误时, 可通过此配置覆盖
     * compute_v2: compute_v2
     * image: image/v1
     * identity: identity/v3
     */
    'endpoint_path' => [
        'image'    => 'image/v1',
        'identity' => 'identity/v3',
    ],

    'debug'        => true,
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
$client->withService('image')->getJson('images', ['limit' => 1000, 'details' => true])
// 实际请求 URL: https://<gateway>/api/s/image/v1/images?limit=1000&details=1


// 获取主机规格
$client->withService('compute_v2')->getJson('serverskus', ['limit' => 1000, 'details' => true]);

// 创建秘钥对 
$client->withService('compute_v2')->postJson('keypairs', [
            'count'   => 1,
            'keypair' => [
                'description' => 'description',
                'name'        => 'name',
            ],
        ]);

// 创建虚拟机
$client->withService('compute_v2')->postJson('servers', $params);

// 删除虚拟机
$client->withService('compute_v2')->deleteJson('servers/' . $id, [
            'OverridePendingDelete' => true,
            'Purge'                 => true,
            'DeleteSnapshots'       => true,
            'DeleteEip'             => true,
            'DeleteDisks'           => true,
        ]);


// 获取 monitor 监控服务 unified monitor 的查询接口签名
$app->getUtils()->unifiedMonitorSign($params);
```

## Contributing

You can contribute in one of three ways:

1. ...
2. ...

## License

MIT

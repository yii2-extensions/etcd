<!-- markdownlint-disable MD041 -->
<p align="center">
    <picture>
        <source media="(prefers-color-scheme: dark)" srcset="https://www.yiiframework.com/image/design/logo/yii3_full_for_dark.svg">
        <source media="(prefers-color-scheme: light)" srcset="https://www.yiiframework.com/image/design/logo/yii3_full_for_light.svg">
        <img src="https://www.yiiframework.com/image/design/logo/yii3_full_for_dark.svg" alt="Yii Framework" width="80%">
    </picture>
    <h1 align="center">etcd</h1>
    <br>
</p>
<!-- markdownlint-enable MD041 -->

<p align="center">
    <a href="https://github.com/yii2-extensions/etcd/actions/workflows/build.yml" target="_blank">
        <img src="https://img.shields.io/github/actions/workflow/status/yii2-extensions/etcd/build.yml?style=for-the-badge&logo=github&label=PHPUnit" alt="PHPUnit">
    </a>
    <a href="https://codecov.io/github/yii2-extensions/etcd" target="_blank">
        <img src="https://img.shields.io/codecov/c/github/yii2-extensions/etcd.svg?style=for-the-badge&logo=codecov&logoColor=white&label=Coverage" alt="CodeCoverage">
    </a>
    <a href="https://github.com/yii2-extensions/etcd/actions/workflows/static.yml" target="_blank">
        <img src="https://img.shields.io/github/actions/workflow/status/yii2-extensions/etcd/static.yml?style=for-the-badge&logo=github&label=PHPStan" alt="PHPStan">
    </a>
</p>


Interaction component with `etcd` (A distributed, reliable key-value store for the most critical data of a distributed system) for `Yii2 Framework`.

https://etcd.io

## Required

- PHP: >= 8.5
- `grpc`, `protobuf` - for RPC

## Install

```bash
composer require yii2-extensions/etcd "^2.0.0"
```

or add

```
"yii2-extensions/etcd": "^2.0.0"
```

to the require section of your composer.json file.

## Supported etcd API version

- v3

## Supported etcd methods

### Main
- `version`

### KV
- `range` (`getKey`, `getRange`)
- `put`
- `deleteRange`
- `txn`
- `compact`

### Auth
- `authenticate`
- `enable`
- `disable`
- `status`
- `userAdd`, `userGet`, `userList`, `userDelete`, `userChangePassword`
- `userGrantRole`, `userRevokeRole`
- `roleAdd`, `roleGet`, `roleList`, `roleDelete`
- `roleGrantPermission`, `roleRevokePermission`

### Cluster
- `memberAdd`
- `memberRemove`
- `memberUpdate`
- `memberList`
- `memberPromote`

### Maintenance
- `alarm`
- `status`
- `defragment`
- `hash`
- `hashKv`
- `moveLeader`
- `downgrade`

### Lease
- `grant`
- `revoke`
- `timeToLive`
- `leases`


## How to use

Configure

```php
$config = [
    'components' => [
        'etcd' => [
            'class' => \Yii2\Extensions\Etcd::class,
            'host' => 'etcd:2379',
            'user' => 'username',
            'password' => 'password',
        ],
    ],
];
```

Get key value
```php
Yii::$app->etcd->getKv()->getKey('hello')->firstKeyValue;

// Hello
```

Get etcd version

```php
Yii::$app->etcd->version;

// {"etcdserver":"3.5.8","etcdcluster":"3.5.0"}
```

### Domain services

Since `2.0.0` all etcd API methods are grouped by domain and reached through a sub-service:

```php
// KV
Yii::$app->etcd->getKv()->put('foo', 'bar');
Yii::$app->etcd->getKv()->getKey('foo')->firstKeyValue;
Yii::$app->etcd->getKv()->getRange('foo', 'fop');
Yii::$app->etcd->getKv()->deleteRange('foo', 'fop');
Yii::$app->etcd->getKv()->txn($compare, $success, $failure);
Yii::$app->etcd->getKv()->compact($revision);

// Auth
Yii::$app->etcd->getAuth()->authStatus();
Yii::$app->etcd->getAuth()->userAdd('alice', 'pw');
Yii::$app->etcd->getAuth()->roleGrantPermission('viewer', PermissionType::READ, '/foo', '/fop');

// Cluster
Yii::$app->etcd->getCluster()->memberList();

// Maintenance
Yii::$app->etcd->getMaintenance()->status();
Yii::$app->etcd->getMaintenance()->hash();

// Lease
Yii::$app->etcd->getLease()->leaseGrant(3600);
Yii::$app->etcd->getLease()->leaseRevoke($id);
```

### Typed responses

Since `2.0.0` the methods that return a non-scalar result return a typed response
object from `\Yii2\Extensions\Etcd\Responses\` (e.g. `DeleteRangeResponse`,
`TxnResponse`, `StatusResponse`, `LeaseGrantResponse`) instead of a plain array.
Nested structures stay arrays, top-level fields are exposed as properties:

```php
$result = Yii::$app->etcd->getKv()->deleteRange('foo', 'fop');
$result->deleted;      // number of deleted keys
$result->prevKvs;      // previous key-value pairs (when prev_kv is set)

$grant = Yii::$app->etcd->getLease()->leaseGrant(3600);
$grant->ID;            // new lease ID
```

### Switch between supported protocol

> etcd v3 uses gRPC for its messaging protocol. For languages with no gRPC support, etcd provides a JSON gRPC gateway. This gateway serves a RESTful proxy that translates HTTP/JSON requests into gRPC messages.


```php
$config = [
    'components' => [
        'etcd' => [
            'class' => \Yii2\Extensions\Etcd\Etcd::class,
            ...
            'protocol' => '\Yii2\Extensions\Etcd\EtcdProtocol::GRPC', // Default value \Yii2\Extensions\Etcd\EtcdProtocol::HTTP
        ],
    ],
];
```

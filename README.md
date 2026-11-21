<!-- markdownlint-disable MD041 -->
<p align="center">
    <picture>
        <source media="(prefers-color-scheme: dark)" srcset="https://www.yiiframework.com/image/design/logo/yii3_full_for_dark.svg">
        <source media="(prefers-color-scheme: light)" srcset="https://www.yiiframework.com/image/design/logo/yii3_full_for_light.svg">
        <img src="https://www.yiiframework.com/image/design/logo/yii3_full_for_light.svg" alt="Yii Framework" width="80%">
    </picture>
    <h1 align="center">etcd</h1>
    <br>
</p>
<!-- markdownlint-enable MD041 -->

<p align="center">
    <a href="https://github.com/yii2-extensions/etcd/actions/workflows/build.yml" target="_blank">
        <img src="https://img.shields.io/github/actions/workflow/status/yii2-extensions/etcd/build.yml?style=for-the-badge&label=PHPUnit&logo=github" alt="PHPUnit">
    </a>
    <a href="https://codecov.io/github/yii2-extensions/etcd" target="_blank">
        <img src="https://img.shields.io/codecov/c/github/yii2-extensions/etcd.svg?style=for-the-badge&logo=codecov&logoColor=white&label=Coverage" alt="Coverage">
    </a>
    <a href="https://github.com/yii2-extensions/etcd/actions/workflows/static.yml" target="_blank">
        <img src="https://img.shields.io/github/actions/workflow/status/yii2-extensions/etcd/static.yml?style=for-the-badge&label=PHPStan&logo=github" alt="PHPStan">
    </a>
    <a href="https://packagist.org/packages/yii2-extensions/etcd" target="_blank">
        <img src="https://img.shields.io/packagist/dt/yii2-extensions/etcd.svg?style=for-the-badge&logo=composer&logoColor=white&label=Downloads" alt="Downloads">
    </a>
</p>

<p align="center">
    <strong>Yii2 component for the <a href="https://etcd.io">etcd</a> v3 API</strong><br>
    <em>KV, Auth, Cluster, Maintenance and Lease services over the HTTP/JSON gateway or gRPC</em>
</p>

## Quick start

### Requirements

- An etcd server that exposes the v3 API.
- The `curl` and `grpc` PHP extensions, both required by `composer.json`.
- The `protobuf` PHP extension is optional; without it, the pure PHP `google/protobuf` runtime is used.

- PHP: >= 8.5

## Install

```bash
composer require yii2-extensions/etcd:^2.0
```

or add

```json
"yii2-extensions/etcd": "^2.0.0"
```

to the require section of your composer.json file.

## Supported etcd version

- etcd >= 3.7 (API v3)

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
            'class' => \Yii2\Extensions\Etcd\Etcd::class,
            'host' => 'etcd:2379',
            'user' => 'username',
            'password' => 'password',
        ],
    ],
];
```

### Basic usage

Get a key value:

```php
Yii::$app->etcd->getKv()->getKey('hello')->firstKeyValue;

// Hello
```

Get the etcd version:

```php
Yii::$app->etcd->version;

// {"etcdserver":"3.7.4","etcdcluster":"3.7.0","storage":"3.7.0"}
```

## Usage

### Domain services

Since `2.0.0` all etcd API methods are grouped by domain and reached through a sub-service:

```php
use Yii2\Extensions\Etcd\PermissionType;

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

Since `2.0.0` the methods that return a non-scalar result return a typed response object from
`\Yii2\Extensions\Etcd\Responses\` (for example `DeleteRangeResponse`, `TxnResponse`, `StatusResponse`,
`LeaseGrantResponse`) instead of a plain array. Nested structures stay arrays, top-level fields are exposed as
properties:

```php
$result = Yii::$app->etcd->getKv()->deleteRange('foo', 'fop');
$result->deleted;      // number of deleted keys
$result->prevKvs;      // previous key-value pairs (when prev_kv is set)

$grant = Yii::$app->etcd->getLease()->leaseGrant(3600);
$grant->ID;            // new lease ID
```

### Switch between supported protocols

> etcd v3 uses gRPC for its messaging protocol. For languages with no gRPC support, etcd provides a JSON gRPC gateway.
> This gateway serves a RESTful proxy that translates HTTP/JSON requests into gRPC messages.

The component uses the HTTP/JSON gateway by default (`EtcdProtocol::HTTP`). Set `protocol` to `EtcdProtocol::GRPC` to
use gRPC:

```php
$config = [
    'components' => [
        'etcd' => [
            'class' => \Yii2\Extensions\Etcd\Etcd::class,
            ...
            'protocol' => \Yii2\Extensions\Etcd\EtcdProtocol::GRPC, // Default value \Yii2\Extensions\Etcd\EtcdProtocol::HTTP
        ],
    ],
];
```

## Package information

[![PHP](https://img.shields.io/badge/%3E%3D8.5-777BB4.svg?style=for-the-badge&logo=php&logoColor=white)](https://www.php.net/releases/8.5/en.php)
[![Yii 2.0.55+](https://img.shields.io/badge/%3E%3D2.0.55-0073AA.svg?style=for-the-badge&logo=yii&logoColor=white)](https://github.com/yiisoft/yii2/releases/tag/2.0.55)

## Project status

[![PHPStan Level 8](https://img.shields.io/badge/PHPStan-Level%208-4F5D95.svg?style=for-the-badge&logo=github&logoColor=white)](https://github.com/yii2-extensions/etcd/actions/workflows/static.yml)
[![Quality](https://img.shields.io/github/actions/workflow/status/yii2-extensions/etcd/quality.yml?style=for-the-badge&label=Quality&logo=github)](https://github.com/yii2-extensions/etcd/actions/workflows/quality.yml)

## Security

[![OpenSSF Scorecard](https://api.scorecard.dev/projects/github.com/yii2-extensions/etcd/badge?style=for-the-badge)](https://scorecard.dev/viewer/?uri=github.com/yii2-extensions/etcd)
[![Zizmor](https://img.shields.io/github/actions/workflow/status/yii2-extensions/etcd/security.yml?style=for-the-badge&label=Zizmor&logo=github)](https://github.com/yii2-extensions/etcd/actions/workflows/security.yml)

## License

[![License](https://img.shields.io/badge/License-BSD--3--Clause-brightgreen.svg?style=for-the-badge&logo=opensourceinitiative&logoColor=white&labelColor=555555)](LICENSE)

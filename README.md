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

- PHP: >= 8.2
- `grpc`, `protobuf` - for RPC

## Install

```bash
composer require s1lver/yii2-etcd "^1.0.0"
```

or add

```
"s1lver/yii2-etcd": "^1.0.0"
```

to the require section of your composer.json file.

## Supported etcd API version

- v3

## Supported etcd methods

### Main
- `version`

### KV
- `range`
- `put`

### Auth
- `authenticate`


## How to use

Configure

```php
$config = [
    'components' => [
        'etcd' => [
            'class' => \S1lver\Etcd\Etcd::class,
            'host' => 'etcd:2379',
            'user' => 'username',
            'password' => 'password',
        ],
    ],
];
```

Get key value
```php
Yii::$app->etcd->getKey('hello')->firstKeyValue;

// Hello
```

Get etcd version

```php
Yii::$app->etcd->version;

// {"etcdserver":"3.5.8","etcdcluster":"3.5.0"}
```

### Switch between supported protocol

> etcd v3 uses gRPC for its messaging protocol. For languages with no gRPC support, etcd provides a JSON gRPC gateway. This gateway serves a RESTful proxy that translates HTTP/JSON requests into gRPC messages.


```php
$config = [
    'components' => [
        'etcd' => [
            'class' => \S1lver\Etcd\Etcd::class,
            ...
            'protocol' => '\S1lver\Etcd\EtcdProtocol::GRPC', // Default value \S1lver\Etcd\EtcdProtocol::HTTP
        ],
    ],
];
```

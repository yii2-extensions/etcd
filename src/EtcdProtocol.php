<?php

declare(strict_types=1);

namespace Yii2\Extensions\Etcd;

/**
 * Supported protocol for gateway
 */
final class EtcdProtocol
{
    public const HTTP = 'http';

    public const GRPC = 'grpc';
}

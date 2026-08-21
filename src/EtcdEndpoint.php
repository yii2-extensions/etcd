<?php

declare(strict_types=1);

namespace Yii2\Extensions\Etcd;

final class EtcdEndpoint
{
    // Version
    public const ETCD_VERSION = '/v3';

    // Common
    public const VERSION = '/version';

    // KV
    public const PUT = '/kv/put';
    public const RANGE = '/kv/range';

    // Authentication
    public const AUTHENTICATE =  '/auth/authenticate';
}

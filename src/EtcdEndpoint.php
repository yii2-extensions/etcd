<?php

declare(strict_types=1);

namespace Yii2\Extensions\Etcd;

final class EtcdEndpoint
{
    // Version
    public const string ETCD_VERSION = '/v3';

    // Common
    public const string VERSION = '/version';

    // KV
    public const string PUT = '/kv/put';
    public const string RANGE = '/kv/range';

    // Authentication
    public const string AUTHENTICATE =  '/auth/authenticate';
}

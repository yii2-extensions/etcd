<?php

declare(strict_types=1);

namespace Yii2\Extensions\Etcd;

/**
 * Alarm types (matches `etcdserverpb.AlarmType`)
 */
final class AlarmType
{
    public const string NONE = 'NONE';

    public const string NOSPACE = 'NOSPACE';

    public const string CORRUPT = 'CORRUPT';
}

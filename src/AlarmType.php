<?php

declare(strict_types=1);

namespace Yii2\Extensions\Etcd;

/**
 * Alarm types (matches `etcdserverpb.AlarmType`)
 */
enum AlarmType: string
{
    case NONE = 'NONE';
    case NOSPACE = 'NOSPACE';
    case CORRUPT = 'CORRUPT';
}

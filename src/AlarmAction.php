<?php

declare(strict_types=1);

namespace Yii2\Extensions\Etcd;

/**
 * Alarm request actions (matches `etcdserverpb.AlarmRequest.AlarmAction`)
 */
enum AlarmAction: string
{
    case GET = 'GET';
    case ACTIVATE = 'ACTIVATE';
    case DEACTIVATE = 'DEACTIVATE';
}

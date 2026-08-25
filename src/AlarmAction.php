<?php

declare(strict_types=1);

namespace Yii2\Extensions\Etcd;

/**
 * Alarm request actions (matches `etcdserverpb.AlarmRequest.AlarmAction`)
 */
final class AlarmAction
{
    public const string GET = 'GET';

    public const string ACTIVATE = 'ACTIVATE';

    public const string DEACTIVATE = 'DEACTIVATE';
}

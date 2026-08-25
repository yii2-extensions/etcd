<?php

declare(strict_types=1);

namespace Yii2\Extensions\Etcd;

/**
 * Downgrade actions (matches `etcdserverpb.DowngradeRequest.DowngradeAction`)
 */
final class DowngradeAction
{
    public const string VALIDATE = 'VALIDATE';

    public const string ENABLE = 'ENABLE';

    public const string CANCEL = 'CANCEL';
}

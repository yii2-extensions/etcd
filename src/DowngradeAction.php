<?php

declare(strict_types=1);

namespace Yii2\Extensions\Etcd;

/**
 * Downgrade actions (matches `etcdserverpb.DowngradeRequest.DowngradeAction`)
 */
enum DowngradeAction: string
{
    case VALIDATE = 'VALIDATE';
    case ENABLE = 'ENABLE';
    case CANCEL = 'CANCEL';
}

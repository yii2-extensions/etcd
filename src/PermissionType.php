<?php

declare(strict_types=1);

namespace Yii2\Extensions\Etcd;

/**
 * Permission types (matches `authpb.Permission.Type`)
 */
enum PermissionType: string
{
    case READ = 'READ';
    case WRITE = 'WRITE';
    case READWRITE = 'READWRITE';
}

<?php

declare(strict_types=1);

namespace Yii2\Extensions\Etcd;

/**
 * Permission types (matches `authpb.Permission.Type`)
 */
final class PermissionType
{
    public const string READ = 'READ';

    public const string WRITE = 'WRITE';

    public const string READWRITE = 'READWRITE';
}

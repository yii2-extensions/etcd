<?php

declare(strict_types=1);

namespace Yii2\Extensions\Etcd\Services;

interface EtcdAuthInterface
{
    public function authenticate(): string;
}

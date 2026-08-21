<?php

declare(strict_types=1);

namespace Yii2\Extensions\Etcd;

interface EtcdRangeResponseInterface
{
    public function getFirstKeyValue(): string;
}

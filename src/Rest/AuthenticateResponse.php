<?php

declare(strict_types=1);

namespace Yii2\Extensions\Etcd\Rest;

use yii\base\BaseObject;

class AuthenticateResponse extends BaseObject
{
    /** @var array<string, mixed> */
    public array $header = [];
    public string $token = '';
}

<?php

declare(strict_types=1);

namespace Yii2\Extensions\Etcd\Responses;

final class AuthStatusResponse
{
    /** @var array<string, mixed> */
    public array $header = [];

    public bool $enabled = false;

    public int $authRevision = 0;

    /**
     * @param array<string, mixed> $data
     */
    public function __construct(array $data)
    {
        /** @var array<string, mixed> $header */
        $header = $data['header'] ?? [];
        $this->header = $header;

        $this->enabled = (bool) ($data['enabled'] ?? false);
        $this->authRevision = (int) ($data['authRevision'] ?? 0);
    }
}

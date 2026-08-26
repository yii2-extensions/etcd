<?php

declare(strict_types=1);

namespace Yii2\Extensions\Etcd\Responses;

final readonly class AuthStatusResponse
{
    /** @var array<string, mixed> */
    public readonly array $header;

    public readonly bool $enabled;

    public readonly int $authRevision;

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

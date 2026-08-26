<?php

declare(strict_types=1);

namespace Yii2\Extensions\Etcd\Responses;

final readonly class HashResponse
{
    /** @var array<string, mixed> */
    public readonly array $header;

    public readonly int $hash;

    /**
     * @param array<string, mixed> $data
     */
    public function __construct(array $data)
    {
        /** @var array<string, mixed> $header */
        $header = $data['header'] ?? [];
        $this->header = $header;

        $this->hash = (int) ($data['hash'] ?? 0);
    }
}

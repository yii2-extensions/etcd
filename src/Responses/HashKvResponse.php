<?php

declare(strict_types=1);

namespace Yii2\Extensions\Etcd\Responses;

final class HashKvResponse
{
    /** @var array<string, mixed> */
    public array $header = [];

    public int $hash = 0;

    public int $compactRevision = 0;

    public int $hashRevision = 0;

    /**
     * @param array<string, mixed> $data
     */
    public function __construct(array $data)
    {
        /** @var array<string, mixed> $header */
        $header = $data['header'] ?? [];
        $this->header = $header;

        $this->hash = (int) ($data['hash'] ?? 0);
        $this->compactRevision = (int) ($data['compact_revision'] ?? 0);
        $this->hashRevision = (int) ($data['hash_revision'] ?? 0);
    }
}

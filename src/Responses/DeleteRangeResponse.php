<?php

declare(strict_types=1);

namespace Yii2\Extensions\Etcd\Responses;

final readonly class DeleteRangeResponse
{
    /** @var array<string, mixed> */
    public readonly array $header;

    public readonly int $deleted;

    /** @var array<int, array<string, mixed>> */
    public readonly array $prevKvs;

    /**
     * @param array<string, mixed> $data
     */
    public function __construct(array $data)
    {
        /** @var array<string, mixed> $header */
        $header = $data['header'] ?? [];
        $this->header = $header;

        $this->deleted = (int) ($data['deleted'] ?? 0);

        /** @var array<int, array<string, mixed>> $prevKvs */
        $prevKvs = $data['prev_kvs'] ?? [];
        $this->prevKvs = $prevKvs;
    }
}

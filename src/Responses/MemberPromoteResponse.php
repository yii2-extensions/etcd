<?php

declare(strict_types=1);

namespace Yii2\Extensions\Etcd\Responses;

final readonly class MemberPromoteResponse
{
    /** @var array<string, mixed> */
    public readonly array $header;

    /** @var array<int, array<string, mixed>> */
    public readonly array $members;

    /**
     * @param array<string, mixed> $data
     */
    public function __construct(array $data)
    {
        /** @var array<string, mixed> $header */
        $header = $data['header'] ?? [];
        $this->header = $header;

        /** @var array<int, array<string, mixed>> $members */
        $members = $data['members'] ?? [];
        $this->members = $members;
    }
}

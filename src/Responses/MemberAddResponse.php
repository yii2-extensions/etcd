<?php

declare(strict_types=1);

namespace Yii2\Extensions\Etcd\Responses;

final readonly class MemberAddResponse
{
    /** @var array<string, mixed> */
    public readonly array $header;

    /** @var array<string, mixed> */
    public readonly array $member;

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

        /** @var array<string, mixed> $member */
        $member = $data['member'] ?? [];
        $this->member = $member;

        /** @var array<int, array<string, mixed>> $members */
        $members = $data['members'] ?? [];
        $this->members = $members;
    }
}

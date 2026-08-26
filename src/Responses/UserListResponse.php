<?php

declare(strict_types=1);

namespace Yii2\Extensions\Etcd\Responses;

final readonly class UserListResponse
{
    /** @var array<string, mixed> */
    public readonly array $header;

    /** @var array<int, string> */
    public readonly array $users;

    /**
     * @param array<string, mixed> $data
     */
    public function __construct(array $data)
    {
        /** @var array<string, mixed> $header */
        $header = $data['header'] ?? [];
        $this->header = $header;

        /** @var array<int, string> $users */
        $users = $data['users'] ?? [];
        $this->users = $users;
    }
}

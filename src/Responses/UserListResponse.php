<?php

declare(strict_types=1);

namespace Yii2\Extensions\Etcd\Responses;

final class UserListResponse
{
    /** @var array<string, mixed> */
    public array $header = [];

    /** @var array<int, string> */
    public array $users = [];

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

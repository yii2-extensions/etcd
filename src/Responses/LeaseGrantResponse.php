<?php

declare(strict_types=1);

namespace Yii2\Extensions\Etcd\Responses;

final class LeaseGrantResponse
{
    /** @var array<string, mixed> */
    public array $header = [];

    public int|string $ID = 0;

    public int $TTL = 0;

    public string $error = '';

    /**
     * @param array<string, mixed> $data
     */
    public function __construct(array $data)
    {
        /** @var array<string, mixed> $header */
        $header = $data['header'] ?? [];
        $this->header = $header;

        /** @var int|string $ID */
        $ID = $data['ID'] ?? 0;
        $this->ID = $ID;

        $this->TTL = (int) ($data['TTL'] ?? 0);
        $this->error = (string) ($data['error'] ?? '');
    }
}

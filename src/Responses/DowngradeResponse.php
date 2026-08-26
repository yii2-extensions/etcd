<?php

declare(strict_types=1);

namespace Yii2\Extensions\Etcd\Responses;

final class DowngradeResponse
{
    /** @var array<string, mixed> */
    public array $header = [];

    public string $version = '';

    /**
     * @param array<string, mixed> $data
     */
    public function __construct(array $data)
    {
        /** @var array<string, mixed> $header */
        $header = $data['header'] ?? [];
        $this->header = $header;

        $this->version = (string) ($data['version'] ?? '');
    }
}

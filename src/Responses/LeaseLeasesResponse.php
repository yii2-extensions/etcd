<?php

declare(strict_types=1);

namespace Yii2\Extensions\Etcd\Responses;

final readonly class LeaseLeasesResponse
{
    /** @var array<string, mixed> */
    public readonly array $header;

    /** @var array<int, array<string, mixed>> */
    public readonly array $leases;

    /**
     * @param array<string, mixed> $data
     */
    public function __construct(array $data)
    {
        /** @var array<string, mixed> $header */
        $header = $data['header'] ?? [];
        $this->header = $header;

        /** @var array<int, array<string, mixed>> $leases */
        $leases = $data['leases'] ?? [];
        $this->leases = $leases;
    }
}

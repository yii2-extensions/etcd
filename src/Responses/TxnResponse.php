<?php

declare(strict_types=1);

namespace Yii2\Extensions\Etcd\Responses;

final class TxnResponse
{
    /** @var array<string, mixed> */
    public array $header = [];

    public bool $succeeded = false;

    /** @var array<int, array<string, mixed>> */
    public array $responses = [];

    /**
     * @param array<string, mixed> $data
     */
    public function __construct(array $data)
    {
        /** @var array<string, mixed> $header */
        $header = $data['header'] ?? [];
        $this->header = $header;

        $this->succeeded = (bool) ($data['succeeded'] ?? false);

        /** @var array<int, array<string, mixed>> $responses */
        $responses = $data['responses'] ?? [];
        $this->responses = $responses;
    }
}

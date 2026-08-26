<?php

declare(strict_types=1);

namespace Yii2\Extensions\Etcd\Responses;

final class AlarmResponse
{
    /** @var array<string, mixed> */
    public array $header = [];

    /** @var array<int, array<string, mixed>> */
    public array $alarms = [];

    /**
     * @param array<string, mixed> $data
     */
    public function __construct(array $data)
    {
        /** @var array<string, mixed> $header */
        $header = $data['header'] ?? [];
        $this->header = $header;

        /** @var array<int, array<string, mixed>> $alarms */
        $alarms = $data['alarms'] ?? [];
        $this->alarms = $alarms;
    }
}

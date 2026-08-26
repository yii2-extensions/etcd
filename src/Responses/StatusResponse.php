<?php

declare(strict_types=1);

namespace Yii2\Extensions\Etcd\Responses;

final class StatusResponse
{
    /** @var array<string, mixed> */
    public array $header = [];

    public string $version = '';

    public int $dbSize = 0;

    public int|string $leader = 0;

    public int $raftIndex = 0;

    public int $raftTerm = 0;

    public int $raftAppliedIndex = 0;

    /** @var array<int, string> */
    public array $errors = [];

    public int $dbSizeInUse = 0;

    public bool $isLearner = false;

    public string $storageVersion = '';

    public int $dbSizeQuota = 0;

    /** @var array<string, mixed> */
    public array $downgradeInfo = [];

    /**
     * @param array<string, mixed> $data
     */
    public function __construct(array $data)
    {
        /** @var array<string, mixed> $header */
        $header = $data['header'] ?? [];
        $this->header = $header;

        $this->version = (string) ($data['version'] ?? '');
        $this->dbSize = (int) ($data['dbSize'] ?? 0);

        /** @var int|string $leader */
        $leader = $data['leader'] ?? 0;
        $this->leader = $leader;

        $this->raftIndex = (int) ($data['raftIndex'] ?? 0);
        $this->raftTerm = (int) ($data['raftTerm'] ?? 0);
        $this->raftAppliedIndex = (int) ($data['raftAppliedIndex'] ?? 0);

        /** @var array<int, string> $errors */
        $errors = $data['errors'] ?? [];
        $this->errors = $errors;

        $this->dbSizeInUse = (int) ($data['dbSizeInUse'] ?? 0);
        $this->isLearner = (bool) ($data['isLearner'] ?? false);
        $this->storageVersion = (string) ($data['storageVersion'] ?? '');
        $this->dbSizeQuota = (int) ($data['dbSizeQuota'] ?? 0);

        /** @var array<string, mixed> $downgradeInfo */
        $downgradeInfo = $data['downgradeInfo'] ?? [];
        $this->downgradeInfo = $downgradeInfo;
    }
}

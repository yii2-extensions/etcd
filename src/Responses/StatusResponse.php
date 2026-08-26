<?php

declare(strict_types=1);

namespace Yii2\Extensions\Etcd\Responses;

final readonly class StatusResponse
{
    /** @var array<string, mixed> */
    public readonly array $header;

    public readonly string $version;

    public readonly int $dbSize;

    public readonly int|string $leader;

    public readonly int $raftIndex;

    public readonly int $raftTerm;

    public readonly int $raftAppliedIndex;

    /** @var array<int, string> */
    public readonly array $errors;

    public readonly int $dbSizeInUse;

    public readonly bool $isLearner;

    public readonly string $storageVersion;

    public readonly int $dbSizeQuota;

    /** @var array<string, mixed> */
    public readonly array $downgradeInfo;

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

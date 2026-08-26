<?php

declare(strict_types=1);

namespace Yii2\Extensions\Etcd;

use Yii2\Extensions\Etcd\Responses\AlarmResponse;
use Yii2\Extensions\Etcd\Responses\DowngradeResponse;
use Yii2\Extensions\Etcd\Responses\HashKvResponse;
use Yii2\Extensions\Etcd\Responses\HashResponse;
use Yii2\Extensions\Etcd\Responses\StatusResponse;

interface EtcdMaintenanceServiceInterface
{
    public function getVersion(): string;

    /**
     * Activates, deactivates, and queries alarms regarding cluster health.
     *
     * @param string $action one of {@see AlarmAction} values
     * @param string $alarmType one of {@see AlarmType} values
     */
    public function alarm(string $action, int|string $memberId = 0, string $alarmType = AlarmType::NONE): AlarmResponse;

    /**
     * Gets the status of the member.
     */
    public function status(): StatusResponse;

    /**
     * Defragments a member's backend database to recover storage space.
     */
    public function defragment(): bool;

    /**
     * Computes the hash of whole backend keyspace (designed for testing ONLY!).
     */
    public function hash(): HashResponse;

    /**
     * Computes the hash of all MVCC keys up to a given revision.
     */
    public function hashKv(int $revision = 0): HashKvResponse;

    /**
     * Requests current leader node to transfer its leadership to transferee.
     */
    public function moveLeader(int|string $targetId): bool;

    /**
     * Requests downgrades, verifies feasibility or cancels downgrade on the cluster version.
     *
     * @param string $action one of {@see DowngradeAction} values
     */
    public function downgrade(string $action, string $version = ''): DowngradeResponse;
}

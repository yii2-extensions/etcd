<?php

declare(strict_types=1);

namespace Yii2\Extensions\Etcd;

interface EtcdMaintenanceServiceInterface
{
    public function getVersion(): string;

    /**
     * Activates, deactivates, and queries alarms regarding cluster health.
     *
     * @param string $action one of {@see AlarmAction} values
     * @param string $alarmType one of {@see AlarmType} values
     * @return array<string, mixed> response fields: header, alarms
     */
    public function alarm(string $action, int|string $memberId = 0, string $alarmType = AlarmType::NONE): array;

    /**
     * Gets the status of the member.
     *
     * @return array<string, mixed> response fields: header, version, dbSize, leader, raftIndex, raftTerm, ...
     */
    public function status(): array;

    /**
     * Defragments a member's backend database to recover storage space.
     */
    public function defragment(): bool;

    /**
     * Computes the hash of whole backend keyspace (designed for testing ONLY!).
     *
     * @return array<string, mixed> response fields: header, hash
     */
    public function hash(): array;

    /**
     * Computes the hash of all MVCC keys up to a given revision.
     *
     * @return array<string, mixed> response fields: header, hash, compact_revision, hash_revision
     */
    public function hashKv(int $revision = 0): array;

    /**
     * Requests current leader node to transfer its leadership to transferee.
     */
    public function moveLeader(int|string $targetId): bool;

    /**
     * Requests downgrades, verifies feasibility or cancels downgrade on the cluster version.
     *
     * @param string $action one of {@see DowngradeAction} values
     * @return array<string, mixed> response fields: header, version
     */
    public function downgrade(string $action, string $version = ''): array;
}

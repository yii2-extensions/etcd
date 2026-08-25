<?php

declare(strict_types=1);

namespace Yii2\Extensions\Etcd;

use Yii2\Extensions\Etcd\Services\EtcdAuthInterface;

interface EtcdServiceInterface
{
    public function getKey(string $key): EtcdRangeResponseInterface;

    public function getRange(string $key, string $rangeEnd): EtcdRangeResponseInterface;

    public function put(string $key, string $value): bool;

    public function getVersion(): string;

    public function getAuthModel(): EtcdAuthInterface;

    /**
     * DeleteRange deletes the given range from the key-value store.
     *
     * @return array<string, mixed> response fields: header, deleted, prev_kvs
     */
    public function deleteRange(string $key, string $rangeEnd = ''): array;

    /**
     * Txn processes multiple requests in a single transaction.
     *
     * @param array<int, array<string, mixed>> $compare
     * @param array<int, array<string, mixed>> $success
     * @param array<int, array<string, mixed>> $failure
     * @return array<string, mixed> response fields: header, succeeded, responses
     */
    public function txn(array $compare, array $success, array $failure): array;

    /**
     * Compact compacts the event history in the key-value store up to a given revision.
     */
    public function compact(int $revision, bool $physical = false): bool;

    /**
     * Enables authentication.
     */
    public function authEnable(): bool;

    /**
     * Disables authentication.
     */
    public function authDisable(): bool;

    /**
     * Displays authentication status.
     *
     * @return array<string, mixed> response fields: header, enabled, authRevision
     */
    public function authStatus(): array;

    /**
     * Adds a new user. User name cannot be empty.
     */
    public function userAdd(string $name, string $password, bool $noPassword = false): bool;

    /**
     * Gets detailed user information.
     *
     * @return array<string, mixed> response fields: header, roles
     */
    public function userGet(string $name): array;

    /**
     * Gets a list of all users.
     *
     * @return array<string, mixed> response fields: header, users
     */
    public function userList(): array;

    /**
     * Deletes a specified user.
     */
    public function userDelete(string $name): bool;

    /**
     * Changes the password of a specified user.
     */
    public function userChangePassword(string $name, string $password): bool;

    /**
     * Grants a role to a specified user.
     */
    public function userGrantRole(string $user, string $role): bool;

    /**
     * Revokes a role of specified user.
     */
    public function userRevokeRole(string $user, string $role): bool;

    /**
     * Adds a new role. Role name cannot be empty.
     */
    public function roleAdd(string $name): bool;

    /**
     * Gets detailed role information.
     *
     * @return array<string, mixed> response fields: header, perm
     */
    public function roleGet(string $name): array;

    /**
     * Gets lists of all roles.
     *
     * @return array<string, mixed> response fields: header, roles
     */
    public function roleList(): array;

    /**
     * Deletes a specified role.
     */
    public function roleDelete(string $name): bool;

    /**
     * Grants a permission of a specified key or range to a specified role.
     *
     * @param string $permType one of {@see PermissionType} values
     */
    public function roleGrantPermission(string $name, string $permType, string $key, string $rangeEnd = ''): bool;

    /**
     * Revokes a key or range permission of a specified role.
     */
    public function roleRevokePermission(string $role, string $key, string $rangeEnd = ''): bool;

    /**
     * Adds a member into the cluster.
     *
     * @param string[] $peerUrls
     * @return array<string, mixed> response fields: header, member, members
     */
    public function memberAdd(array $peerUrls, bool $isLearner = false): array;

    /**
     * Removes an existing member from the cluster.
     *
     * @return array<string, mixed> response fields: header, members
     */
    public function memberRemove(int|string $id): array;

    /**
     * Updates the member configuration.
     *
     * @param string[] $peerUrls
     * @return array<string, mixed> response fields: header, members
     */
    public function memberUpdate(int|string $id, array $peerUrls): array;

    /**
     * Lists all the members in the cluster.
     *
     * @return array<string, mixed> response fields: header, members
     */
    public function memberList(bool $linearizable = false): array;

    /**
     * Promotes a member from raft learner (non-voting) to raft voting member.
     *
     * @return array<string, mixed> response fields: header, members
     */
    public function memberPromote(int|string $id): array;

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

    /**
     * Creates a lease which expires if the server does not receive a keepAlive within a given TTL period.
     *
     * @return array<string, mixed> response fields: header, ID, TTL, error
     */
    public function leaseGrant(int $ttl, int $id = 0): array;

    /**
     * Revokes a lease. All keys attached to the lease will expire and be deleted.
     */
    public function leaseRevoke(int $id): bool;

    /**
     * Retrieves lease information.
     *
     * @return array<string, mixed> response fields: header, ID, TTL, grantedTTL, keys
     */
    public function leaseTimeToLive(int $id, bool $keys = false): array;

    /**
     * Lists all existing leases.
     *
     * @return array<string, mixed> response fields: header, leases
     */
    public function leaseLeases(): array;
}

<?php

declare(strict_types=1);

namespace Yii2\Extensions\Etcd;

interface EtcdLeaseServiceInterface
{
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

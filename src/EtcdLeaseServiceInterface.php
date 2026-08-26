<?php

declare(strict_types=1);

namespace Yii2\Extensions\Etcd;

use Yii2\Extensions\Etcd\Responses\LeaseGrantResponse;
use Yii2\Extensions\Etcd\Responses\LeaseLeasesResponse;
use Yii2\Extensions\Etcd\Responses\LeaseTimeToLiveResponse;

interface EtcdLeaseServiceInterface
{
    /**
     * Creates a lease which expires if the server does not receive a keepAlive within a given TTL period.
     */
    public function leaseGrant(int $ttl, int $id = 0): LeaseGrantResponse;

    /**
     * Revokes a lease. All keys attached to the lease will expire and be deleted.
     */
    public function leaseRevoke(int $id): bool;

    /**
     * Retrieves lease information.
     */
    public function leaseTimeToLive(int $id, bool $keys = false): LeaseTimeToLiveResponse;

    /**
     * Lists all existing leases.
     */
    public function leaseLeases(): LeaseLeasesResponse;
}

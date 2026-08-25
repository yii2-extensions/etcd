<?php

declare(strict_types=1);

namespace Yii2\Extensions\Etcd\Rest;

use GuzzleHttp\Exception\GuzzleException;
use JsonException;
use Yii2\Extensions\Etcd\EtcdEndpoint;
use Yii2\Extensions\Etcd\EtcdLeaseServiceInterface;

final class EtcdRestLease extends AbstractEtcdRestService implements EtcdLeaseServiceInterface
{
    /**
     * @return array<string, mixed>
     * @throws GuzzleException|JsonException
     */
    public function leaseGrant(int $ttl, int $id = 0): array
    {
        return $this->request(EtcdEndpoint::LEASE_GRANT, ['TTL' => (string) $ttl, 'ID' => (string) $id]);
    }

    /**
     * @throws GuzzleException|JsonException
     */
    public function leaseRevoke(int $id): bool
    {
        return isset($this->request(EtcdEndpoint::LEASE_REVOKE, ['ID' => (string) $id])['header']);
    }

    /**
     * @return array<string, mixed>
     * @throws GuzzleException|JsonException
     */
    public function leaseTimeToLive(int $id, bool $keys = false): array
    {
        return $this->request(EtcdEndpoint::LEASE_TIME_TO_LIVE, ['ID' => (string) $id, 'keys' => $keys]);
    }

    /**
     * @return array<string, mixed>
     * @throws GuzzleException|JsonException
     */
    public function leaseLeases(): array
    {
        return $this->request(EtcdEndpoint::LEASE_LEASES, []);
    }
}

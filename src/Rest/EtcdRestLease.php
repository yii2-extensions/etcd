<?php

declare(strict_types=1);

namespace Yii2\Extensions\Etcd\Rest;

use GuzzleHttp\Exception\GuzzleException;
use JsonException;
use Yii2\Extensions\Etcd\EtcdEndpoint;
use Yii2\Extensions\Etcd\EtcdLeaseServiceInterface;
use Yii2\Extensions\Etcd\Responses\LeaseGrantResponse;
use Yii2\Extensions\Etcd\Responses\LeaseLeasesResponse;
use Yii2\Extensions\Etcd\Responses\LeaseTimeToLiveResponse;

final class EtcdRestLease extends AbstractEtcdRestService implements EtcdLeaseServiceInterface
{
    /**
     * @throws GuzzleException|JsonException
     */
    public function leaseGrant(int $ttl, int $id = 0): LeaseGrantResponse
    {
        return new LeaseGrantResponse(
            $this->request(EtcdEndpoint::LEASE_GRANT, ['TTL' => (string) $ttl, 'ID' => (string) $id])
        );
    }

    /**
     * @throws GuzzleException|JsonException
     */
    public function leaseRevoke(int $id): bool
    {
        return isset($this->request(EtcdEndpoint::LEASE_REVOKE, ['ID' => (string) $id])['header']);
    }

    /**
     * @throws GuzzleException|JsonException
     */
    public function leaseTimeToLive(int $id, bool $keys = false): LeaseTimeToLiveResponse
    {
        return new LeaseTimeToLiveResponse(
            $this->request(EtcdEndpoint::LEASE_TIME_TO_LIVE, ['ID' => (string) $id, 'keys' => $keys])
        );
    }

    /**
     * @throws GuzzleException|JsonException
     */
    public function leaseLeases(): LeaseLeasesResponse
    {
        return new LeaseLeasesResponse($this->request(EtcdEndpoint::LEASE_LEASES, []));
    }
}

<?php

declare(strict_types=1);

namespace Yii2\Extensions\Etcd\RPC;

use Etcd\LeaseClient;
use Etcd\LeaseGrantRequest;
use Etcd\LeaseGrantResponse;
use Etcd\LeaseLeasesRequest;
use Etcd\LeaseLeasesResponse;
use Etcd\LeaseRevokeRequest;
use Etcd\LeaseTimeToLiveRequest;
use Etcd\LeaseTimeToLiveResponse;
use Google\Protobuf\Internal\RepeatedField;
use Yii2\Extensions\Etcd\EtcdLeaseServiceInterface;

final class EtcdGrpcLease extends AbstractEtcdGrpcService implements EtcdLeaseServiceInterface
{
    private ?LeaseClient $client = null;

    private function getClient(): LeaseClient
    {
        return $this->client ??= new LeaseClient($this->connection->host, $this->getConnectionOptions());
    }

    public function leaseGrant(int $ttl, int $id = 0): array
    {
        $request = new LeaseGrantRequest();
        $request->setTTL($ttl);
        $request->setID($id);

        /** @var LeaseGrantResponse $response */
        $response = $this->wait($this->getClient()->LeaseGrant($request));

        return [
            'header' => $this->convertHeader($response->getHeader()),
            'ID' => $response->getID(),
            'TTL' => $response->getTTL(),
            'error' => $response->getError(),
        ];
    }

    public function leaseRevoke(int $id): bool
    {
        $request = new LeaseRevokeRequest();
        $request->setID($id);

        $this->wait($this->getClient()->LeaseRevoke($request));

        return true;
    }

    public function leaseTimeToLive(int $id, bool $keys = false): array
    {
        $request = new LeaseTimeToLiveRequest();
        $request->setID($id);
        $request->setKeys($keys);

        /** @var LeaseTimeToLiveResponse $response */
        $response = $this->wait($this->getClient()->LeaseTimeToLive($request));

        return [
            'header' => $this->convertHeader($response->getHeader()),
            'ID' => $response->getID(),
            'TTL' => $response->getTTL(),
            'grantedTTL' => $response->getGrantedTTL(),
            'keys' => $this->convertStringList($response->getKeys()),
        ];
    }

    public function leaseLeases(): array
    {
        /** @var LeaseLeasesResponse $response */
        $response = $this->wait($this->getClient()->LeaseLeases(new LeaseLeasesRequest()));

        $leases = [];

        /** @var RepeatedField<\Etcd\LeaseStatus> $leaseList */
        $leaseList = $response->getLeases();

        foreach ($leaseList as $lease) {
            $leases[] = ['ID' => $lease->getID()];
        }

        return [
            'header' => $this->convertHeader($response->getHeader()),
            'leases' => $leases,
        ];
    }
}

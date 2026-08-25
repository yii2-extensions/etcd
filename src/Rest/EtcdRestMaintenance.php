<?php

declare(strict_types=1);

namespace Yii2\Extensions\Etcd\Rest;

use GuzzleHttp\Exception\GuzzleException;
use JsonException;
use Yii2\Extensions\Etcd\EtcdEndpoint;
use Yii2\Extensions\Etcd\EtcdMaintenanceServiceInterface;

final class EtcdRestMaintenance extends AbstractEtcdRestService implements EtcdMaintenanceServiceInterface
{
    /**
     * @return string
     * @throws GuzzleException
     */
    public function getVersion(): string
    {
        $response = $this->connection->client->get($this->connection->host . EtcdEndpoint::VERSION);

        return $response->getBody()->getContents();
    }

    /**
     * @return array<string, mixed>
     * @throws GuzzleException|JsonException
     */
    public function alarm(string $action, int|string $memberId = 0, string $alarmType = 'NONE'): array
    {
        return $this->request(
            EtcdEndpoint::ALARM,
            ['action' => $action, 'memberID' => (string) $memberId, 'alarm' => $alarmType]
        );
    }

    /**
     * @return array<string, mixed>
     * @throws GuzzleException|JsonException
     */
    public function status(): array
    {
        return $this->request(EtcdEndpoint::STATUS, []);
    }

    /**
     * @throws GuzzleException|JsonException
     */
    public function defragment(): bool
    {
        $this->request(EtcdEndpoint::DEFRAGMENT, []);

        return true;
    }

    /**
     * @return array<string, mixed>
     * @throws GuzzleException|JsonException
     */
    public function hash(): array
    {
        return $this->request(EtcdEndpoint::HASH, []);
    }

    /**
     * @return array<string, mixed>
     * @throws GuzzleException|JsonException
     */
    public function hashKv(int $revision = 0): array
    {
        return $this->request(EtcdEndpoint::HASH_KV, ['revision' => (string) $revision]);
    }

    /**
     * @throws GuzzleException|JsonException
     */
    public function moveLeader(int|string $targetId): bool
    {
        $this->request(EtcdEndpoint::MOVE_LEADER, ['targetID' => (string) $targetId]);

        return true;
    }

    /**
     * @return array<string, mixed>
     * @throws GuzzleException|JsonException
     */
    public function downgrade(string $action, string $version = ''): array
    {
        return $this->request(EtcdEndpoint::DOWNGRADE, ['action' => $action, 'version' => $version]);
    }
}

<?php

declare(strict_types=1);

namespace Yii2\Extensions\Etcd\Rest;

use GuzzleHttp\Exception\GuzzleException;
use JsonException;
use Yii2\Extensions\Etcd\EtcdEndpoint;
use Yii2\Extensions\Etcd\EtcdMaintenanceServiceInterface;
use Yii2\Extensions\Etcd\Responses\AlarmResponse;
use Yii2\Extensions\Etcd\Responses\DowngradeResponse;
use Yii2\Extensions\Etcd\Responses\HashKvResponse;
use Yii2\Extensions\Etcd\Responses\HashResponse;
use Yii2\Extensions\Etcd\Responses\StatusResponse;

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
     * @throws GuzzleException|JsonException
     */
    public function alarm(string $action, int|string $memberId = 0, string $alarmType = 'NONE'): AlarmResponse
    {
        return new AlarmResponse(
            $this->request(
                EtcdEndpoint::ALARM,
                ['action' => $action, 'memberID' => (string) $memberId, 'alarm' => $alarmType]
            )
        );
    }

    /**
     * @throws GuzzleException|JsonException
     */
    public function status(): StatusResponse
    {
        return new StatusResponse($this->request(EtcdEndpoint::STATUS, []));
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
     * @throws GuzzleException|JsonException
     */
    public function hash(): HashResponse
    {
        return new HashResponse($this->request(EtcdEndpoint::HASH, []));
    }

    /**
     * @throws GuzzleException|JsonException
     */
    public function hashKv(int $revision = 0): HashKvResponse
    {
        return new HashKvResponse($this->request(EtcdEndpoint::HASH_KV, ['revision' => (string) $revision]));
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
     * @throws GuzzleException|JsonException
     */
    public function downgrade(string $action, string $version = ''): DowngradeResponse
    {
        return new DowngradeResponse($this->request(EtcdEndpoint::DOWNGRADE, ['action' => $action, 'version' => $version]));
    }
}

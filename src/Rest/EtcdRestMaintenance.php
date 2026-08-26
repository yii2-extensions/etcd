<?php

declare(strict_types=1);

namespace Yii2\Extensions\Etcd\Rest;

use GuzzleHttp\Exception\GuzzleException;
use JsonException;
use Yii2\Extensions\Etcd\AlarmAction;
use Yii2\Extensions\Etcd\AlarmType;
use Yii2\Extensions\Etcd\DowngradeAction;
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
    #[\Override]
    public function getVersion(): string
    {
        $response = $this->connection->client->get($this->connection->host . EtcdEndpoint::VERSION);

        return $response->getBody()->getContents();
    }

    /**
     * @throws GuzzleException|JsonException
     */
    #[\Override]
    public function alarm(AlarmAction $action, int|string $memberId = 0, AlarmType $alarmType = AlarmType::NONE): AlarmResponse
    {
        return new AlarmResponse(
            $this->request(
                EtcdEndpoint::ALARM,
                ['action' => $action->value, 'memberID' => (string) $memberId, 'alarm' => $alarmType->value]
            )
        );
    }

    /**
     * @throws GuzzleException|JsonException
     */
    #[\Override]
    public function status(): StatusResponse
    {
        return new StatusResponse($this->request(EtcdEndpoint::STATUS, []));
    }

    /**
     * @throws GuzzleException|JsonException
     */
    #[\Override]
    public function defragment(): bool
    {
        $this->request(EtcdEndpoint::DEFRAGMENT, []);

        return true;
    }

    /**
     * @throws GuzzleException|JsonException
     */
    #[\Override]
    public function hash(): HashResponse
    {
        return new HashResponse($this->request(EtcdEndpoint::HASH, []));
    }

    /**
     * @throws GuzzleException|JsonException
     */
    #[\Override]
    public function hashKv(int $revision = 0): HashKvResponse
    {
        return new HashKvResponse($this->request(EtcdEndpoint::HASH_KV, ['revision' => (string) $revision]));
    }

    /**
     * @throws GuzzleException|JsonException
     */
    #[\Override]
    public function moveLeader(int|string $targetId): bool
    {
        $this->request(EtcdEndpoint::MOVE_LEADER, ['targetID' => (string) $targetId]);

        return true;
    }

    /**
     * @throws GuzzleException|JsonException
     */
    #[\Override]
    public function downgrade(DowngradeAction $action, string $version = ''): DowngradeResponse
    {
        return new DowngradeResponse(
            $this->request(EtcdEndpoint::DOWNGRADE, ['action' => $action->value, 'version' => $version])
        );
    }
}

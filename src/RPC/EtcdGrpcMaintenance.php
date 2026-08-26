<?php

declare(strict_types=1);

namespace Yii2\Extensions\Etcd\RPC;

use Etcd\AlarmRequest;
use Etcd\AlarmRequest\AlarmAction as EtcdAlarmAction;
use Etcd\AlarmType as EtcdAlarmType;
use Etcd\DowngradeRequest;
use Etcd\DowngradeRequest\DowngradeAction as EtcdDowngradeAction;
use Etcd\HashKVRequest;
use Etcd\HashKVResponse as EtcdHashKVResponse;
use Etcd\HashRequest;
use Etcd\HashResponse as EtcdHashResponse;
use Etcd\MaintenanceClient;
use Etcd\MoveLeaderRequest;
use Etcd\StatusRequest;
use Etcd\StatusResponse as EtcdStatusResponse;
use Google\Protobuf\Internal\RepeatedField;
use Yii2\Extensions\Etcd\AlarmAction;
use Yii2\Extensions\Etcd\AlarmType;
use Yii2\Extensions\Etcd\DowngradeAction;
use Yii2\Extensions\Etcd\EtcdMaintenanceServiceInterface;
use Yii2\Extensions\Etcd\Responses\AlarmResponse;
use Yii2\Extensions\Etcd\Responses\DowngradeResponse;
use Yii2\Extensions\Etcd\Responses\HashKvResponse;
use Yii2\Extensions\Etcd\Responses\HashResponse;
use Yii2\Extensions\Etcd\Responses\StatusResponse;

final class EtcdGrpcMaintenance extends AbstractEtcdGrpcService implements EtcdMaintenanceServiceInterface
{
    private ?MaintenanceClient $client = null;

    private function getClient(): MaintenanceClient
    {
        return $this->client ??= new MaintenanceClient($this->connection->host, $this->getConnectionOptions());
    }

    #[\Override]
    public function getVersion(): string
    {
        return 'Not supported';
    }

    #[\Override]
    public function alarm(AlarmAction $action, int|string $memberId = 0, AlarmType $alarmType = AlarmType::NONE): AlarmResponse
    {
        $request = new AlarmRequest();
        $request->setAction((int) EtcdAlarmAction::value($action->value));
        $request->setMemberID($memberId);
        $request->setAlarm((int) EtcdAlarmType::value($alarmType->value));

        /** @var \Etcd\AlarmResponse $response */
        $response = $this->wait($this->getClient()->Alarm($request));

        $alarms = [];

        /** @var RepeatedField<\Etcd\AlarmMember> $alarmList */
        $alarmList = $response->getAlarms();

        foreach ($alarmList as $alarm) {
            $alarms[] = [
                'memberID' => $alarm->getMemberID(),
                'alarm' => EtcdAlarmType::name($alarm->getAlarm()),
            ];
        }

        return new AlarmResponse([
            'header' => $this->convertHeader($response->getHeader()),
            'alarms' => $alarms,
        ]);
    }

    #[\Override]
    public function status(): StatusResponse
    {
        /** @var EtcdStatusResponse $response */
        $response = $this->wait($this->getClient()->Status(new StatusRequest()));

        $result = [
            'header' => $this->convertHeader($response->getHeader()),
            'version' => $response->getVersion(),
            'dbSize' => $response->getDbSize(),
            'leader' => $response->getLeader(),
            'raftIndex' => $response->getRaftIndex(),
            'raftTerm' => $response->getRaftTerm(),
            'raftAppliedIndex' => $response->getRaftAppliedIndex(),
            'errors' => $this->convertStringList($response->getErrors()),
            'dbSizeInUse' => $response->getDbSizeInUse(),
            'isLearner' => $response->getIsLearner(),
            'storageVersion' => $response->getStorageVersion(),
            'dbSizeQuota' => $response->getDbSizeQuota(),
        ];

        if (null !== $response->getDowngradeInfo()) {
            $result['downgradeInfo'] = [
                'enabled' => $response->getDowngradeInfo()->getEnabled(),
                'targetVersion' => $response->getDowngradeInfo()->getTargetVersion(),
            ];
        }

        return new StatusResponse($result);
    }

    #[\Override]
    public function defragment(): bool
    {
        $this->wait($this->getClient()->Defragment(new \Etcd\DefragmentRequest()));

        return true;
    }

    #[\Override]
    public function hash(): HashResponse
    {
        /** @var EtcdHashResponse $response */
        $response = $this->wait($this->getClient()->Hash(new HashRequest()));

        return new HashResponse([
            'header' => $this->convertHeader($response->getHeader()),
            'hash' => $response->getHash(),
        ]);
    }

    #[\Override]
    public function hashKv(int $revision = 0): HashKvResponse
    {
        $request = new HashKVRequest();
        $request->setRevision($revision);

        /** @var EtcdHashKVResponse $response */
        $response = $this->wait($this->getClient()->HashKV($request));

        return new HashKvResponse([
            'header' => $this->convertHeader($response->getHeader()),
            'hash' => $response->getHash(),
            'compact_revision' => $response->getCompactRevision(),
            'hash_revision' => $response->getHashRevision(),
        ]);
    }

    #[\Override]
    public function moveLeader(int|string $targetId): bool
    {
        $request = new MoveLeaderRequest();
        $request->setTargetID($targetId);

        $this->wait($this->getClient()->MoveLeader($request));

        return true;
    }

    #[\Override]
    public function downgrade(DowngradeAction $action, string $version = ''): DowngradeResponse
    {
        $request = new DowngradeRequest();
        $request->setAction((int) EtcdDowngradeAction::value($action->value));

        if ('' !== $version) {
            $request->setVersion($version);
        }

        /** @var \Etcd\DowngradeResponse $response */
        $response = $this->wait($this->getClient()->Downgrade($request));

        return new DowngradeResponse([
            'header' => $this->convertHeader($response->getHeader()),
            'version' => $response->getVersion(),
        ]);
    }
}

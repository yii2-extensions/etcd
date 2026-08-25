<?php

declare(strict_types=1);

namespace Yii2\Extensions\Etcd\RPC;

use Etcd\AlarmRequest;
use Etcd\AlarmRequest\AlarmAction;
use Etcd\AlarmType;
use Etcd\DowngradeRequest;
use Etcd\DowngradeRequest\DowngradeAction;
use Etcd\HashKVRequest;
use Etcd\HashKVResponse;
use Etcd\HashRequest;
use Etcd\HashResponse;
use Etcd\MaintenanceClient;
use Etcd\MoveLeaderRequest;
use Etcd\StatusRequest;
use Etcd\StatusResponse;
use Google\Protobuf\Internal\RepeatedField;
use Yii2\Extensions\Etcd\EtcdMaintenanceServiceInterface;

final class EtcdGrpcMaintenance extends AbstractEtcdGrpcService implements EtcdMaintenanceServiceInterface
{
    private ?MaintenanceClient $client = null;

    private function getClient(): MaintenanceClient
    {
        return $this->client ??= new MaintenanceClient($this->connection->host, $this->getConnectionOptions());
    }

    public function getVersion(): string
    {
        return 'Not supported';
    }

    public function alarm(string $action, int|string $memberId = 0, string $alarmType = 'NONE'): array
    {
        $request = new AlarmRequest();
        $request->setAction((int) AlarmAction::value($action));
        $request->setMemberID($memberId);
        $request->setAlarm((int) AlarmType::value($alarmType));

        /** @var \Etcd\AlarmResponse $response */
        $response = $this->wait($this->getClient()->Alarm($request));

        $alarms = [];

        /** @var RepeatedField<\Etcd\AlarmMember> $alarmList */
        $alarmList = $response->getAlarms();

        foreach ($alarmList as $alarm) {
            $alarms[] = [
                'memberID' => $alarm->getMemberID(),
                'alarm' => AlarmType::name($alarm->getAlarm()),
            ];
        }

        return [
            'header' => $this->convertHeader($response->getHeader()),
            'alarms' => $alarms,
        ];
    }

    public function status(): array
    {
        /** @var StatusResponse $response */
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

        return $result;
    }

    public function defragment(): bool
    {
        $this->wait($this->getClient()->Defragment(new \Etcd\DefragmentRequest()));

        return true;
    }

    public function hash(): array
    {
        /** @var HashResponse $response */
        $response = $this->wait($this->getClient()->Hash(new HashRequest()));

        return [
            'header' => $this->convertHeader($response->getHeader()),
            'hash' => $response->getHash(),
        ];
    }

    public function hashKv(int $revision = 0): array
    {
        $request = new HashKVRequest();
        $request->setRevision($revision);

        /** @var HashKVResponse $response */
        $response = $this->wait($this->getClient()->HashKV($request));

        return [
            'header' => $this->convertHeader($response->getHeader()),
            'hash' => $response->getHash(),
            'compact_revision' => $response->getCompactRevision(),
            'hash_revision' => $response->getHashRevision(),
        ];
    }

    public function moveLeader(int|string $targetId): bool
    {
        $request = new MoveLeaderRequest();
        $request->setTargetID($targetId);

        $this->wait($this->getClient()->MoveLeader($request));

        return true;
    }

    public function downgrade(string $action, string $version = ''): array
    {
        $request = new DowngradeRequest();
        $request->setAction((int) DowngradeAction::value($action));

        if ('' !== $version) {
            $request->setVersion($version);
        }

        /** @var \Etcd\DowngradeResponse $response */
        $response = $this->wait($this->getClient()->Downgrade($request));

        return [
            'header' => $this->convertHeader($response->getHeader()),
            'version' => $response->getVersion(),
        ];
    }
}

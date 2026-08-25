<?php

declare(strict_types=1);

namespace Yii2\Extensions\Etcd;

use Yii2\Extensions\Etcd\Rest\EtcdRestModel;
use Yii2\Extensions\Etcd\RPC\EtcdGrpcModel;
use Yii2\Extensions\Etcd\Services\EtcdAuthInterface;
use yii\base\Component;

/**
 * Yii2 etcd component
 *
 * @property-read array[] $tokenOptions
 * @property-read EtcdAuthInterface $auth
 * @property-read string $version
 */
class Etcd extends Component
{
    public string $host = '';
    public string $user = '';
    public string $password = '';
    /**
     * @var array guzzle client options
     */
    public array $clientOptions = [];
    public string $protocol = EtcdProtocol::HTTP;

    private EtcdServiceInterface $service;

    private array $protocolList = [
        EtcdProtocol::GRPC => EtcdGrpcModel::class,
        EtcdProtocol::HTTP => EtcdRestModel::class,
    ];

    /**
     * @inheritDoc
     */
    public function init(): void
    {
        parent::init();

        $this->service = new $this->protocolList[$this->protocol]($this->host, $this->user, $this->password, $this->clientOptions);
    }

    /**
     * @return string
     */
    public function getVersion(): string
    {
        return $this->service->getVersion();
    }

    /**
     * @param string $key
     * @return EtcdRangeResponseInterface
     */
    public function getKey(string $key): EtcdRangeResponseInterface
    {
        return $this->service->getKey($key);
    }

    /**
     * Get all keys prefixed with "foo" ($key = foo, $rangeEnd = fop)
     *
     * @param string $key
     * @param string $rangeEnd
     * @return EtcdRangeResponseInterface
     */
    public function getRange(string $key, string $rangeEnd): EtcdRangeResponseInterface
    {
        return $this->service->getRange($key, $rangeEnd);
    }

    /**
     * @param string $key
     * @param string $value
     * @return bool
     */
    public function put(string $key, string $value): bool
    {
        return $this->service->put($key, $value);
    }

    /**
     * DeleteRange deletes the given range from the key-value store.
     *
     * @param string $key
     * @param string $rangeEnd
     * @return array
     */
    public function deleteRange(string $key, string $rangeEnd = ''): array
    {
        return $this->service->deleteRange($key, $rangeEnd);
    }

    /**
     * Txn processes multiple requests in a single transaction.
     *
     * @param array $compare
     * @param array $success
     * @param array $failure
     * @return array
     */
    public function txn(array $compare, array $success, array $failure): array
    {
        return $this->service->txn($compare, $success, $failure);
    }

    /**
     * Compact compacts the event history in the key-value store up to a given revision.
     *
     * @param int $revision
     * @param bool $physical
     * @return bool
     */
    public function compact(int $revision, bool $physical = false): bool
    {
        return $this->service->compact($revision, $physical);
    }

    /**
     * @return bool
     */
    public function authEnable(): bool
    {
        return $this->service->authEnable();
    }

    /**
     * @return bool
     */
    public function authDisable(): bool
    {
        return $this->service->authDisable();
    }

    /**
     * @return array
     */
    public function authStatus(): array
    {
        return $this->service->authStatus();
    }

    /**
     * @param string $name
     * @param string $password
     * @param bool $noPassword
     * @return bool
     */
    public function userAdd(string $name, string $password, bool $noPassword = false): bool
    {
        return $this->service->userAdd($name, $password, $noPassword);
    }

    /**
     * @param string $name
     * @return array
     */
    public function userGet(string $name): array
    {
        return $this->service->userGet($name);
    }

    /**
     * @return array
     */
    public function userList(): array
    {
        return $this->service->userList();
    }

    /**
     * @param string $name
     * @return bool
     */
    public function userDelete(string $name): bool
    {
        return $this->service->userDelete($name);
    }

    /**
     * @param string $name
     * @param string $password
     * @return bool
     */
    public function userChangePassword(string $name, string $password): bool
    {
        return $this->service->userChangePassword($name, $password);
    }

    /**
     * @param string $user
     * @param string $role
     * @return bool
     */
    public function userGrantRole(string $user, string $role): bool
    {
        return $this->service->userGrantRole($user, $role);
    }

    /**
     * @param string $user
     * @param string $role
     * @return bool
     */
    public function userRevokeRole(string $user, string $role): bool
    {
        return $this->service->userRevokeRole($user, $role);
    }

    /**
     * @param string $name
     * @return bool
     */
    public function roleAdd(string $name): bool
    {
        return $this->service->roleAdd($name);
    }

    /**
     * @param string $name
     * @return array
     */
    public function roleGet(string $name): array
    {
        return $this->service->roleGet($name);
    }

    /**
     * @return array
     */
    public function roleList(): array
    {
        return $this->service->roleList();
    }

    /**
     * @param string $name
     * @return bool
     */
    public function roleDelete(string $name): bool
    {
        return $this->service->roleDelete($name);
    }

    /**
     * @param string $name
     * @param string $permType
     * @param string $key
     * @param string $rangeEnd
     * @return bool
     */
    public function roleGrantPermission(string $name, string $permType, string $key, string $rangeEnd = ''): bool
    {
        return $this->service->roleGrantPermission($name, $permType, $key, $rangeEnd);
    }

    /**
     * @param string $role
     * @param string $key
     * @param string $rangeEnd
     * @return bool
     */
    public function roleRevokePermission(string $role, string $key, string $rangeEnd = ''): bool
    {
        return $this->service->roleRevokePermission($role, $key, $rangeEnd);
    }

    /**
     * @param string[] $peerUrls
     * @param bool $isLearner
     * @return array
     */
    public function memberAdd(array $peerUrls, bool $isLearner = false): array
    {
        return $this->service->memberAdd($peerUrls, $isLearner);
    }

    /**
     * @param int|string $id
     * @return array
     */
    public function memberRemove(int|string $id): array
    {
        return $this->service->memberRemove($id);
    }

    /**
     * @param int|string $id
     * @param string[] $peerUrls
     * @return array
     */
    public function memberUpdate(int|string $id, array $peerUrls): array
    {
        return $this->service->memberUpdate($id, $peerUrls);
    }

    /**
     * @param bool $linearizable
     * @return array
     */
    public function memberList(bool $linearizable = false): array
    {
        return $this->service->memberList($linearizable);
    }

    /**
     * @param int|string $id
     * @return array
     */
    public function memberPromote(int|string $id): array
    {
        return $this->service->memberPromote($id);
    }

    /**
     * @param string $action
     * @param int|string $memberId
     * @param string $alarmType
     * @return array
     */
    public function alarm(string $action, int|string $memberId = 0, string $alarmType = AlarmType::NONE): array
    {
        return $this->service->alarm($action, $memberId, $alarmType);
    }

    /**
     * @return array
     */
    public function status(): array
    {
        return $this->service->status();
    }

    /**
     * @return bool
     */
    public function defragment(): bool
    {
        return $this->service->defragment();
    }

    /**
     * @return array
     */
    public function hash(): array
    {
        return $this->service->hash();
    }

    /**
     * @param int $revision
     * @return array
     */
    public function hashKv(int $revision = 0): array
    {
        return $this->service->hashKv($revision);
    }

    /**
     * @param int|string $targetId
     * @return bool
     */
    public function moveLeader(int|string $targetId): bool
    {
        return $this->service->moveLeader($targetId);
    }

    /**
     * @param string $action
     * @param string $version
     * @return array
     */
    public function downgrade(string $action, string $version = ''): array
    {
        return $this->service->downgrade($action, $version);
    }

    /**
     * @param int $ttl
     * @param int $id
     * @return array
     */
    public function leaseGrant(int $ttl, int $id = 0): array
    {
        return $this->service->leaseGrant($ttl, $id);
    }

    /**
     * @param int $id
     * @return bool
     */
    public function leaseRevoke(int $id): bool
    {
        return $this->service->leaseRevoke($id);
    }

    /**
     * @param int $id
     * @param bool $keys
     * @return array
     */
    public function leaseTimeToLive(int $id, bool $keys = false): array
    {
        return $this->service->leaseTimeToLive($id, $keys);
    }

    /**
     * @return array
     */
    public function leaseLeases(): array
    {
        return $this->service->leaseLeases();
    }

    /**
     * Authentication
     *
     * @return EtcdAuthInterface
     */
    public function getAuth(): EtcdAuthInterface
    {
        return $this->service->getAuthModel();
    }
}

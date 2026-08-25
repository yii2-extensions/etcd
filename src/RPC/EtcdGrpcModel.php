<?php

declare(strict_types=1);

namespace Yii2\Extensions\Etcd\RPC;

use Etcd\AlarmRequest;
use Etcd\AlarmRequest\AlarmAction;
use Etcd\AlarmType;
use Etcd\AuthClient;
use Etcd\AuthDisableRequest;
use Etcd\AuthEnableRequest;
use Etcd\AuthRoleAddRequest;
use Etcd\AuthRoleDeleteRequest;
use Etcd\AuthRoleGetRequest;
use Etcd\AuthRoleGrantPermissionRequest;
use Etcd\AuthRoleListRequest;
use Etcd\AuthRoleRevokePermissionRequest;
use Etcd\AuthStatusRequest;
use Etcd\AuthStatusResponse;
use Etcd\AuthUserAddRequest;
use Etcd\AuthUserChangePasswordRequest;
use Etcd\AuthUserDeleteRequest;
use Etcd\AuthUserGetRequest;
use Etcd\AuthUserGrantRoleRequest;
use Etcd\AuthUserListRequest;
use Etcd\AuthUserRevokeRoleRequest;
use Etcd\ClusterClient;
use Etcd\Compare;
use Etcd\Compare\CompareResult;
use Etcd\Compare\CompareTarget;
use Etcd\CompactionRequest;
use Etcd\DeleteRangeRequest;
use Etcd\DeleteRangeResponse;
use Etcd\DowngradeRequest;
use Etcd\DowngradeRequest\DowngradeAction;
use Etcd\HashKVRequest;
use Etcd\HashKVResponse;
use Etcd\HashRequest;
use Etcd\HashResponse;
use Etcd\KeyValue;
use Etcd\KVClient;
use Etcd\LeaseClient;
use Etcd\LeaseGrantRequest;
use Etcd\LeaseGrantResponse;
use Etcd\LeaseLeasesRequest;
use Etcd\LeaseLeasesResponse;
use Etcd\LeaseRevokeRequest;
use Etcd\LeaseTimeToLiveRequest;
use Etcd\LeaseTimeToLiveResponse;
use Etcd\MaintenanceClient;
use Etcd\Member;
use Etcd\MemberAddRequest;
use Etcd\MemberListRequest;
use Etcd\MemberPromoteRequest;
use Etcd\MemberRemoveRequest;
use Etcd\MemberUpdateRequest;
use Etcd\MoveLeaderRequest;
use Etcd\Permission;
use Etcd\Permission\Type as EtcdPermissionType;
use Etcd\PutRequest;
use Etcd\PutResponse;
use Etcd\RangeRequest;
use Etcd\RangeResponse as EtcdRangeResponse;
use Etcd\RequestOp;
use Etcd\ResponseHeader;
use Etcd\ResponseOp;
use Etcd\StatusRequest;
use Etcd\StatusResponse;
use Etcd\TxnRequest;
use Etcd\TxnResponse;
use Etcd\UserAddOptions;
use Google\Protobuf\Internal\Message;
use Google\Protobuf\Internal\RepeatedField;
use Grpc\ChannelCredentials;
use Grpc\UnaryCall;
use Yii2\Extensions\Etcd\EtcdServiceInterface;
use Yii2\Extensions\Etcd\Exceptions\EtcdException;
use Yii2\Extensions\Etcd\Services\EtcdAuthGrpc;
use Yii2\Extensions\Etcd\Services\EtcdAuthInterface;

use const Grpc\STATUS_OK;

class EtcdGrpcModel implements EtcdServiceInterface
{
    public string $host = '';
    public string $user = '';
    public string $password = '';
    /** @var array<string, mixed> */
    public array $clientOptions = [];

    /** @var array<string, mixed> */
    public array $metadata = [];

    private KVClient $client;
    private ?AuthClient $authClient = null;
    private ?ClusterClient $clusterClient = null;
    private ?MaintenanceClient $maintenanceClient = null;
    private ?LeaseClient $leaseClient = null;

    /**
     * @param string $host
     * @param string $user
     * @param string $password
     * @param array<string, mixed> $clientOptions
     * @throws EtcdException
     */
    public function __construct(string $host, string $user, string $password, array $clientOptions)
    {
        if (!extension_loaded('grpc')) {
            throw new EtcdException('Not install grpc extensions');
        }

        $this->host = $host;
        $this->user = $user;
        $this->password = $password;
        $this->clientOptions = $clientOptions;

        $this->client = $this->getClient();
    }

    public function getAuthModel(): EtcdAuthInterface
    {
        return new EtcdAuthGrpc($this->host, $this->user, $this->password);
    }

    public function getVersion(): string
    {
        return 'Not supported';
    }

    public function getRange(string $key, string $rangeEnd): RangeResponse
    {
        $request = new RangeRequest();
        $request->setKey($key);
        $request->setRangeEnd($rangeEnd);

        /** @var EtcdRangeResponse $response */
        $response = $this->wait($this->client->Range($request));

        return new RangeResponse($this->collectKvs($response->getKvs()));
    }

    public function getKey(string $key): RangeResponse
    {
        $request = new RangeRequest();
        $request->setKey($key);

        /** @var EtcdRangeResponse $response */
        $response = $this->wait($this->client->Range($request));

        return new RangeResponse($this->collectKvs($response->getKvs()));
    }

    public function put(string $key, string $value): bool
    {
        $request = new PutRequest();
        $request->setKey($key);
        $request->setValue($value);

        $this->wait($this->client->Put($request));

        return true;
    }

    public function deleteRange(string $key, string $rangeEnd = ''): array
    {
        $request = new DeleteRangeRequest();
        $request->setKey($key);

        if ('' !== $rangeEnd) {
            $request->setRangeEnd($rangeEnd);
        }

        /** @var DeleteRangeResponse $response */
        $response = $this->wait($this->client->DeleteRange($request));

        return $this->convertDeleteRangeResponse($response);
    }

    public function txn(array $compare, array $success, array $failure): array
    {
        $request = new TxnRequest();
        $request->setCompare($this->buildCompares($compare));
        $request->setSuccess($this->buildRequestOps($success));
        $request->setFailure($this->buildRequestOps($failure));

        /** @var TxnResponse $response */
        $response = $this->wait($this->client->Txn($request));

        return $this->convertTxnResponse($response);
    }

    public function compact(int $revision, bool $physical = false): bool
    {
        $request = new CompactionRequest();
        $request->setRevision($revision);
        $request->setPhysical($physical);

        $this->wait($this->client->Compact($request));

        return true;
    }

    public function authEnable(): bool
    {
        $this->wait($this->getAuthClient()->AuthEnable(new AuthEnableRequest()));

        return true;
    }

    public function authDisable(): bool
    {
        $this->wait($this->getAuthClient()->AuthDisable(new AuthDisableRequest()));

        return true;
    }

    public function authStatus(): array
    {
        /** @var AuthStatusResponse $response */
        $response = $this->wait($this->getAuthClient()->AuthStatus(new AuthStatusRequest()));

        return [
            'header' => $this->convertHeader($response->getHeader()),
            'enabled' => $response->getEnabled(),
            'authRevision' => $response->getAuthRevision(),
        ];
    }

    public function userAdd(string $name, string $password, bool $noPassword = false): bool
    {
        $request = new AuthUserAddRequest();
        $request->setName($name);
        $request->setPassword($password);

        if ($noPassword) {
            $request->setOptions(new UserAddOptions(['no_password' => true]));
        }

        $this->wait($this->getAuthClient()->UserAdd($request));

        return true;
    }

    public function userGet(string $name): array
    {
        $request = new AuthUserGetRequest();
        $request->setName($name);

        /** @var \Etcd\AuthUserGetResponse $response */
        $response = $this->wait($this->getAuthClient()->UserGet($request));

        return [
            'header' => $this->convertHeader($response->getHeader()),
            'roles' => $this->convertStringList($response->getRoles()),
        ];
    }

    public function userList(): array
    {
        /** @var \Etcd\AuthUserListResponse $response */
        $response = $this->wait($this->getAuthClient()->UserList(new AuthUserListRequest()));

        return [
            'header' => $this->convertHeader($response->getHeader()),
            'users' => $this->convertStringList($response->getUsers()),
        ];
    }

    public function userDelete(string $name): bool
    {
        $request = new AuthUserDeleteRequest();
        $request->setName($name);

        $this->wait($this->getAuthClient()->UserDelete($request));

        return true;
    }

    public function userChangePassword(string $name, string $password): bool
    {
        $request = new AuthUserChangePasswordRequest();
        $request->setName($name);
        $request->setPassword($password);

        $this->wait($this->getAuthClient()->UserChangePassword($request));

        return true;
    }

    public function userGrantRole(string $user, string $role): bool
    {
        $request = new AuthUserGrantRoleRequest();
        $request->setUser($user);
        $request->setRole($role);

        $this->wait($this->getAuthClient()->UserGrantRole($request));

        return true;
    }

    public function userRevokeRole(string $user, string $role): bool
    {
        $request = new AuthUserRevokeRoleRequest();
        $request->setName($user);
        $request->setRole($role);

        $this->wait($this->getAuthClient()->UserRevokeRole($request));

        return true;
    }

    public function roleAdd(string $name): bool
    {
        $request = new AuthRoleAddRequest();
        $request->setName($name);

        $this->wait($this->getAuthClient()->RoleAdd($request));

        return true;
    }

    public function roleGet(string $name): array
    {
        $request = new AuthRoleGetRequest();
        $request->setRole($name);

        /** @var \Etcd\AuthRoleGetResponse $response */
        $response = $this->wait($this->getAuthClient()->RoleGet($request));

        return [
            'header' => $this->convertHeader($response->getHeader()),
            'perm' => $this->convertPermissions($response->getPerm()),
        ];
    }

    public function roleList(): array
    {
        /** @var \Etcd\AuthRoleListResponse $response */
        $response = $this->wait($this->getAuthClient()->RoleList(new AuthRoleListRequest()));

        return [
            'header' => $this->convertHeader($response->getHeader()),
            'roles' => $this->convertStringList($response->getRoles()),
        ];
    }

    public function roleDelete(string $name): bool
    {
        $request = new AuthRoleDeleteRequest();
        $request->setRole($name);

        $this->wait($this->getAuthClient()->RoleDelete($request));

        return true;
    }

    public function roleGrantPermission(string $name, string $permType, string $key, string $rangeEnd = ''): bool
    {
        $perm = new Permission();
        $perm->setPermType((int) EtcdPermissionType::value($permType));
        $perm->setKey($key);

        if ('' !== $rangeEnd) {
            $perm->setRangeEnd($rangeEnd);
        }

        $request = new AuthRoleGrantPermissionRequest();
        $request->setName($name);
        $request->setPerm($perm);

        $this->wait($this->getAuthClient()->RoleGrantPermission($request));

        return true;
    }

    public function roleRevokePermission(string $role, string $key, string $rangeEnd = ''): bool
    {
        $request = new AuthRoleRevokePermissionRequest();
        $request->setRole($role);
        $request->setKey($key);

        if ('' !== $rangeEnd) {
            $request->setRangeEnd($rangeEnd);
        }

        $this->wait($this->getAuthClient()->RoleRevokePermission($request));

        return true;
    }

    public function memberAdd(array $peerUrls, bool $isLearner = false): array
    {
        $request = new MemberAddRequest();
        $request->setPeerUrls($peerUrls);
        $request->setIsLearner($isLearner);

        /** @var \Etcd\MemberAddResponse $response */
        $response = $this->wait($this->getClusterClient()->MemberAdd($request));

        return [
            'header' => $this->convertHeader($response->getHeader()),
            'member' => null === $response->getMember() ? [] : $this->convertMember($response->getMember()),
            'members' => $this->convertMembers($response->getMembers()),
        ];
    }

    public function memberRemove(int|string $id): array
    {
        $request = new MemberRemoveRequest();
        $request->setID($id);

        /** @var \Etcd\MemberRemoveResponse $response */
        $response = $this->wait($this->getClusterClient()->MemberRemove($request));

        return [
            'header' => $this->convertHeader($response->getHeader()),
            'members' => $this->convertMembers($response->getMembers()),
        ];
    }

    public function memberUpdate(int|string $id, array $peerUrls): array
    {
        $request = new MemberUpdateRequest();
        $request->setID($id);
        $request->setPeerUrls($peerUrls);

        /** @var \Etcd\MemberUpdateResponse $response */
        $response = $this->wait($this->getClusterClient()->MemberUpdate($request));

        return [
            'header' => $this->convertHeader($response->getHeader()),
            'members' => $this->convertMembers($response->getMembers()),
        ];
    }

    public function memberList(bool $linearizable = false): array
    {
        $request = new MemberListRequest();
        $request->setLinearizable($linearizable);

        /** @var \Etcd\MemberListResponse $response */
        $response = $this->wait($this->getClusterClient()->MemberList($request));

        return [
            'header' => $this->convertHeader($response->getHeader()),
            'members' => $this->convertMembers($response->getMembers()),
        ];
    }

    public function memberPromote(int|string $id): array
    {
        $request = new MemberPromoteRequest();
        $request->setID($id);

        /** @var \Etcd\MemberPromoteResponse $response */
        $response = $this->wait($this->getClusterClient()->MemberPromote($request));

        return [
            'header' => $this->convertHeader($response->getHeader()),
            'members' => $this->convertMembers($response->getMembers()),
        ];
    }

    public function alarm(string $action, int|string $memberId = 0, string $alarmType = 'NONE'): array
    {
        $request = new AlarmRequest();
        $request->setAction((int) AlarmAction::value($action));
        $request->setMemberID($memberId);
        $request->setAlarm((int) AlarmType::value($alarmType));

        /** @var \Etcd\AlarmResponse $response */
        $response = $this->wait($this->getMaintenanceClient()->Alarm($request));

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
        $response = $this->wait($this->getMaintenanceClient()->Status(new StatusRequest()));

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
        $this->wait($this->getMaintenanceClient()->Defragment(new \Etcd\DefragmentRequest()));

        return true;
    }

    public function hash(): array
    {
        /** @var HashResponse $response */
        $response = $this->wait($this->getMaintenanceClient()->Hash(new HashRequest()));

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
        $response = $this->wait($this->getMaintenanceClient()->HashKV($request));

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

        $this->wait($this->getMaintenanceClient()->MoveLeader($request));

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
        $response = $this->wait($this->getMaintenanceClient()->Downgrade($request));

        return [
            'header' => $this->convertHeader($response->getHeader()),
            'version' => $response->getVersion(),
        ];
    }

    public function leaseGrant(int $ttl, int $id = 0): array
    {
        $request = new LeaseGrantRequest();
        $request->setTTL($ttl);
        $request->setID($id);

        /** @var LeaseGrantResponse $response */
        $response = $this->wait($this->getLeaseClient()->LeaseGrant($request));

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

        $this->wait($this->getLeaseClient()->LeaseRevoke($request));

        return true;
    }

    public function leaseTimeToLive(int $id, bool $keys = false): array
    {
        $request = new LeaseTimeToLiveRequest();
        $request->setID($id);
        $request->setKeys($keys);

        /** @var LeaseTimeToLiveResponse $response */
        $response = $this->wait($this->getLeaseClient()->LeaseTimeToLive($request));

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
        $response = $this->wait($this->getLeaseClient()->LeaseLeases(new LeaseLeasesRequest()));

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

    private function getClient(): KVClient
    {
        return new KVClient($this->host, $this->getConnectionOptions());
    }

    private function getAuthClient(): AuthClient
    {
        return $this->authClient ??= new AuthClient($this->host, $this->getConnectionOptions());
    }

    private function getClusterClient(): ClusterClient
    {
        return $this->clusterClient ??= new ClusterClient($this->host, $this->getConnectionOptions());
    }

    private function getMaintenanceClient(): MaintenanceClient
    {
        return $this->maintenanceClient ??= new MaintenanceClient($this->host, $this->getConnectionOptions());
    }

    private function getLeaseClient(): LeaseClient
    {
        return $this->leaseClient ??= new LeaseClient($this->host, $this->getConnectionOptions());
    }

    /**
     * @return array<string, mixed>
     */
    private function getConnectionOptions(): array
    {
        return [
            'credentials' => ChannelCredentials::createInsecure(),
            'update_metadata' => function ($metaData) {
                $token = $this->getAuthModel()->authenticate();

                if ('' !== $token) {
                    $metaData['Authorization'] = [$token];
                }

                return $metaData;
            },
        ];
    }

    /**
     * Wait for the unary call to finish and validate the response status.
     *
     * @template T of Message
     * @param UnaryCall<T> $call
     * @return T
     */
    private function wait(UnaryCall $call)
    {
        [$response, $status] = $call->wait();

        if (STATUS_OK !== $status->code) {
            throw new EtcdException('Errors: ' . $status->details);
        }

        if (null === $response) {
            throw new EtcdException('Errors: empty response');
        }

        return $response;
    }

    /**
     * @param array<int, array<string, mixed>> $compares
     * @return Compare[]
     */
    private function buildCompares(array $compares): array
    {
        $result = [];

        foreach ($compares as $compare) {
            $item = new Compare();
            $item->setResult(is_int($compare['result'])
                ? $compare['result']
                : (int) CompareResult::value($compare['result']));
            $item->setTarget(is_int($compare['target'])
                ? $compare['target']
                : (int) CompareTarget::value($compare['target']));
            $item->setKey((string) $compare['key']);

            if (isset($compare['range_end'])) {
                $item->setRangeEnd((string) $compare['range_end']);
            }

            if (isset($compare['version'])) {
                $item->setVersion($compare['version']);
            } elseif (isset($compare['create_revision'])) {
                $item->setCreateRevision($compare['create_revision']);
            } elseif (isset($compare['mod_revision'])) {
                $item->setModRevision($compare['mod_revision']);
            } elseif (isset($compare['lease'])) {
                $item->setLease($compare['lease']);
            } elseif (isset($compare['value'])) {
                $item->setValue((string) $compare['value']);
            }

            $result[] = $item;
        }

        return $result;
    }

    /**
     * @param array<int, array<string, mixed>> $ops
     * @return RequestOp[]
     */
    private function buildRequestOps(array $ops): array
    {
        $result = [];

        foreach ($ops as $op) {
            $item = new RequestOp();

            if (isset($op['request_range'])) {
                $item->setRequestRange($this->buildRangeRequest($op['request_range']));
            } elseif (isset($op['request_put'])) {
                $item->setRequestPut($this->buildPutRequest($op['request_put']));
            } elseif (isset($op['request_delete_range'])) {
                $item->setRequestDeleteRange($this->buildDeleteRangeRequest($op['request_delete_range']));
            } elseif (isset($op['request_txn'])) {
                $txn = $op['request_txn'];
                $request = new TxnRequest();
                $request->setCompare($this->buildCompares($txn['compare']));
                $request->setSuccess($this->buildRequestOps($txn['success']));
                $request->setFailure($this->buildRequestOps($txn['failure']));
                $item->setRequestTxn($request);
            }

            $result[] = $item;
        }

        return $result;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function buildRangeRequest(array $data): RangeRequest
    {
        $request = new RangeRequest();

        if (isset($data['key'])) {
            $request->setKey((string) $data['key']);
        }

        if (isset($data['range_end'])) {
            $request->setRangeEnd((string) $data['range_end']);
        }

        if (isset($data['limit'])) {
            $request->setLimit($data['limit']);
        }

        if (isset($data['revision'])) {
            $request->setRevision($data['revision']);
        }

        if (isset($data['sort_order'])) {
            $request->setSortOrder($data['sort_order']);
        }

        if (isset($data['sort_target'])) {
            $request->setSortTarget($data['sort_target']);
        }

        if (isset($data['serializable'])) {
            $request->setSerializable((bool) $data['serializable']);
        }

        if (isset($data['keys_only'])) {
            $request->setKeysOnly((bool) $data['keys_only']);
        }

        if (isset($data['count_only'])) {
            $request->setCountOnly((bool) $data['count_only']);
        }

        return $request;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function buildPutRequest(array $data): PutRequest
    {
        $request = new PutRequest();

        if (isset($data['key'])) {
            $request->setKey((string) $data['key']);
        }

        if (isset($data['value'])) {
            $request->setValue((string) $data['value']);
        }

        if (isset($data['lease'])) {
            $request->setLease($data['lease']);
        }

        if (isset($data['prev_kv'])) {
            $request->setPrevKv((bool) $data['prev_kv']);
        }

        if (isset($data['ignore_value'])) {
            $request->setIgnoreValue((bool) $data['ignore_value']);
        }

        if (isset($data['ignore_lease'])) {
            $request->setIgnoreLease((bool) $data['ignore_lease']);
        }

        return $request;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function buildDeleteRangeRequest(array $data): DeleteRangeRequest
    {
        $request = new DeleteRangeRequest();

        if (isset($data['key'])) {
            $request->setKey((string) $data['key']);
        }

        if (isset($data['range_end'])) {
            $request->setRangeEnd((string) $data['range_end']);
        }

        if (isset($data['prev_kv'])) {
            $request->setPrevKv((bool) $data['prev_kv']);
        }

        return $request;
    }

    /**
     * @return array<string, mixed>
     */
    private function convertHeader(?ResponseHeader $header): array
    {
        if (null === $header) {
            return [];
        }

        return [
            'cluster_id' => $header->getClusterId(),
            'member_id' => $header->getMemberId(),
            'revision' => $header->getRevision(),
            'raft_term' => $header->getRaftTerm(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function convertKeyValue(KeyValue $kv): array
    {
        return [
            'key' => $kv->getKey(),
            'value' => $kv->getValue(),
            'create_revision' => $kv->getCreateRevision(),
            'mod_revision' => $kv->getModRevision(),
            'version' => $kv->getVersion(),
            'lease' => $kv->getLease(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function convertRangeResponse(?EtcdRangeResponse $response): array
    {
        if (null === $response) {
            return [];
        }

        $kvs = [];

        /** @var RepeatedField<\Etcd\KeyValue> $kvList */
        $kvList = $response->getKvs();

        foreach ($kvList as $kv) {
            $kvs[] = $this->convertKeyValue($kv);
        }

        return [
            'header' => $this->convertHeader($response->getHeader()),
            'kvs' => $kvs,
            'more' => $response->getMore(),
            'count' => $response->getCount(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function convertPutResponse(?PutResponse $response): array
    {
        if (null === $response) {
            return [];
        }

        return [
            'header' => $this->convertHeader($response->getHeader()),
            'prev_kv' => null === $response->getPrevKv() ? [] : $this->convertKeyValue($response->getPrevKv()),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function convertDeleteRangeResponse(?DeleteRangeResponse $response): array
    {
        if (null === $response) {
            return [];
        }

        return [
            'header' => $this->convertHeader($response->getHeader()),
            'deleted' => $response->getDeleted(),
            'prev_kvs' => $this->convertKeyValues($response->getPrevKvs()),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function convertTxnResponse(?TxnResponse $response): array
    {
        if (null === $response) {
            return [];
        }

        $responses = [];

        /** @var RepeatedField<\Etcd\ResponseOp> $opList */
        $opList = $response->getResponses();

        foreach ($opList as $op) {
            $responses[] = $this->convertResponseOp($op);
        }

        return [
            'header' => $this->convertHeader($response->getHeader()),
            'succeeded' => $response->getSucceeded(),
            'responses' => $responses,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function convertResponseOp(ResponseOp $op): array
    {
        if ($op->hasResponseRange()) {
            return ['response_range' => $this->convertRangeResponse($op->getResponseRange())];
        }

        if ($op->hasResponsePut()) {
            return ['response_put' => $this->convertPutResponse($op->getResponsePut())];
        }

        if ($op->hasResponseDeleteRange()) {
            return ['response_delete_range' => $this->convertDeleteRangeResponse($op->getResponseDeleteRange())];
        }

        if ($op->hasResponseTxn()) {
            return ['response_txn' => $this->convertTxnResponse($op->getResponseTxn())];
        }

        return [];
    }

    /**
     * @return array<string, mixed>
     */
    private function convertMember(Member $member): array
    {
        return [
            'ID' => $member->getID(),
            'name' => $member->getName(),
            'peerURLs' => $this->convertStringList($member->getPeerURLs()),
            'clientURLs' => $this->convertStringList($member->getClientURLs()),
            'isLearner' => $member->getIsLearner(),
        ];
    }

    /**
     * @param RepeatedField<\Etcd\Member> $members
     * @return array<int, array<string, mixed>>
     */
    private function convertMembers(RepeatedField $members): array
    {
        $result = [];

        /** @var RepeatedField<\Etcd\Member> $memberList */
        $memberList = $members;

        foreach ($memberList as $member) {
            $result[] = $this->convertMember($member);
        }

        return $result;
    }

    /**
     * @param iterable<string> $fields
     * @return array<int, string>
     */
    private function convertStringList($fields): array
    {
        $result = [];

        foreach ($fields as $field) {
            $result[] = $field;
        }

        return $result;
    }

    /**
     * @param RepeatedField<\Etcd\KeyValue> $kvs
     * @return array<int, array<string, mixed>>
     */
    private function convertKeyValues(RepeatedField $kvs): array
    {
        $result = [];

        /** @var RepeatedField<\Etcd\KeyValue> $kvList */
        $kvList = $kvs;

        foreach ($kvList as $kv) {
            $result[] = $this->convertKeyValue($kv);
        }

        return $result;
    }

    /**
     * @param RepeatedField<\Etcd\Permission> $permissions
     * @return array<int, array<string, mixed>>
     */
    private function convertPermissions(RepeatedField $permissions): array
    {
        $result = [];

        /** @var RepeatedField<\Etcd\Permission> $permList */
        $permList = $permissions;

        foreach ($permList as $perm) {
            $result[] = [
                'permType' => EtcdPermissionType::name($perm->getPermType()),
                'key' => $perm->getKey(),
                'range_end' => $perm->getRangeEnd(),
            ];
        }

        return $result;
    }

    /**
     * @param RepeatedField<\Etcd\KeyValue> $fields
     * @return array<string, mixed>
     */
    private function collectKvs(RepeatedField $fields): array
    {
        $kvs = [];
        $protobufKvs = $fields;

        foreach ($protobufKvs as $item) {
            /** @var KeyValue $item */
            $kvs['kvs'][] = [
                'key' => $item->getKey(),
                'value' => $item->getValue(),
            ];
        }

        return $kvs;
    }
}

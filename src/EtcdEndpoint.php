<?php

declare(strict_types=1);

namespace Yii2\Extensions\Etcd;

final class EtcdEndpoint
{
    // Version
    public const string ETCD_VERSION = '/v3';

    // Common
    public const string VERSION = '/version';

    // KV
    public const string PUT = '/kv/put';
    public const string RANGE = '/kv/range';
    public const string DELETE_RANGE = '/kv/deleterange';
    public const string TXN = '/kv/txn';
    public const string COMPACTION = '/kv/compaction';

    // Authentication
    public const string AUTHENTICATE = '/auth/authenticate';
    public const string AUTH_ENABLE = '/auth/enable';
    public const string AUTH_DISABLE = '/auth/disable';
    public const string AUTH_STATUS = '/auth/status';
    public const string USER_ADD = '/auth/user/add';
    public const string USER_GET = '/auth/user/get';
    public const string USER_LIST = '/auth/user/list';
    public const string USER_DELETE = '/auth/user/delete';
    public const string USER_CHANGE_PASSWORD = '/auth/user/changepw';
    public const string USER_GRANT_ROLE = '/auth/user/grant';
    public const string USER_REVOKE_ROLE = '/auth/user/revoke';
    public const string ROLE_ADD = '/auth/role/add';
    public const string ROLE_GET = '/auth/role/get';
    public const string ROLE_LIST = '/auth/role/list';
    public const string ROLE_DELETE = '/auth/role/delete';
    public const string ROLE_GRANT_PERMISSION = '/auth/role/grant';
    public const string ROLE_REVOKE_PERMISSION = '/auth/role/revoke';

    // Cluster
    public const string MEMBER_ADD = '/cluster/member/add';
    public const string MEMBER_REMOVE = '/cluster/member/remove';
    public const string MEMBER_UPDATE = '/cluster/member/update';
    public const string MEMBER_LIST = '/cluster/member/list';
    public const string MEMBER_PROMOTE = '/cluster/member/promote';

    // Maintenance
    public const string ALARM = '/maintenance/alarm';
    public const string STATUS = '/maintenance/status';
    public const string DEFRAGMENT = '/maintenance/defragment';
    public const string HASH = '/maintenance/hash';
    public const string HASH_KV = '/maintenance/hashkv';
    public const string MOVE_LEADER = '/maintenance/transfer-leadership';
    public const string DOWNGRADE = '/maintenance/downgrade';

    // Lease
    public const string LEASE_GRANT = '/lease/grant';
    public const string LEASE_REVOKE = '/lease/revoke';
    public const string LEASE_TIME_TO_LIVE = '/lease/timetolive';
    public const string LEASE_LEASES = '/lease/leases';
}

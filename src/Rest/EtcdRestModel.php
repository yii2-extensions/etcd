<?php

declare(strict_types=1);

namespace Yii2\Extensions\Etcd\Rest;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use JsonException;
use Yii2\Extensions\Etcd\EtcdEndpoint;
use Yii2\Extensions\Etcd\EtcdServiceInterface;
use Yii2\Extensions\Etcd\Services\EtcdAuthInterface;
use Yii2\Extensions\Etcd\Services\EtcdAuthRest;

class EtcdRestModel implements EtcdServiceInterface
{
    public string $host = '';
    public string $user = '';
    public string $password = '';
    private Client $client;

    /**
     * @param string $host
     * @param string $user
     * @param string $password
     * @param array<string, mixed> $clientOptions
     */
    public function __construct(string $host, string $user, string $password, array $clientOptions)
    {
        $this->host = $host;
        $this->user = $user;
        $this->password = $password;
        $this->client = new Client($clientOptions);
    }

    public function getKey(string $key): RangeResponse
    {
        /** @var array{body: string, headers?: array<string, string>} $options */
        $options = [
            RequestOptions::BODY => json_encode(['key' => trim(base64_encode($key))], JSON_THROW_ON_ERROR),
        ];
        $response = $this->client->post(
            $this->host . EtcdEndpoint::ETCD_VERSION . EtcdEndpoint::RANGE,
            array_merge($options, $this->getTokenOptions())
        );

        return new RangeResponse(json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR));
    }

    /**
     * Get all keys prefixed with "foo" ($key = foo, $rangeEnd = fop)
     *
     * @param string $key
     * @param string $rangeEnd
     * @return RangeResponse
     * @throws GuzzleException|JsonException
     */
    public function getRange(string $key, string $rangeEnd): RangeResponse
    {
        /** @var array{body: string, headers?: array<string, string>} $options */
        $options = [
            RequestOptions::BODY => json_encode(
                ['key' => trim(base64_encode($key)), 'range_end' => trim(base64_encode($rangeEnd))],
                JSON_THROW_ON_ERROR
            ),
        ];

        $response = $this->client->post(
            $this->host . EtcdEndpoint::ETCD_VERSION . EtcdEndpoint::RANGE,
            array_merge($options, $this->getTokenOptions())
        );

        return new RangeResponse(json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR));
    }

    public function put(string $key, string $value): bool
    {
        /** @var array{body: string, headers?: array<string, string>} $options */
        $options = [
            RequestOptions::BODY => json_encode(
                ['key' => base64_encode(trim($key)), 'value' => base64_encode(trim($value))],
                JSON_THROW_ON_ERROR
            ),
        ];
        $response = $this->client->post(
            $this->host . EtcdEndpoint::ETCD_VERSION . EtcdEndpoint::PUT,
            array_merge($options, $this->getTokenOptions())
        );

        return 200 === $response->getStatusCode();
    }

    /**
     * @throws GuzzleException|JsonException
     */
    public function deleteRange(string $key, string $rangeEnd = ''): array
    {
        $body = ['key' => base64_encode(trim($key))];

        if ('' !== $rangeEnd) {
            $body['range_end'] = base64_encode(trim($rangeEnd));
        }

        return $this->request(EtcdEndpoint::DELETE_RANGE, $body);
    }

    /**
     * @param array<int, array<string, mixed>> $compare
     * @param array<int, array<string, mixed>> $success
     * @param array<int, array<string, mixed>> $failure
     * @return array<string, mixed>
     * @throws GuzzleException|JsonException
     */
    public function txn(array $compare, array $success, array $failure): array
    {
        return $this->request(
            EtcdEndpoint::TXN,
            [
                'compare' => $this->transformCompares($compare),
                'success' => $this->transformRequestOps($success),
                'failure' => $this->transformRequestOps($failure),
            ]
        );
    }

    /**
     * @throws GuzzleException|JsonException
     */
    public function compact(int $revision, bool $physical = false): bool
    {
        $result = $this->request(
            EtcdEndpoint::COMPACTION,
            ['revision' => (string) $revision, 'physical' => $physical]
        );

        return isset($result['header']);
    }

    /**
     * @throws GuzzleException|JsonException
     */
    public function authEnable(): bool
    {
        return isset($this->request(EtcdEndpoint::AUTH_ENABLE, [])['header']);
    }

    /**
     * @throws GuzzleException|JsonException
     */
    public function authDisable(): bool
    {
        return isset($this->request(EtcdEndpoint::AUTH_DISABLE, [])['header']);
    }

    /**
     * @return array<string, mixed>
     * @throws GuzzleException|JsonException
     */
    public function authStatus(): array
    {
        return $this->request(EtcdEndpoint::AUTH_STATUS, []);
    }

    /**
     * @throws GuzzleException|JsonException
     */
    public function userAdd(string $name, string $password, bool $noPassword = false): bool
    {
        $body = ['name' => $name, 'password' => $password];

        if ($noPassword) {
            $body['options'] = ['no_password' => true];
        }

        return isset($this->request(EtcdEndpoint::USER_ADD, $body)['header']);
    }

    /**
     * @return array<string, mixed>
     * @throws GuzzleException|JsonException
     */
    public function userGet(string $name): array
    {
        return $this->request(EtcdEndpoint::USER_GET, ['name' => $name]);
    }

    /**
     * @return array<string, mixed>
     * @throws GuzzleException|JsonException
     */
    public function userList(): array
    {
        return $this->request(EtcdEndpoint::USER_LIST, []);
    }

    /**
     * @throws GuzzleException|JsonException
     */
    public function userDelete(string $name): bool
    {
        return isset($this->request(EtcdEndpoint::USER_DELETE, ['name' => $name])['header']);
    }

    /**
     * @throws GuzzleException|JsonException
     */
    public function userChangePassword(string $name, string $password): bool
    {
        return isset(
            $this->request(EtcdEndpoint::USER_CHANGE_PASSWORD, ['name' => $name, 'password' => $password])['header']
        );
    }

    /**
     * @throws GuzzleException|JsonException
     */
    public function userGrantRole(string $user, string $role): bool
    {
        return isset($this->request(EtcdEndpoint::USER_GRANT_ROLE, ['user' => $user, 'role' => $role])['header']);
    }

    /**
     * @throws GuzzleException|JsonException
     */
    public function userRevokeRole(string $user, string $role): bool
    {
        return isset($this->request(EtcdEndpoint::USER_REVOKE_ROLE, ['name' => $user, 'role' => $role])['header']);
    }

    /**
     * @throws GuzzleException|JsonException
     */
    public function roleAdd(string $name): bool
    {
        return isset($this->request(EtcdEndpoint::ROLE_ADD, ['name' => $name])['header']);
    }

    /**
     * @return array<string, mixed>
     * @throws GuzzleException|JsonException
     */
    public function roleGet(string $name): array
    {
        return $this->request(EtcdEndpoint::ROLE_GET, ['role' => $name]);
    }

    /**
     * @return array<string, mixed>
     * @throws GuzzleException|JsonException
     */
    public function roleList(): array
    {
        return $this->request(EtcdEndpoint::ROLE_LIST, []);
    }

    /**
     * @throws GuzzleException|JsonException
     */
    public function roleDelete(string $name): bool
    {
        return isset($this->request(EtcdEndpoint::ROLE_DELETE, ['role' => $name])['header']);
    }

    /**
     * @throws GuzzleException|JsonException
     */
    public function roleGrantPermission(string $name, string $permType, string $key, string $rangeEnd = ''): bool
    {
        $perm = ['permType' => $permType, 'key' => base64_encode(trim($key))];

        if ('' !== $rangeEnd) {
            $perm['range_end'] = base64_encode(trim($rangeEnd));
        }

        return isset($this->request(EtcdEndpoint::ROLE_GRANT_PERMISSION, ['name' => $name, 'perm' => $perm])['header']);
    }

    /**
     * @throws GuzzleException|JsonException
     */
    public function roleRevokePermission(string $role, string $key, string $rangeEnd = ''): bool
    {
        $body = ['role' => $role, 'key' => base64_encode(trim($key))];

        if ('' !== $rangeEnd) {
            $body['range_end'] = base64_encode(trim($rangeEnd));
        }

        return isset($this->request(EtcdEndpoint::ROLE_REVOKE_PERMISSION, $body)['header']);
    }

    /**
     * @param string[] $peerUrls
     * @return array<string, mixed>
     * @throws GuzzleException|JsonException
     */
    public function memberAdd(array $peerUrls, bool $isLearner = false): array
    {
        return $this->request(EtcdEndpoint::MEMBER_ADD, ['peerURLs' => $peerUrls, 'isLearner' => $isLearner]);
    }

    /**
     * @return array<string, mixed>
     * @throws GuzzleException|JsonException
     */
    public function memberRemove(int|string $id): array
    {
        return $this->request(EtcdEndpoint::MEMBER_REMOVE, ['ID' => (string) $id]);
    }

    /**
     * @param string[] $peerUrls
     * @return array<string, mixed>
     * @throws GuzzleException|JsonException
     */
    public function memberUpdate(int|string $id, array $peerUrls): array
    {
        return $this->request(EtcdEndpoint::MEMBER_UPDATE, ['ID' => (string) $id, 'peerURLs' => $peerUrls]);
    }

    /**
     * @return array<string, mixed>
     * @throws GuzzleException|JsonException
     */
    public function memberList(bool $linearizable = false): array
    {
        return $this->request(EtcdEndpoint::MEMBER_LIST, ['linearizable' => $linearizable]);
    }

    /**
     * @return array<string, mixed>
     * @throws GuzzleException|JsonException
     */
    public function memberPromote(int|string $id): array
    {
        return $this->request(EtcdEndpoint::MEMBER_PROMOTE, ['ID' => (string) $id]);
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

    /**
     * @return array<string, mixed>
     * @throws GuzzleException|JsonException
     */
    public function leaseGrant(int $ttl, int $id = 0): array
    {
        return $this->request(EtcdEndpoint::LEASE_GRANT, ['TTL' => (string) $ttl, 'ID' => (string) $id]);
    }

    /**
     * @throws GuzzleException|JsonException
     */
    public function leaseRevoke(int $id): bool
    {
        return isset($this->request(EtcdEndpoint::LEASE_REVOKE, ['ID' => (string) $id])['header']);
    }

    /**
     * @return array<string, mixed>
     * @throws GuzzleException|JsonException
     */
    public function leaseTimeToLive(int $id, bool $keys = false): array
    {
        return $this->request(EtcdEndpoint::LEASE_TIME_TO_LIVE, ['ID' => (string) $id, 'keys' => $keys]);
    }

    /**
     * @return array<string, mixed>
     * @throws GuzzleException|JsonException
     */
    public function leaseLeases(): array
    {
        return $this->request(EtcdEndpoint::LEASE_LEASES, []);
    }

    /**
     * @return string
     * @throws GuzzleException
     */
    public function getVersion(): string
    {
        $response = $this->client->get($this->host . EtcdEndpoint::VERSION);

        return $response->getBody()->getContents();
    }

    /**
     * @return array{headers?: array<string, string>}
     */
    private function getTokenOptions(): array
    {
        $token = $this->getAuthModel()->authenticate();

        return empty($token)
            ? []
            : [
                RequestOptions::HEADERS => ['Authorization' => $token],
            ];
    }

    public function getAuthModel(): EtcdAuthInterface
    {
        return new EtcdAuthRest($this->host, $this->user, $this->password, $this->client);
    }

    /**
     * Post JSON body to the grpc-gateway endpoint and decode the JSON response.
     *
     * @param string $endpoint
     * @param array<string, mixed> $body
     * @return array<string, mixed>
     * @throws GuzzleException|JsonException
     */
    private function request(string $endpoint, array $body): array
    {
        $json = $body === [] ? '{}' : json_encode($body, JSON_THROW_ON_ERROR);

        /** @var array{body: string, headers?: array<string, string>} $options */
        $options = array_merge(
            [RequestOptions::BODY => $json],
            $this->getTokenOptions()
        );

        $response = $this->client->post(
            $this->host . EtcdEndpoint::ETCD_VERSION . $endpoint,
            $options
        );

        return json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * Encode bytes fields and cast int64 fields for transaction operations.
     *
     * @param array<int, array<string, mixed>> $ops
     * @return array<int, array<string, mixed>>
     */
    private function transformRequestOps(array $ops): array
    {
        $result = [];

        foreach ($ops as $op) {
            if (isset($op['request_range'])) {
                $op['request_range'] = $this->transformRangeRequest($op['request_range']);
            } elseif (isset($op['request_put'])) {
                $op['request_put'] = $this->transformPutRequest($op['request_put']);
            } elseif (isset($op['request_delete_range'])) {
                $op['request_delete_range'] = $this->transformDeleteRangeRequest($op['request_delete_range']);
            } elseif (isset($op['request_txn'])) {
                $txn = $op['request_txn'];
                $op['request_txn'] = [
                    'compare' => $this->transformCompares($txn['compare']),
                    'success' => $this->transformRequestOps($txn['success']),
                    'failure' => $this->transformRequestOps($txn['failure']),
                ];
            }

            $result[] = $op;
        }

        return $result;
    }

    /**
     * @param array<int, array<string, mixed>> $compares
     * @return array<int, array<string, mixed>>
     */
    private function transformCompares(array $compares): array
    {
        $result = [];

        foreach ($compares as $compare) {
            if (isset($compare['key'])) {
                $compare['key'] = base64_encode($compare['key']);
            }

            if (isset($compare['range_end'])) {
                $compare['range_end'] = base64_encode($compare['range_end']);
            }

            if (isset($compare['value'])) {
                $compare['value'] = base64_encode((string) $compare['value']);
            }

            foreach (['version', 'create_revision', 'mod_revision', 'lease'] as $field) {
                if (isset($compare[$field])) {
                    $compare[$field] = (string) $compare[$field];
                }
            }

            $result[] = $compare;
        }

        return $result;
    }

    /**
     * @param array<string, mixed> $request
     * @return array<string, mixed>
     */
    private function transformRangeRequest(array $request): array
    {
        if (isset($request['key'])) {
            $request['key'] = base64_encode((string) $request['key']);
        }

        if (isset($request['range_end'])) {
            $request['range_end'] = base64_encode((string) $request['range_end']);
        }

        foreach (['limit', 'revision', 'min_mod_revision', 'max_mod_revision', 'min_create_revision', 'max_create_revision'] as $field) {
            if (isset($request[$field])) {
                $request[$field] = (string) $request[$field];
            }
        }

        return $request;
    }

    /**
     * @param array<string, mixed> $request
     * @return array<string, mixed>
     */
    private function transformPutRequest(array $request): array
    {
        if (isset($request['key'])) {
            $request['key'] = base64_encode((string) $request['key']);
        }

        if (isset($request['value'])) {
            $request['value'] = base64_encode((string) $request['value']);
        }

        if (isset($request['lease'])) {
            $request['lease'] = (string) $request['lease'];
        }

        return $request;
    }

    /**
     * @param array<string, mixed> $request
     * @return array<string, mixed>
     */
    private function transformDeleteRangeRequest(array $request): array
    {
        if (isset($request['key'])) {
            $request['key'] = base64_encode((string) $request['key']);
        }

        if (isset($request['range_end'])) {
            $request['range_end'] = base64_encode((string) $request['range_end']);
        }

        return $request;
    }
}

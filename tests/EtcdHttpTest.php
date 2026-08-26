<?php

declare(strict_types=1);

namespace Yii2\Extensions\Etcd\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use GuzzleHttp\Exception\ClientException;
use Yii2\Extensions\Etcd\AlarmAction;
use Yii2\Extensions\Etcd\DowngradeAction;
use Yii2\Extensions\Etcd\Etcd;
use Yii2\Extensions\Etcd\PermissionType;
use Yii2\Extensions\Etcd\Rest\RangeResponse;

final class EtcdHttpTest extends TestCase
{
    /**
     * @return array<int, array{string, string}>
     */
    public static function putDataProvider(): array
    {
        return [
            ['test-key', 'test-value'],
            ['test-key-1', '111111'],
        ];
    }

    public function testVersion(): void
    {
        $etcd = new Etcd(['host' => ETCD_HOST]);
        $version = json_decode($etcd->version, true, 512, JSON_THROW_ON_ERROR);

        self::assertArrayHasKey('etcdserver', $version);
        self::assertArrayHasKey('etcdcluster', $version);
        self::assertArrayHasKey('storage', $version);
    }

    #[DataProvider('putDataProvider')]
    public function testPut(string $key, string $value): void
    {
        $etcd = new Etcd(['host' => ETCD_HOST]);
        $etcd->getKv()->put($key, $value);

        self::assertEquals($value, $etcd->getKv()->getKey($key)->getFirstKeyValue());
    }

    public function testGetRange(): void
    {
        $etcd = new Etcd(['host' => ETCD_HOST]);
        $prefix = 'get-range-key';
        $etcd->getKv()->deleteRange($prefix, $prefix . 'k');
        $etcd->getKv()->put($prefix . '-1', 'one');
        $etcd->getKv()->put($prefix . '-2', 'two');

        $response = $etcd->getKv()->getRange($prefix, $prefix . 'k');

        self::assertInstanceOf(RangeResponse::class, $response);
        $values = array_map(
            static fn (array $kv): string => base64_decode($kv['value']),
            $response->kvs
        );
        self::assertContains('one', $values);
        self::assertContains('two', $values);
    }

    public function testDeleteRangePrefix(): void
    {
        $etcd = new Etcd(['host' => ETCD_HOST]);
        $prefix = 'delete-range-prefix-key';
        $etcd->getKv()->deleteRange($prefix, $prefix . 'k');
        $etcd->getKv()->put($prefix . '-1', 'one');
        $etcd->getKv()->put($prefix . '-2', 'two');

        $result = $etcd->getKv()->deleteRange($prefix, $prefix . 'k');

        self::assertObjectHasProperty('header', $result);
        self::assertEquals(2, (int) $result->deleted);
        $response = $etcd->getKv()->getRange($prefix, $prefix . 'k');
        self::assertInstanceOf(RangeResponse::class, $response);
        self::assertCount(0, $response->kvs);
    }

    public function testDeleteRange(): void
    {
        $etcd = new Etcd(['host' => ETCD_HOST]);
        $key = 'delete-range-key';
        $etcd->getKv()->put($key, 'value');

        $result = $etcd->getKv()->deleteRange($key);

        self::assertObjectHasProperty('header', $result);
        self::assertEquals(1, (int) $result->deleted);
        self::assertEquals('', $etcd->getKv()->getKey($key)->getFirstKeyValue());
    }

    public function testTxn(): void
    {
        $etcd = new Etcd(['host' => ETCD_HOST]);
        $key = 'txn-key';
        $etcd->getKv()->deleteRange($key);
        $etcd->getKv()->put($key, 'value-1');

        $result = $etcd->getKv()->txn(
            [['result' => 'EQUAL', 'target' => 'VERSION', 'key' => $key, 'version' => 1]],
            [['request_put' => ['key' => $key, 'value' => 'value-2']]],
            []
        );

        self::assertTrue($result->succeeded);
        self::assertEquals('value-2', $etcd->getKv()->getKey($key)->getFirstKeyValue());
    }

    public function testTxnOps(): void
    {
        $etcd = new Etcd(['host' => ETCD_HOST]);
        $key = 'txn-ops-key';
        $etcd->getKv()->deleteRange($key, $key . 'k');
        $etcd->getKv()->put($key, 'value-1');
        $etcd->getKv()->put($key . '-scratch', 'scratch-value');
        $etcd->getKv()->put($key . '-ignored', 'ignored-before');

        $result = $etcd->getKv()->txn(
            [
                ['result' => 0, 'target' => 0, 'key' => $key, 'range_end' => '', 'version' => 1],
                ['result' => 'GREATER', 'target' => 'CREATE', 'key' => $key, 'create_revision' => 0],
                ['result' => 'GREATER', 'target' => 'MOD', 'key' => $key, 'mod_revision' => 0],
                ['result' => 'EQUAL', 'target' => 'LEASE', 'key' => $key, 'lease' => 0],
                ['result' => 'EQUAL', 'target' => 'VALUE', 'key' => $key, 'value' => 'value-1'],
            ],
            [
                ['request_put' => ['key' => $key, 'value' => 'value-2', 'lease' => 0, 'prev_kv' => true]],
                [
                    'request_range' => [
                        'key' => $key,
                        'range_end' => $key . 'k',
                        'limit' => 10,
                        'revision' => 0,
                        'min_mod_revision' => 0,
                        'max_mod_revision' => 0,
                        'min_create_revision' => 0,
                        'max_create_revision' => 0,
                    ],
                ],
                [
                    'request_delete_range' => [
                        'key' => $key . '-scratch',
                        'range_end' => $key . '-scratchk',
                        'prev_kv' => true,
                    ],
                ],
                [
                    'request_txn' => [
                        'compare' => [['result' => 1, 'target' => 0, 'key' => $key, 'version' => 0]],
                        'success' => [['request_put' => ['key' => $key . '-nested', 'value' => 'nested']]],
                        'failure' => [],
                    ],
                ],
                ['request_put' => ['key' => $key . '-ignored', 'value' => '', 'ignore_value' => true, 'ignore_lease' => true]],
            ],
            [
                [
                    'request_range' => [
                        'key' => 'txn-missing',
                        'sort_order' => 2,
                        'sort_target' => 0,
                        'serializable' => true,
                        'keys_only' => true,
                        'count_only' => true,
                    ],
                ],
            ]
        );

        self::assertTrue($result->succeeded);
        self::assertCount(5, $result->responses);
        self::assertArrayHasKey('response_put', $result->responses[0]);
        self::assertArrayHasKey('response_range', $result->responses[1]);
        self::assertArrayHasKey('response_delete_range', $result->responses[2]);
        self::assertArrayHasKey('response_txn', $result->responses[3]);
        self::assertArrayHasKey('response_put', $result->responses[4]);
        self::assertEquals('value-2', $etcd->getKv()->getKey($key)->getFirstKeyValue());
        self::assertEquals('nested', $etcd->getKv()->getKey($key . '-nested')->getFirstKeyValue());
        self::assertEquals('ignored-before', $etcd->getKv()->getKey($key . '-ignored')->getFirstKeyValue());
    }

    public function testCompact(): void
    {
        $etcd = new Etcd(['host' => ETCD_HOST]);
        $key = 'compact-key';
        $etcd->getKv()->deleteRange($key);
        $etcd->getKv()->put($key, 'value');

        $response = $etcd->getKv()->getKey($key);
        self::assertInstanceOf(RangeResponse::class, $response);
        $revision = (int) $response->header['revision'];

        self::assertTrue($etcd->getKv()->compact($revision));
        self::assertEquals('value', $etcd->getKv()->getKey($key)->getFirstKeyValue());
    }

    public function testAuthStatus(): void
    {
        $etcd = new Etcd(['host' => ETCD_HOST]);

        $result = $etcd->getAuth()->authStatus();

        self::assertObjectHasProperty('authRevision', $result);
        self::assertFalse($result->enabled);
    }

    public function testMemberList(): void
    {
        $etcd = new Etcd(['host' => ETCD_HOST]);

        $result = $etcd->getCluster()->memberList();

        self::assertObjectHasProperty('members', $result);
        self::assertNotEmpty($result->members);
    }

    public function testStatus(): void
    {
        $etcd = new Etcd(['host' => ETCD_HOST]);

        $result = $etcd->getMaintenance()->status();

        self::assertObjectHasProperty('version', $result);
        self::assertNotEmpty($result->version);
        self::assertObjectHasProperty('leader', $result);
    }

    public function testHash(): void
    {
        $etcd = new Etcd(['host' => ETCD_HOST]);

        $result = $etcd->getMaintenance()->hash();

        self::assertObjectHasProperty('hash', $result);
    }

    public function testHashKv(): void
    {
        $etcd = new Etcd(['host' => ETCD_HOST]);

        $result = $etcd->getMaintenance()->hashKv();

        self::assertObjectHasProperty('hash', $result);
    }

    public function testDefragment(): void
    {
        $etcd = new Etcd(['host' => ETCD_HOST]);

        self::assertTrue($etcd->getMaintenance()->defragment());
    }

    public function testAlarm(): void
    {
        $etcd = new Etcd(['host' => ETCD_HOST]);

        $result = $etcd->getMaintenance()->alarm(AlarmAction::GET);

        self::assertObjectHasProperty('header', $result);
    }

    public function testDowngrade(): void
    {
        $etcd = new Etcd(['host' => ETCD_HOST]);

        $result = $etcd->getMaintenance()->downgrade(DowngradeAction::VALIDATE, '3.6');

        self::assertObjectHasProperty('version', $result);
    }

    public function testLease(): void
    {
        $etcd = new Etcd(['host' => ETCD_HOST]);

        $grant = $etcd->getLease()->leaseGrant(3600);

        self::assertObjectHasProperty('ID', $grant);

        $id = (int) $grant->ID;

        self::assertGreaterThan(0, $id);

        $ttl = $etcd->getLease()->leaseTimeToLive($id);

        self::assertObjectHasProperty('TTL', $ttl);

        $leases = $etcd->getLease()->leaseLeases();

        self::assertObjectHasProperty('leases', $leases);
        self::assertNotEmpty($leases->leases);

        self::assertTrue($etcd->getLease()->leaseRevoke($id));
    }

    public function testMoveLeader(): void
    {
        $etcd = new Etcd(['host' => ETCD_HOST]);

        $status = $etcd->getMaintenance()->status();

        self::assertObjectHasProperty('leader', $status);
        self::assertTrue($etcd->getMaintenance()->moveLeader($status->leader));
    }

    public function testMemberPromote(): void
    {
        $etcd = new Etcd(['host' => ETCD_HOST]);

        $leader = $etcd->getMaintenance()->status()->leader;

        $this->expectException(ClientException::class);
        $this->expectExceptionMessage('can only promote a learner member');

        $etcd->getCluster()->memberPromote($leader);
    }

    public function testMember(): void
    {
        $etcd = new Etcd(['host' => ETCD_HOST]);

        $addResult = $etcd->getCluster()->memberAdd(['http://127.0.0.1:2381'], true);

        self::assertObjectHasProperty('member', $addResult);
        self::assertArrayHasKey('ID', $addResult->member);
        self::assertTrue($addResult->member['isLearner'] ?? false);

        $memberId = (string) $addResult->member['ID'];

        try {
            $members = $etcd->getCluster()->memberList();

            self::assertObjectHasProperty('members', $members);
            self::assertContains($memberId, array_map('strval', array_column($members->members, 'ID')));

            $updateResult = $etcd->getCluster()->memberUpdate($memberId, ['http://127.0.0.1:2382']);

            $updated = $this->findMember($updateResult->members, $memberId);
            self::assertContains('http://127.0.0.1:2382', $updated['peerURLs'] ?? []);

            $removeResult = $etcd->getCluster()->memberRemove($memberId);

            self::assertObjectHasProperty('members', $removeResult);
            self::assertNotContains($memberId, array_map('strval', array_column($removeResult->members, 'ID')));
        } finally {
            try {
                $etcd->getCluster()->memberRemove($memberId);
            } catch (\Throwable) {
            }
        }
    }

    public function testAuth(): void
    {
        $rootUser = 'root';
        $rootPassword = 'root-secret';
        $etcd = new Etcd(['host' => ETCD_HOST, 'user' => $rootUser, 'password' => $rootPassword]);
        $etcdNoAuth = new Etcd(['host' => ETCD_HOST]);

        if ($etcdNoAuth->getAuth()->authStatus()->enabled) {
            $etcd->getAuth()->authDisable();
        }

        try {
            $etcdNoAuth->getAuth()->userDelete($rootUser);
        } catch (\Throwable) {
        }
        try {
            $etcdNoAuth->getAuth()->roleDelete($rootUser);
        } catch (\Throwable) {
        }

        self::assertTrue($etcdNoAuth->getAuth()->userAdd($rootUser, $rootPassword));
        self::assertTrue($etcdNoAuth->getAuth()->roleAdd($rootUser));
        self::assertTrue($etcdNoAuth->getAuth()->userGrantRole($rootUser, $rootUser));
        self::assertTrue($etcdNoAuth->getAuth()->authEnable());

        try {
            self::assertTrue($etcd->getAuth()->authStatus()->enabled);
            self::assertObjectHasProperty('authRevision', $etcd->getAuth()->authStatus());
            self::assertNotEmpty($etcd->getAuth()->authenticate());

            self::assertTrue($etcd->getAuth()->userAdd('alice', 'pw1'));
            self::assertContains('alice', $etcd->getAuth()->userList()->users);
            self::assertTrue($etcd->getAuth()->userChangePassword('alice', 'pw2'));
            self::assertTrue($etcd->getAuth()->userAdd('nopass', '', true));
            $users = $etcd->getAuth()->userList();
            self::assertContains('nopass', $users->users);
            self::assertTrue($etcd->getAuth()->userGrantRole('alice', $rootUser));
            self::assertContains($rootUser, $etcd->getAuth()->userGet('alice')->roles);
            self::assertTrue($etcd->getAuth()->userRevokeRole('alice', $rootUser));
            self::assertNotContains($rootUser, $etcd->getAuth()->userGet('alice')->roles);

            self::assertTrue($etcd->getAuth()->roleAdd('viewer'));
            self::assertContains('viewer', $etcd->getAuth()->roleList()->roles);
            self::assertTrue($etcd->getAuth()->roleGrantPermission('viewer', PermissionType::READ, '/foo', '/fop'));

            $role = $etcd->getAuth()->roleGet('viewer');
            self::assertNotEmpty($role->perm);

            self::assertTrue($etcd->getAuth()->roleRevokePermission('viewer', '/foo', '/fop'));

            $role = $etcd->getAuth()->roleGet('viewer');
            self::assertEmpty($role->perm);

            self::assertTrue($etcd->getAuth()->roleDelete('viewer'));
            self::assertNotContains('viewer', $etcd->getAuth()->roleList()->roles);

            self::assertTrue($etcd->getAuth()->userDelete('nopass'));
            self::assertTrue($etcd->getAuth()->userDelete('alice'));
            self::assertNotContains('alice', $etcd->getAuth()->userList()->users);
        } finally {
            try {
                $etcd->getAuth()->authDisable();
            } catch (\Throwable) {
            }
            try {
                $etcdNoAuth->getAuth()->userDelete($rootUser);
            } catch (\Throwable) {
            }
            try {
                $etcdNoAuth->getAuth()->roleDelete($rootUser);
            } catch (\Throwable) {
            }
        }
    }

    /**
     * @param array<int, array<string, mixed>> $members
     * @return array<string, mixed>
     */
    private function findMember(array $members, string $id): array
    {
        foreach ($members as $member) {
            if ((string) ($member['ID'] ?? '') === $id) {
                return $member;
            }
        }

        return [];
    }
}

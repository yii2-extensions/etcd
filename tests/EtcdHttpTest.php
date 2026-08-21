<?php

declare(strict_types=1);

namespace Yii2\Extensions\Etcd\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Yii2\Extensions\Etcd\Etcd;

final class EtcdHttpTest extends TestCase
{
    public static function putDataProvider(): array
    {
        return [
            ['test-key', 'test-value'],
            ['test-key-1', '111111'],
        ];
    }

    public function testVersion(): void
    {
        $etcd = new Etcd(['host' => 'etcd:2379']);
        $version = json_decode($etcd->version, true, 512, JSON_THROW_ON_ERROR);

        self::assertArrayHasKey('etcdserver', $version);
        self::assertArrayHasKey('etcdcluster', $version);
        self::assertArrayHasKey('storage', $version);
    }

    #[DataProvider('putDataProvider')]
    public function testPut(string $key, string $value): void
    {
        $etcd = new Etcd(['host' => 'etcd:2379']);
        $etcd->put($key, $value);

        self::assertEquals($value, $etcd->getKey($key)->getFirstKeyValue());
    }
}

<?php

declare(strict_types=1);

namespace Yii2\Extensions\Etcd\Tests;

use PHPUnit\Framework\TestCase;
use Yii2\Extensions\Etcd\Etcd;
use Yii2\Extensions\Etcd\EtcdProtocol;

final class EtcdGrpcTest extends TestCase
{
    public function testVersion(): void
    {
        $etcd = new Etcd(['host' => ETCD_HOST, 'protocol' => EtcdProtocol::GRPC]);

        self::assertEquals('Not supported', $etcd->version);
    }
}

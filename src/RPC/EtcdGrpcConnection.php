<?php

declare(strict_types=1);

namespace Yii2\Extensions\Etcd\RPC;

use Yii2\Extensions\Etcd\EtcdAuthServiceInterface;
use Yii2\Extensions\Etcd\EtcdClusterServiceInterface;
use Yii2\Extensions\Etcd\EtcdConnectionInterface;
use Yii2\Extensions\Etcd\EtcdKvServiceInterface;
use Yii2\Extensions\Etcd\EtcdLeaseServiceInterface;
use Yii2\Extensions\Etcd\EtcdMaintenanceServiceInterface;
use Yii2\Extensions\Etcd\Exceptions\EtcdException;

final class EtcdGrpcConnection implements EtcdConnectionInterface
{
    public private(set) string $host = '';
    public private(set) string $user = '';
    public private(set) string $password = '';

    /**
     * @var array<string, mixed>
     */
    public private(set) array $clientOptions = [];

    private ?EtcdGrpcKv $kv = null;
    private ?EtcdGrpcAuth $auth = null;
    private ?EtcdGrpcCluster $cluster = null;
    private ?EtcdGrpcMaintenance $maintenance = null;
    private ?EtcdGrpcLease $lease = null;

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
    }

    #[\Override]
    public function getKv(): EtcdKvServiceInterface
    {
        return $this->kv ??= new EtcdGrpcKv($this);
    }

    #[\Override]
    public function getAuth(): EtcdAuthServiceInterface
    {
        return $this->auth ??= new EtcdGrpcAuth($this);
    }

    #[\Override]
    public function getCluster(): EtcdClusterServiceInterface
    {
        return $this->cluster ??= new EtcdGrpcCluster($this);
    }

    #[\Override]
    public function getMaintenance(): EtcdMaintenanceServiceInterface
    {
        return $this->maintenance ??= new EtcdGrpcMaintenance($this);
    }

    #[\Override]
    public function getLease(): EtcdLeaseServiceInterface
    {
        return $this->lease ??= new EtcdGrpcLease($this);
    }
}

<?php

declare(strict_types=1);

namespace Yii2\Extensions\Etcd\Rest;

use GuzzleHttp\Client;
use Yii2\Extensions\Etcd\EtcdAuthServiceInterface;
use Yii2\Extensions\Etcd\EtcdClusterServiceInterface;
use Yii2\Extensions\Etcd\EtcdConnectionInterface;
use Yii2\Extensions\Etcd\EtcdKvServiceInterface;
use Yii2\Extensions\Etcd\EtcdLeaseServiceInterface;
use Yii2\Extensions\Etcd\EtcdMaintenanceServiceInterface;

final class EtcdRestConnection implements EtcdConnectionInterface
{
    public string $host = '';
    public string $user = '';
    public string $password = '';
    public Client $client;

    private ?EtcdRestKv $kv = null;
    private ?EtcdRestAuth $auth = null;
    private ?EtcdRestCluster $cluster = null;
    private ?EtcdRestMaintenance $maintenance = null;
    private ?EtcdRestLease $lease = null;

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

    public function getKv(): EtcdKvServiceInterface
    {
        return $this->kv ??= new EtcdRestKv($this);
    }

    public function getAuth(): EtcdAuthServiceInterface
    {
        return $this->auth ??= new EtcdRestAuth($this);
    }

    public function getCluster(): EtcdClusterServiceInterface
    {
        return $this->cluster ??= new EtcdRestCluster($this);
    }

    public function getMaintenance(): EtcdMaintenanceServiceInterface
    {
        return $this->maintenance ??= new EtcdRestMaintenance($this);
    }

    public function getLease(): EtcdLeaseServiceInterface
    {
        return $this->lease ??= new EtcdRestLease($this);
    }
}

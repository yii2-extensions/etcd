<?php

declare(strict_types=1);

namespace Yii2\Extensions\Etcd;

interface EtcdConnectionInterface
{
    public function getKv(): EtcdKvServiceInterface;

    public function getAuth(): EtcdAuthServiceInterface;

    public function getCluster(): EtcdClusterServiceInterface;

    public function getMaintenance(): EtcdMaintenanceServiceInterface;

    public function getLease(): EtcdLeaseServiceInterface;
}

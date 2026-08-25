<?php

declare(strict_types=1);

namespace Yii2\Extensions\Etcd;

use Yii2\Extensions\Etcd\Rest\EtcdRestConnection;
use Yii2\Extensions\Etcd\RPC\EtcdGrpcConnection;
use yii\base\Component;

/**
 * Yii2 etcd component
 *
 * @property-read EtcdAuthServiceInterface $auth
 * @property-read string $version
 */
class Etcd extends Component
{
    public string $host = '';
    public string $user = '';
    public string $password = '';
    /**
     * @var array<string, mixed> guzzle client options
     */
    public array $clientOptions = [];
    public string $protocol = EtcdProtocol::HTTP;

    private EtcdConnectionInterface $connection;

    /**
     * @var array<string, class-string<EtcdConnectionInterface>>
     */
    private array $protocolList = [
        EtcdProtocol::GRPC => EtcdGrpcConnection::class,
        EtcdProtocol::HTTP => EtcdRestConnection::class,
    ];

    /**
     * @inheritDoc
     */
    public function init(): void
    {
        parent::init();

        $this->connection = new $this->protocolList[$this->protocol]($this->host, $this->user, $this->password, $this->clientOptions);
    }

    public function getKv(): EtcdKvServiceInterface
    {
        return $this->connection->getKv();
    }

    /**
     * Authentication
     *
     * @return EtcdAuthServiceInterface
     */
    public function getAuth(): EtcdAuthServiceInterface
    {
        return $this->connection->getAuth();
    }

    public function getCluster(): EtcdClusterServiceInterface
    {
        return $this->connection->getCluster();
    }

    public function getMaintenance(): EtcdMaintenanceServiceInterface
    {
        return $this->connection->getMaintenance();
    }

    public function getLease(): EtcdLeaseServiceInterface
    {
        return $this->connection->getLease();
    }

    /**
     * @return string
     */
    public function getVersion(): string
    {
        return $this->connection->getMaintenance()->getVersion();
    }
}

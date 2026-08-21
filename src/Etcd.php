<?php

declare(strict_types=1);

namespace Yii2\Extensions\Etcd;

use Yii2\Extensions\Etcd\Rest\EtcdRestModel;
use Yii2\Extensions\Etcd\RPC\EtcdGrpcModel;
use Yii2\Extensions\Etcd\Services\EtcdAuthInterface;
use yii\base\Component;

/**
 * Yii2 etcd component
 *
 * @property-read array[] $tokenOptions
 * @property-read EtcdAuthInterface $auth
 * @property-read string $version
 */
class Etcd extends Component
{
    public string $host = '';
    public string $user = '';
    public string $password = '';
    /**
     * @var array guzzle client options
     */
    public array $clientOptions = [];
    public string $protocol = EtcdProtocol::HTTP;

    private EtcdServiceInterface $service;

    private array $protocolList = [
        EtcdProtocol::GRPC => EtcdGrpcModel::class,
        EtcdProtocol::HTTP => EtcdRestModel::class,
    ];

    /**
     * @inheritDoc
     */
    public function init(): void
    {
        parent::init();

        $this->service = new $this->protocolList[$this->protocol]($this->host, $this->user, $this->password, $this->clientOptions);
    }

    /**
     * @return string
     */
    public function getVersion(): string
    {
        return $this->service->getVersion();
    }

    /**
     * @param string $key
     * @return EtcdRangeResponseInterface
     */
    public function getKey(string $key): EtcdRangeResponseInterface
    {
        return $this->service->getKey($key);
    }

    /**
     * Get all keys prefixed with "foo" ($key = foo, $rangeEnd = fop)
     *
     * @param string $key
     * @param string $rangeEnd
     * @return EtcdRangeResponseInterface
     */
    public function getRange(string $key, string $rangeEnd): EtcdRangeResponseInterface
    {
        return $this->service->getRange($key, $rangeEnd);
    }

    /**
     * @param string $key
     * @param string $value
     * @return bool
     */
    public function put(string $key, string $value): bool
    {
        return $this->service->put($key, $value);
    }

    /**
     * Authentication
     *
     * @return EtcdAuthInterface
     */
    public function getAuth(): EtcdAuthInterface
    {
        return $this->service->getAuthModel();
    }
}

<?php

declare(strict_types=1);

namespace Yii2\Extensions\Etcd\RPC;

use Etcd\KeyValue;
use Etcd\KVClient;
use Etcd\PutRequest;
use Etcd\RangeRequest;
use Google\Protobuf\Internal\RepeatedField;
use Grpc\ChannelCredentials;
use Yii2\Extensions\Etcd\EtcdServiceInterface;
use Yii2\Extensions\Etcd\Exceptions\EtcdException;
use Yii2\Extensions\Etcd\Services\EtcdAuthGrpc;
use Yii2\Extensions\Etcd\Services\EtcdAuthInterface;

use const Grpc\STATUS_OK;

class EtcdGrpcModel implements EtcdServiceInterface
{
    public string $host = '';
    public string $user = '';
    public string $password = '';
    public array $clientOptions = [];

    public array $metadata = [];

    private KVClient $client;

    /**
     * @param string $host
     * @param string $user
     * @param string $password
     * @param array $clientOptions
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

        $this->client = $this->getClient();
    }

    public function getAuthModel(): EtcdAuthInterface
    {
        return new EtcdAuthGrpc($this->host, $this->user, $this->password);
    }

    public function getVersion(): string
    {
        return 'Not supported';
    }

    public function getRange(string $key, string $rangeEnd): RangeResponse
    {
        $request = new RangeRequest();
        $request->setKey($key);
        $request->setRangeEnd($rangeEnd);

        /** @var \Etcd\RangeResponse $response */
        [$response, $status] = $this->client->Range($request)->wait();

        if (STATUS_OK !== $status->code) {
            throw new EtcdException('Errors: ' . $status->details);
        }

        return new RangeResponse($this->collectKvs($response->getKvs()));
    }

    public function getKey(string $key): RangeResponse
    {
        $request = new RangeRequest();
        $request->setKey($key);

        /** @var \Etcd\RangeResponse $response */
        [$response, $status] = $this->client->Range($request)->wait();

        if (STATUS_OK !== $status->code) {
            throw new EtcdException('Errors: ' . $status->details);
        }

        return new RangeResponse($this->collectKvs($response->getKvs()));
    }

    public function put(string $key, string $value): bool
    {
        $request = new PutRequest();
        $request->setKey($key);
        $request->setValue($value);

        [$response, $status] = $this->client->Put($request)->wait();

        return STATUS_OK === $status->code;
    }

    private function getClient(): KVClient
    {
        return new KVClient($this->host, [
            'credentials' => ChannelCredentials::createInsecure(),
            'update_metadata' => function ($metaData) {
                $metaData['Authorization'] = [$this->getAuthModel()->authenticate()];

                return $metaData;
            }
        ]);
    }

    /**
     * @param RepeatedField $fields
     * @return array
     */
    private function collectKvs(RepeatedField $fields): array
    {
        $kvs = [];
        $protobufKvs = $fields;

        foreach ($protobufKvs as $item) {
            /** @var KeyValue $item */
            $kvs['kvs'][] = [
                'key' => $item->getKey(),
                'value' => $item->getValue(),
            ];
        }

        return $kvs;
    }
}

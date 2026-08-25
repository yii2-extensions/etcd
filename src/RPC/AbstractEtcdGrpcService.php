<?php

declare(strict_types=1);

namespace Yii2\Extensions\Etcd\RPC;

use Etcd\ResponseHeader;
use Google\Protobuf\Internal\Message;
use Grpc\ChannelCredentials;
use Grpc\UnaryCall;
use Yii2\Extensions\Etcd\Exceptions\EtcdException;
use Yii2\Extensions\Etcd\Services\EtcdAuthGrpc;

use const Grpc\STATUS_OK;

abstract class AbstractEtcdGrpcService
{
    protected EtcdGrpcConnection $connection;

    private ?EtcdAuthGrpc $authenticator = null;

    public function __construct(EtcdGrpcConnection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @return array<string, mixed>
     */
    protected function getConnectionOptions(): array
    {
        return [
            'credentials' => ChannelCredentials::createInsecure(),
            'update_metadata' => function ($metaData) {
                $token = $this->authenticator()->authenticate();

                if ('' !== $token) {
                    $metaData['Authorization'] = [$token];
                }

                return $metaData;
            },
        ];
    }

    protected function authenticator(): EtcdAuthGrpc
    {
        return $this->authenticator ??= new EtcdAuthGrpc(
            $this->connection->host,
            $this->connection->user,
            $this->connection->password
        );
    }

    /**
     * Wait for the unary call to finish and validate the response status.
     *
     * @template T of Message
     * @param UnaryCall<T> $call
     * @return T
     */
    protected function wait(UnaryCall $call)
    {
        [$response, $status] = $call->wait();

        if (STATUS_OK !== $status->code) {
            throw new EtcdException('Errors: ' . $status->details);
        }

        if (null === $response) {
            throw new EtcdException('Errors: empty response');
        }

        return $response;
    }

    /**
     * @return array<string, mixed>
     */
    protected function convertHeader(?ResponseHeader $header): array
    {
        if (null === $header) {
            return [];
        }

        return [
            'cluster_id' => $header->getClusterId(),
            'member_id' => $header->getMemberId(),
            'revision' => $header->getRevision(),
            'raft_term' => $header->getRaftTerm(),
        ];
    }

    /**
     * @param iterable<string> $fields
     * @return array<int, string>
     */
    protected function convertStringList($fields): array
    {
        $result = [];

        foreach ($fields as $field) {
            $result[] = $field;
        }

        return $result;
    }
}

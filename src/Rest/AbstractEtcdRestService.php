<?php

declare(strict_types=1);

namespace Yii2\Extensions\Etcd\Rest;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use JsonException;
use Yii2\Extensions\Etcd\EtcdEndpoint;
use Yii2\Extensions\Etcd\Services\EtcdAuthRest;

abstract class AbstractEtcdRestService
{
    protected EtcdRestConnection $connection;

    public function __construct(EtcdRestConnection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @return array{headers?: array<string, string>}
     * @throws GuzzleException|JsonException
     */
    protected function getTokenOptions(): array
    {
        $token = $this->authenticator()->authenticate();

        return empty($token)
            ? []
            : [
                RequestOptions::HEADERS => ['Authorization' => $token],
            ];
    }

    /**
     * @return EtcdAuthRest
     * @throws GuzzleException|JsonException
     */
    protected function authenticator(): EtcdAuthRest
    {
        return new EtcdAuthRest(
            $this->connection->host,
            $this->connection->user,
            $this->connection->password,
            $this->connection->client
        );
    }

    /**
     * Post JSON body to the grpc-gateway endpoint and decode the JSON response.
     *
     * @param array<string, mixed> $body
     * @return array<string, mixed>
     * @throws GuzzleException|JsonException
     */
    protected function request(string $endpoint, array $body): array
    {
        $json = $body === [] ? '{}' : json_encode($body, JSON_THROW_ON_ERROR);

        /** @var array{body: string, headers?: array<string, string>} $options */
        $options = array_merge(
            [RequestOptions::BODY => $json],
            $this->getTokenOptions()
        );

        $response = $this->connection->client->post(
            $this->connection->host . EtcdEndpoint::ETCD_VERSION . $endpoint,
            $options
        );

        return json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
    }
}

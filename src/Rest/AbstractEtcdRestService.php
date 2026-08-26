<?php

declare(strict_types=1);

namespace Yii2\Extensions\Etcd\Rest;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use JsonException;
use Psr\Http\Message\ResponseInterface;
use Yii2\Extensions\Etcd\EtcdEndpoint;
use Yii2\Extensions\Etcd\Services\AuthTokenProvider;
use Yii2\Extensions\Etcd\Services\EtcdAuthRest;

abstract class AbstractEtcdRestService
{
    private ?AuthTokenProvider $tokenProvider = null;

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
        $token = $this->tokenProvider()->authenticate();

        return empty($token)
            ? []
            : [
                RequestOptions::HEADERS => ['Authorization' => $token],
            ];
    }

    protected function tokenProvider(): AuthTokenProvider
    {
        return $this->tokenProvider ??= new AuthTokenProvider(
            $this->authenticator(),
            $this->connection->user,
        );
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
     * Post JSON body to the grpc-gateway endpoint and return the raw HTTP response.
     *
     * @param array<string, mixed> $body
     * @return ResponseInterface
     * @throws GuzzleException|JsonException
     */
    protected function requestRaw(string $endpoint, array $body): ResponseInterface
    {
        $json = $body === [] ? '{}' : json_encode($body, JSON_THROW_ON_ERROR);

        /** @var array{body: string, headers?: array<string, string>} $options */
        $options = array_merge(
            [RequestOptions::BODY => $json],
            $this->getTokenOptions()
        );

        return $this->connection->client->post(
            $this->connection->host . EtcdEndpoint::ETCD_VERSION . $endpoint,
            $options
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
        return json_decode(
            $this->requestRaw($endpoint, $body)->getBody()->getContents(),
            true,
            512,
            JSON_THROW_ON_ERROR
        );
    }
}

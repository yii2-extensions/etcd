<?php

declare(strict_types=1);

namespace Yii2\Extensions\Etcd\Services;

use Etcd\AuthClient;
use Etcd\AuthenticateRequest;
use Etcd\AuthenticateResponse;
use Grpc\ChannelCredentials;
use Yii2\Extensions\Etcd\Exceptions\EtcdException;

use const Grpc\STATUS_OK;

class EtcdAuthGrpc implements EtcdAuthInterface
{
    public string $host = '';
    public string $user = '';
    public string $password = '';

    private AuthClient $client;

    public function __construct(string $host, string $user, string $password)
    {
        $this->host = $host;
        $this->user = $user;
        $this->password = $password;

        $this->client = $this->getClient();
    }

    public function authenticate(): string
    {
        $request = new AuthenticateRequest();
        $request->setName($this->user);
        $request->setPassword($this->password);

        /** @var AuthenticateResponse $response */
        [$response, $status] = $this->client->Authenticate($request)->wait();

        if (STATUS_OK !== $status->code) {
            throw new EtcdException('Errors: ' . $status->details);
        }

        return $response->getToken();
    }

    public function getClient(): AuthClient
    {
        return new AuthClient($this->host, [
            'credentials' => ChannelCredentials::createInsecure(),
        ]);
    }
}

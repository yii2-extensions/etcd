<?php

declare(strict_types=1);

namespace Yii2\Extensions\Etcd\Rest;

use GuzzleHttp\Exception\GuzzleException;
use JsonException;
use Yii2\Extensions\Etcd\EtcdClusterServiceInterface;
use Yii2\Extensions\Etcd\EtcdEndpoint;

final class EtcdRestCluster extends AbstractEtcdRestService implements EtcdClusterServiceInterface
{
    /**
     * @param string[] $peerUrls
     * @return array<string, mixed>
     * @throws GuzzleException|JsonException
     */
    public function memberAdd(array $peerUrls, bool $isLearner = false): array
    {
        return $this->request(EtcdEndpoint::MEMBER_ADD, ['peerURLs' => $peerUrls, 'isLearner' => $isLearner]);
    }

    /**
     * @return array<string, mixed>
     * @throws GuzzleException|JsonException
     */
    public function memberRemove(int|string $id): array
    {
        return $this->request(EtcdEndpoint::MEMBER_REMOVE, ['ID' => (string) $id]);
    }

    /**
     * @param string[] $peerUrls
     * @return array<string, mixed>
     * @throws GuzzleException|JsonException
     */
    public function memberUpdate(int|string $id, array $peerUrls): array
    {
        return $this->request(EtcdEndpoint::MEMBER_UPDATE, ['ID' => (string) $id, 'peerURLs' => $peerUrls]);
    }

    /**
     * @return array<string, mixed>
     * @throws GuzzleException|JsonException
     */
    public function memberList(bool $linearizable = false): array
    {
        return $this->request(EtcdEndpoint::MEMBER_LIST, ['linearizable' => $linearizable]);
    }

    /**
     * @return array<string, mixed>
     * @throws GuzzleException|JsonException
     */
    public function memberPromote(int|string $id): array
    {
        return $this->request(EtcdEndpoint::MEMBER_PROMOTE, ['ID' => (string) $id]);
    }
}

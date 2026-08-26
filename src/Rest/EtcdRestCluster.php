<?php

declare(strict_types=1);

namespace Yii2\Extensions\Etcd\Rest;

use GuzzleHttp\Exception\GuzzleException;
use JsonException;
use Yii2\Extensions\Etcd\EtcdClusterServiceInterface;
use Yii2\Extensions\Etcd\EtcdEndpoint;
use Yii2\Extensions\Etcd\Responses\MemberAddResponse;
use Yii2\Extensions\Etcd\Responses\MemberListResponse;
use Yii2\Extensions\Etcd\Responses\MemberPromoteResponse;
use Yii2\Extensions\Etcd\Responses\MemberRemoveResponse;
use Yii2\Extensions\Etcd\Responses\MemberUpdateResponse;

final class EtcdRestCluster extends AbstractEtcdRestService implements EtcdClusterServiceInterface
{
    /**
     * @param string[] $peerUrls
     * @throws GuzzleException|JsonException
     */
    public function memberAdd(array $peerUrls, bool $isLearner = false): MemberAddResponse
    {
        return new MemberAddResponse(
            $this->request(EtcdEndpoint::MEMBER_ADD, ['peerURLs' => $peerUrls, 'isLearner' => $isLearner])
        );
    }

    /**
     * @throws GuzzleException|JsonException
     */
    public function memberRemove(int|string $id): MemberRemoveResponse
    {
        return new MemberRemoveResponse($this->request(EtcdEndpoint::MEMBER_REMOVE, ['ID' => (string) $id]));
    }

    /**
     * @param string[] $peerUrls
     * @throws GuzzleException|JsonException
     */
    public function memberUpdate(int|string $id, array $peerUrls): MemberUpdateResponse
    {
        return new MemberUpdateResponse(
            $this->request(EtcdEndpoint::MEMBER_UPDATE, ['ID' => (string) $id, 'peerURLs' => $peerUrls])
        );
    }

    /**
     * @throws GuzzleException|JsonException
     */
    public function memberList(bool $linearizable = false): MemberListResponse
    {
        return new MemberListResponse(
            $this->request(EtcdEndpoint::MEMBER_LIST, ['linearizable' => $linearizable])
        );
    }

    /**
     * @throws GuzzleException|JsonException
     */
    public function memberPromote(int|string $id): MemberPromoteResponse
    {
        return new MemberPromoteResponse($this->request(EtcdEndpoint::MEMBER_PROMOTE, ['ID' => (string) $id]));
    }
}

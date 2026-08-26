<?php

declare(strict_types=1);

namespace Yii2\Extensions\Etcd\RPC;

use Etcd\ClusterClient;
use Etcd\Member;
use Etcd\MemberAddRequest;
use Etcd\MemberListRequest;
use Etcd\MemberPromoteRequest;
use Etcd\MemberRemoveRequest;
use Etcd\MemberUpdateRequest;
use Google\Protobuf\Internal\RepeatedField;
use Yii2\Extensions\Etcd\EtcdClusterServiceInterface;
use Yii2\Extensions\Etcd\Responses\MemberAddResponse;
use Yii2\Extensions\Etcd\Responses\MemberListResponse;
use Yii2\Extensions\Etcd\Responses\MemberPromoteResponse;
use Yii2\Extensions\Etcd\Responses\MemberRemoveResponse;
use Yii2\Extensions\Etcd\Responses\MemberUpdateResponse;

final class EtcdGrpcCluster extends AbstractEtcdGrpcService implements EtcdClusterServiceInterface
{
    private ?ClusterClient $client = null;

    private function getClient(): ClusterClient
    {
        return $this->client ??= new ClusterClient($this->connection->host, $this->getConnectionOptions());
    }

    public function memberAdd(array $peerUrls, bool $isLearner = false): MemberAddResponse
    {
        $request = new MemberAddRequest();
        $request->setPeerUrls($peerUrls);
        $request->setIsLearner($isLearner);

        /** @var \Etcd\MemberAddResponse $response */
        $response = $this->wait($this->getClient()->MemberAdd($request));

        return new MemberAddResponse([
            'header' => $this->convertHeader($response->getHeader()),
            'member' => null === $response->getMember() ? [] : $this->convertMember($response->getMember()),
            'members' => $this->convertMembers($response->getMembers()),
        ]);
    }

    public function memberRemove(int|string $id): MemberRemoveResponse
    {
        $request = new MemberRemoveRequest();
        $request->setID($id);

        /** @var \Etcd\MemberRemoveResponse $response */
        $response = $this->wait($this->getClient()->MemberRemove($request));

        return new MemberRemoveResponse([
            'header' => $this->convertHeader($response->getHeader()),
            'members' => $this->convertMembers($response->getMembers()),
        ]);
    }

    public function memberUpdate(int|string $id, array $peerUrls): MemberUpdateResponse
    {
        $request = new MemberUpdateRequest();
        $request->setID($id);
        $request->setPeerUrls($peerUrls);

        /** @var \Etcd\MemberUpdateResponse $response */
        $response = $this->wait($this->getClient()->MemberUpdate($request));

        return new MemberUpdateResponse([
            'header' => $this->convertHeader($response->getHeader()),
            'members' => $this->convertMembers($response->getMembers()),
        ]);
    }

    public function memberList(bool $linearizable = false): MemberListResponse
    {
        $request = new MemberListRequest();
        $request->setLinearizable($linearizable);

        /** @var \Etcd\MemberListResponse $response */
        $response = $this->wait($this->getClient()->MemberList($request));

        return new MemberListResponse([
            'header' => $this->convertHeader($response->getHeader()),
            'members' => $this->convertMembers($response->getMembers()),
        ]);
    }

    public function memberPromote(int|string $id): MemberPromoteResponse
    {
        $request = new MemberPromoteRequest();
        $request->setID($id);

        /** @var \Etcd\MemberPromoteResponse $response */
        $response = $this->wait($this->getClient()->MemberPromote($request));

        return new MemberPromoteResponse([
            'header' => $this->convertHeader($response->getHeader()),
            'members' => $this->convertMembers($response->getMembers()),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function convertMember(Member $member): array
    {
        return [
            'ID' => $member->getID(),
            'name' => $member->getName(),
            'peerURLs' => $this->convertStringList($member->getPeerURLs()),
            'clientURLs' => $this->convertStringList($member->getClientURLs()),
            'isLearner' => $member->getIsLearner(),
        ];
    }

    /**
     * @param RepeatedField<\Etcd\Member> $members
     * @return array<int, array<string, mixed>>
     */
    private function convertMembers(RepeatedField $members): array
    {
        $result = [];

        /** @var RepeatedField<\Etcd\Member> $memberList */
        $memberList = $members;

        foreach ($memberList as $member) {
            $result[] = $this->convertMember($member);
        }

        return $result;
    }
}

<?php

declare(strict_types=1);

namespace Yii2\Extensions\Etcd;

use Yii2\Extensions\Etcd\Responses\MemberAddResponse;
use Yii2\Extensions\Etcd\Responses\MemberListResponse;
use Yii2\Extensions\Etcd\Responses\MemberPromoteResponse;
use Yii2\Extensions\Etcd\Responses\MemberRemoveResponse;
use Yii2\Extensions\Etcd\Responses\MemberUpdateResponse;

interface EtcdClusterServiceInterface
{
    /**
     * Adds a member into the cluster.
     *
     * @param string[] $peerUrls
     */
    public function memberAdd(array $peerUrls, bool $isLearner = false): MemberAddResponse;

    /**
     * Removes an existing member from the cluster.
     */
    public function memberRemove(int|string $id): MemberRemoveResponse;

    /**
     * Updates the member configuration.
     *
     * @param string[] $peerUrls
     */
    public function memberUpdate(int|string $id, array $peerUrls): MemberUpdateResponse;

    /**
     * Lists all the members in the cluster.
     */
    public function memberList(bool $linearizable = false): MemberListResponse;

    /**
     * Promotes a member from raft learner (non-voting) to raft voting member.
     */
    public function memberPromote(int|string $id): MemberPromoteResponse;
}

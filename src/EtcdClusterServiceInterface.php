<?php

declare(strict_types=1);

namespace Yii2\Extensions\Etcd;

interface EtcdClusterServiceInterface
{
    /**
     * Adds a member into the cluster.
     *
     * @param string[] $peerUrls
     * @return array<string, mixed> response fields: header, member, members
     */
    public function memberAdd(array $peerUrls, bool $isLearner = false): array;

    /**
     * Removes an existing member from the cluster.
     *
     * @return array<string, mixed> response fields: header, members
     */
    public function memberRemove(int|string $id): array;

    /**
     * Updates the member configuration.
     *
     * @param string[] $peerUrls
     * @return array<string, mixed> response fields: header, members
     */
    public function memberUpdate(int|string $id, array $peerUrls): array;

    /**
     * Lists all the members in the cluster.
     *
     * @return array<string, mixed> response fields: header, members
     */
    public function memberList(bool $linearizable = false): array;

    /**
     * Promotes a member from raft learner (non-voting) to raft voting member.
     *
     * @return array<string, mixed> response fields: header, members
     */
    public function memberPromote(int|string $id): array;
}

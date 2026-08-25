<?php
// GENERATED CODE -- DO NOT EDIT!

namespace Etcd;

/**
 */
class ClusterClient extends \Grpc\BaseStub {

    /**
     * @param string $hostname hostname
     * @param array $opts channel options
     * @param \Grpc\Channel $channel (optional) re-use channel object
     */
    public function __construct($hostname, $opts, $channel = null) {
        parent::__construct($hostname, $opts, $channel);
    }

    /**
     * MemberAdd adds a member into the cluster.
     * @param \Etcd\MemberAddRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall<\Etcd\MemberAddResponse>
     */
    public function MemberAdd(\Etcd\MemberAddRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/etcdserverpb.Cluster/MemberAdd',
        $argument,
        ['\Etcd\MemberAddResponse', 'decode'],
        $metadata, $options);
    }

    /**
     * MemberRemove removes an existing member from the cluster.
     * @param \Etcd\MemberRemoveRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall<\Etcd\MemberRemoveResponse>
     */
    public function MemberRemove(\Etcd\MemberRemoveRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/etcdserverpb.Cluster/MemberRemove',
        $argument,
        ['\Etcd\MemberRemoveResponse', 'decode'],
        $metadata, $options);
    }

    /**
     * MemberUpdate updates the member configuration.
     * @param \Etcd\MemberUpdateRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall<\Etcd\MemberUpdateResponse>
     */
    public function MemberUpdate(\Etcd\MemberUpdateRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/etcdserverpb.Cluster/MemberUpdate',
        $argument,
        ['\Etcd\MemberUpdateResponse', 'decode'],
        $metadata, $options);
    }

    /**
     * MemberList lists all the members in the cluster.
     * @param \Etcd\MemberListRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall<\Etcd\MemberListResponse>
     */
    public function MemberList(\Etcd\MemberListRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/etcdserverpb.Cluster/MemberList',
        $argument,
        ['\Etcd\MemberListResponse', 'decode'],
        $metadata, $options);
    }

    /**
     * MemberPromote promotes a member from raft learner (non-voting) to raft voting member.
     * @param \Etcd\MemberPromoteRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall<\Etcd\MemberPromoteResponse>
     */
    public function MemberPromote(\Etcd\MemberPromoteRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/etcdserverpb.Cluster/MemberPromote',
        $argument,
        ['\Etcd\MemberPromoteResponse', 'decode'],
        $metadata, $options);
    }

}

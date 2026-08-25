<?php
// GENERATED CODE -- DO NOT EDIT!

namespace Etcd;

/**
 */
class MaintenanceClient extends \Grpc\BaseStub {

    /**
     * @param string $hostname hostname
     * @param array $opts channel options
     * @param \Grpc\Channel $channel (optional) re-use channel object
     */
    public function __construct($hostname, $opts, $channel = null) {
        parent::__construct($hostname, $opts, $channel);
    }

    /**
     * Alarm activates, deactivates, and queries alarms regarding cluster health.
     * @param \Etcd\AlarmRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall<\Etcd\AlarmResponse>
     */
    public function Alarm(\Etcd\AlarmRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/etcdserverpb.Maintenance/Alarm',
        $argument,
        ['\Etcd\AlarmResponse', 'decode'],
        $metadata, $options);
    }

    /**
     * Status gets the status of the member.
     * @param \Etcd\StatusRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall<\Etcd\StatusResponse>
     */
    public function Status(\Etcd\StatusRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/etcdserverpb.Maintenance/Status',
        $argument,
        ['\Etcd\StatusResponse', 'decode'],
        $metadata, $options);
    }

    /**
     * Defragment defragments a member's backend database to recover storage space.
     * @param \Etcd\DefragmentRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall<\Etcd\DefragmentResponse>
     */
    public function Defragment(\Etcd\DefragmentRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/etcdserverpb.Maintenance/Defragment',
        $argument,
        ['\Etcd\DefragmentResponse', 'decode'],
        $metadata, $options);
    }

    /**
     * Hash computes the hash of whole backend keyspace,
     * including key, lease, and other buckets in storage.
     * This is designed for testing ONLY!
     * Do not rely on this in production with ongoing transactions,
     * since Hash operation does not hold MVCC locks.
     * Use "HashKV" API instead for "key" bucket consistency checks.
     * @param \Etcd\HashRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall<\Etcd\HashResponse>
     */
    public function Hash(\Etcd\HashRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/etcdserverpb.Maintenance/Hash',
        $argument,
        ['\Etcd\HashResponse', 'decode'],
        $metadata, $options);
    }

    /**
     * HashKV computes the hash of all MVCC keys up to a given revision.
     * It only iterates "key" bucket in backend storage.
     * @param \Etcd\HashKVRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall<\Etcd\HashKVResponse>
     */
    public function HashKV(\Etcd\HashKVRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/etcdserverpb.Maintenance/HashKV',
        $argument,
        ['\Etcd\HashKVResponse', 'decode'],
        $metadata, $options);
    }

    /**
     * Snapshot sends a snapshot of the entire backend from a member over a stream to a client.
     * @param \Etcd\SnapshotRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\ServerStreamingCall
     */
    public function Snapshot(\Etcd\SnapshotRequest $argument,
      $metadata = [], $options = []) {
        return $this->_serverStreamRequest('/etcdserverpb.Maintenance/Snapshot',
        $argument,
        ['\Etcd\SnapshotResponse', 'decode'],
        $metadata, $options);
    }

    /**
     * MoveLeader requests current leader node to transfer its leadership to transferee.
     * @param \Etcd\MoveLeaderRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall<\Etcd\MoveLeaderResponse>
     */
    public function MoveLeader(\Etcd\MoveLeaderRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/etcdserverpb.Maintenance/MoveLeader',
        $argument,
        ['\Etcd\MoveLeaderResponse', 'decode'],
        $metadata, $options);
    }

    /**
     * Downgrade requests downgrades, verifies feasibility or cancels downgrade
     * on the cluster version.
     * Supported since etcd 3.5.
     * @param \Etcd\DowngradeRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall<\Etcd\DowngradeResponse>
     */
    public function Downgrade(\Etcd\DowngradeRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/etcdserverpb.Maintenance/Downgrade',
        $argument,
        ['\Etcd\DowngradeResponse', 'decode'],
        $metadata, $options);
    }

}

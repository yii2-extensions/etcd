<?php
// GENERATED CODE -- DO NOT EDIT!

namespace Etcd;

/**
 */
class LeaseClient extends \Grpc\BaseStub {

    /**
     * @param string $hostname hostname
     * @param array $opts channel options
     * @param \Grpc\Channel $channel (optional) re-use channel object
     */
    public function __construct($hostname, $opts, $channel = null) {
        parent::__construct($hostname, $opts, $channel);
    }

    /**
     * LeaseGrant creates a lease which expires if the server does not receive a keepAlive
     * within a given time to live period. All keys attached to the lease will be expired and
     * deleted if the lease expires. Each expired key generates a delete event in the event history.
     * @param \Etcd\LeaseGrantRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall<\Etcd\LeaseGrantResponse>
     */
    public function LeaseGrant(\Etcd\LeaseGrantRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/etcdserverpb.Lease/LeaseGrant',
        $argument,
        ['\Etcd\LeaseGrantResponse', 'decode'],
        $metadata, $options);
    }

    /**
     * LeaseRevoke revokes a lease. All keys attached to the lease will expire and be deleted.
     * @param \Etcd\LeaseRevokeRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall<\Etcd\LeaseRevokeResponse>
     */
    public function LeaseRevoke(\Etcd\LeaseRevokeRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/etcdserverpb.Lease/LeaseRevoke',
        $argument,
        ['\Etcd\LeaseRevokeResponse', 'decode'],
        $metadata, $options);
    }

    /**
     * LeaseKeepAlive keeps the lease alive by streaming keep alive requests from the client
     * to the server and streaming keep alive responses from the server to the client.
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\BidiStreamingCall
     */
    public function LeaseKeepAlive($metadata = [], $options = []) {
        return $this->_bidiRequest('/etcdserverpb.Lease/LeaseKeepAlive',
        ['\Etcd\LeaseKeepAliveResponse','decode'],
        $metadata, $options);
    }

    /**
     * LeaseTimeToLive retrieves lease information.
     * @param \Etcd\LeaseTimeToLiveRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall<\Etcd\LeaseTimeToLiveResponse>
     */
    public function LeaseTimeToLive(\Etcd\LeaseTimeToLiveRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/etcdserverpb.Lease/LeaseTimeToLive',
        $argument,
        ['\Etcd\LeaseTimeToLiveResponse', 'decode'],
        $metadata, $options);
    }

    /**
     * LeaseLeases lists all existing leases.
     * @param \Etcd\LeaseLeasesRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall<\Etcd\LeaseLeasesResponse>
     */
    public function LeaseLeases(\Etcd\LeaseLeasesRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/etcdserverpb.Lease/LeaseLeases',
        $argument,
        ['\Etcd\LeaseLeasesResponse', 'decode'],
        $metadata, $options);
    }

}

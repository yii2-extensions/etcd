<?php
// GENERATED CODE -- DO NOT EDIT!

namespace Etcd;

/**
 */
class KVClient extends \Grpc\BaseStub {

    /**
     * @param string $hostname hostname
     * @param array $opts channel options
     * @param \Grpc\Channel $channel (optional) re-use channel object
     */
    public function __construct($hostname, $opts, $channel = null) {
        parent::__construct($hostname, $opts, $channel);
    }

    /**
     * Range gets the keys in the range from the key-value store.
     * @param \Etcd\RangeRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall<\Etcd\RangeResponse>
     */
    public function Range(\Etcd\RangeRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/etcdserverpb.KV/Range',
        $argument,
        ['\Etcd\RangeResponse', 'decode'],
        $metadata, $options);
    }

    /**
     * RangeStream gets the keys in the range from the key-value store.
     *
     * This RPC is intentionally gRPC-only and does not provide a
     * grpc-gateway REST mapping, because streaming chunked responses
     * are not a good fit for standard JSON/REST semantics.
     * @param \Etcd\RangeRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\ServerStreamingCall
     */
    public function RangeStream(\Etcd\RangeRequest $argument,
      $metadata = [], $options = []) {
        return $this->_serverStreamRequest('/etcdserverpb.KV/RangeStream',
        $argument,
        ['\Etcd\RangeStreamResponse', 'decode'],
        $metadata, $options);
    }

    /**
     * Put puts the given key into the key-value store.
     * A put request increments the revision of the key-value store
     * and generates one event in the event history.
     * @param \Etcd\PutRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall<\Etcd\PutResponse>
     */
    public function Put(\Etcd\PutRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/etcdserverpb.KV/Put',
        $argument,
        ['\Etcd\PutResponse', 'decode'],
        $metadata, $options);
    }

    /**
     * DeleteRange deletes the given range from the key-value store.
     * A delete request increments the revision of the key-value store
     * and generates a delete event in the event history for every deleted key.
     * @param \Etcd\DeleteRangeRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall<\Etcd\DeleteRangeResponse>
     */
    public function DeleteRange(\Etcd\DeleteRangeRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/etcdserverpb.KV/DeleteRange',
        $argument,
        ['\Etcd\DeleteRangeResponse', 'decode'],
        $metadata, $options);
    }

    /**
     * Txn processes multiple requests in a single transaction.
     * A txn request increments the revision of the key-value store
     * and generates events with the same revision for every completed request.
     * It is not allowed to modify the same key several times within one txn.
     * @param \Etcd\TxnRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall<\Etcd\TxnResponse>
     */
    public function Txn(\Etcd\TxnRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/etcdserverpb.KV/Txn',
        $argument,
        ['\Etcd\TxnResponse', 'decode'],
        $metadata, $options);
    }

    /**
     * Compact compacts the event history in the etcd key-value store. The key-value
     * store should be periodically compacted or the event history will continue to grow
     * indefinitely.
     * @param \Etcd\CompactionRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall<\Etcd\CompactionResponse>
     */
    public function Compact(\Etcd\CompactionRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/etcdserverpb.KV/Compact',
        $argument,
        ['\Etcd\CompactionResponse', 'decode'],
        $metadata, $options);
    }

}

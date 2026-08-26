<?php

declare(strict_types=1);

namespace Yii2\Extensions\Etcd\RPC;

use Etcd\Compare;
use Etcd\Compare\CompareResult;
use Etcd\Compare\CompareTarget;
use Etcd\CompactionRequest;
use Etcd\DeleteRangeRequest;
use Etcd\DeleteRangeResponse as EtcdDeleteRangeResponse;
use Etcd\KeyValue;
use Etcd\KVClient;
use Etcd\PutRequest;
use Etcd\PutResponse;
use Etcd\RangeRequest;
use Etcd\RangeResponse as EtcdRangeResponse;
use Etcd\RequestOp;
use Etcd\ResponseOp;
use Etcd\TxnRequest;
use Etcd\TxnResponse as EtcdTxnResponse;
use Google\Protobuf\Internal\RepeatedField;
use Yii2\Extensions\Etcd\EtcdKvServiceInterface;
use Yii2\Extensions\Etcd\Responses\DeleteRangeResponse;
use Yii2\Extensions\Etcd\Responses\TxnResponse;

final class EtcdGrpcKv extends AbstractEtcdGrpcService implements EtcdKvServiceInterface
{
    private ?KVClient $client = null;

    private function getClient(): KVClient
    {
        return $this->client ??= new KVClient($this->connection->host, $this->getConnectionOptions());
    }

    #[\Override]
    public function getRange(string $key, string $rangeEnd): RangeResponse
    {
        $request = new RangeRequest();
        $request->setKey($key);
        $request->setRangeEnd($rangeEnd);

        /** @var EtcdRangeResponse $response */
        $response = $this->wait($this->getClient()->Range($request));

        return new RangeResponse($this->collectKvs($response->getKvs()));
    }

    #[\Override]
    public function getKey(string $key): RangeResponse
    {
        $request = new RangeRequest();
        $request->setKey($key);

        /** @var EtcdRangeResponse $response */
        $response = $this->wait($this->getClient()->Range($request));

        return new RangeResponse($this->collectKvs($response->getKvs()));
    }

    #[\Override]
    public function put(string $key, string $value): bool
    {
        $request = new PutRequest();
        $request->setKey($key);
        $request->setValue($value);

        $this->wait($this->getClient()->Put($request));

        return true;
    }

    #[\Override]
    public function deleteRange(string $key, string $rangeEnd = ''): DeleteRangeResponse
    {
        $request = new DeleteRangeRequest();
        $request->setKey($key);

        if ('' !== $rangeEnd) {
            $request->setRangeEnd($rangeEnd);
        }

        /** @var EtcdDeleteRangeResponse $response */
        $response = $this->wait($this->getClient()->DeleteRange($request));

        return new DeleteRangeResponse($this->convertDeleteRangeResponse($response));
    }

    #[\Override]
    public function txn(array $compare, array $success, array $failure): TxnResponse
    {
        $request = new TxnRequest();
        $request->setCompare($this->buildCompares($compare));
        $request->setSuccess($this->buildRequestOps($success));
        $request->setFailure($this->buildRequestOps($failure));

        /** @var EtcdTxnResponse $response */
        $response = $this->wait($this->getClient()->Txn($request));

        return new TxnResponse($this->convertTxnResponse($response));
    }

    #[\Override]
    public function compact(int $revision, bool $physical = false): bool
    {
        $request = new CompactionRequest();
        $request->setRevision($revision);
        $request->setPhysical($physical);

        $this->wait($this->getClient()->Compact($request));

        return true;
    }

    /**
     * @param array<int, array<string, mixed>> $compares
     * @return Compare[]
     */
    private function buildCompares(array $compares): array
    {
        $result = [];

        foreach ($compares as $compare) {
            $item = new Compare();
            $item->setResult(is_int($compare['result'])
                ? $compare['result']
                : (int) CompareResult::value($compare['result']));
            $item->setTarget(is_int($compare['target'])
                ? $compare['target']
                : (int) CompareTarget::value($compare['target']));
            $item->setKey((string) $compare['key']);

            if (isset($compare['range_end'])) {
                $item->setRangeEnd((string) $compare['range_end']);
            }

            if (isset($compare['version'])) {
                $item->setVersion($compare['version']);
            } elseif (isset($compare['create_revision'])) {
                $item->setCreateRevision($compare['create_revision']);
            } elseif (isset($compare['mod_revision'])) {
                $item->setModRevision($compare['mod_revision']);
            } elseif (isset($compare['lease'])) {
                $item->setLease($compare['lease']);
            } elseif (isset($compare['value'])) {
                $item->setValue((string) $compare['value']);
            }

            $result[] = $item;
        }

        return $result;
    }

    /**
     * @param array<int, array<string, mixed>> $ops
     * @return RequestOp[]
     */
    private function buildRequestOps(array $ops): array
    {
        $result = [];

        foreach ($ops as $op) {
            $item = new RequestOp();

            if (isset($op['request_range'])) {
                $item->setRequestRange($this->buildRangeRequest($op['request_range']));
            } elseif (isset($op['request_put'])) {
                $item->setRequestPut($this->buildPutRequest($op['request_put']));
            } elseif (isset($op['request_delete_range'])) {
                $item->setRequestDeleteRange($this->buildDeleteRangeRequest($op['request_delete_range']));
            } elseif (isset($op['request_txn'])) {
                $txn = $op['request_txn'];
                $request = new TxnRequest();
                $request->setCompare($this->buildCompares($txn['compare']));
                $request->setSuccess($this->buildRequestOps($txn['success']));
                $request->setFailure($this->buildRequestOps($txn['failure']));
                $item->setRequestTxn($request);
            }

            $result[] = $item;
        }

        return $result;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function buildRangeRequest(array $data): RangeRequest
    {
        $request = new RangeRequest();

        if (isset($data['key'])) {
            $request->setKey((string) $data['key']);
        }

        if (isset($data['range_end'])) {
            $request->setRangeEnd((string) $data['range_end']);
        }

        if (isset($data['limit'])) {
            $request->setLimit($data['limit']);
        }

        if (isset($data['revision'])) {
            $request->setRevision($data['revision']);
        }

        if (isset($data['sort_order'])) {
            $request->setSortOrder($data['sort_order']);
        }

        if (isset($data['sort_target'])) {
            $request->setSortTarget($data['sort_target']);
        }

        if (isset($data['serializable'])) {
            $request->setSerializable((bool) $data['serializable']);
        }

        if (isset($data['keys_only'])) {
            $request->setKeysOnly((bool) $data['keys_only']);
        }

        if (isset($data['count_only'])) {
            $request->setCountOnly((bool) $data['count_only']);
        }

        return $request;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function buildPutRequest(array $data): PutRequest
    {
        $request = new PutRequest();

        if (isset($data['key'])) {
            $request->setKey((string) $data['key']);
        }

        if (isset($data['value'])) {
            $request->setValue((string) $data['value']);
        }

        if (isset($data['lease'])) {
            $request->setLease($data['lease']);
        }

        if (isset($data['prev_kv'])) {
            $request->setPrevKv((bool) $data['prev_kv']);
        }

        if (isset($data['ignore_value'])) {
            $request->setIgnoreValue((bool) $data['ignore_value']);
        }

        if (isset($data['ignore_lease'])) {
            $request->setIgnoreLease((bool) $data['ignore_lease']);
        }

        return $request;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function buildDeleteRangeRequest(array $data): DeleteRangeRequest
    {
        $request = new DeleteRangeRequest();

        if (isset($data['key'])) {
            $request->setKey((string) $data['key']);
        }

        if (isset($data['range_end'])) {
            $request->setRangeEnd((string) $data['range_end']);
        }

        if (isset($data['prev_kv'])) {
            $request->setPrevKv((bool) $data['prev_kv']);
        }

        return $request;
    }

    /**
     * @return array<string, mixed>
     */
    private function convertKeyValue(KeyValue $kv): array
    {
        return [
            'key' => $kv->getKey(),
            'value' => $kv->getValue(),
            'create_revision' => $kv->getCreateRevision(),
            'mod_revision' => $kv->getModRevision(),
            'version' => $kv->getVersion(),
            'lease' => $kv->getLease(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function convertRangeResponse(?EtcdRangeResponse $response): array
    {
        if (null === $response) {
            return [];
        }

        $kvs = [];

        /** @var RepeatedField<\Etcd\KeyValue> $kvList */
        $kvList = $response->getKvs();

        foreach ($kvList as $kv) {
            $kvs[] = $this->convertKeyValue($kv);
        }

        return [
            'header' => $this->convertHeader($response->getHeader()),
            'kvs' => $kvs,
            'more' => $response->getMore(),
            'count' => $response->getCount(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function convertPutResponse(?PutResponse $response): array
    {
        if (null === $response) {
            return [];
        }

        return [
            'header' => $this->convertHeader($response->getHeader()),
            'prev_kv' => null === $response->getPrevKv() ? [] : $this->convertKeyValue($response->getPrevKv()),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function convertDeleteRangeResponse(?EtcdDeleteRangeResponse $response): array
    {
        if (null === $response) {
            return [];
        }

        return [
            'header' => $this->convertHeader($response->getHeader()),
            'deleted' => $response->getDeleted(),
            'prev_kvs' => $this->convertKeyValues($response->getPrevKvs()),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function convertTxnResponse(?EtcdTxnResponse $response): array
    {
        if (null === $response) {
            return [];
        }

        $responses = [];

        /** @var RepeatedField<\Etcd\ResponseOp> $opList */
        $opList = $response->getResponses();

        foreach ($opList as $op) {
            $responses[] = $this->convertResponseOp($op);
        }

        return [
            'header' => $this->convertHeader($response->getHeader()),
            'succeeded' => $response->getSucceeded(),
            'responses' => $responses,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function convertResponseOp(ResponseOp $op): array
    {
        if ($op->hasResponseRange()) {
            return ['response_range' => $this->convertRangeResponse($op->getResponseRange())];
        }

        if ($op->hasResponsePut()) {
            return ['response_put' => $this->convertPutResponse($op->getResponsePut())];
        }

        if ($op->hasResponseDeleteRange()) {
            return ['response_delete_range' => $this->convertDeleteRangeResponse($op->getResponseDeleteRange())];
        }

        if ($op->hasResponseTxn()) {
            return ['response_txn' => $this->convertTxnResponse($op->getResponseTxn())];
        }

        return [];
    }

    /**
     * @param RepeatedField<\Etcd\KeyValue> $kvs
     * @return array<int, array<string, mixed>>
     */
    private function convertKeyValues(RepeatedField $kvs): array
    {
        $result = [];

        /** @var RepeatedField<\Etcd\KeyValue> $kvList */
        $kvList = $kvs;

        foreach ($kvList as $kv) {
            $result[] = $this->convertKeyValue($kv);
        }

        return $result;
    }

    /**
     * @param RepeatedField<\Etcd\KeyValue> $fields
     * @return array<string, mixed>
     */
    private function collectKvs(RepeatedField $fields): array
    {
        $kvs = [];
        $protobufKvs = $fields;

        foreach ($protobufKvs as $item) {
            /** @var KeyValue $item */
            $kvs['kvs'][] = [
                'key' => $item->getKey(),
                'value' => $item->getValue(),
            ];
        }

        return $kvs;
    }
}

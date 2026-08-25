<?php

declare(strict_types=1);

namespace Yii2\Extensions\Etcd\Rest;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use JsonException;
use Yii2\Extensions\Etcd\EtcdEndpoint;
use Yii2\Extensions\Etcd\EtcdKvServiceInterface;

final class EtcdRestKv extends AbstractEtcdRestService implements EtcdKvServiceInterface
{
    public function getKey(string $key): RangeResponse
    {
        /** @var array{body: string, headers?: array<string, string>} $options */
        $options = [
            RequestOptions::BODY => json_encode(['key' => trim(base64_encode($key))], JSON_THROW_ON_ERROR),
        ];
        $response = $this->connection->client->post(
            $this->connection->host . EtcdEndpoint::ETCD_VERSION . EtcdEndpoint::RANGE,
            array_merge($options, $this->getTokenOptions())
        );

        return new RangeResponse(json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR));
    }

    /**
     * Get all keys prefixed with "foo" ($key = foo, $rangeEnd = fop)
     *
     * @param string $key
     * @param string $rangeEnd
     * @return RangeResponse
     * @throws GuzzleException|JsonException
     */
    public function getRange(string $key, string $rangeEnd): RangeResponse
    {
        /** @var array{body: string, headers?: array<string, string>} $options */
        $options = [
            RequestOptions::BODY => json_encode(
                ['key' => trim(base64_encode($key)), 'range_end' => trim(base64_encode($rangeEnd))],
                JSON_THROW_ON_ERROR
            ),
        ];

        $response = $this->connection->client->post(
            $this->connection->host . EtcdEndpoint::ETCD_VERSION . EtcdEndpoint::RANGE,
            array_merge($options, $this->getTokenOptions())
        );

        return new RangeResponse(json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR));
    }

    public function put(string $key, string $value): bool
    {
        /** @var array{body: string, headers?: array<string, string>} $options */
        $options = [
            RequestOptions::BODY => json_encode(
                ['key' => base64_encode(trim($key)), 'value' => base64_encode(trim($value))],
                JSON_THROW_ON_ERROR
            ),
        ];
        $response = $this->connection->client->post(
            $this->connection->host . EtcdEndpoint::ETCD_VERSION . EtcdEndpoint::PUT,
            array_merge($options, $this->getTokenOptions())
        );

        return 200 === $response->getStatusCode();
    }

    /**
     * @throws GuzzleException|JsonException
     */
    public function deleteRange(string $key, string $rangeEnd = ''): array
    {
        $body = ['key' => base64_encode(trim($key))];

        if ('' !== $rangeEnd) {
            $body['range_end'] = base64_encode(trim($rangeEnd));
        }

        return $this->request(EtcdEndpoint::DELETE_RANGE, $body);
    }

    /**
     * @param array<int, array<string, mixed>> $compare
     * @param array<int, array<string, mixed>> $success
     * @param array<int, array<string, mixed>> $failure
     * @return array<string, mixed>
     * @throws GuzzleException|JsonException
     */
    public function txn(array $compare, array $success, array $failure): array
    {
        return $this->request(
            EtcdEndpoint::TXN,
            [
                'compare' => $this->transformCompares($compare),
                'success' => $this->transformRequestOps($success),
                'failure' => $this->transformRequestOps($failure),
            ]
        );
    }

    /**
     * @throws GuzzleException|JsonException
     */
    public function compact(int $revision, bool $physical = false): bool
    {
        $result = $this->request(
            EtcdEndpoint::COMPACTION,
            ['revision' => (string) $revision, 'physical' => $physical]
        );

        return isset($result['header']);
    }

    /**
     * Encode bytes fields and cast int64 fields for transaction operations.
     *
     * @param array<int, array<string, mixed>> $ops
     * @return array<int, array<string, mixed>>
     */
    private function transformRequestOps(array $ops): array
    {
        $result = [];

        foreach ($ops as $op) {
            if (isset($op['request_range'])) {
                $op['request_range'] = $this->transformRangeRequest($op['request_range']);
            } elseif (isset($op['request_put'])) {
                $op['request_put'] = $this->transformPutRequest($op['request_put']);
            } elseif (isset($op['request_delete_range'])) {
                $op['request_delete_range'] = $this->transformDeleteRangeRequest($op['request_delete_range']);
            } elseif (isset($op['request_txn'])) {
                $txn = $op['request_txn'];
                $op['request_txn'] = [
                    'compare' => $this->transformCompares($txn['compare']),
                    'success' => $this->transformRequestOps($txn['success']),
                    'failure' => $this->transformRequestOps($txn['failure']),
                ];
            }

            $result[] = $op;
        }

        return $result;
    }

    /**
     * @param array<int, array<string, mixed>> $compares
     * @return array<int, array<string, mixed>>
     */
    private function transformCompares(array $compares): array
    {
        $result = [];

        foreach ($compares as $compare) {
            if (isset($compare['key'])) {
                $compare['key'] = base64_encode($compare['key']);
            }

            if (isset($compare['range_end'])) {
                $compare['range_end'] = base64_encode($compare['range_end']);
            }

            if (isset($compare['value'])) {
                $compare['value'] = base64_encode((string) $compare['value']);
            }

            foreach (['version', 'create_revision', 'mod_revision', 'lease'] as $field) {
                if (isset($compare[$field])) {
                    $compare[$field] = (string) $compare[$field];
                }
            }

            $result[] = $compare;
        }

        return $result;
    }

    /**
     * @param array<string, mixed> $request
     * @return array<string, mixed>
     */
    private function transformRangeRequest(array $request): array
    {
        if (isset($request['key'])) {
            $request['key'] = base64_encode((string) $request['key']);
        }

        if (isset($request['range_end'])) {
            $request['range_end'] = base64_encode((string) $request['range_end']);
        }

        foreach (['limit', 'revision', 'min_mod_revision', 'max_mod_revision', 'min_create_revision', 'max_create_revision'] as $field) {
            if (isset($request[$field])) {
                $request[$field] = (string) $request[$field];
            }
        }

        return $request;
    }

    /**
     * @param array<string, mixed> $request
     * @return array<string, mixed>
     */
    private function transformPutRequest(array $request): array
    {
        if (isset($request['key'])) {
            $request['key'] = base64_encode((string) $request['key']);
        }

        if (isset($request['value'])) {
            $request['value'] = base64_encode((string) $request['value']);
        }

        if (isset($request['lease'])) {
            $request['lease'] = (string) $request['lease'];
        }

        return $request;
    }

    /**
     * @param array<string, mixed> $request
     * @return array<string, mixed>
     */
    private function transformDeleteRangeRequest(array $request): array
    {
        if (isset($request['key'])) {
            $request['key'] = base64_encode((string) $request['key']);
        }

        if (isset($request['range_end'])) {
            $request['range_end'] = base64_encode((string) $request['range_end']);
        }

        return $request;
    }
}

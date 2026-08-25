<?php

declare(strict_types=1);

namespace Yii2\Extensions\Etcd;

interface EtcdKvServiceInterface
{
    public function getKey(string $key): EtcdRangeResponseInterface;

    public function getRange(string $key, string $rangeEnd): EtcdRangeResponseInterface;

    public function put(string $key, string $value): bool;

    /**
     * DeleteRange deletes the given range from the key-value store.
     *
     * @return array<string, mixed> response fields: header, deleted, prev_kvs
     */
    public function deleteRange(string $key, string $rangeEnd = ''): array;

    /**
     * Txn processes multiple requests in a single transaction.
     *
     * @param array<int, array<string, mixed>> $compare
     * @param array<int, array<string, mixed>> $success
     * @param array<int, array<string, mixed>> $failure
     * @return array<string, mixed> response fields: header, succeeded, responses
     */
    public function txn(array $compare, array $success, array $failure): array;

    /**
     * Compact compacts the event history in the key-value store up to a given revision.
     */
    public function compact(int $revision, bool $physical = false): bool;
}

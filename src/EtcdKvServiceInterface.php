<?php

declare(strict_types=1);

namespace Yii2\Extensions\Etcd;

use Yii2\Extensions\Etcd\Responses\DeleteRangeResponse;
use Yii2\Extensions\Etcd\Responses\TxnResponse;

interface EtcdKvServiceInterface
{
    public function getKey(string $key): EtcdRangeResponseInterface;

    public function getRange(string $key, string $rangeEnd): EtcdRangeResponseInterface;

    public function put(string $key, string $value): bool;

    /**
     * DeleteRange deletes the given range from the key-value store.
     */
    public function deleteRange(string $key, string $rangeEnd = ''): DeleteRangeResponse;

    /**
     * Txn processes multiple requests in a single transaction.
     *
     * @param array<int, array<string, mixed>> $compare
     * @param array<int, array<string, mixed>> $success
     * @param array<int, array<string, mixed>> $failure
     */
    public function txn(array $compare, array $success, array $failure): TxnResponse;

    /**
     * Compact compacts the event history in the key-value store up to a given revision.
     */
    public function compact(int $revision, bool $physical = false): bool;
}

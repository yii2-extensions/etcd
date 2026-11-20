# Benchmark

Measured library: `yii2-extensions/etcd` 2.0.0, commit `6797620` on `main`.

Median of three runs. Time is in milliseconds. Memory is the peak RSS (`VmHWM`) of that scenario's PHP process, in MiB.

A cold client is a new `Etcd` object on every call inside one process. It is not a new PHP process. Iterations after the discarded warmup: long-lived and raw calls 800, cold client 120, 128 B / 8 KiB / 256 KiB payloads 600 / 300 / 80, ranges of 1 / 100 / 1000 keys 300 / 120 / 40, auth without a token refresh 400, cold token 60, lease 200. Concurrency is a 3 second window; ops/s is the total across processes.

`opcache.enable_cli=1` and `xdebug.mode=off` were set for the run. The Xdebug extension stayed loaded. The image default is `opcache.enable_cli=Off`.

## Conditions

| | |
|---|---|
| Library | yii2-extensions/etcd 2.0.0 |
| Revision | `6797620` (`main`) |
| Date | 2026-11-20 |
| PHP | 8.5.10 |
| gRPC | extension 1.83.1, composer `grpc/grpc` 1.85.0 |
| etcd | 3.7.1 (cluster 3.7.0) |
| Host | `etcd-php` container, `etcd:2379`, Docker Desktop 8 CPU / 8 GB |
| opcache | On for the run (`opcache.enable_cli=1`) |
| xdebug | loaded, `xdebug.mode=off` |
| Measurement window | median of 3 runs |
| Warmup discarded | yes |

## Long-lived process

One `Etcd` instance for the whole series, 128-byte values, no authentication.

| Operation | Protocol | p50 | p95 | p99 | ops/s | Memory |
|---|---|---:|---:|---:|---:|---:|
| put | REST | 1.87 | 2.90 | 4.21 | 504 | 46.2 |
| put | gRPC | 2.15 | 3.74 | 6.25 | 424 | 47.5 |
| getKey + getFirstKeyValue | REST | 1.02 | 1.47 | 1.80 | 940 | 45.7 |
| getKey + getFirstKeyValue | gRPC | 0.86 | 1.34 | 1.55 | 1099 | 47.7 |
| deleteRange, one key | REST | 1.88 | 2.97 | 3.73 | 502 | 46.0 |
| deleteRange, one key | gRPC | 1.79 | 2.77 | 3.74 | 520 | 47.5 |
| txn, compare + put | REST | 2.47 | 3.95 | 5.30 | 382 | 46.1 |
| txn, compare + put | gRPC | 1.89 | 3.03 | 3.52 | 495 | 47.6 |

## Cold client

A new `Etcd` instance for every call, 128-byte values, no authentication.

| Operation | Protocol | p50 | p95 | p99 | ops/s | Memory |
|---|---|---:|---:|---:|---:|---:|
| put | REST | 3.52 | 7.36 | 11.89 | 249 | 49.8 |
| put | gRPC | 2.15 | 3.40 | 4.03 | 442 | 47.6 |
| getKey + getFirstKeyValue | REST | 2.26 | 3.68 | 8.18 | 401 | 49.4 |
| getKey + getFirstKeyValue | gRPC | 1.08 | 1.55 | 1.96 | 873 | 48.1 |
| deleteRange, one key | REST | 3.11 | 4.18 | 11.10 | 298 | 49.8 |
| deleteRange, one key | gRPC | 1.71 | 2.46 | 3.36 | 540 | 47.7 |
| txn, compare + put | REST | 3.36 | 5.87 | 11.19 | 264 | 49.9 |
| txn, compare + put | gRPC | 1.94 | 3.31 | 4.54 | 408 | 48.0 |

## Value size

Long-lived process, no authentication.

| Operation | Protocol | Size | p50 | p95 | p99 | ops/s | Memory |
|---|---|---:|---:|---:|---:|---:|---:|
| put | REST | 128 B | 1.81 | 2.93 | 4.31 | 513 | 46.2 |
| put | REST | 8 KiB | 3.08 | 4.74 | 7.64 | 289 | 45.9 |
| put | REST | 256 KiB | 23.51 | 71.44 | 100.94 | 31.4 | 48.1 |
| put | gRPC | 128 B | 1.76 | 2.91 | 3.96 | 527 | 47.4 |
| put | gRPC | 8 KiB | 2.84 | 3.74 | 6.39 | 337 | 47.6 |
| put | gRPC | 256 KiB | 7.50 | 19.51 | 101.72 | 106 | 47.5 |
| getKey + getFirstKeyValue | REST | 128 B | 0.99 | 1.41 | 1.71 | 959 | 46.3 |
| getKey + getFirstKeyValue | REST | 8 KiB | 1.15 | 1.59 | 2.16 | 841 | 46.1 |
| getKey + getFirstKeyValue | REST | 256 KiB | 4.49 | 11.41 | 86.70 | 161 | 47.9 |
| getKey + getFirstKeyValue | gRPC | 128 B | 0.77 | 1.23 | 1.77 | 1211 | 48.1 |
| getKey + getFirstKeyValue | gRPC | 8 KiB | 0.83 | 1.28 | 1.53 | 1153 | 48.0 |
| getKey + getFirstKeyValue | gRPC | 256 KiB | 2.09 | 3.04 | 6.77 | 432 | 46.5 |

## Range

128-byte values, long-lived process. "Decode all values" is `getRange` plus decoding every value. On REST that is `base64_decode` of each value. On gRPC the component has already decoded the values, and the extra step reads them.

| Operation | Keys | Protocol | p50 | p95 | p99 | ops/s | Memory |
|---|---:|---|---:|---:|---:|---:|---:|
| getRange | 1 | REST | 1.17 | 1.60 | 2.19 | 824 | 46.3 |
| getRange | 1 | gRPC | 0.75 | 1.16 | 1.44 | 1246 | 46.8 |
| getRange | 100 | REST | 1.77 | 2.38 | 2.68 | 542 | 46.2 |
| getRange | 100 | gRPC | 1.38 | 1.79 | 2.30 | 708 | 46.3 |
| getRange | 1000 | REST | 8.15 | 14.16 | 18.30 | 108 | 48.3 |
| getRange | 1000 | gRPC | 4.78 | 5.66 | 8.80 | 205 | 47.7 |
| decode all values | 1 | REST | 1.08 | 1.50 | 1.70 | 895 | 46.1 |
| decode all values | 1 | gRPC | 0.78 | 1.24 | 1.44 | 1201 | 46.6 |
| decode all values | 100 | REST | 1.87 | 2.35 | 3.00 | 516 | 45.9 |
| decode all values | 100 | gRPC | 1.27 | 1.76 | 2.32 | 751 | 47.7 |
| decode all values | 1000 | REST | 9.62 | 12.58 | 26.92 | 96.6 | 48.1 |
| decode all values | 1000 | gRPC | 6.50 | 8.00 | 9.17 | 146 | 47.8 |

## Authentication

`getKey` + `getFirstKeyValue`, 128-byte values.

| Mode | Protocol | p50 | p95 | p99 | ops/s |
|---|---|---:|---:|---:|---:|
| empty user | REST | 1.24 | 1.74 | 2.13 | 777 |
| empty user | gRPC | 0.69 | 1.05 | 1.34 | 1362 |
| cached token | REST | 1.34 | 1.88 | 2.16 | 726 |
| cached token | gRPC | 0.71 | 1.11 | 1.29 | 1317 |
| new Etcd, cold token | REST | 81.27 | 87.76 | 101.68 | 12.1 |
| new Etcd, cold token | gRPC | 78.86 | 83.05 | 95.95 | 12.6 |

## Lease

Long-lived process.

| Operation | Protocol | p50 | p95 | p99 | ops/s |
|---|---|---:|---:|---:|---:|
| leaseGrant | REST | 2.44 | 3.41 | 5.26 | 393 |
| leaseGrant | gRPC | 1.79 | 2.51 | 3.15 | 535 |
| leaseTimeToLive | REST | 1.29 | 1.93 | 2.59 | 756 |
| leaseTimeToLive | gRPC | 0.85 | 1.27 | 1.42 | 1153 |
| leaseRevoke | REST | 3.79 | 4.89 | 6.01 | 260 |
| leaseRevoke | gRPC | 3.19 | 4.01 | 4.87 | 304 |

## Concurrency

A long-lived client in each process, 128-byte values, no authentication. ops/s is the total across all processes.

| Operation | Processes | Protocol | p50 | p95 | p99 | ops/s |
|---|---:|---|---:|---:|---:|---:|
| put | 1 | REST | 2.44 | 3.80 | 5.14 | 381 |
| put | 1 | gRPC | 1.91 | 3.17 | 4.40 | 483 |
| put | 8 | REST | 3.85 | 6.87 | 9.74 | 1927 |
| put | 8 | gRPC | 3.25 | 5.25 | 7.08 | 2350 |
| put | 32 | REST | 11.04 | 18.54 | 25.09 | 2701 |
| put | 32 | gRPC | 10.23 | 21.93 | 35.73 | 2704 |
| getKey + getFirstKeyValue | 1 | REST | 1.44 | 2.14 | 2.79 | 650 |
| getKey + getFirstKeyValue | 1 | gRPC | 0.82 | 1.37 | 1.97 | 1107 |
| getKey + getFirstKeyValue | 8 | REST | 2.30 | 4.08 | 5.69 | 3206 |
| getKey + getFirstKeyValue | 8 | gRPC | 1.38 | 2.66 | 4.20 | 5316 |
| getKey + getFirstKeyValue | 32 | REST | 6.98 | 17.41 | 26.23 | 3880 |
| getKey + getFirstKeyValue | 32 | gRPC | 4.33 | 9.87 | 17.11 | 6315 |

## Wrapper cost

Long-lived process, 128-byte values. Difference = component p95 − raw call p95 on the same protocol.

| Operation | Protocol | Component p95 | Raw call p95 | Difference |
|---|---|---:|---:|---:|
| put | REST | 2.90 | 3.80 | -0.90 |
| put | gRPC | 3.74 | 2.84 | 0.90 |
| getKey + getFirstKeyValue | REST | 1.47 | 2.65 | -1.18 |
| getKey + getFirstKeyValue | gRPC | 1.34 | 1.26 | 0.08 |

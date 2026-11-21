# Benchmark

Measurements of `yii2-extensions/etcd`, one row per release. To record a new version, copy each row in a section and
fill it under the same operation and protocol. Time is in milliseconds. Memory is the peak RSS (`VmHWM`) of that
scenario's PHP process, in MiB.

A cold client is a new `Etcd` object on every call inside one process. It is not a new PHP process. Iterations after the
discarded warmup: long-lived and raw calls 800, cold client 120, 128 B / 8 KiB / 256 KiB payloads 600 / 300 / 80, ranges
of 1 / 100 / 1000 keys 300 / 120 / 40, auth without a token refresh 400, cold token 60, lease 200. Concurrency is a 3
second window; ops/s is the total across processes. Each run is the median of three passes, with warmup discarded.

## Conditions

|          | 2.0.0                                                          | 2.1.0                                                          |
| -------- | -------------------------------------------------------------- | -------------------------------------------------------------- |
| Revision | `6797620` (`main`)                                             | `8037ce0` (working tree)                                       |
| Date     | 2026-11-20                                                     | 2026-11-21                                                     |
| PHP      | 8.5.10                                                         | 8.5.10                                                         |
| gRPC     | extension 1.83.1, composer `grpc/grpc` 1.85.0                  | extension 1.83.1, composer `grpc/grpc` 1.85.0                  |
| etcd     | 3.7.1 (cluster 3.7.0)                                          | 3.7.1 (cluster 3.7.0)                                          |
| Host     | `etcd-php` container, `etcd:2379`, Docker Desktop 8 CPU / 8 GB | `etcd-php` container, `etcd:2379`, Docker Desktop 8 CPU / 8 GB |
| opcache  | On for the run (`opcache.enable_cli=1`)                        | On for the run (`opcache.enable_cli=1`)                        |
| xdebug   | loaded, `xdebug.mode=off`                                      | loaded, `xdebug.mode=off`                                      |

## Long-lived process

One `Etcd` instance for the whole series, 128-byte values, no authentication.

| Operation                 | Protocol | Version |  p50 |  p95 |  p99 | ops/s | Memory |
| ------------------------- | -------- | ------- | ---: | ---: | ---: | ----: | -----: |
| put                       | REST     | 2.0.0   | 1.87 | 2.90 | 4.21 |   504 |   46.2 |
| put                       | REST     | 2.1.0   | 2.22 | 4.04 | 6.22 |   406 |   46.0 |
| put                       | gRPC     | 2.0.0   | 2.15 | 3.74 | 6.25 |   424 |   47.5 |
| put                       | gRPC     | 2.1.0   | 2.11 | 3.48 | 5.30 |   437 |   47.9 |
| getKey + getFirstKeyValue | REST     | 2.0.0   | 1.02 | 1.47 | 1.80 |   940 |   45.7 |
| getKey + getFirstKeyValue | REST     | 2.1.0   | 1.19 | 2.78 | 3.72 |   714 |   46.2 |
| getKey + getFirstKeyValue | gRPC     | 2.0.0   | 0.86 | 1.34 | 1.55 |  1099 |   47.7 |
| getKey + getFirstKeyValue | gRPC     | 2.1.0   | 0.80 | 1.38 | 1.97 |  1137 |   47.9 |
| deleteRange, one key      | REST     | 2.0.0   | 1.88 | 2.97 | 3.73 |   502 |   46.0 |
| deleteRange, one key      | REST     | 2.1.0   | 2.14 | 3.62 | 4.83 |   423 |   46.2 |
| deleteRange, one key      | gRPC     | 2.0.0   | 1.79 | 2.77 | 3.74 |   520 |   47.5 |
| deleteRange, one key      | gRPC     | 2.1.0   | 2.21 | 3.38 | 4.98 |   434 |   48.0 |
| txn, compare + put        | REST     | 2.0.0   | 2.47 | 3.95 | 5.30 |   382 |   46.1 |
| txn, compare + put        | REST     | 2.1.0   | 2.22 | 3.32 | 4.04 |   429 |   46.2 |
| txn, compare + put        | gRPC     | 2.0.0   | 1.89 | 3.03 | 3.52 |   495 |   47.6 |
| txn, compare + put        | gRPC     | 2.1.0   | 2.42 | 4.00 | 6.13 |   384 |   47.5 |

## Cold client

A new `Etcd` instance for every call, 128-byte values, no authentication.

| Operation                 | Protocol | Version |  p50 |  p95 |   p99 | ops/s | Memory |
| ------------------------- | -------- | ------- | ---: | ---: | ----: | ----: | -----: |
| put                       | REST     | 2.0.0   | 3.52 | 7.36 | 11.89 |   249 |   49.8 |
| put                       | REST     | 2.1.0   | 2.04 | 3.18 |  8.06 |   438 |   49.4 |
| put                       | gRPC     | 2.0.0   | 2.15 | 3.40 |  4.03 |   442 |   47.6 |
| put                       | gRPC     | 2.1.0   | 2.02 | 3.04 |  3.88 |   459 |   47.5 |
| getKey + getFirstKeyValue | REST     | 2.0.0   | 2.26 | 3.68 |  8.18 |   401 |   49.4 |
| getKey + getFirstKeyValue | REST     | 2.1.0   | 1.33 | 2.05 |  6.58 |   659 |   49.5 |
| getKey + getFirstKeyValue | gRPC     | 2.0.0   | 1.08 | 1.55 |  1.96 |   873 |   48.1 |
| getKey + getFirstKeyValue | gRPC     | 2.1.0   | 0.98 | 1.45 |  2.25 |   975 |   47.8 |
| deleteRange, one key      | REST     | 2.0.0   | 3.11 | 4.18 | 11.10 |   298 |   49.8 |
| deleteRange, one key      | REST     | 2.1.0   | 1.91 | 2.91 |  7.11 |   469 |   49.7 |
| deleteRange, one key      | gRPC     | 2.0.0   | 1.71 | 2.46 |  3.36 |   540 |   47.7 |
| deleteRange, one key      | gRPC     | 2.1.0   | 2.21 | 3.45 |  4.50 |   431 |   47.8 |
| txn, compare + put        | REST     | 2.0.0   | 3.36 | 5.87 | 11.19 |   264 |   49.9 |
| txn, compare + put        | REST     | 2.1.0   | 3.86 | 7.47 | 10.05 |   213 |   49.6 |
| txn, compare + put        | gRPC     | 2.0.0   | 1.94 | 3.31 |  4.54 |   408 |   48.0 |
| txn, compare + put        | gRPC     | 2.1.0   | 2.38 | 3.60 |  4.56 |   399 |   47.9 |

## Value size

Long-lived process, no authentication.

| Operation                 | Protocol |    Size | Version |   p50 |    p95 |    p99 | ops/s | Memory |
| ------------------------- | -------- | ------: | ------- | ----: | -----: | -----: | ----: | -----: |
| put                       | REST     |   128 B | 2.0.0   |  1.81 |   2.93 |   4.31 |   513 |   46.2 |
| put                       | REST     |   128 B | 2.1.0   |  1.86 |   2.79 |   3.27 |   512 |   45.9 |
| put                       | REST     |   8 KiB | 2.0.0   |  3.08 |   4.74 |   7.64 |   289 |   45.9 |
| put                       | REST     |   8 KiB | 2.1.0   |  3.05 |   4.52 |   6.63 |   311 |   46.2 |
| put                       | REST     | 256 KiB | 2.0.0   | 23.51 |  71.44 | 100.94 |  31.4 |   48.1 |
| put                       | REST     | 256 KiB | 2.1.0   | 18.18 | 133.36 | 329.15 |  28.5 |   47.9 |
| put                       | gRPC     |   128 B | 2.0.0   |  1.76 |   2.91 |   3.96 |   527 |   47.4 |
| put                       | gRPC     |   128 B | 2.1.0   |  1.89 |   3.42 |   5.44 |   469 |   48.1 |
| put                       | gRPC     |   8 KiB | 2.0.0   |  2.84 |   3.74 |   6.39 |   337 |   47.6 |
| put                       | gRPC     |   8 KiB | 2.1.0   |  3.29 |   5.21 |   6.95 |   285 |   51.1 |
| put                       | gRPC     | 256 KiB | 2.0.0   |  7.50 |  19.51 | 101.72 |   106 |   47.5 |
| put                       | gRPC     | 256 KiB | 2.1.0   |  7.23 |  15.48 |  26.69 |   111 |   69.3 |
| getKey + getFirstKeyValue | REST     |   128 B | 2.0.0   |  0.99 |   1.41 |   1.71 |   959 |   46.3 |
| getKey + getFirstKeyValue | REST     |   128 B | 2.1.0   |  1.03 |   1.44 |   1.77 |   931 |   46.1 |
| getKey + getFirstKeyValue | REST     |   8 KiB | 2.0.0   |  1.15 |   1.59 |   2.16 |   841 |   46.1 |
| getKey + getFirstKeyValue | REST     |   8 KiB | 2.1.0   |  1.19 |   1.72 |   2.11 |   793 |   45.6 |
| getKey + getFirstKeyValue | REST     | 256 KiB | 2.0.0   |  4.49 |  11.41 |  86.70 |   161 |   47.9 |
| getKey + getFirstKeyValue | REST     | 256 KiB | 2.1.0   |  5.24 |  13.53 |  26.50 |   126 |   48.3 |
| getKey + getFirstKeyValue | gRPC     |   128 B | 2.0.0   |  0.77 |   1.23 |   1.77 |  1211 |   48.1 |
| getKey + getFirstKeyValue | gRPC     |   128 B | 2.1.0   |  0.79 |   1.37 |   1.83 |  1148 |   47.8 |
| getKey + getFirstKeyValue | gRPC     |   8 KiB | 2.0.0   |  0.83 |   1.28 |   1.53 |  1153 |   48.0 |
| getKey + getFirstKeyValue | gRPC     |   8 KiB | 2.1.0   |  0.97 |   1.47 |   1.86 |   965 |   47.8 |
| getKey + getFirstKeyValue | gRPC     | 256 KiB | 2.0.0   |  2.09 |   3.04 |   6.77 |   432 |   46.5 |
| getKey + getFirstKeyValue | gRPC     | 256 KiB | 2.1.0   |  2.58 |   5.29 |  13.70 |   328 |   47.8 |

## Range

128-byte values, long-lived process. "Decode all values" is `getRange` plus decoding every value. On REST that is
`base64_decode` of each value. On gRPC the component has already decoded the values, and the extra step reads them.

| Operation         | Keys | Protocol | Version |   p50 |   p95 |   p99 | ops/s | Memory |
| ----------------- | ---: | -------- | ------- | ----: | ----: | ----: | ----: | -----: |
| getRange          |    1 | REST     | 2.0.0   |  1.17 |  1.60 |  2.19 |   824 |   46.3 |
| getRange          |    1 | REST     | 2.1.0   |  1.19 |  1.97 |  2.55 |   788 |   46.1 |
| getRange          |    1 | gRPC     | 2.0.0   |  0.75 |  1.16 |  1.44 |  1246 |   46.8 |
| getRange          |    1 | gRPC     | 2.1.0   |  0.84 |  1.26 |  1.47 |  1095 |   46.8 |
| getRange          |  100 | REST     | 2.0.0   |  1.77 |  2.38 |  2.68 |   542 |   46.2 |
| getRange          |  100 | REST     | 2.1.0   |  3.10 |  5.37 |  6.75 |   301 |   46.2 |
| getRange          |  100 | gRPC     | 2.0.0   |  1.38 |  1.79 |  2.30 |   708 |   46.3 |
| getRange          |  100 | gRPC     | 2.1.0   |  1.33 |  1.76 |  2.72 |   712 |   47.7 |
| getRange          | 1000 | REST     | 2.0.0   |  8.15 | 14.16 | 18.30 |   108 |   48.3 |
| getRange          | 1000 | REST     | 2.1.0   |  9.87 | 14.27 | 15.92 |  90.3 |   47.8 |
| getRange          | 1000 | gRPC     | 2.0.0   |  4.78 |  5.66 |  8.80 |   205 |   47.7 |
| getRange          | 1000 | gRPC     | 2.1.0   |  4.27 |  4.78 |  5.51 |   231 |   46.7 |
| decode all values |    1 | REST     | 2.0.0   |  1.08 |  1.50 |  1.70 |   895 |   46.1 |
| decode all values |    1 | REST     | 2.1.0   |  1.68 |  6.02 | 19.50 |   385 |   45.6 |
| decode all values |    1 | gRPC     | 2.0.0   |  0.78 |  1.24 |  1.44 |  1201 |   46.6 |
| decode all values |    1 | gRPC     | 2.1.0   |  0.79 |  1.17 |  1.43 |  1194 |   46.7 |
| decode all values |  100 | REST     | 2.0.0   |  1.87 |  2.35 |  3.00 |   516 |   45.9 |
| decode all values |  100 | REST     | 2.1.0   |  2.03 |  2.67 |  3.41 |   461 |   45.9 |
| decode all values |  100 | gRPC     | 2.0.0   |  1.27 |  1.76 |  2.32 |   751 |   47.7 |
| decode all values |  100 | gRPC     | 2.1.0   |  1.29 |  1.62 |  1.94 |   757 |   46.8 |
| decode all values | 1000 | REST     | 2.0.0   |  9.62 | 12.58 | 26.92 |  96.6 |   48.1 |
| decode all values | 1000 | REST     | 2.1.0   | 10.48 | 14.18 | 14.72 |  93.4 |   47.8 |
| decode all values | 1000 | gRPC     | 2.0.0   |  6.50 |  8.00 |  9.17 |   146 |   47.8 |
| decode all values | 1000 | gRPC     | 2.1.0   |  4.51 |  5.49 |  7.83 |   214 |   48.1 |

## Authentication

`getKey` + `getFirstKeyValue`, 128-byte values.

| Mode                 | Protocol | Version |   p50 |   p95 |    p99 | ops/s |
| -------------------- | -------- | ------- | ----: | ----: | -----: | ----: |
| empty user           | REST     | 2.0.0   |  1.24 |  1.74 |   2.13 |   777 |
| empty user           | REST     | 2.1.0   |  1.91 |  2.72 |   4.05 |   506 |
| empty user           | gRPC     | 2.0.0   |  0.69 |  1.05 |   1.34 |  1362 |
| empty user           | gRPC     | 2.1.0   |  0.77 |  1.93 |   3.32 |  1010 |
| cached token         | REST     | 2.0.0   |  1.34 |  1.88 |   2.16 |   726 |
| cached token         | REST     | 2.1.0   |  1.95 |  2.65 |   3.03 |   491 |
| cached token         | gRPC     | 2.0.0   |  0.71 |  1.11 |   1.29 |  1317 |
| cached token         | gRPC     | 2.1.0   |  0.80 |  1.24 |   1.47 |  1188 |
| new Etcd, cold token | REST     | 2.0.0   | 81.27 | 87.76 | 101.68 |  12.1 |
| new Etcd, cold token | REST     | 2.1.0   | 92.67 | 99.17 | 131.69 |  10.7 |
| new Etcd, cold token | gRPC     | 2.0.0   | 78.86 | 83.05 |  95.95 |  12.6 |
| new Etcd, cold token | gRPC     | 2.1.0   | 76.18 | 82.37 |  82.97 |  12.6 |

## Lease

Long-lived process.

| Operation       | Protocol | Version |  p50 |  p95 |  p99 | ops/s |
| --------------- | -------- | ------- | ---: | ---: | ---: | ----: |
| leaseGrant      | REST     | 2.0.0   | 2.44 | 3.41 | 5.26 |   393 |
| leaseGrant      | REST     | 2.1.0   | 2.41 | 3.13 | 3.71 |   404 |
| leaseGrant      | gRPC     | 2.0.0   | 1.79 | 2.51 | 3.15 |   535 |
| leaseGrant      | gRPC     | 2.1.0   | 1.60 | 2.75 | 3.83 |   520 |
| leaseTimeToLive | REST     | 2.0.0   | 1.29 | 1.93 | 2.59 |   756 |
| leaseTimeToLive | REST     | 2.1.0   | 1.14 | 1.56 | 1.71 |   827 |
| leaseTimeToLive | gRPC     | 2.0.0   | 0.85 | 1.27 | 1.42 |  1153 |
| leaseTimeToLive | gRPC     | 2.1.0   | 0.63 | 0.90 | 1.03 |  1536 |
| leaseRevoke     | REST     | 2.0.0   | 3.79 | 4.89 | 6.01 |   260 |
| leaseRevoke     | REST     | 2.1.0   | 3.45 | 4.45 | 4.99 |   285 |
| leaseRevoke     | gRPC     | 2.0.0   | 3.19 | 4.01 | 4.87 |   304 |
| leaseRevoke     | gRPC     | 2.1.0   | 2.69 | 3.30 | 3.56 |   365 |

## Concurrency

A long-lived client in each process, 128-byte values, no authentication. ops/s is the total across all processes.

| Operation                 | Processes | Protocol | Version |   p50 |   p95 |   p99 | ops/s |
| ------------------------- | --------: | -------- | ------- | ----: | ----: | ----: | ----: |
| put                       |         1 | REST     | 2.0.0   |  2.44 |  3.80 |  5.14 |   381 |
| put                       |         1 | REST     | 2.1.0   |  2.59 |  4.02 |  5.69 |   351 |
| put                       |         1 | gRPC     | 2.0.0   |  1.91 |  3.17 |  4.40 |   483 |
| put                       |         1 | gRPC     | 2.1.0   |  1.50 |  2.37 |  3.05 |   625 |
| put                       |         8 | REST     | 2.0.0   |  3.85 |  6.87 |  9.74 |  1927 |
| put                       |         8 | REST     | 2.1.0   |  4.37 |  7.86 | 10.88 |  1666 |
| put                       |         8 | gRPC     | 2.0.0   |  3.25 |  5.25 |  7.08 |  2350 |
| put                       |         8 | gRPC     | 2.1.0   |  3.19 |  8.06 | 15.59 |  2038 |
| put                       |        32 | REST     | 2.0.0   | 11.04 | 18.54 | 25.09 |  2701 |
| put                       |        32 | REST     | 2.1.0   | 12.59 | 31.34 | 55.44 |  2090 |
| put                       |        32 | gRPC     | 2.0.0   | 10.23 | 21.93 | 35.73 |  2704 |
| put                       |        32 | gRPC     | 2.1.0   | 10.13 | 23.19 | 44.02 |  2381 |
| getKey + getFirstKeyValue |         1 | REST     | 2.0.0   |  1.44 |  2.14 |  2.79 |   650 |
| getKey + getFirstKeyValue |         1 | REST     | 2.1.0   |  1.60 |  2.49 |  3.07 |   590 |
| getKey + getFirstKeyValue |         1 | gRPC     | 2.0.0   |  0.82 |  1.37 |  1.97 |  1107 |
| getKey + getFirstKeyValue |         1 | gRPC     | 2.1.0   |  0.67 |  1.07 |  1.48 |  1395 |
| getKey + getFirstKeyValue |         8 | REST     | 2.0.0   |  2.30 |  4.08 |  5.69 |  3206 |
| getKey + getFirstKeyValue |         8 | REST     | 2.1.0   |  2.44 |  4.03 |  5.81 |  3060 |
| getKey + getFirstKeyValue |         8 | gRPC     | 2.0.0   |  1.38 |  2.66 |  4.20 |  5316 |
| getKey + getFirstKeyValue |         8 | gRPC     | 2.1.0   |  1.62 |  3.74 |  4.98 |  4022 |
| getKey + getFirstKeyValue |        32 | REST     | 2.0.0   |  6.98 | 17.41 | 26.23 |  3880 |
| getKey + getFirstKeyValue |        32 | REST     | 2.1.0   |  7.57 | 18.34 | 25.28 |  3624 |
| getKey + getFirstKeyValue |        32 | gRPC     | 2.0.0   |  4.33 |  9.87 | 17.11 |  6315 |
| getKey + getFirstKeyValue |        32 | gRPC     | 2.1.0   |  7.18 | 23.96 | 40.59 |  3403 |

## Wrapper cost

Long-lived process, 128-byte values. Difference = component p95 − raw call p95 on the same protocol.

| Operation                 | Protocol | Version | Component p95 | Raw call p95 | Difference |
| ------------------------- | -------- | ------- | ------------: | -----------: | ---------: |
| put                       | REST     | 2.0.0   |          2.90 |         3.80 |      -0.90 |
| put                       | REST     | 2.1.0   |          4.04 |         4.61 |      -0.57 |
| put                       | gRPC     | 2.0.0   |          3.74 |         2.84 |       0.90 |
| put                       | gRPC     | 2.1.0   |          3.48 |         2.37 |       1.11 |
| getKey + getFirstKeyValue | REST     | 2.0.0   |          1.47 |         2.65 |      -1.18 |
| getKey + getFirstKeyValue | REST     | 2.1.0   |          2.78 |         2.15 |       0.63 |
| getKey + getFirstKeyValue | gRPC     | 2.0.0   |          1.34 |         1.26 |       0.08 |
| getKey + getFirstKeyValue | gRPC     | 2.1.0   |          1.38 |         0.97 |       0.41 |

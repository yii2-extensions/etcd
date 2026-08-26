# Changelog yii2 etcd component

## 2.0.0 (Under development)

- Enh: Minimum `PHP` version raised to `8.5` (@s1lver)
- Enh: Minimum `Yii2` version raised to `2.0.55` (@s1lver)
- Chg: Change namespace from `S1lver\Etcd` to `Yii2\Extensions\Etcd` (@s1lver)
- Chg: Split `EtcdServiceInterface` into domain service interfaces (`EtcdKvServiceInterface`, `EtcdAuthServiceInterface`, `EtcdClusterServiceInterface`, `EtcdMaintenanceServiceInterface`, `EtcdLeaseServiceInterface`) behind a protocol-neutral connection (`EtcdConnectionInterface` with REST and gRPC implementations). The `Etcd` component now exposes `getKv()`, `getAuth()`, `getCluster()`, `getMaintenance()` and `getLease()`; all API methods are accessed through the matching sub-service instead of being delegated on the component (`$etcd->put(...)` becomes `$etcd->getKv()->put(...)`) (@s1lver)
- Enh: Applying Yii2 coding standards (@s1lver)
- Enh #11: Static analysis with PHPStan has been added (@s1lver)
- Enh: Minimum `grpc/grpc` version raised to `1.82` (@s1lver)
- Enh: Minimum `google/protobuf` version raised to `5.36` (@s1lver)
- Enh: Minimum `guzzlehttp/guzzle` version raised to `8.0` (@s1lver)
- Enh: The `etcd` service is now provisioned in CI (`build.yml`) before running the tests (@s1lver)
- Enh: Test `etcd` host is configurable via the `ETCD_HOST` environment variable (`tests/bootstrap.php`) (@s1lver)
- Enh: gRPC `.proto` files updated to the etcd v3.7 API reference (`src/RPC/Proto/`), adding `auth.proto` and `version.proto` and regenerated PHP stubs (@s1lver)
- Enh: Implemented the etcd v3.7 API methods (KV, Auth, Cluster, Maintenance, Lease) for both REST and gRPC protocols: `deleteRange`, `txn`, `compact`, `authEnable`, `authDisable`, `authStatus`, `authenticate`, `userAdd`, `userGet`, `userList`, `userDelete`, `userChangePassword`, `userGrantRole`, `userRevokeRole`, `roleAdd`, `roleGet`, `roleList`, `roleDelete`, `roleGrantPermission`, `roleRevokePermission`, `memberAdd`, `memberRemove`, `memberUpdate`, `memberList`, `memberPromote`, `alarm`, `status`, `defragment`, `hash`, `hashKv`, `moveLeader`, `downgrade`, `leaseGrant`, `leaseRevoke`, `leaseTimeToLive`, `leaseLeases` (@s1lver)
- Enh: Added `EtcdEndpoint` constants, `AlarmAction`, `AlarmType`, `PermissionType` and `DowngradeAction` helper classes (@s1lver)
- Enh: Added REST integration tests for the new API methods (`tests/EtcdHttpTest.php`) (@s1lver)
- Enh: Added gRPC integration tests against a live etcd server (`tests/EtcdGrpcTest.php`) (@s1lver)
- Enh: Added integration tests covering every implemented method for both protocols, including the auth user/role CRUD lifecycle (auth is enabled, exercised and disabled around the scenario) and cluster membership operations (learner member add/update/remove) (`tests/EtcdHttpTest.php`, `tests/EtcdGrpcTest.php`) (@s1lver)
- Fix: REST `moveLeader()` now returns `true` on success; the v3.7 gRPC gateway answers the request with an empty body which has no `header` field (`src/Rest/EtcdRestMaintenance.php`) (@s1lver)
- Fix: gRPC `authenticate()` now returns an empty token (instead of throwing) when authentication is not enabled, matching the REST behavior (`src/Services/EtcdAuthGrpc.php`) (@s1lver)
- Enh: Added typed response objects for the `deleteRange`, `txn`, auth, cluster, maintenance and lease domains (`src/Responses/`); array-typed responses are replaced with `\Yii2\Extensions\Etcd\Responses\*Response` instances exposing public properties (e.g. `$result->deleted`, `$grant->ID`) (@s1lver)
- Enh: Response objects are `readonly` classes — their properties cannot be mutated after construction (`src/Responses/`) (@s1lver)
- Enh: Token caching via `AuthTokenProvider` (`src/Services/AuthTokenProvider.php`): the auth token is fetched once and reused for `ttl` (default 300) seconds by both the REST and gRPC paths instead of being re-authenticated on every request (@s1lver)
- Enh: `AlarmAction`, `AlarmType`, `DowngradeAction` and `PermissionType` are now backed enums; `alarm()`, `downgrade()` and `roleGrantPermission()` parameters are type-hinted with them (breaking change: pass enum cases instead of strings, e.g. `AlarmAction::GET`) (@s1lver)
- Enh: Added `#[Override]` attributes to all interface implementations and `private(set)` visibility to connection configuration, preventing accidental mutation after construction (@s1lver)
- Enh: Tests use `array_find()` and first-class callable syntax (`strval(...)`) and named arguments for long option lists (`txn()`, `roleGrantPermission()`, `memberAdd()`, `userAdd()`) (@s1lver)


## 1.1.0 (2023-05-25)

- Added the ability to switch protocol between gRPC and HTTP (@s1lver)
- Returned `grpc/grpc` and added `google/protobuf` packages (@s1lver)

## 1.0.6 (2023-05-23)

- Added configure options for HTTP client (alxlapin)

## 1.0.5 (2023-04-26)

- Fixed `put` method (@s1lver)

## 1.0.4 (2023-04-20)

- Removed `grpc/grpc` package from pre-implementation dependencies (@s1lver)

## 1.0.3 (2023-04-17)

- Added request handler without authenticate (@s1lver)
- Added `getRange` method (@s1lver)

## 1.0.2 (2023-04-14)

- Fixed `getKey` method (@s1lver)

## 1.0.1 (2023-04-14)

- Added authenticate (@s1lver)

## 1.0.0 (2023-04-14)

- Initial version (@s1lver)

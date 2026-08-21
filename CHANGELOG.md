# Changelog yii2 etcd component

## 2.0.0 (Under development)

- Enh: Minimum `PHP` version raised to `8.5` (@s1lver)
- Enh: Minimum `Yii2` version raised to `2.0.55` (@s1lver)
- Chg: Change namespace from `S1lver\Etcd` to `Yii2\Extensions\Etcd` (@s1lver)
- Enh: Applying Yii2 coding standards (@s1lver)


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

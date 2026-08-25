<?php

declare(strict_types=1);

namespace Yii2\Extensions\Etcd\RPC;

use Etcd\AuthClient;
use Etcd\AuthDisableRequest;
use Etcd\AuthEnableRequest;
use Etcd\AuthRoleAddRequest;
use Etcd\AuthRoleDeleteRequest;
use Etcd\AuthRoleGetRequest;
use Etcd\AuthRoleGrantPermissionRequest;
use Etcd\AuthRoleListRequest;
use Etcd\AuthRoleRevokePermissionRequest;
use Etcd\AuthStatusRequest;
use Etcd\AuthStatusResponse;
use Etcd\AuthUserAddRequest;
use Etcd\AuthUserChangePasswordRequest;
use Etcd\AuthUserDeleteRequest;
use Etcd\AuthUserGetRequest;
use Etcd\AuthUserGrantRoleRequest;
use Etcd\AuthUserListRequest;
use Etcd\AuthUserRevokeRoleRequest;
use Etcd\Permission;
use Etcd\Permission\Type as EtcdPermissionType;
use Etcd\UserAddOptions;
use Google\Protobuf\Internal\RepeatedField;
use Yii2\Extensions\Etcd\EtcdAuthServiceInterface;

final class EtcdGrpcAuth extends AbstractEtcdGrpcService implements EtcdAuthServiceInterface
{
    private ?AuthClient $client = null;

    private function getClient(): AuthClient
    {
        return $this->client ??= new AuthClient($this->connection->host, $this->getConnectionOptions());
    }

    public function authenticate(): string
    {
        return $this->authenticator()->authenticate();
    }

    public function authEnable(): bool
    {
        $this->wait($this->getClient()->AuthEnable(new AuthEnableRequest()));

        return true;
    }

    public function authDisable(): bool
    {
        $this->wait($this->getClient()->AuthDisable(new AuthDisableRequest()));

        return true;
    }

    public function authStatus(): array
    {
        /** @var AuthStatusResponse $response */
        $response = $this->wait($this->getClient()->AuthStatus(new AuthStatusRequest()));

        return [
            'header' => $this->convertHeader($response->getHeader()),
            'enabled' => $response->getEnabled(),
            'authRevision' => $response->getAuthRevision(),
        ];
    }

    public function userAdd(string $name, string $password, bool $noPassword = false): bool
    {
        $request = new AuthUserAddRequest();
        $request->setName($name);
        $request->setPassword($password);

        if ($noPassword) {
            $request->setOptions(new UserAddOptions(['no_password' => true]));
        }

        $this->wait($this->getClient()->UserAdd($request));

        return true;
    }

    public function userGet(string $name): array
    {
        $request = new AuthUserGetRequest();
        $request->setName($name);

        /** @var \Etcd\AuthUserGetResponse $response */
        $response = $this->wait($this->getClient()->UserGet($request));

        return [
            'header' => $this->convertHeader($response->getHeader()),
            'roles' => $this->convertStringList($response->getRoles()),
        ];
    }

    public function userList(): array
    {
        /** @var \Etcd\AuthUserListResponse $response */
        $response = $this->wait($this->getClient()->UserList(new AuthUserListRequest()));

        return [
            'header' => $this->convertHeader($response->getHeader()),
            'users' => $this->convertStringList($response->getUsers()),
        ];
    }

    public function userDelete(string $name): bool
    {
        $request = new AuthUserDeleteRequest();
        $request->setName($name);

        $this->wait($this->getClient()->UserDelete($request));

        return true;
    }

    public function userChangePassword(string $name, string $password): bool
    {
        $request = new AuthUserChangePasswordRequest();
        $request->setName($name);
        $request->setPassword($password);

        $this->wait($this->getClient()->UserChangePassword($request));

        return true;
    }

    public function userGrantRole(string $user, string $role): bool
    {
        $request = new AuthUserGrantRoleRequest();
        $request->setUser($user);
        $request->setRole($role);

        $this->wait($this->getClient()->UserGrantRole($request));

        return true;
    }

    public function userRevokeRole(string $user, string $role): bool
    {
        $request = new AuthUserRevokeRoleRequest();
        $request->setName($user);
        $request->setRole($role);

        $this->wait($this->getClient()->UserRevokeRole($request));

        return true;
    }

    public function roleAdd(string $name): bool
    {
        $request = new AuthRoleAddRequest();
        $request->setName($name);

        $this->wait($this->getClient()->RoleAdd($request));

        return true;
    }

    public function roleGet(string $name): array
    {
        $request = new AuthRoleGetRequest();
        $request->setRole($name);

        /** @var \Etcd\AuthRoleGetResponse $response */
        $response = $this->wait($this->getClient()->RoleGet($request));

        return [
            'header' => $this->convertHeader($response->getHeader()),
            'perm' => $this->convertPermissions($response->getPerm()),
        ];
    }

    public function roleList(): array
    {
        /** @var \Etcd\AuthRoleListResponse $response */
        $response = $this->wait($this->getClient()->RoleList(new AuthRoleListRequest()));

        return [
            'header' => $this->convertHeader($response->getHeader()),
            'roles' => $this->convertStringList($response->getRoles()),
        ];
    }

    public function roleDelete(string $name): bool
    {
        $request = new AuthRoleDeleteRequest();
        $request->setRole($name);

        $this->wait($this->getClient()->RoleDelete($request));

        return true;
    }

    public function roleGrantPermission(string $name, string $permType, string $key, string $rangeEnd = ''): bool
    {
        $perm = new Permission();
        $perm->setPermType((int) EtcdPermissionType::value($permType));
        $perm->setKey($key);

        if ('' !== $rangeEnd) {
            $perm->setRangeEnd($rangeEnd);
        }

        $request = new AuthRoleGrantPermissionRequest();
        $request->setName($name);
        $request->setPerm($perm);

        $this->wait($this->getClient()->RoleGrantPermission($request));

        return true;
    }

    public function roleRevokePermission(string $role, string $key, string $rangeEnd = ''): bool
    {
        $request = new AuthRoleRevokePermissionRequest();
        $request->setRole($role);
        $request->setKey($key);

        if ('' !== $rangeEnd) {
            $request->setRangeEnd($rangeEnd);
        }

        $this->wait($this->getClient()->RoleRevokePermission($request));

        return true;
    }

    /**
     * @param RepeatedField<\Etcd\Permission> $permissions
     * @return array<int, array<string, mixed>>
     */
    private function convertPermissions(RepeatedField $permissions): array
    {
        $result = [];

        /** @var RepeatedField<\Etcd\Permission> $permList */
        $permList = $permissions;

        foreach ($permList as $perm) {
            $result[] = [
                'permType' => EtcdPermissionType::name($perm->getPermType()),
                'key' => $perm->getKey(),
                'range_end' => $perm->getRangeEnd(),
            ];
        }

        return $result;
    }
}

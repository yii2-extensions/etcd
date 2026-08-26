<?php

declare(strict_types=1);

namespace Yii2\Extensions\Etcd;

use Yii2\Extensions\Etcd\Responses\AuthStatusResponse;
use Yii2\Extensions\Etcd\Responses\RoleGetResponse;
use Yii2\Extensions\Etcd\Responses\RoleListResponse;
use Yii2\Extensions\Etcd\Responses\UserGetResponse;
use Yii2\Extensions\Etcd\Responses\UserListResponse;
use Yii2\Extensions\Etcd\Services\EtcdAuthInterface;

interface EtcdAuthServiceInterface extends EtcdAuthInterface
{
    /**
     * Enables authentication.
     */
    public function authEnable(): bool;

    /**
     * Disables authentication.
     */
    public function authDisable(): bool;

    /**
     * Displays authentication status.
     */
    public function authStatus(): AuthStatusResponse;

    /**
     * Adds a new user. User name cannot be empty.
     */
    public function userAdd(string $name, string $password, bool $noPassword = false): bool;

    /**
     * Gets detailed user information.
     */
    public function userGet(string $name): UserGetResponse;

    /**
     * Gets a list of all users.
     */
    public function userList(): UserListResponse;

    /**
     * Deletes a specified user.
     */
    public function userDelete(string $name): bool;

    /**
     * Changes the password of a specified user.
     */
    public function userChangePassword(string $name, string $password): bool;

    /**
     * Grants a role to a specified user.
     */
    public function userGrantRole(string $user, string $role): bool;

    /**
     * Revokes a role of specified user.
     */
    public function userRevokeRole(string $user, string $role): bool;

    /**
     * Adds a new role. Role name cannot be empty.
     */
    public function roleAdd(string $name): bool;

    /**
     * Gets detailed role information.
     */
    public function roleGet(string $name): RoleGetResponse;

    /**
     * Gets lists of all roles.
     */
    public function roleList(): RoleListResponse;

    /**
     * Deletes a specified role.
     */
    public function roleDelete(string $name): bool;

    /**
     * Grants a permission of a specified key or range to a specified role.
     *
     * @param string $permType one of {@see PermissionType} values
     */
    public function roleGrantPermission(string $name, string $permType, string $key, string $rangeEnd = ''): bool;

    /**
     * Revokes a key or range permission of a specified role.
     */
    public function roleRevokePermission(string $role, string $key, string $rangeEnd = ''): bool;
}

<?php

declare(strict_types=1);

namespace Yii2\Extensions\Etcd;

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
     *
     * @return array<string, mixed> response fields: header, enabled, authRevision
     */
    public function authStatus(): array;

    /**
     * Adds a new user. User name cannot be empty.
     */
    public function userAdd(string $name, string $password, bool $noPassword = false): bool;

    /**
     * Gets detailed user information.
     *
     * @return array<string, mixed> response fields: header, roles
     */
    public function userGet(string $name): array;

    /**
     * Gets a list of all users.
     *
     * @return array<string, mixed> response fields: header, users
     */
    public function userList(): array;

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
     *
     * @return array<string, mixed> response fields: header, perm
     */
    public function roleGet(string $name): array;

    /**
     * Gets lists of all roles.
     *
     * @return array<string, mixed> response fields: header, roles
     */
    public function roleList(): array;

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

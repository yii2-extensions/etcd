<?php

declare(strict_types=1);

namespace Yii2\Extensions\Etcd\Rest;

use GuzzleHttp\Exception\GuzzleException;
use JsonException;
use Yii2\Extensions\Etcd\EtcdAuthServiceInterface;
use Yii2\Extensions\Etcd\EtcdEndpoint;
use Yii2\Extensions\Etcd\Responses\AuthStatusResponse;
use Yii2\Extensions\Etcd\Responses\RoleGetResponse;
use Yii2\Extensions\Etcd\Responses\RoleListResponse;
use Yii2\Extensions\Etcd\Responses\UserGetResponse;
use Yii2\Extensions\Etcd\Responses\UserListResponse;

final class EtcdRestAuth extends AbstractEtcdRestService implements EtcdAuthServiceInterface
{
    public function authenticate(): string
    {
        return $this->tokenProvider()->authenticate();
    }

    /**
     * @throws GuzzleException|JsonException
     */
    public function authEnable(): bool
    {
        return isset($this->request(EtcdEndpoint::AUTH_ENABLE, [])['header']);
    }

    /**
     * @throws GuzzleException|JsonException
     */
    public function authDisable(): bool
    {
        return isset($this->request(EtcdEndpoint::AUTH_DISABLE, [])['header']);
    }

    /**
     * @throws GuzzleException|JsonException
     */
    public function authStatus(): AuthStatusResponse
    {
        return new AuthStatusResponse($this->request(EtcdEndpoint::AUTH_STATUS, []));
    }

    /**
     * @throws GuzzleException|JsonException
     */
    public function userAdd(string $name, string $password, bool $noPassword = false): bool
    {
        $body = ['name' => $name, 'password' => $password];

        if ($noPassword) {
            $body['options'] = ['no_password' => true];
        }

        return isset($this->request(EtcdEndpoint::USER_ADD, $body)['header']);
    }

    /**
     * @throws GuzzleException|JsonException
     */
    public function userGet(string $name): UserGetResponse
    {
        return new UserGetResponse($this->request(EtcdEndpoint::USER_GET, ['name' => $name]));
    }

    /**
     * @throws GuzzleException|JsonException
     */
    public function userList(): UserListResponse
    {
        return new UserListResponse($this->request(EtcdEndpoint::USER_LIST, []));
    }

    /**
     * @throws GuzzleException|JsonException
     */
    public function userDelete(string $name): bool
    {
        return isset($this->request(EtcdEndpoint::USER_DELETE, ['name' => $name])['header']);
    }

    /**
     * @throws GuzzleException|JsonException
     */
    public function userChangePassword(string $name, string $password): bool
    {
        return isset(
            $this->request(EtcdEndpoint::USER_CHANGE_PASSWORD, ['name' => $name, 'password' => $password])['header']
        );
    }

    /**
     * @throws GuzzleException|JsonException
     */
    public function userGrantRole(string $user, string $role): bool
    {
        return isset($this->request(EtcdEndpoint::USER_GRANT_ROLE, ['user' => $user, 'role' => $role])['header']);
    }

    /**
     * @throws GuzzleException|JsonException
     */
    public function userRevokeRole(string $user, string $role): bool
    {
        return isset($this->request(EtcdEndpoint::USER_REVOKE_ROLE, ['name' => $user, 'role' => $role])['header']);
    }

    /**
     * @throws GuzzleException|JsonException
     */
    public function roleAdd(string $name): bool
    {
        return isset($this->request(EtcdEndpoint::ROLE_ADD, ['name' => $name])['header']);
    }

    /**
     * @throws GuzzleException|JsonException
     */
    public function roleGet(string $name): RoleGetResponse
    {
        return new RoleGetResponse($this->request(EtcdEndpoint::ROLE_GET, ['role' => $name]));
    }

    /**
     * @throws GuzzleException|JsonException
     */
    public function roleList(): RoleListResponse
    {
        return new RoleListResponse($this->request(EtcdEndpoint::ROLE_LIST, []));
    }

    /**
     * @throws GuzzleException|JsonException
     */
    public function roleDelete(string $name): bool
    {
        return isset($this->request(EtcdEndpoint::ROLE_DELETE, ['role' => $name])['header']);
    }

    /**
     * @throws GuzzleException|JsonException
     */
    public function roleGrantPermission(string $name, string $permType, string $key, string $rangeEnd = ''): bool
    {
        $perm = ['permType' => $permType, 'key' => base64_encode(trim($key))];

        if ('' !== $rangeEnd) {
            $perm['range_end'] = base64_encode(trim($rangeEnd));
        }

        return isset($this->request(EtcdEndpoint::ROLE_GRANT_PERMISSION, ['name' => $name, 'perm' => $perm])['header']);
    }

    /**
     * @throws GuzzleException|JsonException
     */
    public function roleRevokePermission(string $role, string $key, string $rangeEnd = ''): bool
    {
        $body = ['role' => $role, 'key' => base64_encode(trim($key))];

        if ('' !== $rangeEnd) {
            $body['range_end'] = base64_encode(trim($rangeEnd));
        }

        return isset($this->request(EtcdEndpoint::ROLE_REVOKE_PERMISSION, $body)['header']);
    }
}

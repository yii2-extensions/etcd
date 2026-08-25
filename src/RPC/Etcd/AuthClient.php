<?php
// GENERATED CODE -- DO NOT EDIT!

namespace Etcd;

/**
 */
class AuthClient extends \Grpc\BaseStub {

    /**
     * @param string $hostname hostname
     * @param array $opts channel options
     * @param \Grpc\Channel $channel (optional) re-use channel object
     */
    public function __construct($hostname, $opts, $channel = null) {
        parent::__construct($hostname, $opts, $channel);
    }

    /**
     * AuthEnable enables authentication.
     * @param \Etcd\AuthEnableRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall<\Etcd\AuthEnableResponse>
     */
    public function AuthEnable(\Etcd\AuthEnableRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/etcdserverpb.Auth/AuthEnable',
        $argument,
        ['\Etcd\AuthEnableResponse', 'decode'],
        $metadata, $options);
    }

    /**
     * AuthDisable disables authentication.
     * @param \Etcd\AuthDisableRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall<\Etcd\AuthDisableResponse>
     */
    public function AuthDisable(\Etcd\AuthDisableRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/etcdserverpb.Auth/AuthDisable',
        $argument,
        ['\Etcd\AuthDisableResponse', 'decode'],
        $metadata, $options);
    }

    /**
     * AuthStatus displays authentication status.
     * @param \Etcd\AuthStatusRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall<\Etcd\AuthStatusResponse>
     */
    public function AuthStatus(\Etcd\AuthStatusRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/etcdserverpb.Auth/AuthStatus',
        $argument,
        ['\Etcd\AuthStatusResponse', 'decode'],
        $metadata, $options);
    }

    /**
     * Authenticate processes an authenticate request.
     * @param \Etcd\AuthenticateRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall<\Etcd\AuthenticateResponse>
     */
    public function Authenticate(\Etcd\AuthenticateRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/etcdserverpb.Auth/Authenticate',
        $argument,
        ['\Etcd\AuthenticateResponse', 'decode'],
        $metadata, $options);
    }

    /**
     * UserAdd adds a new user. User name cannot be empty.
     * @param \Etcd\AuthUserAddRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall<\Etcd\AuthUserAddResponse>
     */
    public function UserAdd(\Etcd\AuthUserAddRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/etcdserverpb.Auth/UserAdd',
        $argument,
        ['\Etcd\AuthUserAddResponse', 'decode'],
        $metadata, $options);
    }

    /**
     * UserGet gets detailed user information.
     * @param \Etcd\AuthUserGetRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall<\Etcd\AuthUserGetResponse>
     */
    public function UserGet(\Etcd\AuthUserGetRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/etcdserverpb.Auth/UserGet',
        $argument,
        ['\Etcd\AuthUserGetResponse', 'decode'],
        $metadata, $options);
    }

    /**
     * UserList gets a list of all users.
     * @param \Etcd\AuthUserListRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall<\Etcd\AuthUserListResponse>
     */
    public function UserList(\Etcd\AuthUserListRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/etcdserverpb.Auth/UserList',
        $argument,
        ['\Etcd\AuthUserListResponse', 'decode'],
        $metadata, $options);
    }

    /**
     * UserDelete deletes a specified user.
     * @param \Etcd\AuthUserDeleteRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall<\Etcd\AuthUserDeleteResponse>
     */
    public function UserDelete(\Etcd\AuthUserDeleteRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/etcdserverpb.Auth/UserDelete',
        $argument,
        ['\Etcd\AuthUserDeleteResponse', 'decode'],
        $metadata, $options);
    }

    /**
     * UserChangePassword changes the password of a specified user.
     * @param \Etcd\AuthUserChangePasswordRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall<\Etcd\AuthUserChangePasswordResponse>
     */
    public function UserChangePassword(\Etcd\AuthUserChangePasswordRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/etcdserverpb.Auth/UserChangePassword',
        $argument,
        ['\Etcd\AuthUserChangePasswordResponse', 'decode'],
        $metadata, $options);
    }

    /**
     * UserGrantRole grants a role to a specified user.
     * @param \Etcd\AuthUserGrantRoleRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall<\Etcd\AuthUserGrantRoleResponse>
     */
    public function UserGrantRole(\Etcd\AuthUserGrantRoleRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/etcdserverpb.Auth/UserGrantRole',
        $argument,
        ['\Etcd\AuthUserGrantRoleResponse', 'decode'],
        $metadata, $options);
    }

    /**
     * UserRevokeRole revokes a role of specified user.
     * @param \Etcd\AuthUserRevokeRoleRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall<\Etcd\AuthUserRevokeRoleResponse>
     */
    public function UserRevokeRole(\Etcd\AuthUserRevokeRoleRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/etcdserverpb.Auth/UserRevokeRole',
        $argument,
        ['\Etcd\AuthUserRevokeRoleResponse', 'decode'],
        $metadata, $options);
    }

    /**
     * RoleAdd adds a new role. Role name cannot be empty.
     * @param \Etcd\AuthRoleAddRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall<\Etcd\AuthRoleAddResponse>
     */
    public function RoleAdd(\Etcd\AuthRoleAddRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/etcdserverpb.Auth/RoleAdd',
        $argument,
        ['\Etcd\AuthRoleAddResponse', 'decode'],
        $metadata, $options);
    }

    /**
     * RoleGet gets detailed role information.
     * @param \Etcd\AuthRoleGetRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall<\Etcd\AuthRoleGetResponse>
     */
    public function RoleGet(\Etcd\AuthRoleGetRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/etcdserverpb.Auth/RoleGet',
        $argument,
        ['\Etcd\AuthRoleGetResponse', 'decode'],
        $metadata, $options);
    }

    /**
     * RoleList gets lists of all roles.
     * @param \Etcd\AuthRoleListRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall<\Etcd\AuthRoleListResponse>
     */
    public function RoleList(\Etcd\AuthRoleListRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/etcdserverpb.Auth/RoleList',
        $argument,
        ['\Etcd\AuthRoleListResponse', 'decode'],
        $metadata, $options);
    }

    /**
     * RoleDelete deletes a specified role.
     * @param \Etcd\AuthRoleDeleteRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall<\Etcd\AuthRoleDeleteResponse>
     */
    public function RoleDelete(\Etcd\AuthRoleDeleteRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/etcdserverpb.Auth/RoleDelete',
        $argument,
        ['\Etcd\AuthRoleDeleteResponse', 'decode'],
        $metadata, $options);
    }

    /**
     * RoleGrantPermission grants a permission of a specified key or range to a specified role.
     * @param \Etcd\AuthRoleGrantPermissionRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall<\Etcd\AuthRoleGrantPermissionResponse>
     */
    public function RoleGrantPermission(\Etcd\AuthRoleGrantPermissionRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/etcdserverpb.Auth/RoleGrantPermission',
        $argument,
        ['\Etcd\AuthRoleGrantPermissionResponse', 'decode'],
        $metadata, $options);
    }

    /**
     * RoleRevokePermission revokes a key or range permission of a specified role.
     * @param \Etcd\AuthRoleRevokePermissionRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall<\Etcd\AuthRoleRevokePermissionResponse>
     */
    public function RoleRevokePermission(\Etcd\AuthRoleRevokePermissionRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/etcdserverpb.Auth/RoleRevokePermission',
        $argument,
        ['\Etcd\AuthRoleRevokePermissionResponse', 'decode'],
        $metadata, $options);
    }

}

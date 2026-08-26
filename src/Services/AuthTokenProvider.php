<?php

declare(strict_types=1);

namespace Yii2\Extensions\Etcd\Services;

/**
 * Caches the auth token and skips authentication entirely when no credentials are configured.
 *
 * When auth is disabled (or creds are empty) the very first lookup returns an empty token and
 * the result is cached, so no authenticate round-trip happens on subsequent calls.
 */
final class AuthTokenProvider implements EtcdAuthInterface
{
    public const int DEFAULT_TTL = 300;

    private string $token = '';
    private bool $fetched = false;
    private int $expiresAt = 0;

    public function __construct(
        private readonly EtcdAuthInterface $authenticator,
        private readonly string $user,
        private readonly int $ttl = self::DEFAULT_TTL,
    ) {
    }

    public function authenticate(): string
    {
        if ('' === $this->user) {
            return '';
        }

        if ($this->fetched && ('' === $this->token || time() < $this->expiresAt)) {
            return $this->token;
        }

        $this->token = $this->authenticator->authenticate();
        $this->fetched = true;
        $this->expiresAt = time() + $this->ttl;

        return $this->token;
    }
}

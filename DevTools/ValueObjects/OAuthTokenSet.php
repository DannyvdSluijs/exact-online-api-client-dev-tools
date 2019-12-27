<?php

declare(strict_types=1);

namespace DevTools\ValueObjects;

class OAuthTokenSet implements \JsonSerializable
{
    /** @var string */
    private $accessToken;
    /** @var \DateTimeImmutable */
    private $accessTokenValidUntil;
    /** @var string */
    private $refreshToken;

    public function __construct(string $accessToken, \DateTimeImmutable $accessTokenValidUntil, string $refreshToken)
    {
        $this->accessToken = $accessToken;
        $this->accessTokenValidUntil = $accessTokenValidUntil;
        $this->refreshToken = $refreshToken;
    }

    public function getAccessToken(): string
    {
        return $this->accessToken;
    }

    public function getAccessTokenValidUntil(): \DateTimeImmutable
    {
        return $this->accessTokenValidUntil;
    }

    public function getRefreshToken(): string
    {
        return $this->refreshToken;
    }

    public function jsonSerialize()
    {
        return [
            'access_token' => $this->accessToken,
            'refresh_token' => $this->refreshToken,
            'expires_at' => $this->accessTokenValidUntil->format(\DATE_ATOM)
        ];
    }
}

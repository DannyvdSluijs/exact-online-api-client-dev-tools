<?php declare(strict_types=1);

namespace DevTools\ValueObjects;


class OAuthClient implements \JsonSerializable
{
    /** @var string */
    private $id;
    /** @var string */
    private $secret;

    public function __construct(string $id, string $secret)
    {
        $this->id = $id;
        $this->secret = $secret;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getSecret(): string
    {
        return $this->secret;
    }

    public function jsonSerialize(): array
    {
        return [
            'client_id' => $this->id,
            'client_secret' => $this->secret
        ];
    }
}
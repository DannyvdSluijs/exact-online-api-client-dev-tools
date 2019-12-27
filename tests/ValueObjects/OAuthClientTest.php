<?php declare(strict_types=1);

namespace DevTools\Tests\ValueObjects;

use DevTools\Tests\TestCase;
use DevTools\ValueObjects\OAuthClient;

class OAuthClientTest extends TestCase
{
    public function testValueObjectCanHoldProperties(): void
    {
        $client = new OAuthClient(
            $id = $this->faker()->numerify(str_repeat('#', 32)),
            $secret = $this->faker()->password(64, 64)
        );

        self::assertEquals($id, $client->getId());
        self::assertEquals($secret, $client->getSecret());
    }

    public function testValueObjectCanBeSerialized(): void
    {
        $client = new OAuthClient(
            $id = $this->faker()->numerify(str_repeat('#', 32)),
            $secret = $this->faker()->password(64, 64)
        );

        $data = json_encode($client);

        self::assertEquals(json_encode(['client_id' => $id, 'client_secret' => $secret]), $data);
    }
}

<?php

declare(strict_types=1);

namespace Scroom\Api\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpKernel\Client;

/**
 * @author Daniëlle Suurlant <danielle@connectholland.nl>
 */
class RoomControllerTest extends WebTestCase
{
    public function testItCreatesARoom()
    {
        /** @var Client $client */
        $client = self::createClient();

        $client->request('POST', '/room');
    }
}

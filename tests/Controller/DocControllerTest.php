<?php

declare(strict_types=1);

namespace Scroom\Api\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Daniëlle Suurlant <danielle@connectholland.nl>
 */
class DocControllerTest extends WebTestCase
{
    public function testItGetsTheDoc()
    {
        $client = self::createClient();
        $client->request('GET', '/doc');

        $response = $client->getResponse();

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertNotEmpty($response->getContent());
        $this->assertContains('openapi', $response->getContent());
    }
}

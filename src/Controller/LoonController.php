<?php

declare(strict_types=1);

namespace Scroom\Api\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @author Daniëlle Suurlant <danielle@connectholland.nl>
 */
final class LoonController
{
    /**
     * @param string $name
     *
     * @return JsonResponse
     */
    public function enter(string $name): JsonResponse
    {
        return new JsonResponse();
    }

}

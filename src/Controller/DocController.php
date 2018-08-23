<?php

declare(strict_types=1);

namespace Scroom\Api\Controller;

use Assert\Assertion;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Daniëlle Suurlant <danielle@connectholland.nl>
 */
class DocController
{
    /**
     * @var string
     */
    private $doc;

    /**
     * DocController constructor.
     *
     * @param string $doc
     *
     * @throws \Assert\AssertionFailedException
     */
    public function __construct(string $doc)
    {
        Assertion::file($doc);

        $this->doc = $doc;
    }

    public function get()
    {
        $contents = file_get_contents($this->doc);

        return new Response($contents);
    }
}

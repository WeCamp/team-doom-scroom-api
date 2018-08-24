<?php

declare(strict_types=1);

namespace Scroom\Api\Entity;

use Ramsey\Uuid\Uuid;

/**
 * @author Daniëlle Suurlant <danielle@connectholland.nl>
 */
class Room
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * Room constructor.
     *
     * @throws \Exception
     */
    public function __construct()
    {
        $this->id = (string)Uuid::uuid4();
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     *
     * @return self
     */
    public function setName($name): self
    {
        $this->name = $name;

        return $this;
    }
}

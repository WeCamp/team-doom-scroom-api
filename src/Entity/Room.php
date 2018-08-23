<?php

declare(strict_types=1);

namespace Scroom\Api;

/**
 * @author Daniëlle Suurlant <danielle@connectholland.nl>
 */
class Room
{
    private $name;

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     *
     * @return self
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }
}

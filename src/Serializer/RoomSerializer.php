<?php

declare(strict_types=1);

namespace Scroom\Api\Serializer;

use Scroom\Loon;
use Scroom\Room;
use Scroom\Turn;

/**
 * @author Daniëlle Suurlant <danielle@connectholland.nl>
 */
final class RoomSerializer
{
    /**
     * @param Room $room
     *
     * @return array
     */
    public static function serialize(Room $room): array
    {
        return [
            'id' => $room->id(),
            'name' => $room->name(),
            'loons' => static::loons($room),
            'play' => static::play($room),
        ];
    }

    /**
     * @param Room $room
     *
     * @return array
     */
    private static function loons(Room $room): array
    {
        $loons = [];

        /** @var Loon $loon */
        foreach ($room->loons() as $loon) {
            $loons[] = [
                'id' => $loon->id(),
                'pickedCard' => $loon->pickedCard() !== null ? $loon->pickedCard()->toString() : null,
            ];
        }

        return $loons;
    }

    /**
     * @param Room $room
     *
     * @return array
     */
    private static function play(Room $room): array
    {
        if ($room->currentPlay() === null) {
            return null;
        }

        return [
            'id' => $room->currentPlay()->id(),
            'turns' => static::turns($room->currentPlay()->turns()),
            'hasEnded' => $room->currentPlay()->hasEnded(),
        ];
    }

    /**
     * @param array $turns
     *
     * @return array
     */
    private static function turns(array $turns): array
    {
        $serializedTurns = [];

        /** @var Turn $turn */
        foreach ($turns as $turn) {
            $serializedTurns[] = [
                'id' => $turn->id(),
                'hasEnded' => $turn->hasEnded(),
            ];
        }

        return $serializedTurns;
    }
}

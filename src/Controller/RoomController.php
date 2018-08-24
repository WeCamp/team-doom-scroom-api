<?php

declare(strict_types=1);

namespace Scroom\Api\Controller;

use Scroom\Api\Repository\Exception\NonUniqueResultException;
use Scroom\Api\Repository\RoomRepository;
use Scroom\Room;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @author Daniëlle Suurlant <danielle@connectholland.nl>
 */
final class RoomController
{
    /**
     * @var RoomRepository
     */
    private $roomRepository;

    /**
     * RoomController constructor.
     *
     * @param RoomRepository $roomRepository
     */
    public function __construct(RoomRepository $roomRepository)
    {
        $this->roomRepository = $roomRepository;
    }

    /**
     * @param string $name
     *
     * @return JsonResponse
     * @throws \Doctrine\DBAL\DBALException
     */
    public function openUp(string $name): JsonResponse
    {
        if ($this->roomRepository->exists($name) === true) {
            return new JsonResponse(['message' => 'Room already exists'], Response::HTTP_BAD_REQUEST);
        }

        $room = Room::openUp($name);
        $this->roomRepository->save($room);

        return new JsonResponse(['id' => $room->id()], Response::HTTP_CREATED);
    }

    /**
     * @param string $name
     *
     * @return JsonResponse
     * @throws \Doctrine\DBAL\DBALException
     * @throws \ReflectionException
     */
    public function retrieve(string $name): JsonResponse
    {
        try {
            $room = $this->roomRepository->find($name);

            if (!$room instanceof Room) {
                throw new NotFoundHttpException('Room not found.');
            }

            return new JsonResponse(['id' => $room->id(), 'name' => $room->name()]);
        } catch (NonUniqueResultException $e) {
            return new JsonResponse(['error' => 'Unable to retrieve room; name is not unique.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

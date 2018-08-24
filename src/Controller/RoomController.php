<?php

declare(strict_types=1);

namespace Scroom\Api\Controller;

use Assert\Assertion;
use Assert\AssertionFailedException;
use Doctrine\DBAL\DBALException;
use Scroom\Api\Repository\Exception\NonUniqueResultException;
use Scroom\Api\Repository\RoomRepository;
use Scroom\Api\Serializer\RoomSerializer;
use Scroom\Room;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
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
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function openUp(Request $request): JsonResponse
    {
        try {
            Assertion::notEmpty($request->getContent());
            Assertion::contains($request->getContent(), 'name');

            $content = json_decode($request->getContent(), true);

            if ($this->roomRepository->exists($content['name']) === true) {
                return new JsonResponse(['message' => 'Room already exists'], Response::HTTP_BAD_REQUEST);
            }

            $room = Room::openUp($content['name']);
            $this->roomRepository->save($room);

            return new JsonResponse(RoomSerializer::serialize($room), Response::HTTP_CREATED);
        } catch (AssertionFailedException $e) {
            return new JsonResponse(['error' => 'Request content empty or invalid.'], Response::HTTP_BAD_REQUEST);
        } catch (DBALException $e) {
            return new JsonResponse(['error' => 'Unable to open room.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param string  $id
     *
     * @return JsonResponse
     */
    public function poll(string $id): JsonResponse
    {
        try {
            $room = $this->roomRepository->find($id);

            if (!$room instanceof Room) {
                throw new NotFoundHttpException('Room not found.');
            }

            return new JsonResponse(RoomSerializer::serialize($room));
        } catch (NonUniqueResultException|DBALException $e) {
            return new JsonResponse(['error' => 'Unable to retrieve room.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

<?php

declare(strict_types=1);

namespace Scroom\Api\Controller;

use Assert\Assertion;
use Assert\AssertionFailedException;
use Doctrine\DBAL\DBALException;
use InvalidArgumentException;
use Scroom\Api\Repository\Exception\NonUniqueResultException;
use Scroom\Api\Repository\RoomRepository;
use Scroom\Api\Serializer\RoomSerializer;
use Scroom\Card;
use Scroom\Loon;
use Scroom\Room;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
    public function open(Request $request): JsonResponse
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
     * @param string $id
     *
     * @return JsonResponse
     */
    public function poll(string $id): JsonResponse
    {
        try {
            $room = $this->roomRepository->find($id);

            if (!$room instanceof Room) {
                return new JsonResponse(['message' => 'Room not found'], Response::HTTP_NOT_FOUND);
            }

            return new JsonResponse(RoomSerializer::serialize($room));
        } catch (NonUniqueResultException|DBALException $e) {
            return new JsonResponse(['error' => 'Unable to retrieve room.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param string $id
     *
     * @return JsonResponse
     */
    public function enter(string $id): JsonResponse
    {
        try {
            $room = $this->roomRepository->find($id);

            if (!$room instanceof Room) {
                return new JsonResponse(['message' => 'Room not found'], Response::HTTP_NOT_FOUND);
            }

            $loon = Loon::enter($room);
            $this->roomRepository->update($room);

            return new JsonResponse(
                [
                    'id' => $loon->id(),
                    'pickedCard' => $loon->pickedCard(),
                ]
            );
        } catch (NonUniqueResultException|DBALException $e) {
            return new JsonResponse(['error' => 'Unable to retrieve room.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param string  $id
     * @param Request $request
     *
     * @return JsonResponse
     * @throws AssertionFailedException
     */
    public function card(string $id, Request $request): JsonResponse
    {
        try {
            Assertion::notEmpty($request->getContent());

            $room = $this->roomRepository->find($id);

            if (!$room instanceof Room) {
                return new JsonResponse(['message' => 'Room not found'], Response::HTTP_NOT_FOUND);
            }

            Assertion::contains($request->getContent(), 'id');
            Assertion::contains($request->getContent(), 'pickedCard');

            $content = json_decode($request->getContent(), true);

            $found   = array_filter($room->loons(), function (Loon $loon) use ($content) {
                return $loon->id() === $content['id'];
            });

            if (count($found) !== 1) {
                return new JsonResponse(['message' => 'Loon not found'], Response::HTTP_NOT_FOUND);
            }

            /** @var Loon $loon */
            $loon = reset($found);

            $card = Card::new($content['pickedCard']);
            $loon->pick($card);

            $this->roomRepository->update($room);

            return new JsonResponse(
                [
                    'id' => $loon->id(),
                    'pickedCard' => $loon->pickedCard() !== null ? $loon->pickedCard()->toString() : null,
                ]
            );

        } catch (NonUniqueResultException|DBALException|InvalidArgumentException $e) {
            dump($e);
            return new JsonResponse(['error' => 'Unable to pick card.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

}

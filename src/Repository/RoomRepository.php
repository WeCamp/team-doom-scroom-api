<?php

declare(strict_types=1);

namespace Scroom\Api\Repository;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\ResultStatement;
use Doctrine\DBAL\ParameterType;
use Scroom\Api\Repository\Exception\NonUniqueResultException;
use Scroom\Api\Repository\Exception\UnserializeException;
use Scroom\Room;

/**
 * @author Daniëlle Suurlant <danielle@connectholland.nl>
 */
final class RoomRepository
{
    /**
     * @var Connection
     */
    private $connection;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->connection = $doctrine->getConnection();
    }

    /**
     * @param string $name
     *
     * @return bool
     * @throws \Doctrine\DBAL\DBALException
     */
    public function exists(string $name): bool
    {
        $sql = 'SELECT count(id) FROM room WHERE room.name = ?';

        /** @var ResultStatement $result */
        $result = $this->connection->executeQuery(
            $sql, [$name], [ParameterType::STRING]
        );

        return $result->fetchColumn() > 0;
    }

    /**
     * @param Room $room
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function save(Room $room): void
    {
        $sql = 'INSERT INTO room (id, name, data) VALUES (?, ?, ?)';
        $this->connection->executeUpdate(
            $sql, [$room->id(), $room->name(), serialize($room)],
            [ParameterType::STRING, ParameterType::STRING, ParameterType::STRING]
        );
    }

    /**
     * @param Room $room
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function update(Room $room): void
    {
        $sql = 'UPDATE room SET data = ? where id = ?';
        $this->connection->executeUpdate(
            $sql, [serialize($room), $room->id()],
            [ParameterType::STRING, ParameterType::STRING]
        );
    }

    /**
     * @param string $id
     *
     * @return null|Room
     * @throws NonUniqueResultException
     * @throws \Doctrine\DBAL\DBALException
     */
    public function find(string $id): ?Room
    {
        $sql    = 'SELECT id, name, data FROM room WHERE id = ?';
        $result = $this->connection->executeQuery($sql, [$id], [ParameterType::STRING]);

        $rooms = $result->fetchAll();

        if (count($rooms) > 1) {
            throw new NonUniqueResultException('Found more than one room with that name.');
        }

        if (count($rooms) === 0) {
            return null;
        }

        try {
            $room = $this->make($rooms[0]['id'], $rooms[0]['name'], $rooms[0]['data']);
        } catch (UnserializeException $e) {
            return null;
        }

        return $room;
    }

    /**
     * @param string $id
     * @param string $name
     * @param string $data
     *
     * @return Room
     * @throws UnserializeException
     */
    private function make(string $id, string $name, string $data): Room
    {
        $room = unserialize($data);

        if (!$room instanceof Room || $room->id() !== $id || $room->name() !== $name) {
            throw new UnserializeException('Could not unserialize Room object.');
        }

        return $room;
    }
}

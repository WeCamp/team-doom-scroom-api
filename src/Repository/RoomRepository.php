<?php

declare(strict_types=1);

namespace Scroom\Api\Repository;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\ResultStatement;
use Doctrine\DBAL\ParameterType;
use ReflectionClass;
use Scroom\Api\Repository\Exception\NonUniqueResultException;
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
        $sql = 'INSERT INTO room (id, name) VALUES (?, ?)';
        $this->connection->executeUpdate($sql, [$room->id(), $room->name()], [ParameterType::STRING]);
    }

    /**
     * @param string $name
     *
     * @return Room|null
     * @throws NonUniqueResultException
     * @throws \Doctrine\DBAL\DBALException
     * @throws \ReflectionException
     */
    public function find(string $name): ?Room
    {
        $sql    = 'SELECT id, name FROM room WHERE name = ?';
        $result = $this->connection->executeQuery($sql, [$name], [ParameterType::STRING]);

        $rooms = $result->fetchAll();

        if (count($rooms) > 1) {
            throw new NonUniqueResultException('Found more than one room with that name.');
        }

        if (count($rooms) === 0) {
            return null;
        }

        $room = $this->make($rooms[0]['id'], $rooms[0]['name']);

        return $room;
    }

    /**
     * @param string $id
     * @param string $name
     *
     * @return Room
     * @throws \ReflectionException
     */
    private function make(string $id, string $name): Room
    {
        $room = Room::openUp($name);

        $reflect = new ReflectionClass($room);
        $idProp  = $reflect->getProperty('id');
        $idProp->setAccessible(true);
        $idProp->setValue($room, $id);
        $idProp->setAccessible(false);

        return $room;
    }
}

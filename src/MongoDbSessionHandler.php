<?php

namespace ForFit\Session;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Query\Builder;
use MongoDB\BSON\Binary;
use MongoDB\BSON\UTCDateTime;
use MongoDB\Driver\Exception\BulkWriteException;
use SessionHandlerInterface;

class MongoDbSessionHandler implements SessionHandlerInterface
{
    protected ConnectionInterface $connection;
    protected int $minutes;
    protected string $table;

    public function __construct(ConnectionInterface $connection, string $table = 'sessions', int $minutes = 60)
    {
        $this->connection = $connection;
        $this->minutes = (int)$minutes;
        $this->table = $table;
    }

    /**
     * {@inheritdoc}
     */
    public function open($path, $name): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function close(): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function read($id): false|string
    {
        $session = $this->query()->find($id);

        return $session ? $session->payload : '';
    }

    /**
     * {@inheritdoc}
     */
    public function write($id, $data): bool
    {
        try {
            return (bool)$this->query()
                ->where('_id', $id)
                ->update($this->buildPayload($data), ['upsert' => true]);
        } catch (BulkWriteException $exception) {
            // high concurrency exception
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function destroy($id): bool
    {
        $this->query()->where('_id', $id)->delete();

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function gc($max_lifetime): false|int
    {
        // Garbage collection is handled by ttl index in the database
        return true;
    }

    /**
     * Returns the query builder
     *
     */
    protected function query(): Builder
    {
        return $this->connection->table($this->table);
    }

    /**
     * Returns the payload to be stored in the database
     *
     * @param string|null $data
     * @return array
     */
    protected function buildPayload($data): array
    {
        return [
            'payload' => new Binary($data, Binary::TYPE_OLD_BINARY),
            'expires_at' => new UTCDateTime((time() + $this->minutes * 60) * 1000),
            'last_activity' => new UTCDateTime,
        ];
    }
}

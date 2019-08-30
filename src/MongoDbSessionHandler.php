<?php

namespace ForFit\Session;

use MongoDB\BSON\UTCDateTime;
use MongoDB\BSON\Binary;
use MongoDB\Driver\Exception\BulkWriteException;
use SessionHandlerInterface;

class MongoDbSessionHandler implements SessionHandlerInterface
{
    protected $connection;
    protected $minutes;
    protected $table;

    /**
     * @param  \Illuminate\Database\ConnectionInterface  $connection
     * @param string $table
     * @param integer $minutes
     */
    public function __construct($connection, $table = 'sessions', $minutes = 60)
    {
        $this->connection = $connection;
        $this->minutes = (int) $minutes;
        $this->table = $table;
    }

    /**
     * {@inheritdoc}
     */
    public function open($savePath, $sessionName)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function close()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function read($sessionId)
    {
        $session = $this->query()->find($sessionId);

        return $session ? $session['payload'] : '';
    }

    /**
     * {@inheritdoc}
     */
    public function write($sessionId, $data)
    {
        try {
            return (bool) $this->query()
                ->where('_id', $sessionId)
                ->update($this->buildPayload($data), ['upsert' => true]);
        } catch (BulkWriteException $exception) {
            // high concurrency exception
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function destroy($sessionId)
    {
        $this->query()->where('_id', $sessionId)->delete();

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function gc($lifetime)
    {
        // Garbage collection is handled by ttl index in the database
        return true;
    }

    /**
     * Returns the query builder
     *
     * @return \Jenssegers\Mongodb\Query\Builder
     */
    protected function query()
    {
        return $this->connection->table($this->table);
    }

    /**
     * Returns the payload to be stored in the database
     *
     * @param string|null $data
     * @return array
     */
    protected function buildPayload($data)
    {
        return [
            'payload' => new Binary($data, Binary::TYPE_OLD_BINARY),
            'expires_at' => new UTCDateTime((time() + $this->minutes * 60) * 1000),
            'last_activity' => new UTCDateTime,
        ];
    }
}

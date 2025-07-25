<?php

namespace ForFit\Session;

use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Container\Container;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use MongoDB\Driver\Exception\BulkWriteException;
use SessionHandlerInterface;

class MongoDbSessionHandler implements SessionHandlerInterface
{
    protected bool $exists;

    public function __construct(
        protected ConnectionInterface $connection,
        protected string              $table = 'sessions',
        protected int                 $minutes = 60,
        protected ?Container          $container = null
    )
    {
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
        $session = (object)$this->getQuery()->find($id);

        if ($this->expired($session)) {
            $this->exists = true;

            return '';
        }

        if (isset($session->payload)) {
            $this->exists = true;

            return base64_decode($session->payload);
        }

        return '';
    }

    /**
     * {@inheritdoc}
     * @throws BindingResolutionException
     */
    public function write($id, $data): bool
    {
        $payload = $this->getDefaultPayload($data);
        try {
            if (!$this->exists) {
                $this->read($id);
            }

            if ($this->exists) {
                $this->performUpdate($id, $payload);
            } else {
                $this->performInsert($id, $payload);
            }

            return $this->exists = true;
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
        $this->getQuery()->where('_id', $id)->delete();

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
     * Set the application instance used by the handler.
     *
     * @param \Illuminate\Contracts\Foundation\Application $container
     * @return $this
     */
    public function setContainer($container)
    {
        $this->container = $container;

        return $this;
    }

    /**
     * Set the existence state for the session.
     *
     * @param bool $value
     * @return $this
     */
    public function setExists($value)
    {
        $this->exists = $value;

        return $this;
    }

    /**
     * Perform an insert operation on the session ID.
     *
     * @param string $sessionId
     * @param array<string, mixed> $payload
     * @return bool|null
     */
    protected function performInsert(string $sessionId, array $payload): ?bool
    {
        try {
            return $this->getQuery()->insert(Arr::set($payload, '_id', $sessionId));
        } catch (QueryException) {
            $this->performUpdate($sessionId, $payload);
        }
    }

    /**
     * Perform an update operation on the session ID.
     *
     * @param string $sessionId
     * @param array<string, mixed> $payload
     * @return int
     */
    protected function performUpdate($sessionId, $payload): int
    {
        return $this->getQuery()->where('_id', $sessionId)->update($payload, ['upsert' => true]);
    }

    /**
     * Get the default payload for the session.
     *
     * @throws BindingResolutionException
     */
    protected function getDefaultPayload(string $data): array
    {
        $payload = [
            'payload' => base64_encode($data),
            'last_activity' => Carbon::now()->getTimestamp(),
        ];

        if (!$this->container) {
            return $payload;
        }

        return tap($payload, function (&$payload) {
            $this->addUserInformation($payload)
                ->addRequestInformation($payload);
        });
    }

    /**
     * Add the user information to the session payload.
     *
     * @return $this
     * @throws BindingResolutionException
     */
    protected function addUserInformation(array &$payload): static
    {
        if ($this->container->bound(Guard::class)) {
            $payload['user_id'] = $this->userId();
        }

        return $this;
    }

    /**
     * Get the currently authenticated user's ID.
     *
     * @throws BindingResolutionException
     */
    protected function userId()
    {
        return $this->container->make(Guard::class)->id();
    }

    /**
     * Add the request information to the session payload.
     *
     */
    protected function addRequestInformation(array &$payload): static
    {
        if ($this->container->bound('request')) {
            $payload = array_merge($payload, [
                'ip_address' => $this->ipAddress(),
                'user_agent' => $this->userAgent(),
            ]);
        }

        return $this;
    }

    /**
     * Get the IP address for the current request.
     *
     * @return string|null
     * @throws BindingResolutionException
     */
    protected function ipAddress(): ?string
    {
        return $this->container->make('request')->ip();
    }

    /**
     * Get the user agent for the current request.
     *
     * @return string
     * @throws BindingResolutionException
     */
    protected function userAgent(): string
    {
        return substr((string)$this->container->make('request')->header('User-Agent'), 0, 500);
    }

    /**
     * Returns the query builder
     *
     */
    protected function getQuery(): Builder
    {
        return $this->connection->table($this->table);
    }

    /**
     * Determine if the session is expired.
     *
     * @param \stdClass $session
     * @return bool
     */
    protected function expired($session): bool
    {
        return isset($session->last_activity) &&
            $session->last_activity < Carbon::now()->subMinutes($this->minutes)->getTimestamp();
    }
}

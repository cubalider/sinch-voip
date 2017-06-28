<?php

namespace Yosmy\Voip\Sinch;

use MongoDB\BSON\Persistable;

class Call implements Persistable, \JsonSerializable
{
    const STATUS_STARTED   = 'started';
    const STATUS_COMPLETED = 'completed';

    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $status;

    /**
     * @var array
     */
    private $events;

    /**
     * @param string     $id
     * @param string     $status
     * @param array|null $events
     */
    public function __construct(
        string $id,
        string $status,
        array $events = null
    ) {
        $this->id = $id;
        $this->status = $status;
        $this->events = $events ?: [];
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @return array
     */
    public function getEvents(): array
    {
        return $this->events;
    }

    /**
     * {@inheritdoc}
     */
    public function bsonSerialize()
    {
        return [
            '_id' => $this->id,
            'status' => $this->status,
            'events' => $this->events,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function bsonUnserialize(array $data)
    {
        $this->id = $data['_id'];
        $this->status = $data['status'];
        $this->events = $data['events'];
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'events' => $this->events,
        ];
    }
}

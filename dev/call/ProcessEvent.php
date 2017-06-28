<?php

namespace Yosmy\Voip\Sinch\Dev;

use Yosmy\Voip\Sinch;

/**
 * @di\service()
 */
class ProcessEvent
{
    /**
     * @var Sinch\ProcessEvent
     */
    private $processEvent;

    /**
     * @param Sinch\ProcessEvent $processEvent
     */
    public function __construct(Sinch\ProcessEvent $processEvent)
    {
        $this->processEvent = $processEvent;
    }

    /**
     * @param string $id
     * @param string $from
     * @param string $connection
     *
     * @return array
     */
    public function processICE(
        string $id,
        string $from,
        string $connection
    ) {
        return $this->processEvent->process([
            'event' => 'ice',
            'callid' => $id,
            'cli' => $from,
            'to' => [
                'endpoint' => $connection
            ]
        ]);
    }

    /**
     * @param string $id
     *
     * @return array
     */
    public function processACE(
        string $id
    ) {
        return $this->processEvent->process([
            'event' => 'ace',
            'callid' => $id,
        ]);
    }

    /**
     * @param string $id
     * @param int $duration
     * @param int $end
     * @param float $amount
     *
     * @return array
     */
    public function processDICE(
        string $id,
        int $duration,
        int $end,
        float $amount
    ) {
        return $this->processEvent->process([
            'event' => 'dice',
            'callid' => $id,
            'duration' => (string) $duration,
            'timestamp' => (string) $end,
            'debit' => [
                'amount' => (string) $amount,
                'currencyId' => 'usd'
            ]
        ]);
    }
}
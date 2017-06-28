<?php

namespace Yosmy\Voip\Sinch;

use Yosmy\Phone\InvalidNumberException;
use Yosmy\Voip;
use MongoDB\Driver\Exception\BulkWriteException;

/**
 * @di\service()
 */
class ProcessEvent
{
    /**
     * @var Call\SelectCollection
     */
    private $selectCallCollection;

    /**
     * @var Voip\StartCall
     */
    private $startCall;

    /**
     * @var Voip\CompleteCall
     */
    private $completeCall;

    /**
     * @var TranslateConnectResponse
     */
    private $translateConnectResponse;

    /**
     * @var TranslateHangupResponse
     */
    private $translateHangupResponse;

    /**
     * @var TranslateContinueResponse
     */
    private $translateContinueResponse;

    /**
     * @param Call\SelectCollection     $selectCallCollection
     * @param Voip\StartCall            $startCall
     * @param Voip\CompleteCall         $completeCall
     * @param TranslateConnectResponse  $translateConnectResponse
     * @param TranslateHangupResponse   $translateHangupResponse
     * @param TranslateContinueResponse $translateContinueResponse
     */
    public function __construct(
        Call\SelectCollection $selectCallCollection,
        Voip\StartCall $startCall,
        Voip\CompleteCall $completeCall,
        TranslateConnectResponse $translateConnectResponse,
        TranslateHangupResponse $translateHangupResponse,
        TranslateContinueResponse $translateContinueResponse
    )
    {
        $this->selectCallCollection = $selectCallCollection;
        $this->startCall = $startCall;
        $this->completeCall = $completeCall;
        $this->translateConnectResponse = $translateConnectResponse;
        $this->translateHangupResponse = $translateHangupResponse;
        $this->translateContinueResponse = $translateContinueResponse;
    }

    /**
     * @param array $payload
     *
     * @return array
     *
     * @throws \LogicException
     */
    public function process($payload)
    {
        /* Insert call with no events */

        try {
            $this->selectCallCollection->select()->insertOne(new Call(
                $payload['callid'],
                Call::STATUS_STARTED
            ));
        } catch (BulkWriteException $e) {
            if ($e->getCode() == 'E11000') {
                // Ignore it if it was already added
            } else {
                throw $e;
            }
        }

        /* Log event */

        $this->selectCallCollection->select()->updateOne(
            ['_id' => $payload['callid']],
            ['$push' => [
                'events' => $payload
            ]]
        );

        /* Process event */

        switch ($payload['event']) {
            case 'ice':
                $response = $this->processICE($payload);

                break;
            case 'ace':
                $response = $this->processACE();

                break;
            case 'dice':
                $response = $this->processDICE($payload);

                break;
            default:
                throw new \LogicException();
        }

        /* Log response */

        $this->selectCallCollection->select()->updateOne(
            ['_id' => $payload['callid']],
            ['$push' => [
                'events' => $response
            ]]
        );

        return $response;
    }

    /**
     * @param array $payload
     *
     * @return array
     *
     * @throws \LogicException
     */
    private function processICE($payload)
    {
        try {
            $response = $this->startCall->start(
                'sinch',
                $payload['callid'],
                $payload['cli'],
                $payload['to']['endpoint']
            );
        } catch (InvalidNumberException $e) {
            throw new \LogicException();
        }

        if ($response instanceof Voip\ConnectResponse) {
            try {
                $response = $this->translateConnectResponse->translate(
                    $response,
                    $payload['to']['endpoint'] // With high call volume, provider will allow to use $payload['cli']
                );
            } catch (InvalidNumberException $e) {
                throw new \LogicException();
            }
        } elseif ($response instanceof Voip\HangupResponse) {
            $response = $this->translateHangupResponse->translate($response);
        } else {
            throw new \LogicException();
        }

        return $response;
    }

    /**
     * @return array
     */
    private function processACE()
    {
        $response = $this->translateContinueResponse->translate();

        return $response;
    }

    /**
     * @param array $payload
     *
     * @return array
     *
     * @throws \LogicException
     */
    private function processDICE($payload)
    {
        if (!isset($payload['duration'])) {
            $payload['duration'] = 0;
        }

        try {
            $this->completeCall->complete(
                'sinch',
                $payload['callid'],
                strtotime($payload['timestamp']) - $payload['duration'],
                $payload['duration'],
                $payload['debit']['amount'],
                $payload['debit']['currencyId']
            );
        } catch (Voip\RunningCall\NonExistentException $e) {
            throw new \LogicException();
        }

        $response = $this->translateHangupResponse->translate(new Voip\HangupResponse());

        return $response;
    }
}
<?php

namespace Yosmy\Voip\Sinch;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Yosmy\Voip;

/**
 * @di\service({
 *     tags: ['yosmy.voip.import_connections']
 * })
 */
class ImportConnections implements Voip\ImportConnections
{
    /**
     * @var string
     */
    private $appKey;

    /**
     * @var string
     */
    private $appSecret;

    /**
     * @var Voip\PickProvider
     */
    private $pickProvider;

    /**
     * @var Voip\Connection\SelectCollection
     */
    private $selectConnectionCollection;

    /**
     * @var Voip\AddConnection
     */
    private $addConnection;

    /**
     * @param string                           $appKey
     * @param string                           $appSecret
     * @param Voip\PickProvider                $pickProvider
     * @param Voip\Connection\SelectCollection $selectConnectionCollection
     * @param Voip\AddConnection               $addConnection
     *
     * @di\arguments({
     *     appKey:    "%sinch_app_key%",
     *     appSecret: "%sinch_app_secret%"
     * })
     */
    public function __construct(
        $appKey,
        $appSecret,
        Voip\PickProvider $pickProvider,
        Voip\Connection\SelectCollection $selectConnectionCollection,
        Voip\AddConnection $addConnection
    )
    {
        $this->appKey = $appKey;
        $this->appSecret = $appSecret;
        $this->pickProvider = $pickProvider;
        $this->selectConnectionCollection = $selectConnectionCollection;
        $this->addConnection = $addConnection;
    }

    /**
     * @throws \LogicException
     */
    public function import()
    {
        try {
            $provider = $this->pickProvider->pick('sinch');
        } catch (Voip\Provider\NonexistentException $e) {
            throw new \LogicException('Sinch provider not found.');
        }

        $client = new Client();
        $headers = [
            'Authorization' => sprintf(
                'basic %s',
                base64_encode(sprintf(
                    'application\%s:%s',
                    $this->appKey,
                    $this->appSecret
                ))
            )
        ];

        try {
            $response = $client->request(
                'GET',
                'https://callingapi.sinch.com/v1/configuration/numbers/',
                [
                    'headers' => $headers
                ]
            );
        } catch (GuzzleException $e) {
            throw new \LogicException();
        }

        $response = json_decode((string) $response->getBody(), true);

        $this->selectConnectionCollection->select()->drop();

        $connections = [];
        foreach ($response['numbers'] as $connection) {
            try {
                $this->addConnection->add(
                    $provider,
                    $connection['number']
                );
            } catch (Voip\Connection\ExistentException $e) {
                throw new \LogicException();
            }
        }

        return $connections;
    }
}
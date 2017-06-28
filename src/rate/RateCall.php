<?php

namespace Yosmy\Voip\Sinch;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Yosmy\Phone;
use Yosmy\Voip;

/**
 * @di\service({
 *     tags:[{
 *         key: 'sinch',
 *         name: 'yosmy.voip.rate_call'
 *     }]
 * })
 */
class RateCall implements Voip\RateCall
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
     * @param string $appKey
     * @param string $appSecret
     *
     * @di\arguments({
     *     appKey:    "%sinch_app_key%",
     *     appSecret: "%sinch_app_secret%"
     * })
     */
    public function __construct(
        $appKey,
        $appSecret
    ) {
        $this->appKey = $appKey;
        $this->appSecret = $appSecret;
    }

    /**
     * {@inheritdoc}
     */
    public function rate(
        Voip\Connection $connection,
        Phone $destination
    ) {
        try {
            $response = (new Client)->request(
                'GET',
                sprintf('https://callingapi.sinch.com/v1/calling/query/number/%s', $destination->getNumber()),
                [
                    'headers' => [
                        'Authorization' => sprintf(
                            'basic %s',
                            base64_encode(sprintf(
                                'application\%s:%s',
                                $this->appKey,
                                $this->appSecret
                            ))
                        )
                    ]
                ]
            );
        } catch (GuzzleException $e) {
            throw new \LogicException();
        }

        $response = json_decode((string) $response->getBody(), true);

        $rate = (float) $response['number']['rate']['amount'];

        // Plus inbound call
        $rate += 0.004;

        return new Voip\HotRate($rate);
    }
}
<?php

namespace Yosmy\Voip\Sinch;

use GuzzleHttp\Exception\GuzzleException;
use Yosmy\Voip\CompleteCall;
use GuzzleHttp\Client;
use Yosmy\Voip;

/**
 * @di\service({
 *     tags:[{
 *         key: 'sinch',
 *         name: 'yosmy.voip.recheck_call'
 *     }]
 * })
 */
class RecheckCall implements Voip\RecheckCall
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
     * @var CompleteCall
     */
    private $completeCall;

    /**
     * @di\arguments({
     *     appKey:    "%sinch_app_key%",
     *     appSecret: "%sinch_app_secret%"
     * })
     *
     * @param string       $appKey
     * @param string       $appSecret
     * @param CompleteCall $completeCall
     */
    public function __construct(
        $appKey,
        $appSecret,
        CompleteCall $completeCall
    )
    {
        $this->appKey = $appKey;
        $this->appSecret = $appSecret;
        $this->completeCall = $completeCall;
    }

    /**
     * {@inheritdoc}
     */
    public function recheck($id)
    {
        try {
            $response = (new Client())->request(
                'GET',
                sprintf('https://callingapi.sinch.com/v1/calls/id/%s', $id),
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
            if ($e->getCode() === 404) {
                return;
            }

            throw new \LogicException();
        }

        $response = json_decode((string) $response->getBody(), true);

        if ($response['debit']['amount'] === null) {
            $response['debit']['amount'] = '0';
            $response['debit']['currencyId'] = '';
        }

        $this->completeCall->complete(
            'sinch',
            $id,
            strtotime($response['timestamp']) - $response['duration'],
            $response['duration'],
            $response['debit']['amount'],
            $response['debit']['currencyId']
        );
    }
}
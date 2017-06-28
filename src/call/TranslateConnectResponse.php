<?php

namespace Yosmy\Voip\Sinch;

use Yosmy\Phone;
use Yosmy\Voip\ConnectResponse;

/**
 * @di\service()
 */
class TranslateConnectResponse
{
    /**
     * @var Phone\NormalizeNumber
     */
    private $normalizeNumber;

    /**
     * @param Phone\NormalizeNumber $normalizeNumber
     */
    public function __construct(Phone\NormalizeNumber $normalizeNumber)
    {
        $this->normalizeNumber = $normalizeNumber;
    }

    /**
     * @param ConnectResponse $response
     * @param string          $origin
     *
     * @return array
     *
     * @throws Phone\InvalidNumberException
     */
    public function translate(ConnectResponse $response, $origin)
    {
        try {
            $origin = $this->normalizeNumber->normalize($origin);
        } catch (Phone\InvalidNumberException $e) {
            throw $e;
        }

//        return [
//            'action' => [
//                'name' => 'ConnectSIP',
//                'destination' => [
//                    'endpoint' => sprintf(
//                        '%s@wap.thinq.com?thinQid=13438&thinQtoken=a28abe70c7f216d9ed21abf22eaf51d399d82132',
//                        '18009359935'
//                    )
//                ],
//            ]
//        ];

        return [
            'action' => [
                'name' => 'ConnectPSTN',
                'number' => $response->getDestination(),
                'maxDuration' => $response->getDuration(),
                'cli' => $origin->getNumber()
            ]
        ];
    }
}
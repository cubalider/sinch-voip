<?php

namespace Yosmy\Voip\Sinch;

use Yosmy\Voip\HangupResponse;

/**
 * @di\service()
 */
class TranslateHangupResponse
{
    /**
     * @param HangupResponse $response
     *
     * @return array
     */
    public function translate(HangupResponse $response)
    {
        $translation = [];

        if (!is_null($response->getMessage())) {
            $translation['instructions'] = [
                [
                    'name' => 'Say',
                    'text' => $response->getMessage(),
                    'locale' => 'es-MX'
                ]
            ];
        }

        $translation['action'] = [
            'name' => 'Hangup'
        ];

        return $translation;
    }
}
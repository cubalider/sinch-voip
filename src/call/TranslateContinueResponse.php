<?php

namespace Yosmy\Voip\Sinch;

/**
 * @di\service()
 */
class TranslateContinueResponse
{
    /**
     * @return array
     */
    public function translate()
    {
        return [
            'action' => [
                'name' => 'Continue'
            ]
        ];
    }
}
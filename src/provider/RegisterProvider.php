<?php

namespace Yosmy\Voip\Sinch;

use Yosmy\Voip;

/**
 * @di\service({
 *     tags: ['yosmy.voip.register_provider']
 * })
 */
class RegisterProvider implements Voip\RegisterProvider
{
    /**
     * {@inheritdoc
     */
    public function register()
    {
        return new Voip\Provider('sinch', 'Sinch');
    }
}
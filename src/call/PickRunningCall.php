<?php

namespace Yosmy\Voip\Sinch;

//use Yosmy\Voip;

class PickRunningCall
{
    /**
     * @var Call\SelectCollection
     */
    private $selectCallCollection;

    /**
     * @param Call\SelectCollection $selectCallCollection
     */
    public function __construct(Call\SelectCollection $selectCallCollection)
    {
        $this->selectCallCollection = $selectCallCollection;
    }

    /**
     * {@inheritdoc}
     */
    public function pick($number)
    {
//        $calls = iterator_to_array($this->selectCallCollection->select()->find([
//            'events.0.event' => 'ice',
//            'events.0.cli' => $number,
//            'events.1.action.name' => 'ConnectPSTN',
//            'events.2' => ['$exists' => false],
//        ]));
//
//        if (!$calls) {
//            throw new Voip\NonExistentCallException();
//        }
//
//        if (count($calls) > 1) {
//            throw new \Exception();
//        }
//
//        return $calls[0];
//
//        // Hay llamadas que nunca recibieron en DICE, por tanto estan son
//        // running calls. No se deberia borrar nada de estos logs. Por tanto
//        // este metodo no serviria para mostrar las verdaderas running calls
    }
}
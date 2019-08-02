<?php

namespace App\Action;

use App\Entry\EntryInterface;
use App\Provider\Provider;

class SoundAction extends ActionAbstract
{

    public function run(EntryInterface $object, Provider $provider)
    {
        //  Say it
        shell_exec("spd-say ". $this->config['text']);
    }
}

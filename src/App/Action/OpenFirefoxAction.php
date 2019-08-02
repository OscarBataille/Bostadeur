<?php
declare(strict_types=1);

namespace App\Action;

use App\Entry\EntryInterface;
use App\Provider\Provider;

class OpenFirefoxAction extends ActionAbstract
{

    public function run(EntryInterface $object, Provider $provider)
    {
        // Open firefox
        shell_exec($this->config['binary'] . " " . $object->getUrl());
    }
}

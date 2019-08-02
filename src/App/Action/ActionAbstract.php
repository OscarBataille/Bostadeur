<?php

namespace App\Action;

use App\Entry\EntryInterface;
use App\Provider\Provider;

abstract class ActionAbstract
{
    protected $config = [];

    public function __construct(array $config)
    {
        $this->config = $config;
    }
    
    abstract public function run(EntryInterface $object, Provider $provider);

    public function getName()
    {
        $reflection = new \ReflectionClass($this);

        return preg_replace('~Action$~', '', $reflection->getShortName());

    }

}

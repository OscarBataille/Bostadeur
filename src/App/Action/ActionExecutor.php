<?php

namespace App\Action;

use App\Entry\EntryInterface;
use App\Provider\Provider;

/**
 * Execute all the actions registered
 */
class ActionExecutor
{

    private $actions = [];

    /**
     * @param array $actions [ActionAbstract]
     */
    public function __construct(array $actions)
    {
        $this->actions = $actions;
    }

    public function run(EntryInterface $object, Provider $provider)
    {
        foreach ($this->actions as $action) {
            $action->run($object, $provider);
        }
    }
}

<?php
declare(strict_types=1);

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

    /**
     * The action.
     * @param  EntryInterface $object   The object
     * @param  Provider       $provider The provider
     */
    abstract public function run(EntryInterface $object, Provider $provider);

    public function getName()
    {
        $reflection = new \ReflectionClass($this);

        return preg_replace('~Action$~', '', $reflection->getShortName());
    }
}

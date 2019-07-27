<?php
 
namespace App\Provider;

abstract class Provider {
        
    abstract public function getAvailableEntries(): ProviderResult;

    public function getName(){
        return static::class;
    }
}
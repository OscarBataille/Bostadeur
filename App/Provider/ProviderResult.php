<?php

namespace App\Provider;



class ProviderResult
{

    /**
     * Request status
     * @var intl
     */
    public $status;


    /**
     * Count of results
     * @var integer
     */
    public $count = 0;

    /**
     * Array of EntryInterface
     * @var array
     */
    public $value;
}
<?php

namespace App;

class PublishEntry
{

    protected $data;

    public function __construct(array $data)
    {

        $this->data = $data;
    }

    public function getId(): int
    {
        return $this->data['PublishEntryId'];
    }

    public function getDetails(): string
    {
        $details = $this->data['LeaseOutCase']['Details'];

        return $details['ObjectDescription'];
    }

    public function getAddress(): string
    {
        $address = $this->data['LeaseOutCase']['Address'];

        return $address['PropertyId'] . ' ' . $address['StreetAddress'] . ' ' . $address['PostalAddress'];
    }

    public function getCost(){
        return $this->data['LeaseOutCase']['Rent'];
    }
}

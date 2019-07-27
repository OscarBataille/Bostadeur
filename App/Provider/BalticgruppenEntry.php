<?php

namespace App\Provider;

class BalticgruppenEntry implements EntryInterface
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

    public function getAddress(): string
    {
        $address = $this->data['LeaseOutCase']['Address'];

        return $address['PropertyId'] . ' ' . $address['StreetAddress'] . ' ' . $address['PostalAddress'];
    }

    public function getCost(): int
    {
        return (int) $this->data['LeaseOutCase']['Rent'];
    }

    public function getUrl(): string
    {
        return 'https://u4pp.u4a.se/FN667500P/tenant/dashboard';
    }
}

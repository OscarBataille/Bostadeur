<?php

namespace App\Provider;

class BalticgruppenEntry implements EntryInterface
{

    protected $data;

    protected $domain;

    public function __construct(array $data, string $domain)
    {

        $this->data = $data;

        $this->domain = $domain;
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
        return $this->domain . 'tenant/dashboard';
    }
}

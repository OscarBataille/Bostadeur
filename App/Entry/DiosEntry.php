<?php

namespace App\Entry;

class DiosEntry implements EntryInterface
{

    protected $data;

    public function __construct(array $data)
    {

        $this->data = $data;
    }

    public function getId(): int
    {
        return $this->data['id'];
    }

    public function getAddress(): string
    {
        return $this->data['name'];
    }

    public function getCost(): int
    {
        return (int) $this->data['rent'];
    }

    public function getUrl(): string
    {
        return 'https://www.dios.se' . $this->data['url'];
    }
}

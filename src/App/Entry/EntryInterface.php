<?php

namespace App\Entry;

interface EntryInterface
{

    public function getId(): int;

    public function getAddress(): string;

    public function getCost(): int;

    public function getUrl(): string;

}

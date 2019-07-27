<?php

namespace App\Provider;

interface EntryInterface
{



    public function getId(): int;

    public function getAddress(): string;

    public function getCost(): int;
}

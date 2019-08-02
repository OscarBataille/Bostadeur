<?php
declare(strict_types=1);

namespace App\Entry;

interface EntryInterface
{

    public function getId(): int;

    public function getAddress(): string;

    public function getCost(): int;

    public function getUrl(): string;
}
